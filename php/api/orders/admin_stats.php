<?php
header("Content-Type: application/json");

require_once __DIR__ . "/../../../config/db.php";
require_once __DIR__ . "/../../../config/auth_check.php";
require_once __DIR__ . "/../../../config/role_check.php";

requireAdmin();

// Total orders + revenue
$stmt = $pdo->query(
    "SELECT 
        COUNT(*) AS total_orders,
        COALESCE(SUM(total_amount), 0) AS total_revenue
     FROM orders"
);
$summary = $stmt->fetch();

// Top food items
$stmt = $pdo->query(
    "SELECT 
        f.name,
        SUM(oi.quantity) AS total_sold
     FROM order_items oi
     JOIN food_items f ON oi.food_item_id = f.id
     GROUP BY f.id
     ORDER BY total_sold DESC
     LIMIT 5"
);
$topItems = $stmt->fetchAll();

echo json_encode([
    "status" => "success",
    "summary" => $summary,
    "top_items" => $topItems
]);