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
$id = (int)($data['id'] ?? 0);

if ($id === 0) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Invalid item ID provided."]);
    $conn->close();
    exit();
}

try {
    $stmt = $conn->prepare("DELETE FROM items WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo json_encode(["success" => true, "message" => "Menu item deleted."]);
        } else {
            echo json_encode(["success" => false, "message" => "Menu item not found."]);
        }
    } else {
        http_response_code(500);
        echo json_encode(["success" => false, "message" => "Failed to delete item: " . $stmt->error]);
    }
    $stmt->close();
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Server error: " . $e->getMessage()]);
}

$conn->close();
?>