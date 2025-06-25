<?php
// Enable debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Connect to the database
$conn = new mysqli("localhost", "root", "", "fastfood");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// SQL to get orders with client name and items
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

// Check and output results
if ($result->num_rows > 0) {
    echo "<h2>Customer Orders</h2>";
    echo "<table border='1' cellpadding='10'>
            <tr>
                <th>Order ID</th>
                <th>Customer</th>
                <th>Items</th>
                <th>Total Price (Ksh)</th>
                <th>Order Date</th>
                <th>Status</th>
            </tr>";
    
    while($row = $result->fetch_assoc()) {
        echo "<tr>
                <td>{$row['order_id']}</td>
                <td>{$row['FirstName']} {$row['LastName']}</td>
                <td>{$row['items']}</td>
                <td>{$row['total_price']}</td>
                <td>{$row['order_date']}</td>
                <td>{$row['status']}</td>
              </tr>";
    }

    echo "</table>";
} else {
    echo "No orders found.";
}

$conn->close();
?>
