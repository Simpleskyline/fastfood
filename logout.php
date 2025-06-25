<?php
session_start();
session_unset();     // Clear all session variables
session_destroy();   // Destroy the session

// Redirect to sign in page
header("Location: signin.html");
exit();
?>