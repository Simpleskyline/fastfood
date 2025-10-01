<?php
header("Content-Type: application/json");
session_start();

$servername = "localhost";
$username   = "root";
$password   = "Root@1234";
$dbname     = "fastfood";

// Connect
$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "DB connection failed: " . $conn->connect_error]);
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Get JSON input
    $data = json_decode(file_get_contents("php://input"), true);

    if (!$data) {
        echo json_encode(["success" => false, "message" => "Invalid input."]);
        exit();
    }

    $firstName = $data['firstName'] ?? '';
    $lastName  = $data['lastName'] ?? '';
    $username  = $data['username'] ?? '';
    $email     = $data['email'] ?? '';
    $phone     = $data['phone'] ?? '';
    $password  = $data['password'] ?? '';
    $role      = $data['role'] ?? 'customer';

    // Hash password
    $hashed = password_hash($password, PASSWORD_DEFAULT);

    // Check if username/email exists
    $check = $conn->prepare("SELECT client_id FROM clients WHERE Username=? OR Email=? LIMIT 1");
    $check->bind_param("ss", $username, $email);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        echo json_encode(["success" => false, "message" => "Username or Email already exists."]);
        $check->close();
        $conn->close();
        exit();
    }
    $check->close();

    // Insert new user
    $stmt = $conn->prepare("INSERT INTO clients (FirstName, LastName, Username, Email, Phone, Password, Role) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssss", $firstName, $lastName, $username, $email, $phone, $hashed, $role);

    if ($stmt->execute()) {
        $_SESSION['client_id'] = $stmt->insert_id;
        $_SESSION['username']  = $username;
        $_SESSION['role']      = $role;

        echo json_encode(["success" => true, "message" => "Signup successful", "redirect" => "../html/customer_dashboard.html"]);
    } else {
        echo json_encode(["success" => false, "message" => "Signup failed: " . $stmt->error]);
    }

    $stmt->close();
}

$conn->close();
?>
