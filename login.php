<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "firstprojectdb";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Collect form input
$input_username = $_POST['username'];
$input_password = $_POST['password'];

// Check user by username
$sql = "SELECT * FROM users WHERE Username = '$input_username'";
$result = $conn->query($sql);

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();

    // Verify password
    if (password_verify($input_password, $user['Password'])) {
        $_SESSION['username'] = $user['Username'];

        // Redirect to dashboard.html after login
        header("Location: dashboard.html");
        exit();
    } else {
        echo "<script>alert('Incorrect password.'); window.history.back();</script>";
    }
} else {
    echo "<script>alert('Username not found.'); window.history.back();</script>";
}

$conn->close();
?>