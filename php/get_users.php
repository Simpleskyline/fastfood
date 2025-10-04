<?php
header('Content-Type: application/json');
require 'db.php';

try {
    // Select all users (customers only, or include admin if needed)
    $sql = "SELECT 
                client_id, 
                First_Name, 
                Last_Name, 
                Username,
                Email, 
                Role, 
                created_at 
            FROM clients 
            ORDER BY created_at DESC";
    
    $result = $conn->query($sql);
    
    if (!$result) {
        throw new Exception($conn->error);
    }
    
    $users = [];
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
    
    echo json_encode([
        'success' => true,
        'users' => $users,
        'count' => count($users)
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage(),
        'users' => []
    ]);
}

$conn->close();
?>