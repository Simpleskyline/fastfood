<?php
// Enable debugging (for development, disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set header to indicate JSON content
header('Content-Type: application/json');

// Initialize response array
$response = [
    'success' => false,
    'message' => '',
    'order_id' => null
];

// 1. Connect to the database
$conn = new mysqli("localhost", "root", "", "fastfood");

if ($conn->connect_error) {
    $response['message'] = "Database connection failed: " . $conn->connect_error;
    echo json_encode($response);
    exit();
}

// 2. Get the raw POST data (JSON from JavaScript fetch)
$input_json = file_get_contents('php://input');
$data = json_decode($input_json, true); // Decode as associative array

// 3. Validate input data
if (json_last_error() !== JSON_ERROR_NONE) {
    $response['message'] = 'Invalid JSON received.';
    echo json_encode($response);
    $conn->close();
    exit();
}

// Basic validation for required fields
if (!isset($data['client_id']) || !is_numeric($data['client_id']) ||
    !isset($data['total_price']) || !is_numeric($data['total_price']) ||
    !isset($data['items']) || !is_array($data['items']) || empty($data['items'])) {
    
    $response['message'] = 'Missing or invalid order data.';
    echo json_encode($response);
    $conn->close();
    exit();
}

$client_id = $data['client_id'];
$total_price = $data['total_price'];
$items = $data['items'];

// Start a transaction for data integrity
$conn->begin_transaction();

try {
    // 4. Insert into 'orders' table
    $stmt_order = $conn->prepare("INSERT INTO orders (client_id, total_price) VALUES (?, ?)");
    if (!$stmt_order) {
        throw new Exception("Prepare statement for orders failed: " . $conn->error);
    }
    $stmt_order->bind_param("id", $client_id, $total_price); // 'i' for integer, 'd' for double/decimal

    if (!$stmt_order->execute()) {
        throw new Exception("Execute statement for orders failed: " . $stmt_order->error);
    }
    
    $order_id = $conn->insert_id; // Get the ID of the newly inserted order

    // 5. Insert into 'order_items' table for each item
    $stmt_item = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price_at_order) VALUES (?, ?, ?, ?)");
    if (!$stmt_item) {
        throw new Exception("Prepare statement for order_items failed: " . $conn->error);
    }

    foreach ($items as $item) {
        // Basic validation for each item
        if (!isset($item['product_id']) || !is_numeric($item['product_id']) ||
            !isset($item['quantity']) || !is_numeric($item['quantity']) ||
            !isset($item['price_at_order']) || !is_numeric($item['price_at_order'])) {
            throw new Exception("Invalid item data in order.");
        }

        $product_id = $item['product_id'];
        $quantity = $item['quantity'];
        $price_at_order = $item['price_at_order'];

        $stmt_item->bind_param("iiid", $order_id, $product_id, $quantity, $price_at_order); // 'i' for int, 'd' for double
        if (!$stmt_item->execute()) {
            throw new Exception("Execute statement for order_items failed: " . $stmt_item->error);
        }
    }

    // Commit the transaction if all inserts were successful
    $conn->commit();
    $response['success'] = true;
    $response['message'] = 'Order placed successfully!';
    $response['order_id'] = $order_id;

} catch (Exception $e) {
    // Rollback the transaction if any error occurred
    $conn->rollback();
    $response['message'] = "Error placing order: " . $e->getMessage();
} finally {
    // Close statements and connection
    if (isset($stmt_order)) $stmt_order->close();
    if (isset($stmt_item)) $stmt_item->close();
    $conn->close();
}

// Output the JSON response
echo json_encode($response);
?>