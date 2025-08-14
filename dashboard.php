<?php
session_start();

// Check if client_id is set in session, otherwise redirect
$client_id = isset($_SESSION['client_id']) ? $_SESSION['client_id'] : null;

if ($client_id === null) {
    header("Location: signin.html");
    exit();
}

// Get success message from session if available
$message = '';
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']); // Show only once
}
?>