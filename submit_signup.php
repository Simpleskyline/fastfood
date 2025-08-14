<?php
session_start();

// Database connection
$conn = new mysqli("localhost", "root", "", "fastfood");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $firstname = $_POST['firstname'];
    $lastname  = $_POST['lastname'];
    $email     = $_POST['email'];
    $password  = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Insert new user
    $sql = "INSERT INTO users (firstname, lastname, email, password) 
            VALUES ('$firstname', '$lastname', '$email', '$password')";

    if ($conn->query($sql) === TRUE) {
        // Store success message in session
        $_SESSION['message'] = "Registration successful! Welcome, $firstname.";
        
        // Redirect to dashboard
        header("Location: dashboard.html");
        exit();
    } else {
        echo "Error: " . $conn->error;
    }
}

$conn->close();
?>