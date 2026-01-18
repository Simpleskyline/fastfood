<?php
// Start the session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Unset all session variables
$_SESSION = array();

// Delete the session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destroy the session
session_destroy();

// Clear any client-side storage (localStorage/sessionStorage)
echo '<script>
    if (typeof(Storage) !== "undefined") {
        localStorage.removeItem("fastfood_current");
        sessionStorage.clear();
    }
    window.location.href = "auth.html";
</script>';

// Ensure no further output is sent
exit;
