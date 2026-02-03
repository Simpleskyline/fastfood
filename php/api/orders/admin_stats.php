<?php
header("Content-Type: application/json");

require_once __DIR__ . "/../../../config/db.php";
require_once __DIR__ . "/../../../config/auth_check.php";
require_once __DIR__ . "/../../../config/role_check.php";

requireAdmin();

try {
    $stats = [];

    $stats["total_orders"] = (int)$pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
    $stats["total_revenue"] = (float)$pdo->query("SELECT COALESCE(SUM(total_price), 0) FROM orders")->fetchColumn();

    $stmt = $pdo->query("
        SELECT f.name, SUM(oi.quantity) AS sold
        FROM order_items oi
        JOIN food_items f ON oi.food_id = f.id
        GROUP BY oi.food_id
        ORDER BY sold DESC
        LIMIT 5
    ");

    $stats["top_items"] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($stats);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "error" => "Failed to load admin stats"
    ]);
}
