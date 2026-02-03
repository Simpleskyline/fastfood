<?php
header("Content-Type: application/json");

require_once __DIR__ . "/../../../config/db.php";
require_once __DIR__ . "/../../../config/auth_check.php";

$stmt = $pdo->prepare("
SELECT o.id, o.total_price, o.created_at
FROM orders o
WHERE o.user_id = ?
ORDER BY o.created_at DESC
");
$stmt->execute([$_SESSION["user_id"]]);

echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
