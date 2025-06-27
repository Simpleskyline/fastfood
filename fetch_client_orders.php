<?php
// Enable debugging (for development, disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start(); // <<< IMPORTANT: Start the session to access $_SESSION['client_id']

// Set header to indicate JSON content
header('Content-Type: application/json');

// Initialize response array
$response = [
    'success' => false,
    'message' => '',
    'data' => []
];

// --- Authentication Check ---
// IMPORTANT: Ensure $_SESSION['client_id'] is set during login (e.g., in signin.php)
if (!isset($_SESSION['client_id'])) {
    $response['message'] = 'Authentication required. Please log in to view your orders.';
    // For production, consider redirecting to login page or returning a specific error code
    echo json_encode($response);
    exit();
}

$client_id = $_SESSION['client_id']; // Get the logged-in client's ID

// Connect to the database
$conn = new mysqli("localhost", "root", "", "fastfood"); // Ensure these credentials are correct

if ($conn->connect_error) {
    $response['message'] = "Database connection failed: " . $conn->connect_error;
    echo json_encode($response);
    exit(); // Stop script execution
}

// SQL to get orders with client name and items for the *specific logged-in client*
// Use prepared statement for security!
$sql = "
SELECT 
    o.order_id,
    c.FirstName,
    c.LastName,
    o.total_price,
    o.order_date,
    o.status,
    o.mpesa_transaction_id,
    -- Concatenate product name, quantity, and price_at_order for a detailed summary
    GROUP_CONCAT(CONCAT(p.name, ' x', oi.quantity, ' (KSh', oi.price_at_order, ')') SEPARATOR '; ') AS items_summary
FROM orders o
JOIN clients c ON o.client_id = c.client_id
JOIN order_items oi ON o.order_id = oi.order_id
JOIN products p ON oi.product_id = p.product_id
WHERE o.client_id = ? -- <<< IMPORTANT: Filter by the logged-in client_id
GROUP BY o.order_id, c.FirstName, c.LastName, o.total_price, o.order_date, o.status, o.mpesa_transaction_id
ORDER BY o.order_date DESC
";

// Prepare the statement
$stmt = $conn->prepare($sql);

if (!$stmt) {
    $response['message'] = "Error preparing query: " . $conn->error;
    echo json_encode($response);
    $conn->close();
    exit();
}

// Bind the client_id parameter ( 'i' for integer type )
$stmt->bind_param("i", $client_id);

// Execute the statement
if ($stmt->execute()) {
    $result = $stmt->get_result(); // Get the result set

    if ($result->num_rows > 0) {
        $orders = [];
        while($row = $result->fetch_assoc()) {
            $orders[] = $row; // Add each order row to the data array
        }
        $response['success'] = true;
        $response['message'] = 'Orders fetched successfully.';
        $response['data'] = $orders;
    } else {
        // Query successful, but no rows returned (no orders found for this client)
        $response['success'] = true; // It's still a successful operation, just no data
        $response['message'] = 'No orders found for this client.';
    }
} else {
    // Error executing the query
    $response['message'] = "Error executing query: " . $stmt->error;
}

// Close the prepared statement and database connection
$stmt->close();
$conn->close();

// Output the JSON response
echo json_encode($response);
?>