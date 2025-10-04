<?php
header('Content-Type: application/json');
require 'db.php';

try {
    // Query orders with client information
    $sql = "SELECT 
                o.order_id,
                o.client_id,
                o.total,
                o.status,
                o.created_at,
                c.Username,
                c.First_Name,
                c.Last_Name,
                c.Email
            FROM orders o
            LEFT JOIN clients c ON o.client_id = c.client_id
            ORDER BY o.created_at DESC";
    
    $result = $conn->query($sql);
    
    if (!$result) {
        throw new Exception($conn->error);
    }
    
    $orders = [];
    while ($row = $result->fetch_assoc()) {
        $orders[] = [
            'order_id' => (int)$row['order_id'],
            'client_id' => (int)$row['client_id'],
            'total' => (float)$row['total'],
            'status' => $row['status'],
            'created_at' => $row['created_at'],
            'Username' => $row['Username'] ?? 'Guest',
            'First_Name' => $row['First_Name'] ?? '',
            'Last_Name' => $row['Last_Name'] ?? '',
            'Email' => $row['Email'] ?? ''
        ];
    }
    
    echo json_encode([
        'success' => true,
        'orders' => $orders,
        'count' => count($orders)
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage(),
        'orders' => []
    ]);
}

$conn->close();
?>