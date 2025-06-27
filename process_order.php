<?php
// Enable debugging (for development, disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start(); // Start the session to access $_SESSION variables

// Set header to indicate JSON content
header('Content-Type: application/json');

// Initialize a response array
$response = [
    'success' => false,
    'message' => '',
    'order_id' => null // Will store the ID of the new order if successful
];

// --- Authentication & Client ID Retrieval ---
// IMPORTANT: Ensure $_SESSION['client_id'] is set during login (e.g., in signin.php)
if (!isset($_SESSION['client_id'])) {
    $response['message'] = 'Authentication required. Please log in to place an order.';
    echo json_encode($response);
    exit();
}

$client_id = $_SESSION['client_id']; // Get the logged-in client's ID from session

// 1. Connect to the database
$conn = new mysqli("localhost", "root", "", "fastfood"); // Ensure these credentials are correct

if ($conn->connect_error) {
    $response['message'] = "Database connection failed: " . $conn->connect_error;
    echo json_encode($response);
    exit(); // Stop script execution if database connection fails
}

// 2. Get the raw POST data (JSON from JavaScript fetch)
$input_json = file_get_contents('php://input');
$data = json_decode($input_json, true); // Decode as associative array

// 3. Validate input JSON data
if (json_last_error() !== JSON_ERROR_NONE) {
    $response['message'] = 'Invalid JSON received.';
    echo json_encode($response);
    $conn->close();
    exit();
}

// Basic validation for required fields from the frontend
if (
    !isset($data['total_price']) || !is_numeric($data['total_price']) ||
    !isset($data['items']) || !is_array($data['items']) || empty($data['items'])
) {
    $response['message'] = 'Missing or invalid order data (total_price or items).';
    echo json_encode($response);
    $conn->close();
    exit();
}

$total_price = $data['total_price'];
$items = $data['items'];

// Start a transaction for data integrity
// If any part of the order saving fails, the entire transaction will be rolled back.
$conn->begin_transaction();

try {
    // 4. Insert into 'orders' table
    // The 'status' and 'order_date' columns have defaults in the database, so we don't need to specify them here
    $stmt_order = $conn->prepare("INSERT INTO orders (client_id, total_price) VALUES (?, ?)");
    
    if (!$stmt_order) {
        // If prepare fails, it's usually a SQL syntax error or connection issue
        throw new Exception("Prepare statement for orders failed: " . $conn->error);
    }
    
    // 'i' for integer (client_id), 'd' for double/decimal (total_price)
    $stmt_order->bind_param("id", $client_id, $total_price); 

    if (!$stmt_order->execute()) {
        // If execute fails, it could be due to constraint violations or other DB issues
        throw new Exception("Execute statement for orders failed: " . $stmt_order->error);
    }
    
    $order_id = $conn->insert_id; // Get the ID of the newly inserted order
    $response['order_id'] = $order_id; // Add to response for frontend confirmation

    // 5. Insert into 'order_items' table for each item in the cart
    $stmt_item = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price_at_order) VALUES (?, ?, ?, ?)");
    
    if (!$stmt_item) {
        throw new Exception("Prepare statement for order_items failed: " . $conn->error);
    }

    foreach ($items as $item) {
        // Basic validation for each item received from frontend
        if (
            !isset($item['id']) || !is_numeric($item['id']) || // 'id' from frontend maps to 'product_id' in DB
            !isset($item['quantity']) || !is_numeric($item['quantity']) ||
            !isset($item['price']) || !is_numeric($item['price']) // 'price' from frontend maps to 'price_at_order' in DB
        ) {
            throw new Exception("Invalid item data received: Missing product_id, quantity, or price.");
        }

        $product_id = $item['id'];
        $quantity = $item['quantity'];
        $price_at_order = $item['price']; // Use the price from the cart item

        // 'iiid' -> i (order_id), i (product_id), i (quantity), d (price_at_order)
        $stmt_item->bind_param("iiid", $order_id, $product_id, $quantity, $price_at_order); 
        
        if (!$stmt_item->execute()) {
            throw new Exception("Execute statement for order_items failed for product ID " . $product_id . ": " . $stmt_item->error);
        }
    }

    // If all inserts were successful, commit the transaction
    $conn->commit();
    $response['success'] = true;
    $response['message'] = 'Order placed successfully!';

} catch (Exception $e) {
    // If any error occurred, rollback the transaction
    $conn->rollback();
    $response['message'] = "Error placing order: " . $e->getMessage();
} finally {
    // Close prepared statements if they were successfully prepared
    if (isset($stmt_order)) $stmt_order->close();
    if (isset($stmt_item)) $stmt_item->close();
    
    // Close the database connection
    $conn->close();
}

// Output the JSON response
echo json_encode($response);
?>