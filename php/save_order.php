<?php
session_start();
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection details (assuming root/Root@1234 from previous files)
$servername = "localhost";
$username   = "root"; 
$password   = "Root@1234";     
$dbname     = "fastfood";

// --- 1. Check User Authentication ---
if (!isset($_SESSION['client_id'])) {
    http_response_code(401);
    echo json_encode(["success" => false, "message" => "Authentication required. Please log in again."]);
    exit();
}
$client_id = $_SESSION['client_id'];

// --- 2. Database Connection ---
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Database connection failed: " . $conn->connect_error]);
    exit();
}
$conn->set_charset('utf8mb4');

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // --- 3. Read and Decode JSON Input ---
    // This is CRITICAL for handling data sent with Content-Type: application/json
    $json_data = file_get_contents('php://input');
    $data = json_decode($json_data, true);

    // --- 4. Validate Required Data ---
    if (empty($data['items']) || !is_array($data['items']) || !isset($data['total'])) {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "Invalid or missing order data in request body."]);
        $conn->close();
        exit();
    }

    // Prepare data for insertion
    $items_json = json_encode($data['items']);
    $total = floatval($data['total']);
    
    if ($total <= 0) {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "Total amount must be positive."]);
        $conn->close();
        exit();
    }

    // --- 5. Insert Order into 'orders' table ---
    $stmt = $conn->prepare("INSERT INTO orders (client_id, items, total, status) VALUES (?, ?, ?, 'Pending')");
    
    // Bind parameters: integer (client_id), string (items JSON), double (total)
    $stmt->bind_param("isd", $client_id, $items_json, $total);

    if ($stmt->execute()) {
        $order_id = $conn->insert_id;
        
        // Success response
        echo json_encode([
            "success" => true,
            "message" => "Order placed successfully.",
            "order_id" => $order_id
        ]);
    } else {
        http_response_code(500);
        echo json_encode(["success" => false, "message" => "Failed to save order: " . $stmt->error]);
    }

    $stmt->close();
} else {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Method not allowed."]);
}

$conn->close();
?>
