<?php
session_start();
// Check if client_id is set in session, otherwise default or redirect
$client_id = isset($_SESSION['client_id']) ? $_SESSION['client_id'] : null;

// Optional: If client_id is critical for the page, redirect if not set
if ($client_id === null) {
    header("Location: signin.html"); // Or your login page
    exit();
}
?>