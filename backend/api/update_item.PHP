<?php
header("Content-Type: application/json");
include "db.php";

$data = json_decode(file_get_contents("php://input"), true);

if (isset($data['id'], $data['name'], $data['price'], $data['available'])) {
    $id = intval($data['id']);
    $name = $data['name'];
    $price = floatval($data['price']);
    $available = intval($data['available']);

    $conn = new mysqli("UPDATE items SET name = ?, price = ?, available = ? WHERE id = ?");
    $ok = $stmt->execute([$name, $price, $available, $id]);

    echo json_encode(["success" => $ok]);
} else {
    echo json_encode(["success" => false, "message" => "Invalid input"]);
}
