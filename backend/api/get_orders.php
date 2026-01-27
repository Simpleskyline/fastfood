<?php
session_start();
header('Content-Type: application/json');
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["success" => false, "message" => "Unauthorized"]);
    exit;
}

$sql = "
SELECT 
    o.id,
    CONCAT(c.FirstName, ' ', c.LastName) AS customer,
    o.total,
    o.status,
    o.created_at
FROM orders o
JOIN clients c ON o.user_id = c.ID
ORDER BY o.created_at DESC
";

$result = $conn->query($sql);

$orders = [];
while ($row = $result->fetch_assoc()) {
    $orders[] = $row;
}

echo json_encode(["success" => true, "orders" => $orders]);
$conn->close();
?>