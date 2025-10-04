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

// Connect to the database
$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "DB connection failed: " . $conn->connect_error]);
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Retrieve data from POST, ensuring keys match HTML form names
    $firstName = trim($_POST['firstName'] ?? '');
    $lastName  = trim($_POST['lastName'] ?? '');
    $username  = trim($_POST['username'] ?? '');
    $email     = trim($_POST['email'] ?? '');
    $phone     = trim($_POST['phone'] ?? '');
    $password  = $_POST['password'] ?? '';
    $role      = $_POST['role'] ?? 'customer'; // Role is obtained directly from the signup form

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
    $stmt = $conn->prepare("INSERT INTO clients (First_Name, Last_Name, Username, Email, Phone, Password, Role) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssss", $firstName, $lastName, $username, $email, $phone, $hashed, $role);

    if ($stmt->execute()) {
        // Set session variables
        $_SESSION['client_id'] = $stmt->insert_id;
        $_SESSION['username']  = $username;
        $_SESSION['role']      = $role;

        // Determine redirect URL based on the registered role (customer or admin)
        if ($role === 'admin') {
            $redirectUrl = "../html/admin_dashboard.html";
        } else {
            $redirectUrl = "../html/dashboard.html";
        }

        echo json_encode([
            "success"  => true,
            "message"  => "Signup successful",
            "redirect" => $redirectUrl
        ]);
    } else {
        echo json_encode(["success" => false, "message" => "Signup failed: " . $stmt->error]);
    }

    $stmt->close();
}

$conn->close();
?>
