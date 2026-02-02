<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../middleware/auth_check.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

$client_id = $_SESSION['user_id'] ?? null;
$items = $input['items'] ?? [];
$total = $input['total'] ?? 0;

if (!$client_id || empty($items) || $total <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid order data']);
    exit;
}

try {
    $pdo->beginTransaction();

    // Insert order
    $stmt = $pdo->prepare(
        "INSERT INTO orders (client_id, total_amount) VALUES (?, ?)"
    );
    $stmt->execute([$client_id, $total]);

    $order_id = $pdo->lastInsertId();

    // Insert items
    $itemStmt = $pdo->prepare(
        "INSERT INTO order_items (order_id, product_name, quantity, price)
         VALUES (?, ?, ?, ?)"
    );

    foreach ($items as $item) {
        $itemStmt->execute([
            $order_id,
            $item['name'],
            $item['qty'],
            $item['price']
        ]);
    }

    $pdo->commit();

    echo json_encode([
        'success' => true,
        'order_id' => $order_id
    ]);

} catch (Exception $e) {
    $pdo->rollBack();
    http_response_code(500);
    echo json_encode(['error' => 'Order creation failed']);
}