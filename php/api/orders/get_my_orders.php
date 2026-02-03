<?php
header("Content-Type: application/json");

require_once __DIR__ . "/../../../config/db.php";
require_once __DIR__ . "/../../../config/auth_check.php";

$userId = $_SESSION["user_id"];

$stmt = $pdo->prepare(
    "SELECT id, total_amount, status, created_at
     FROM orders
     WHERE user_id = ?
     ORDER BY created_at DESC"
);
$stmt->execute([$userId]);
$orders = $stmt->fetchAll();

foreach ($orders as &$order) {
    $stmt = $pdo->prepare(
        "SELECT 
            oi.quantity,
            oi.price,
            f.name
         FROM order_items oi
         JOIN food_items f ON oi.food_item_id = f.id
         WHERE oi.order_id = ?"
    );
    $stmt->execute([$order["id"]]);
    $order["items"] = $stmt->fetchAll();
}

echo json_encode([
    "status" => "success",
    "orders" => $orders
]);
