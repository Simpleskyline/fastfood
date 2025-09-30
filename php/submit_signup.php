<?php
session_start();
header('Content-Type: application/json');
include("db.php");

// Collect POST safely
$firstName = $_POST['firstName'] ?? '';
$lastName  = $_POST['lastName'] ?? '';
$username  = $_POST['username'] ?? '';
$email     = $_POST['email'] ?? '';
$phone     = $_POST['phone'] ?? '';
$password  = $_POST['password'] ?? '';
$confirm   = $_POST['confirmPassword'] ?? '';
$role      = $_POST['role'] ?? 'customer';

// Basic validation
if (!$firstName || !$lastName || !$username || !$email || !$password || !$confirm) {
    echo json_encode(["success" => false, "message" => "All fields are required."]);
    exit();
}

if ($password !== $confirm) {
    echo json_encode(["success" => false, "message" => "Passwords do not match."]);
    exit();
}

// Hash password
$hashed = password_hash($password, PASSWORD_DEFAULT);

// Insert into DB
$stmt = $conn->prepare("INSERT INTO clients 
    (First_Name, Last_Name, Username, Email, Phone, Password, Role) 
    VALUES (?, ?, ?, ?, ?, ?, ?)");
if (!$stmt) {
    echo json_encode(["success" => false, "message" => "Prepare failed: " . $conn->error]);
    exit();
}

$stmt->bind_param("sssssss", $firstName, $lastName, $username, $email, $phone, $hashed, $role);

if ($stmt->execute()) {
    // Auto-login user after signup
    $_SESSION['client_id'] = $stmt->insert_id;
    $_SESSION['username']  = $username;
    $_SESSION['role']      = $role;

    $redirect = ($role === 'admin') ? "../html/admin_dashboard.html" : "../html/customer_dashboard.html";

    echo json_encode(["success" => true, "redirect" => $redirect]);
} else {
    if (strpos($stmt->error, "Duplicate") !== false) {
        echo json_encode(["success" => false, "message" => "Username or Email already exists."]);
    } else {
        echo json_encode(["success" => false, "message" => "Error: " . $stmt->error]);
    }
}

$stmt->close();
$conn->close();
?>
