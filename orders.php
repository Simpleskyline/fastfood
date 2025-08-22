<?php
session_start();
require_once "db.php"; // make sure this connects to your DB

header("Content-Type: application/json");

$action = $_GET['action'] ?? '';

if ($action === "process") {
    // Save new order
    $input = json_decode(file_get_contents("php://input"), true);

    if (!$input || !isset($_SESSION['user_id'])) {
        echo json_encode(["success" => false, "message" => "Invalid request"]);
        exit;
    }

    $user_id = $_SESSION['user_id'];
    $items = $input['items'] ?? [];
    $total = $input['total'] ?? 0;

    if (empty($items)) {
        echo json_encode(["success" => false, "message" => "Cart is empty"]);
        exit;
    }

    // Insert into orders table
    $stmt = $conn->prepare("INSERT INTO orders (user_id, total, order_date) VALUES (?, ?, NOW())");
    $stmt->bind_param("id", $user_id, $total);
    $stmt->execute();
    $order_id = $stmt->insert_id;
    $stmt->close();

    // Insert order items
    $stmt = $conn->prepare("INSERT INTO order_items (order_id, item_name, quantity, price) VALUES (?, ?, ?, ?)");
    foreach ($items as $item) {
        $name = $item['name'];
        $qty = $item['quantity'];
        $price = $item['price'];
        $stmt->bind_param("isid", $order_id, $name, $qty, $price);
        $stmt->execute();
    }
    $stmt->close();

    echo json_encode(["success" => true, "order_id" => $order_id]);
    exit;
}

if ($action === "fetch") {
    // Fetch user’s past orders
    if (!isset($_SESSION['user_id'])) {
        echo json_encode([]);
        exit;
    }

    $user_id = $_SESSION['user_id'];

    $orders = [];
    $stmt = $conn->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY order_date DESC");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $order_id = $row['id'];

        $items = [];
        $stmt_items = $conn->prepare("SELECT item_name, quantity, price FROM order_items WHERE order_id = ?");
        $stmt_items->bind_param("i", $order_id);
        $stmt_items->execute();
        $result_items = $stmt_items->get_result();

        while ($item = $result_items->fetch_assoc()) {
            $items[] = [
                "name" => $item['item_name'],
                "quantity" => $item['quantity'],
                "price" => $item['price']
            ];
        }
        $stmt_items->close();

        $orders[] = [
            "id" => $row['id'],
            "total" => $row['total'],
            "order_date" => $row['order_date'],
            "items" => $items
        ];
    }

    $stmt->close();
    echo json_encode($orders);
    exit;
}

// Default: invalid action
echo json_encode(["success" => false, "message" => "Invalid action"]);
