"""
routes/auth_routes.py – Register, Login, Logout, Google OAuth, Forgot/Reset Password
"""
import secrets
import hashlib
from datetime import datetime, timedelta
from fastapi import APIRouter, HTTPException, Request
from fastapi.responses import RedirectResponse
from ..models import RegisterRequest, LoginRequest, TokenResponse, ForgotPasswordRequest, ResetPasswordRequest
from ..db import query_one, query_all, execute
from ..auth import hash_password, verify_password, create_token
from ..config import get_settings

router   = APIRouter(prefix="/auth", tags=["Auth"])
settings = get_settings()


@router.post("/register", response_model=TokenResponse)
def register(req: RegisterRequest):
    role = query_one("SELECT id, name FROM roles WHERE name = %s", (req.role or "customer",))
    if not role:
        raise HTTPException(400, "Invalid role")

    existing = query_one(
        "SELECT id FROM users WHERE email = %s OR username = %s",
        (req.email, req.username),
    )
    if existing:
        raise HTTPException(409, "Email or username already exists")

    uid = execute(
        """INSERT INTO users (role_id, first_name, last_name, username, email, password_hash, auth_provider)
           VALUES (%s, %s, %s, %s, %s, %s, 'local')""",
        (role["id"], req.first_name, req.last_name,
         req.username, req.email, hash_password(req.password)),
    )
    token = create_token(uid, role["name"])
    return {
        "success": True, "token": token,
        "user": {"id": uid, "username": req.username, "email": req.email,
                 "first_name": req.first_name, "last_name": req.last_name, "role": role["name"]},
    }


@router.post("/login", response_model=TokenResponse)
def login(req: LoginRequest):
    user = query_one(
        """SELECT u.id, u.first_name, u.last_name, u.username, u.email,
                  u.password_hash, r.name AS role, u.auth_provider
           FROM users u JOIN roles r ON u.role_id = r.id
           WHERE u.email = %s AND u.is_active = 1 LIMIT 1""",
        (req.email,),
    )
    if not user:
        raise HTTPException(401, "Invalid email or password")
    if user["auth_provider"] == "google":
        raise HTTPException(401, "This account uses Google sign-in. Please use the Google button.")
    if not verify_password(req.password, user["password_hash"] or ""):
        raise HTTPException(401, "Invalid email or password")

    token = create_token(user["id"], user["role"])
    return {
        "success": True, "token": token,
        "user": {"id": user["id"], "username": user["username"], "email": user["email"],
                 "first_name": user["first_name"], "last_name": user["last_name"], "role": user["role"]},
    }


@router.post("/logout")
def logout():
    return {"success": True, "message": "Logged out successfully"}


# ── Google OAuth ──────────────────────────────────────────────────────────────
@router.get("/google")
def google_login():
    """Redirect to Google OAuth consent screen."""
    import urllib.parse
    params = urllib.parse.urlencode({
        "client_id":     settings.google_client_id,
        "redirect_uri":  settings.google_redirect_uri,
        "response_type": "code",
        "scope":         "openid email profile",
        "access_type":   "offline",
    })
    return RedirectResponse(f"https://accounts.google.com/o/oauth2/v2/auth?{params}")


@router.get("/google/callback")
async def google_callback(code: str, request: Request):
    """Exchange Google code for user info, create/login user, redirect to frontend."""
    import httpx
    import json

    try:
        # Exchange code for tokens
        async with httpx.AsyncClient() as client:
            token_res = await client.post("https://oauth2.googleapis.com/token", data={
                "code": code,
                "client_id":     settings.google_client_id,
                "client_secret": settings.google_client_secret,
                "redirect_uri":  settings.google_redirect_uri,
                "grant_type":    "authorization_code",
            })
            tokens = token_res.json()
            if "error" in tokens:
                raise HTTPException(400, tokens["error"])

            # Get user info
            info_res = await client.get("https://www.googleapis.com/oauth2/v2/userinfo",
                                        headers={"Authorization": f"Bearer {tokens['access_token']}"})
            guser = info_res.json()

        google_id = guser["id"]
        email     = guser["email"]
        name_parts = guser.get("name", "").split(" ", 1)
        first_name = name_parts[0]
        last_name  = name_parts[1] if len(name_parts) > 1 else ""
        avatar_url = guser.get("picture")

        # Find or create user
        user = query_one(
            "SELECT u.id, u.username, u.email, u.first_name, u.last_name, r.name AS role "
            "FROM users u JOIN roles r ON u.role_id = r.id "
            "WHERE u.google_id = %s OR u.email = %s LIMIT 1",
            (google_id, email),
        )

        if user:
            # Update google_id if first Google login
            execute(
                "UPDATE users SET google_id=%s, auth_provider='google', avatar_url=%s, updated_at=NOW() WHERE id=%s",
                (google_id, avatar_url, user["id"]),
            )
            uid  = user["id"]
            role = user["role"]
        else:
            # New user — create account
            role_row = query_one("SELECT id FROM roles WHERE name='customer'")
            username = email.split("@")[0] + "_" + secrets.token_hex(3)
            uid = execute(
                """INSERT INTO users (role_id, first_name, last_name, username, email, google_id,
                                     auth_provider, avatar_url, email_verified)
                   VALUES (%s, %s, %s, %s, %s, %s, 'google', %s, TRUE)""",
                (role_row["id"], first_name, last_name, username, email, google_id, avatar_url),
            )
            role = "customer"
            user = {"id": uid, "username": username, "email": email,
                    "first_name": first_name, "last_name": last_name}

        jwt_token = create_token(uid, role)
        import urllib.parse
        user_json  = urllib.parse.quote(json.dumps({
            "id": uid, "username": user.get("username"), "email": email,
            "first_name": first_name or user.get("first_name"),
            "last_name": last_name or user.get("last_name"), "role": role,
        }))
        redirect_base = settings.frontend_url or "http://localhost:5500"
        return RedirectResponse(f"{redirect_base}/auth.html?token={jwt_token}&user={user_json}")

    except Exception as e:
        return RedirectResponse(f"{settings.frontend_url or 'http://localhost:5500'}/auth.html?error=google_auth_failed")


