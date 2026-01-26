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
        document.getElementById("panelText").textContent = "Create your account and choose your role.";
    } else {
        signinForm.style.display = "block";
        signupForm.style.display = "none";
        toggleBtn.textContent = "SIGN UP";
        document.getElementById("panelTitle").textContent = "Hello, Friend!";
        document.getElementById("panelText").textContent = "Enter your personal details and start your journey with us";
    }
}
showSignup(false);

// UI toggle button
toggleBtn.addEventListener("click", () => showSignup(signinForm.style.display !== "none"));

// Show error helper
function showError(element, message) {
    element.innerHTML = message;
    element.style.display = 'block';
}

// Redirect based on role
function redirectToDashboard(user) {
    if (user.role === "admin") {
        window.location.href = "admin_dashboard.html";
    } else {
        window.location.href = "dashboard.html";
    }
}

// ===============================
// SIGNUP
// ===============================
signupForm.addEventListener("submit", async function(e) {
    e.preventDefault();
    signupError.style.display = 'none';

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

    const email = document.getElementById("email").value.trim();
    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
        showError(signupError, "Please enter a valid email address.");
        return;
    }

    // Disable button
    signupBtn.disabled = true;
    signupBtn.textContent = 'Creating account...';

    // Prepare FormData
    const formData = new FormData();
    formData.append('username', document.getElementById("username").value.trim());
    formData.append('email', email);
    formData.append('password', password);
    formData.append('role', document.getElementById("role").value);

    try {
        const response = await fetch('http://localhost:8080/fastfood/register.php', {
            method: 'POST',
            body: formData,
            headers: { 'Accept': 'application/json' }
        });

        const responseText = await response.text();
        let data;

        try { data = JSON.parse(responseText); }
        catch { throw new Error('Invalid response from server. Check PHP code.'); }

        if (data.success) {
            // Save user in local Auth
            Auth.register({
                username: data.user.username,
                email: data.user.email,
                role: data.user.role,
                firstName: data.user.firstName || '',
                lastName: data.user.lastName || ''
            });

            alert(data.message || 'Account created successfully!');
            redirectToDashboard(data.user);
        } else {
            showError(signupError, data.message || 'Signup failed. Try again.');
        }
    } catch (error) {
        console.error('Signup error:', error);
        showError(signupError, error.message || 'An error occurred.');
    } finally {
        signupBtn.disabled = false;
        signupBtn.textContent = 'SIGN UP';
    }
});

// ===============================
// SIGNIN
// ===============================
signinForm.addEventListener('submit', async function(e) {
    e.preventDefault();
    signinError.style.display = 'none';

    const email = document.getElementById('signinEmail').value.trim();
    const password = document.getElementById('signinPassword').value;

    signinBtn.disabled = true;
    signinBtn.textContent = 'Signing in...';

    try {
        const response = await fetch('http://localhost:8080/fastfood/login.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify({ email, password })
        });

        const responseText = await response.text();
        let data;
        try { data = JSON.parse(responseText); }
        catch { throw new Error('Invalid response from server. Check login.php'); }

        if (data.success) {
            const user = data.user;

            // Save in Auth
            Auth.register({
                username: user.Username,
                email: user.Email,
                role: user.Role,
                firstName: user.FirstName || '',
                lastName: user.LastName || ''
            });

            redirectToDashboard(user);
        } else {
            showError(signinError, data.message || 'Login failed. Check credentials.');
        }
    } catch (error) {
        console.error('Login error:', error);
        showError(signinError, error.message || 'An error occurred.');
    } finally {
        signinBtn.disabled = false;
        signinBtn.textContent = 'SIGN IN';
    }
});
