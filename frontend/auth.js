// ===============================
// Signup & Signin Handlers
// ===============================

// Elements
const signinForm = document.getElementById("signinForm");
const signupForm = document.getElementById("signupForm");
const toggleBtn = document.getElementById("toggleBtn");
const signinBtn = document.getElementById("signinBtn");
const signupBtn = document.getElementById("signupBtn");
const signinError = document.getElementById("signinError");
const signupError = document.getElementById("signupError");

// Toggle signup/signin UI
function showSignup(show) {
    signinError.style.display = 'none';
    signupError.style.display = 'none';

    if (show) {
        signinForm.style.display = "none";
        signupForm.style.display = "block";
        toggleBtn.textContent = "SIGN IN";
        document.getElementById("panelTitle").textContent = "Welcome Back!";
        document.getElementById("panelText").textContent = "Create your account and start your journey.";
    } else {
        signinForm.style.display = "block";
        signupForm.style.display = "none";
        toggleBtn.textContent = "SIGN UP";
        document.getElementById("panelTitle").textContent = "Hello, Friend!";
        document.getElementById("panelText").textContent = "Enter your details and sign in";
    }
}
showSignup(false);

toggleBtn.addEventListener("click", () =>
    showSignup(signinForm.style.display !== "none")
);

// Show error helper
function showError(element, message) {
    element.innerHTML = message;
    element.style.display = 'block';
}

// Redirect based on role
function redirectToDashboard(client) {
    if (client.role === "admin") {
        window.location.href = "admin/admin_dashboard.html";
    } else {
        window.location.href = "dashboard.html";
    }
}

// ===============================
// SIGNUP
// ===============================
signupForm.addEventListener("submit", async function (e) {
    e.preventDefault();
    signupError.style.display = 'none';

    const name = document.getElementById("username").value.trim();
    const email = document.getElementById("email").value.trim();
    const password = document.getElementById("password").value;
    const confirmPassword = document.getElementById("confirmPassword").value;

    if (password !== confirmPassword) {
        showError(signupError, "Passwords do not match.");
        return;
    }

    if (password.length < 6) {
        showError(signupError, "Password must be at least 6 characters.");
        return;
    }

    signupBtn.disabled = true;
    signupBtn.textContent = "Creating account...";

    const formData = new FormData();
    formData.append("name", name);
    formData.append("email", email);
    formData.append("password", password);

    try {
        const response = await fetch(
            "http://localhost/FASTFOOD/php/auth/register.php",
            {
                method: "POST",
                body: formData,
                credentials: "same-origin"
            }
        );

        const data = await response.json();

        if (data.status === "success") {
            alert("Account created successfully!");
            redirectToDashboard({ role: "client" });
        } else {
            showError(signupError, data.error || "Signup failed.");
        }
    } catch (err) {
        console.error(err);
        showError(signupError, "Server error.");
    } finally {
        signupBtn.disabled = false;
        signupBtn.textContent = "SIGN UP";
    }
});

// ===============================
// SIGNIN
// ===============================
signinForm.addEventListener("submit", async function (e) {
    e.preventDefault();
    signinError.style.display = 'none';

    const email = document.getElementById("signinEmail").value.trim();
    const password = document.getElementById("signinPassword").value;

    signinBtn.disabled = true;
    signinBtn.textContent = "Signing in...";

    const formData = new FormData();
    formData.append("email", email);
    formData.append("password", password);

    try {
        const response = await fetch(
            "http://localhost/FASTFOOD/php/auth/login.php",
            {
                method: "POST",
                body: formData,
                credentials: "same-origin"
            }
        );

        const data = await response.json();

        if (data.status === "success") {
            redirectToDashboard(data.client);
        } else {
            showError(signinError, data.error || "Invalid credentials.");
        }
    } catch (err) {
        console.error(err);
        showError(signinError, "Server error.");
    } finally {
        signinBtn.disabled = false;
        signinBtn.textContent = "SIGN IN";
    }
});