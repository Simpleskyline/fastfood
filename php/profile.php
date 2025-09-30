<?php
header('Content-Type: application/json');
require 'db.php';
session_start();

if (!isset($_SESSION['client_id'])) {
    echo json_encode(["success" => false, "message" => "Not logged in"]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $lastname = trim($_POST['lastname']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $client_id = $_SESSION['client_id'];

    $stmt = $conn->prepare("UPDATE clients SET First_Name = ?, Last_Name = ?, Email = ?, Phone = ? WHERE client_id = ?");
    $stmt->bind_param("ssssi", $name, $lastname, $email, $phone, $client_id);

    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "Profile updated successfully"]);
    } else {
        echo json_encode(["success" => false, "message" => "Error updating profile: " . $stmt->error]);
    }

    $stmt->close();
    $conn->close();
}
?>