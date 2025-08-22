<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "fastfood";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Determine action
$action = isset($_GET['action']) ? $_GET['action'] : '';

switch ($action) {
    case "save": // LOGIN
        $input_username = $_POST['username'];
        $input_password = $_POST['password'];

        $stmt = $conn->prepare("SELECT * FROM clients WHERE Username = ?");
        $stmt->bind_param("s", $input_username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();

            if (password_verify($input_password, $user['Password'])) {
                $_SESSION['username'] = $user['Username'];
                $_SESSION['client_id'] = $user['client_id'];

                header("Location: dashboard.html");
                exit();
            } else {
                echo "<script>alert('Incorrect password.'); window.history.back();</script>";
            }
        } else {
            echo "<script>alert('Username not found.'); window.history.back();</script>";
        }
        $stmt->close();
        break;

    case "process": // PLACE ORDER
        if (!isset($_SESSION['client_id'])) {
            echo json_encode(["status" => "error", "message" => "Not logged in"]);
            exit();
        }

        $client_id = $_SESSION['client_id'];
        $order_items = $_POST['order_items']; // JSON string
        $order_items = json_decode($order_items, true);

        $stmt = $conn->prepare("INSERT INTO orders (client_id, items, order_date) VALUES (?, ?, NOW())");
        $items_json = json_encode($order_items);
        $stmt->bind_param("is", $client_id, $items_json);
        $stmt->execute();

        echo json_encode(["status" => "success", "message" => "Order placed successfully"]);
        $stmt->close();
        break;

    case "fetch": // FETCH ORDER HISTORY
        if (!isset($_SESSION['client_id'])) {
            echo json_encode(["status" => "error", "message" => "Not logged in"]);
            exit();
        }

        $client_id = $_SESSION['client_id'];
        $stmt = $conn->prepare("SELECT * FROM orders WHERE client_id = ? ORDER BY order_date DESC");
        $stmt->bind_param("i", $client_id);
        $stmt->execute();
        $result = $stmt->get_result();

        $orders = [];
        while ($row = $result->fetch_assoc()) {
            $row['items'] = json_decode($row['items'], true);
            $orders[] = $row;
        }

        echo json_encode($orders);
        $stmt->close();
        break;

    default:
        echo "Invalid action.";
}

$conn->close();
?>
