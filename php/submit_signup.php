<?php
// Force JSON output
header("Content-Type: application/json");
error_reporting(0); // Hide warnings/notices from breaking JSON
session_start();

$servername = "localhost";
$username   = "root";
$password   = "Root@1234";
$dbname     = "fastfood";

// Connect
$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "DB connection failed"]);
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $firstName = trim($_POST['firstName'] ?? '');
    $lastName  = trim($_POST['lastName'] ?? '');
    $username  = trim($_POST['username'] ?? '');
    $email     = trim($_POST['email'] ?? '');
    $phone     = trim($_POST['phone'] ?? '');
    $password  = $_POST['password'] ?? '';
    $role      = $_POST['role'] ?? 'customer';

    if (empty($username) || empty($email) || empty($password)) {
        echo json_encode(["success" => false, "message" => "Missing required fields"]);
        exit();
    }

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

        echo json_encode([
            "success"  => true,
            "message"  => "Signup successful",
            "redirect" => "../html/customer_dashboard.html"
        ]);
    } else {
        echo json_encode(["success" => false, "message" => "Signup failed."]);
    }

    $stmt->close();
}

$conn->close();
?>