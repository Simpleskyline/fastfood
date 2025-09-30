<?php
header('Content-Type: application/json');
require 'db.php';
$data = json_decode(file_get_contents('php://input'), true);
if (!$data || !isset($data['name']) || !isset($data['price'])) { echo json_encode(['success'=>false,'message'=>'Invalid input']); exit; }
$name = trim($data['name']); $price = floatval($data['price']);
$stmt = $conn->prepare('INSERT INTO items (name, price, available, created_at) VALUES (?, ?, 1, NOW())');
$stmt->bind_param('sd', $name, $price);
if ($stmt->execute()) echo json_encode(['success'=>true,'id'=>$stmt->insert_id]); else echo json_encode(['success'=>false,'message'=>$stmt->error]);
$stmt->close(); $conn->close();
?>