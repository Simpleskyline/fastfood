<?php
session_start();

// Database connection
$conn = new mysqli("localhost", "root", "", "fastfood");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Match the form's field names exactly
    $firstname = $_POST['FirstName'];
    $lastname  = $_POST['LastName'];
    $email     = $_POST['Email'];
    $password  = password_hash($_POST['Password'], PASSWORD_DEFAULT);

    // Insert new user
    $sql = "INSERT INTO users (firstname, lastname, email, password) 
            VALUES ('$firstname', '$lastname', '$email', '$password')";

    if ($conn->query($sql) === TRUE) {
        $_SESSION['message'] = "Registration successful! Welcome, $firstname.";
        header("Location: dashboard.html");
        exit();
    } else {
        echo "Error: " . $conn->error;
    }
}

$conn->close();
?>