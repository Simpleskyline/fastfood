<?php
header('Content-Type: application/json');
require 'db.php';
$data = json_decode(file_get_contents('php://input'), true);
if (!$data || !isset($data['order_id']) || !isset($data['amount'])) { echo json_encode(['success'=>false,'message'=>'Invalid input']); exit; }
$order_id = intval($data['order_id']); $payment_method = trim($data['payment_method'] ?? ''); $amount = floatval($data['amount']); $status = $data['status'] ?? 'Pending';
$stmt = $conn->prepare('INSERT INTO payments (order_id, payment_method, amount, status, created_at) VALUES (?, ?, ?, ?, NOW())');
$stmt->bind_param('isds', $order_id, $payment_method, $amount, $status);
if ($stmt->execute()) echo json_encode(['success'=>true,'payment_id'=>$stmt->insert_id]); else echo json_encode(['success'=>false,'message'=>$stmt->error]);
$stmt->close(); $conn->close();
?>