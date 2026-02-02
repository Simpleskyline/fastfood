<?php
// Start session
session_start();

// Destroy all session data
$_SESSION = array();

// Destroy the session cookie
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}

// Destroy the session
session_destroy();

// Redirect to login page
// Adjust this path to match your actual auth page location
header('Location: ../../auth/auth.html');
exit;
?>