<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');

// 1. Connect to database
$conn = new mysqli("localhost", "root", "", "fastfood");

// Check connection
if ($conn->connect_error) {
    echo json_encode([
        "success" => false,
        "message" => "Connection failed: " . $conn->connect_error
    ]);
    exit;
}

// 2. Get and decode incoming JSON
$rawData = file_get_contents("php://input");
$data = json_decode($rawData, true);

// 3. Validate required fields
if (!isset($data['user_id'], $data['items'], $data['total_price'])) {
    echo json_encode([
        "success" => false,
        "message" => "Missing required order fields."
    ]);
    exit;
}
// Validate user_id, total_price, and items
$user_id = intval($data['user_id']);
$total_price = floatval($data['total_price']);
$items = $data['items'];
// Validate items structure
if (empty($items)) {
    echo json_encode([
        "success" => false,
        "message" => "Cart is empty. No items to place order."
    ]);
    exit;
}

// 4. Insert into orders table
$stmt = $conn->prepare("INSERT INTO orders (user_id, total_price, order_date) VALUES (?, ?, NOW())");
if (!$stmt) {
    echo json_encode([
        "success" => false,
        "message" => "Error preparing statement: " . $conn->error
    ]);
    $conn->close();
    exit;
}
// Bind parameters
if (!$stmt->bind_param("id", $user_id, $total_price)) {
    echo json_encode([
        "success" => false,
        "message" => "Error binding parameters for order: " . $stmt->error
    ]);
    $stmt->close();
    $conn->close();
    exit;
}
// Execute the statement
if (!$stmt->execute()) {
    echo json_encode([
        "success" => false,
        "message" => "Error saving order: " . $stmt->error
    ]);
    $stmt->close();
    $conn->close();
    exit;
}
// Get the last inserted order ID
$order_id = $stmt->insert_id;
$stmt->close();

// 5. Insert each item into order_items table
$item_stmt = $conn->prepare("INSERT INTO order_items (order_id, item_id, item_name, price, quantity) VALUES (?, ?, ?, ?, ?)");

foreach ($items as $item) {
    // Ensure item_id is treated as a string if it can be like 'chips-hot'
    $item_id = $item['id'];
    $item_name = $item['name'];
    $price = floatval($item['price']);
    $quantity = intval($item['quantity']);

    if (!$item_stmt->bind_param("issdi", $order_id, $item_id, $item_name, $price, $quantity)) {
        echo json_encode([
            "success" => false,
            "message" => "Error binding parameters for item: " . $item_stmt->error
        ]);
        $item_stmt->close();
        $conn->close();
        exit;
    }

    if (!$item_stmt->execute()) {
        echo json_encode([
            "success" => false,
            "message" => "Error saving item: " . $item_stmt->error
        ]);
        $item_stmt->close();
        $conn->close();
        exit;
    }
}

$item_stmt->close();
$conn->close();

echo json_encode([
    "success" => true,
    "message" => "Thank you for shopping with us!",
    "total" => number_format($total_price, 2)
]);
// End of script
?>
