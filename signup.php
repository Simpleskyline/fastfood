<?php
session_start();

// Database connection
$conn = new mysqli("localhost", "root", "", "fastfood");

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Collect form inputs
$name = $_POST['name'];
$email = $_POST['email'];
$password = password_hash($_POST['password'], PASSWORD_DEFAULT);

// Insert new user
$stmt = $conn->prepare("INSERT INTO clients (name, email, password) VALUES (?, ?, ?)");
$stmt->bind_param("sss", $name, $email, $password);

if ($stmt->execute()) {
    // Automatically log in user
    $_SESSION['email'] = $email;
    $_SESSION['name'] = $name;

    // Redirect to dashboard
    header("Location: dashboard.html");
    exit();
} else {
    echo "Signup failed: " . $conn->error;
}

$stmt->close();
$conn->close();
?>
