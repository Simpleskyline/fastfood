<?php
session_start();
header("Content-Type: application/json");

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["success" => false, "message" => "Login required"]);
    exit;
}

$conn = new mysqli("localhost", "root", "", "fastfood");
if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "DB connection failed"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

if (empty($data['items']) || !isset($data['total'])) {
    echo json_encode(["success" => false, "message" => "Invalid order data"]);
    exit;
}

$items = json_encode($data['items']);
$total = (float)$data['total'];
$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare(
    "INSERT INTO orders (user_id, items, total, created_at)
     VALUES (?, ?, ?, NOW())"
);

$stmt->bind_param("isd", $user_id, $items, $total);

if ($stmt->execute()) {
    echo json_encode([
        "success" => true,
        "order_id" => $stmt->insert_id
    ]);
} else {
    echo json_encode(["success" => false, "message" => "Failed to save order"]);
}
$stmt->close();
$conn->close(); 