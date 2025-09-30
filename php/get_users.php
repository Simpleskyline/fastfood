<?php
header('Content-Type: application/json');
require 'db.php';
session_start();

if (!isset($_SESSION['client_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    http_response_code(403);
    echo json_encode(['success'=>false,'message'=>'Forbidden']);
    exit;
}

$result = $conn->query("SELECT client_id AS id, First_Name, Last_Name, Username, Email, Phone, created_at FROM clients ORDER BY created_at DESC");
$users = [];
while ($row = $result->fetch_assoc()) $users[] = $row;
echo json_encode(['success'=>true,'users'=>$users]);
$conn->close();
?>
