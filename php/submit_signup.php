<?php
// Force JSON output
header("Content-Type: application/json");
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

$servername = "localhost";
$username   = "root";
$password   = "Root@1234";
$dbname     = "fastfood";

// 🐛 FIX 1: PHP variables are case-sensitive. Use $username and $password.
// Connect
$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    // 💡 Add DB error info for better debugging
    echo json_encode(["success" => false, "message" => "DB connection failed: " . $conn->connect_error]);
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // 🐛 FIX 2: Variable names must match the HTML form's 'name' attributes (which are camelCase).
    $firstName = trim($_POST['firstName'] ?? '');
    $lastName  = trim($_POST['lastName'] ?? '');
    $username  = trim($_POST['username'] ?? '');
    $email     = trim($_POST['email'] ?? '');
    $phone     = trim($_POST['phone'] ?? '');
    $password  = $_POST['password'] ?? ''; // Keep raw password for hashing
    $role      = $_POST['role'] ?? 'customer';

    // 🐛 FIX 3: Check the correctly retrieved variables.
    if (empty($username) || empty($email) || empty($password)) {
        echo json_encode(["success" => false, "message" => "Missing required fields"]);
        exit();
    }

    $hashed = password_hash($password, PASSWORD_DEFAULT);

    // Check if username/email exists
    // 🐛 FIX 4: Use the correctly named variables in the bind_param
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
    // 🐛 FIX 5: Use the correctly named variables in the bind_param
    $stmt = $conn->prepare("INSERT INTO clients (First_Name, Last_Name, Username, Email, Phone, Password, Role) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssss", $firstName, $lastName, $username, $email, $phone, $hashed, $role);

    if ($stmt->execute()) {
        // 🐛 FIX 6: Standardize session keys to lowercase for consistency
        $_SESSION['client_id'] = $stmt->insert_id;
        $_SESSION['username']  = $username;
        $_SESSION['role']      = $role;

        echo json_encode([
            "success"  => true,
            "message"  => "Signup successful",
            "redirect" => "../html/customer_dashboard.html"
        ]);
    } else {
        // 💡 Include SQL error for debugging insert failure
        echo json_encode(["success" => false, "message" => "Signup failed: " . $stmt->error]);
    }

    $stmt->close();
}

$conn->close();
?>