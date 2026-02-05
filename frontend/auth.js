document.addEventListener("DOMContentLoaded", () => {

  // ===============================
  // Elements
  // ===============================
  const signinForm = document.getElementById("signinForm");
  const signupForm = document.getElementById("signupForm");
  const toggleBtn = document.getElementById("toggleBtn");
  const signinBtn = document.getElementById("signinBtn");
  const signupBtn = document.getElementById("signupBtn");
  const signinError = document.getElementById("signinError");
  const signupError = document.getElementById("signupError");

  // ===============================
  // UI Helpers
  // ===============================
  function showSignup(show) {
    signinError.style.display = "none";
    signupError.style.display = "none";

    if (show) {
      signinForm.style.display = "none";
      signupForm.style.display = "block";
      toggleBtn.textContent = "SIGN IN";
      document.getElementById("panelTitle").textContent = "Welcome Back!";
      document.getElementById("panelText").textContent = "Create your account and choose your role.";
    } else {
      signinForm.style.display = "block";
      signupForm.style.display = "none";
      toggleBtn.textContent = "SIGN UP";
      document.getElementById("panelTitle").textContent = "Hello, Friend!";
      document.getElementById("panelText").textContent = "Enter your personal details and start your journey with us.";
    }
  }

  function showError(el, msg) {
    el.innerHTML = msg;
    el.style.display = "block";
  }

  function redirectToDashboard(user) {
    if (user.role === "admin") {
      window.location.href = "admin/admin_dashboard.html";
    } else {
      window.location.href = "dashboard.html";
    }
  }

  // Initial State
  showSignup(false);
  toggleBtn.addEventListener("click", () =>
    showSignup(signinForm.style.display !== "none")
  );

  // ===============================
// SIGNUP
// ===============================
signupForm.addEventListener("submit", async e => {
  e.preventDefault();
  signupError.style.display = "none";

  const password = document.getElementById("password").value;
  const confirmPassword = document.getElementById("confirmPassword").value;

  if (password !== confirmPassword) {
    showError(signupError, "Passwords do not match.");
    return;
  }

  signupBtn.disabled = true;
  signupBtn.textContent = "Creating account...";

  const payload = {
  first_name: document.getElementById("firstName").value.trim(),
  last_name: document.getElementById("lastName").value.trim(),
  username: document.getElementById("username").value.trim(),
  email: document.getElementById("email").value.trim(),
  password: password,
  confirm_password: confirmPassword,
  role: document.getElementById("role").value
};

  try {
    const response = await fetch("/FASTFOOD/php/api/auth/register.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        "Accept": "application/json"
      },
      body: JSON.stringify(payload),
      credentials: "same-origin"
    });

    const data = await response.json();

    if (data.success) {
      localStorage.setItem("user", JSON.stringify(data.user));
      redirectToDashboard(data.user);
    } else {
      showError(signupError, data.message || "Registration failed.");
    }
  } catch (err) {
    console.error(err);
    showError(signupError, "Server error. Check console.");
  } finally {
    signupBtn.disabled = false;
    signupBtn.textContent = "SIGN UP";
  }
});
});