<?php
// Debug errors if needed
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

// Database connection
$servername = "localhost";
$username = "root";
$password = ""; // use your MySQL password if set
$dbname = "fastfood";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Collect form data
$FirstName = $_POST['FirstName'];
$LastName = $_POST['LastName'];
$Username = $_POST['Username'];
$Email = $_POST['Email'];
$Password = $_POST['Password'];
$ConfirmPassword = $_POST['ConfirmPassword'];

// Basic password confirmation
if ($Password !== $ConfirmPassword) {
    echo "<script>alert('Passwords do not match.'); window.history.back();</script>";
    exit();
}

// Check if user already exists
$check_sql = "SELECT * FROM clients WHERE Email = '$Email' OR Username = '$Username'";
$result = $conn->query($check_sql);

if ($result->num_rows > 0) {
    echo "<script>
        alert('An account with that email or username already exists. Please sign in instead.');
        window.location.href = 'http://localhost/fastfood/signin.html';
    </script>";
    exit();
}

// Hash password
$hashed_password = password_hash($Password, PASSWORD_DEFAULT);

// Insert new user
$insert_sql = "INSERT INTO clients (FirstName, LastName, Username, Email, Password)
               VALUES ('$FirstName', '$LastName', '$Username', '$Email', '$hashed_password')";
               
// Check if insert was successful
if ($conn->query($insert_sql) === TRUE) {
    echo "<script>
        alert('Registration successful! Please sign in now.');
        window.location.href = 'http://localhost/fastfood/signin.html';
    </script>";
} else {
    echo "Error: " . $conn->error;
}

$conn->close();
