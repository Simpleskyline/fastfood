<?php
// Enable debugging (for development, disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set header to indicate JSON content
header('Content-Type: application/json');

// Initialize a response array
$response = [
    'success' => false,
    'message' => '',
    'data' => []
];

// Connect to the database
$conn = new mysqli("localhost", "root", "", "fastfood");

if ($conn->connect_error) {
    $response['message'] = "Database connection failed: " . $conn->connect_error;
    echo json_encode($response);
    exit(); // Stop script execution
}

// SQL to get orders with client name and items
// Ensure the 'status' column exists in your 'orders' table in the database.
// If it doesn't, add it using: ALTER TABLE orders ADD COLUMN status VARCHAR(50) NOT NULL DEFAULT 'pending';
$sql = "
SELECT 
    o.order_id,
    c.FirstName,
    c.LastName,
    o.total_price,
    o.order_date,
    o.status,
    GROUP_CONCAT(CONCAT(p.name, ' x', oi.quantity) SEPARATOR ', ') AS items
FROM orders o
JOIN clients c ON o.client_id = c.client_id
JOIN order_items oi ON o.order_id = oi.order_id
JOIN products p ON oi.product_id = p.product_id
GROUP BY o.order_id
ORDER BY o.order_date DESC
";

$result = $conn->query($sql);

if ($result) { // Check if the SQL query itself was successful
    if ($result->num_rows > 0) {
        $orders = [];
        while($row = $result->fetch_assoc()) {
            $orders[] = $row; // Add each order row to the data array
        }
        $response['success'] = true;
        $response['message'] = 'Orders fetched successfully.';
        $response['data'] = $orders;
    } else {
        // Query successful, but no rows returned (no orders found)
        $response['success'] = true; 
        $response['message'] = 'No orders found.';
    }
} else {
    // SQL query failed (e.g., 'o.status' column still missing, or syntax error in query)
    $response['message'] = "Error executing query: " . $conn->error;
}

$conn->close();

// Output the JSON response
echo json_encode($response);
?>
