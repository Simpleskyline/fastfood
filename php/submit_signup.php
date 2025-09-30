<?php
// Always start session first (for future login handling)
session_start();

// Turn on error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Use central DB connection
include("db.php");

// Get form inputs safely
$firstName = $_POST['FirstName'] ?? '';
$lastName  = $_POST['LastName'] ?? '';
$user      = $_POST['Username'] ?? '';
$email     = $_POST['Email'] ?? '';
$phone     = $_POST['Phone'] ?? '';
$pass      = $_POST['Password'] ?? '';
$confirm   = $_POST['ConfirmPassword'] ?? '';
$role      = $_POST['role'] ?? 'user';

// Validate password match
if ($pass !== $confirm) {
    die("❌ Passwords do not match.");
}

// Hash password
$hashed = password_hash($pass, PASSWORD_DEFAULT);

// Prepare SQL
$stmt = $conn->prepare("INSERT INTO clients 
    (first_name, last_name, username, email, phone, password, role) 
    VALUES (?, ?, ?, ?, ?, ?, ?)");

if (!$stmt) {
    die("❌ Prepare failed: " . $conn->error);
}

$stmt->bind_param("sssssss", $firstName, $lastName, $user, $email, $phone, $hashed, $role);

// Execute
if ($stmt->execute()) {
    // Store user info in session
    $_SESSION['username'] = $user;
    $_SESSION['role'] = $role;

    // Redirect based on role
    if ($role === 'admin') {
        header("Location: ../html/admin_dashboard.html");
    } else {
        header("Location: ../html/dashboard.html");
    }
    exit();
} else {
    echo "❌ Error: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>