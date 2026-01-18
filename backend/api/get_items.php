<?php
header('Content-Type: application/json');
require 'db.php';

$result = $conn->query("SELECT id, name, price, available FROM items ORDER BY id DESC");

$items = [];
while ($row = $result->fetch_assoc()) {
    $items[] = $row;
}
echo json_encode(["success" => true, "items" => $items]);
?>
