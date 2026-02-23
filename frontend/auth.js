/**
 * auth.js – Skyline Treats
 * Talks to Python FastAPI backend (/api/auth/...)
 * Uses JWT stored in localStorage
 */

const API = "http://localhost:8000/api";

// ── Token helpers ─────────────────────────────────────────────────────────────
function saveAuth(token, user) {
  localStorage.setItem("st_token", token);
  localStorage.setItem("st_user", JSON.stringify(user));
}

function getToken() {
  return localStorage.getItem("st_token");
}

function getUser() {
  try { return JSON.parse(localStorage.getItem("st_user")); } catch { return null; }
}

function clearAuth() {
  localStorage.removeItem("st_token");
  localStorage.removeItem("st_user");
}

function isLoggedIn() {
  return !!getToken();
}

// ── Redirect helpers ──────────────────────────────────────────────────────────
function redirectAfterLogin(user) {
  window.location.href = user.role === "admin"
    ? "admin_dashboard.html"
    : "dashboard.html";
}

// ── Auth page logic (only runs if elements exist) ─────────────────────────────
document.addEventListener("DOMContentLoaded", () => {
  const signinForm  = document.getElementById("signinForm");
  const signupForm  = document.getElementById("signupForm");
  const signinError = document.getElementById("signinError");
  const signupError = document.getElementById("signupError");

  if (!signinForm && !signupForm) return; // not on auth page

  // ── Tab switching ────────────────────────────────────────────────────────────
  window.switchTab = function (tab) {
    ["signinError", "signinSuccess", "signupError", "signupSuccess"].forEach(id => {
      const el = document.getElementById(id);
      if (el) el.style.display = "none";
    });
    const isSignin = tab === "signin";
    signinForm.classList.toggle("active", isSignin);
    signupForm.classList.toggle("active", !isSignin);
    document.getElementById("tabSignin")?.classList.toggle("active", isSignin);
    document.getElementById("tabSignup")?.classList.toggle("active", !isSignin);
    document.getElementById("formTitle").textContent = isSignin ? "Welcome back" : "Join Skyline Treats";
    document.getElementById("formSub").textContent   = isSignin
      ? "Sign in to your Skyline Treats account."
      : "Create an account and start ordering today.";
  };

  // ── Sign In ──────────────────────────────────────────────────────────────────
  signinForm.addEventListener("submit", async (e) => {
    e.preventDefault();
    const btn = document.getElementById("signinBtn");
    signinError.style.display = "none";

    btn.disabled = true;
    btn.textContent = "Signing in…";

    try {
      const res = await fetch(`${API}/auth/login`, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
          email:    document.getElementById("signinEmail").value.trim(),
          password: document.getElementById("signinPassword").value,
        }),
      });

      const data = await res.json();

      if (data.success && data.token) {
        saveAuth(data.token, data.user);
        redirectAfterLogin(data.user);
      } else {
        showMsg("signinError", data.detail || data.error || "Invalid credentials");
      }
    } catch {
      showMsg("signinError", "Cannot reach server. Is the backend running?");
    } finally {
      btn.disabled = false;
      btn.textContent = "SIGN IN";
    }
  });

  // ── Sign Up ───────────────────────────────────────────────────────────────────
  signupForm.addEventListener("submit", async (e) => {
    e.preventDefault();
    const btn = document.getElementById("signupBtn");
    signupError.style.display = "none";

    const password = document.getElementById("password").value;
    const confirm  = document.getElementById("confirmPassword").value;
    if (password !== confirm) { showMsg("signupError", "Passwords do not match"); return; }

    btn.disabled = true;
    btn.textContent = "Creating account…";

    try {
      const res = await fetch(`${API}/auth/register`, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
          first_name:       document.getElementById("firstName").value.trim(),
          last_name:        document.getElementById("lastName").value.trim(),
          username:         document.getElementById("username").value.trim(),
          email:            document.getElementById("email").value.trim(),
          password,
          confirm_password: confirm,
          role:             document.getElementById("role")?.value || "customer",
        }),
      });

      const data = await res.json();

      if (data.success && data.token) {
        saveAuth(data.token, data.user);
        redirectAfterLogin(data.user);
      } else {
        // FastAPI validation errors come as data.detail (array or string)
        const msg = Array.isArray(data.detail)
          ? data.detail.map(e => e.msg).join(", ")
          : (data.detail || data.error || "Registration failed");
        showMsg("signupError", msg);
      }
    } catch {
      showMsg("signupError", "Cannot reach server. Is the backend running?");
    } finally {
      btn.disabled = false;
      btn.textContent = "CREATE ACCOUNT";
    }
  });

  function showMsg(id, msg) {
    const el = document.getElementById(id);
    if (!el) return;
    el.textContent = msg;
    el.style.display = "block";
  }
});
