<?php
header('Content-Type: application/json');
require 'db.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (empty($username) || empty($password)) {
        echo json_encode(["success" => false, "message" => "All fields are required"]);
        exit;
    }

    $stmt = $conn->prepare("SELECT client_id, Username, Password, Role FROM clients WHERE Username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 1) {
        $stmt->bind_result($id, $dbUsername, $dbPassword, $role);
        $stmt->fetch();

        if (password_verify($password, $dbPassword)) {
            $_SESSION['client_id'] = $id;
            $_SESSION['username'] = $dbUsername;
            $_SESSION['role'] = $role;

            echo json_encode(["success" => true, "role" => strtolower($role)]);
        } else {
            echo json_encode(["success" => false, "message" => "Invalid password"]);
        }
    } else {
        echo json_encode(["success" => false, "message" => "User not found"]);
    }

    $stmt->close();
    $conn->close();
}
?>