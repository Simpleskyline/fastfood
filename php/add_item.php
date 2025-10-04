<?php
session_start();
header('Content-Type: application/json');
require_once 'db.php';

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Method not allowed."]);
    exit();
}

if (!isset($_SESSION['client_id']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(["success" => false, "message" => "Access denied. Admin role required."]);
    $conn->close();
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);
$name  = trim($data['name'] ?? '');
$price = floatval($data['price'] ?? 0);

if (empty($name) || $price <= 0) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Invalid name or price provided."]);
    $conn->close();
    exit();
}

try {
    $stmt = $conn->prepare("INSERT INTO items (name, price, available) VALUES (?, ?, 1)");
    $stmt->bind_param("sd", $name, $price);
    
    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "Menu item added."]);
    } else {
        http_response_code(500);
        echo json_encode(["success" => false, "message" => "Failed to add item: " . $stmt->error]);
    }
    $stmt->close();
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Server error: " . $e->getMessage()]);
}

$conn->close();
?>
