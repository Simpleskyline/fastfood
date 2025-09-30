<?php
header('Content-Type: application/json');
require 'db.php';
$data = json_decode(file_get_contents('php://input'), true);
if (!isset($data['id'], $data['name'], $data['price'], $data['available'])) { echo json_encode(['success'=>false,'message'=>'Invalid input']); exit; }
$id = intval($data['id']); $name = trim($data['name']); $price = floatval($data['price']); $available = intval($data['available']);
$stmt = $conn->prepare('UPDATE items SET name = ?, price = ?, available = ? WHERE id = ?');
$stmt->bind_param('sdii', $name, $price, $available, $id);
$ok = $stmt->execute();
echo json_encode(['success'=>$ok,'error'=>$stmt->error]); $stmt->close(); $conn->close();
?>