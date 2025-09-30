<?php
header('Content-Type: application/json');
require 'db.php';
$result = $conn->query('SELECT client_id AS id, first_name, last_name, email, created_at FROM clients ORDER BY created_at DESC');
$users = [];
while ($row = $result->fetch_assoc()) $users[] = $row;
echo json_encode(['success'=>true,'users'=>$users]);
$conn->close();
?>