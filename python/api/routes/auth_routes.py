"""
routes/auth_routes.py – Register, Login, Logout
"""
from fastapi import APIRouter, HTTPException, Request
from ..models import RegisterRequest, LoginRequest, TokenResponse
from ..db import query_one, execute
from ..auth import hash_password, verify_password, create_token

router = APIRouter(prefix="/auth", tags=["Auth"])


@router.post("/register", response_model=TokenResponse)
def register(req: RegisterRequest, request: Request):
    # Check role exists
    role = query_one("SELECT id, name FROM roles WHERE name = %s", (req.role,))
    if not role:
        raise HTTPException(400, "Invalid role")

    # Duplicate check
    existing = query_one(
        "SELECT id FROM users WHERE email = %s OR username = %s",
        (req.email, req.username),
    )
    if existing:
        raise HTTPException(409, "Email or username already exists")

    # Insert user
    uid = execute(
        """INSERT INTO users (role_id, first_name, last_name, username, email, password_hash)
           VALUES (%s, %s, %s, %s, %s, %s)""",
        (role["id"], req.first_name, req.last_name,
         req.username, req.email, hash_password(req.password)),
    )

    token = create_token(uid, role["name"])
    return {
        "success": True,
        "token": token,
        "user": {
            "id": uid,
            "username": req.username,
            "email": req.email,
            "first_name": req.first_name,
            "last_name": req.last_name,
            "role": role["name"],
        },
    }


@router.post("/login", response_model=TokenResponse)
def login(req: LoginRequest, request: Request):
    user = query_one(
        """SELECT u.id, u.first_name, u.last_name, u.username, u.email,
                  u.password_hash, r.name AS role
           FROM users u
           JOIN roles r ON u.role_id = r.id
           WHERE u.email = %s AND u.is_active = 1
           LIMIT 1""",
        (req.email,),
    )

    if not user or not verify_password(req.password, user["password_hash"]):
        raise HTTPException(401, "Invalid email or password")

    token = create_token(user["id"], user["role"])
    return {
        "success": True,
        "token": token,
        "user": {
            "id": user["id"],
            "username": user["username"],
            "email": user["email"],
            "first_name": user["first_name"],
            "last_name": user["last_name"],
            "role": user["role"],
        },
    }


@router.post("/logout")
def logout():
    # JWT is stateless – client discards token; optionally add token blacklist
    return {"success": True, "message": "Logged out successfully"}
