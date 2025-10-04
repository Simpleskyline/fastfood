<?php
session_start();
header('Content-Type: application/json');

$servername = "localhost";
$username   = "root"; 
$password   = "Root@1234";     
$dbname     = "fastfood";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
  echo json_encode(["success" => false, "message" => "Database connection failed."]);
  exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $user = $_POST['username'] ?? '';
  $pass = $_POST['password'] ?? '';

  // Validate empty fields
  if (empty($user) || empty($pass)) {
    echo json_encode(["success" => false, "message" => "Username and password are required."]);
    exit();
  }

  // Query the clients table
  $sql = "SELECT client_id, Username, Role, Password FROM clients WHERE Username=?"; // Select only necessary columns
  $stmt = $conn->prepare($sql);
  if (!$stmt) {
    echo json_encode(["success" => false, "message" => "SQL error: " . $conn->error]);
    exit();
  }

  $stmt->bind_param("s", $user);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($result->num_rows === 1) {
    $row = $result->fetch_assoc();

    // Verify password
    if (password_verify($pass, $row['Password'])) {
      // 🐛 FIX: Standardize session keys to lowercase for consistency
      $_SESSION['client_id'] = $row['client_id'];
      $_SESSION['username']  = $row['Username'];
      $_SESSION['role']      = $row['Role'];

      // Send success JSON with redirect URL
      if ($row['Role'] === 'admin') {
        echo json_encode(["success" => true, "redirect" => "../html/admin_dashboard.html"]);
      } else {
        echo json_encode(["success" => true, "redirect" => "../html/customer_dashboard.html"]);
      }
      exit();
    } else {
      echo json_encode(["success" => false, "message" => "Invalid password."]);
    }
  } else {
    echo json_encode(["success" => false, "message" => "User not found."]);
  }

  $stmt->close();
}

$conn->close();
?>