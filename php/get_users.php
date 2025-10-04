<?php
session_start();
header('Content-Type: application/json');
require_once 'db.php';

// Basic authorization check (assuming only logged-in admin can access)
if (!isset($_SESSION['client_id']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(["success" => false, "message" => "Access denied. Admin role required."]);
    $conn->close();
    exit();
}

try {
    // Select all users, excluding the current admin, order by creation date
    $sql = "SELECT client_id, First_Name, Last_Name, Email, Role, created_at FROM clients WHERE client_id != ? ORDER BY created_at DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $_SESSION['client_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $users = [];
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
    
    echo json_encode(["success" => true, "users" => $users]);

    $stmt->close();
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Server error: " . $e->getMessage()]);
}

$conn->close();
?>
