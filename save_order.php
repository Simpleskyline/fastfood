<?php
header('Content-Type: application/json');
$data = json_decode(file_get_contents("php://input"), true);

$conn = new mysqli("localhost", "root", "", "fastfood");
if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "DB connection failed"]);
    exit;
}

$user_id = $data['user_id'];
$total_price = $data['total_price'];
$items = $data['items'];
$payment_method = $data['payment_method']; // <-- NEW

// Insert into orders
$stmt = $conn->prepare("INSERT INTO orders (client_id, total_price, payment_method) VALUES (?, ?, ?)");
$stmt->bind_param("ids", $user_id, $total_price, $payment_method);
$stmt->execute();
$order_id = $stmt->insert_id;
$stmt->close();

// Insert items
foreach ($items as $item) {
    $stmt = $conn->prepare("INSERT INTO order_items (order_id, product_name, price, quantity) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isdi", $order_id, $item['name'], $item['price'], $item['quantity']);
    $stmt->execute();
    $stmt->close();
}

echo json_encode([
    "success" => true,
    "order_id" => $order_id,
    "payment_method" => $payment_method
]);
$conn->close();
?>
// Returns order ID and payment method for confirmation