# ── Forgot / Reset Password ───────────────────────────────────────────────────
@router.post("/forgot-password")
def forgot_password(req: ForgotPasswordRequest):
    """Generate a password reset token and send email."""
    user = query_one("SELECT id, first_name, email FROM users WHERE email = %s AND is_active = 1", (req.email,))

    # Always return success (don't reveal if email exists)
    if not user:
        return {"success": True, "message": "If that email exists, a reset link has been sent."}

    token    = secrets.token_urlsafe(48)
    exp_time = datetime.utcnow() + timedelta(hours=1)

    # Clean old tokens for this user
    execute("DELETE FROM password_resets WHERE user_id = %s", (user["id"],))
    execute(
        "INSERT INTO password_resets (user_id, email, token, expires_at) VALUES (%s, %s, %s, %s)",
        (user["id"], req.email, token, exp_time),
    )

    # Build reset link
    frontend  = settings.frontend_url or "http://localhost:5500"
    reset_url = f"{frontend}/reset-password.html?token={token}"

    # Send email (if email settings are configured)
    _send_reset_email(user["email"], user["first_name"], reset_url)

    return {"success": True, "message": "If that email exists, a reset link has been sent."}


@router.post("/reset-password")
def reset_password(req: ResetPasswordRequest):
    """Validate token and set new password."""
    row = query_one(
        "SELECT pr.user_id, pr.expires_at, pr.used FROM password_resets pr "
        "WHERE pr.token = %s LIMIT 1",
        (req.token,),
    )
    if not row:
        raise HTTPException(400, "Invalid or expired reset link.")
    if row["used"]:
        raise HTTPException(400, "This reset link has already been used.")
    if datetime.utcnow() > row["expires_at"]:
        raise HTTPException(400, "This reset link has expired. Please request a new one.")

    new_hash = hash_password(req.new_password)
    execute("UPDATE users SET password_hash = %s, auth_provider='local' WHERE id = %s",
            (new_hash, row["user_id"]))
    execute("UPDATE password_resets SET used = TRUE WHERE user_id = %s", (row["user_id"],))

    return {"success": True, "message": "Password updated successfully. You can now sign in."}


def _send_reset_email(email: str, name: str, reset_url: str):
    """Send password reset email using smtplib."""
    try:
        import smtplib
        from email.mime.multipart import MIMEMultipart
        from email.mime.text       import MIMEText
        cfg = get_settings()
        if not cfg.smtp_host:
            return  # Email not configured — skip silently

        msg = MIMEMultipart("alternative")
        msg["Subject"] = "Reset Your Skyline Treats Password"
        msg["From"]    = cfg.smtp_from or cfg.smtp_user
        msg["To"]      = email

        html = f"""
        <html><body style="font-family:Arial,sans-serif;background:#f7f7f5;padding:40px 20px;">
          <div style="max-width:500px;margin:0 auto;background:#fff;border-radius:16px;overflow:hidden;">
            <div style="background:#111;padding:28px 32px;">
              <h1 style="color:#f5c518;font-size:1.4rem;margin:0;">🍔 Skyline Treats</h1>
            </div>
            <div style="padding:32px;">
              <h2 style="color:#111;margin-bottom:12px;">Hi {name}, reset your password</h2>
              <p style="color:#555;line-height:1.6;">We received a request to reset your password. Click the button below — this link expires in <strong>1 hour</strong>.</p>
              <a href="{reset_url}" style="display:inline-block;margin:20px 0;padding:14px 28px;background:#f5c518;color:#111;text-decoration:none;border-radius:10px;font-weight:700;">Reset Password</a>
              <p style="color:#999;font-size:.85rem;">If you didn't request this, you can safely ignore this email.</p>
              <hr style="border:none;border-top:1px solid #eee;margin:20px 0;">
              <p style="color:#aaa;font-size:.8rem;">Skyline Treats · Tom Mboya Street, Nairobi</p>
            </div>
          </div>
        </body></html>"""

        msg.attach(MIMEText(html, "html"))
        with smtplib.SMTP(cfg.smtp_host, cfg.smtp_port or 587) as s:
            s.starttls()
            s.login(cfg.smtp_user, cfg.smtp_pass)
            s.sendmail(msg["From"], [email], msg.as_string())
    except Exception as e:
        print(f"[Email] Failed to send reset email: {e}")
