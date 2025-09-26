<?php
header("Content-Type: application/json");

// DB connection
$conn = new mysqli("localhost", "root", "", "fastfood_db");

// Check connection
if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "DB Connection failed"]);
    exit;
}

// Get raw POST data
$data = json_decode(file_get_contents("php://input"), true);

if (!$data || !isset($data["items"]) || !isset($data["total"])) {
    echo json_encode(["success" => false, "message" => "Invalid request"]);
    exit;
}

$total = $data["total"];
$items = json_encode($data["items"]);

// Insert into database
$sql = "INSERT INTO orders (items, total, created_at) VALUES (?, ?, NOW())";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sd", $items, $total);

if ($stmt->execute()) {
    $order_id = $stmt->insert_id;
    echo json_encode([
        "success" => true,
        "order_id" => $order_id,
        "total" => $total
    ]);
} else {
    echo json_encode(["success" => false, "message" => "Failed to save order"]);
}

$stmt->close();
$conn->close();
