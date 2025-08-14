<?php
session_start();

// Database connection
$conn = new mysqli("localhost", "root", "", "fastfood");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Get inputs & match HTML form names (case sensitive)
    $firstname = $_POST['FirstName'];
    $lastname  = $_POST['LastName'];
    $email     = $_POST['Email'];
    $phone     = $_POST['PhoneNumber'];
    $address   = $_POST['address'];
    $password  = $_POST['Password'];
    $confirmpw = $_POST['ConfirmPassword'];

    // Password match check
    if ($password !== $confirmpw) {
        $_SESSION['message'] = "Passwords do not match!";
        header("Location: signup.html");
        exit();
    }

    // Check if email already exists
    $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $check->bind_param("s", $email);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        $_SESSION['message'] = "Email is already registered. Please log in.";
        header("Location: signin.html");
        exit();
    }
    $check->close();

    // Insert new user
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $sql = $conn->prepare("INSERT INTO users (firstname, lastname, email, phone, address, password) VALUES (?, ?, ?, ?, ?, ?)");
    $sql->bind_param("ssssss", $firstname, $lastname, $email, $phone, $address, $hashedPassword);

    if ($sql->execute()) {
        $_SESSION['message'] = "Registration successful! Welcome, $firstname.";
        $_SESSION['client_id'] = $conn->insert_id; // Store user ID in session
        header("Location: dashboard.php");
        exit();
    } else {
        $_SESSION['message'] = "Something went wrong. Please try again.";
        header("Location: signup.html");
        exit();
    }
}

$conn->close();
?>
