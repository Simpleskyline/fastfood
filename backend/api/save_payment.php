<?php
header('Content-Type: application/json');
$data = json_decode(file_get_contents("php://input"), true);

$conn = new mysqli("localhost", "root", "", "fastfood");
if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "DB connection failed"]);
    exit;
}

$order_id = $data['order_id'];
$payment_method = $data['payment_method'];
$amount = $data['amount'];
$status = $data['status'] ?? 'Pending'; // Default status if not provided

// Insert into payments
$stmt = $conn->prepare("INSERT INTO payments (order_id, payment_method, amount, status) VALUES (?, ?, ?, ?)");
$stmt->bind_param("isds", $order_id, $payment_method, $amount, $status);

if ($stmt->execute()) {
    echo json_encode([
        "success" => true,
        "payment_id" => $stmt->insert_id,
        "message" => "Payment saved successfully"
    ]);
} else {
    echo json_encode(["success" => false, "message" => "Payment save failed"]);
}

$stmt->close();
$conn->close();
?>
