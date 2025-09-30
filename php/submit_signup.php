<?php
header('Content-Type: application/json');
require 'db.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName = trim($_POST['firstName']);
    $lastName = trim($_POST['lastName']);
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $password = trim($_POST['password']);
    $confirmPassword = trim($_POST['confirmPassword']);
    $role = strtolower(trim($_POST['role']));

    if ($password !== $confirmPassword) {
        echo json_encode(["success" => false, "message" => "Passwords do not match"]);
        exit;
    }

    // Check if username or email already exists
    $checkUser = $conn->prepare("SELECT client_id FROM clients WHERE Username = ? OR Email = ?");
    $checkUser->bind_param("ss", $username, $email);
    $checkUser->execute();
    $checkUser->store_result();

    if ($checkUser->num_rows > 0) {
        echo json_encode(["success" => false, "message" => "Username or Email already exists"]);
        exit;
    }
    $checkUser->close();

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO clients (First_Name, Last_Name, Username, Email, Phone, Password, Role) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssss", $firstName, $lastName, $username, $email, $phone, $hashedPassword, $role);

    if ($stmt->execute()) {
        $_SESSION['client_id'] = $stmt->insert_id;
        $_SESSION['username'] = $username;
        $_SESSION['role'] = $role;

        echo json_encode(["success" => true, "role" => $role]);
    } else {
        echo json_encode(["success" => false, "message" => "Error: " . $stmt->error]);
    }

    $stmt->close();
    $conn->close();
}
?>