<?php
header('Content-Type: application/json');
require 'db.php';

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['order_id']) || !isset($input['status'])) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Missing order_id or status'
    ]);
    $conn->close();
    exit();
}

$orderId = (int)$input['order_id'];
$status = trim($input['status']);

// Validate status
$validStatuses = ['Pending', 'Processing', 'Completed', 'Cancelled'];
if (!in_array($status, $validStatuses)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid status. Must be: Pending, Processing, Completed, or Cancelled'
    ]);
    $conn->close();
    exit();
}

try {
    // Check if order exists
    $checkSql = "SELECT order_id FROM orders WHERE order_id = ?";
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->bind_param("i", $orderId);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    
    if ($checkResult->num_rows === 0) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Order not found'
        ]);
        $checkStmt->close();
        $conn->close();
        exit();
    }
    $checkStmt->close();
    
    // Update order status
    $sql = "UPDATE orders SET status = ? WHERE order_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $status, $orderId);
    
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => "Order #$orderId status updated to $status"
        ]);
    } else {
        throw new Exception($stmt->error);
    }
    
    $stmt->close();
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}

$conn->close();
?>