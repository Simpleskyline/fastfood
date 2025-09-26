<?php
session_start();

// Destroy session
session_unset();
session_destroy();

// Redirect back to login page
header("Location: auth.html");
exit;
