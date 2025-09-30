<?php
header('Content-Type: application/json');
require 'db.php';
session_start();

// Decode JSON payload
$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['items']) || !isset($data['total'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

$total = floatval($data['total']);
$items_json = json_encode($data['items'], JSON_UNESCAPED_UNICODE);
$client_id = $_SESSION['client_id'] ?? null;
$status = 'Pending';

if ($client_id === null) {
    // If no client logged in
    $stmt = $conn->prepare('INSERT INTO orders (client_id, items, total, status, created_at) VALUES (NULL, ?, ?, ?, NOW())');
    $stmt->bind_param('sds', $items_json, $total, $status);
} else {
    // Logged in client
    $stmt = $conn->prepare('INSERT INTO orders (client_id, items, total, status, created_at) VALUES (?, ?, ?, ?, NOW())');
    $stmt->bind_param('isds', $client_id, $items_json, $total, $status);
}

if ($stmt->execute()) {
    echo json_encode([
        'success' => true,
        'order_id' => $stmt->insert_id,
        'total' => $total
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => $stmt->error
    ]);
}

$stmt->close();
$conn->close();
?>
