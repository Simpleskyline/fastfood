<?php
header('Content-Type: application/json');
require 'db.php';
$data = json_decode(file_get_contents('php://input'), true);
$id = intval($data['id'] ?? 0); if ($id <= 0) { echo json_encode(['success'=>false,'message'=>'Invalid id']); exit; }
$stmt = $conn->prepare('DELETE FROM items WHERE id = ?'); $stmt->bind_param('i', $id);
if ($stmt->execute()) echo json_encode(['success'=>true]); else echo json_encode(['success'=>false,'message'=>$stmt->error]);
$stmt->close(); $conn->close();
?>