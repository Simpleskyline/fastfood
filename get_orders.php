<?php
header('Content-Type: application/json');
require 'db.php';

$sql = "SELECT o.id, u.name as user, o.total, o.status, o.created_at
        FROM orders o
        JOIN users u ON o.user_id = u.id
        ORDER BY o.created_at DESC";
$result = $conn->query($sql);

$orders = [];
while ($row = $result->fetch_assoc()) {
    $orders[] = $row;
}
echo json_encode(["success" => true, "orders" => $orders]);
?>
