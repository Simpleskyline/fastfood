<?php
session_start();
header("Content-Type: application/json");

$conn = new mysqli("localhost", "root", "", "fastfood");
if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "DB error"]);
    exit;
}

$username = $_POST['username'] ?? '';
$email = $_POST['email'] ?? '';
$passwordRaw = $_POST['password'] ?? '';
$role = $_POST['role'] ?? 'user';

if (!$username || !$email || !$passwordRaw) {
    echo json_encode(["success" => false, "message" => "Missing fields"]);
    exit;
}

$password = password_hash($passwordRaw, PASSWORD_DEFAULT);

$stmt = $conn->prepare(
    "INSERT INTO clients (Username, Email, Password, Role)
     VALUES (?, ?, ?, ?)"
);
$stmt->bind_param("ssss", $username, $email, $password, $role);

if ($stmt->execute()) {
    $_SESSION['user_id'] = $stmt->insert_id;
    $_SESSION['role'] = $role;

    echo json_encode([
        "success" => true,
        "message" => "Account created",
        "user" => [
            "id" => $stmt->insert_id,
            "username" => $username,
            "email" => $email,
            "role" => $role
        ]
    ]);
} else {
    echo json_encode(["success" => false, "message" => "Insert failed"]);
}
$stmt->close();
$conn->close();