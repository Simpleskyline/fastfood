<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
header('Content-Type: application/json');

function sendResponse($success, $message = '', $data = null, $status = 200) {
    http_response_code($status);
    echo json_encode([
        "success" => $success,
        "message" => $message,
        "data" => $data
    ]);
    exit;
}

$conn = new mysqli("localhost", "root", "", "fastfood");
if ($conn->connect_error) {
    sendResponse(false, "Database connection failed", null, 500);
}

$data = json_decode(file_get_contents("php://input"), true);

$email = trim($data['email'] ?? $_POST['email'] ?? '');
$password = $data['password'] ?? $_POST['password'] ?? '';

if (!$email || !$password) {
    sendResponse(false, "Email and password are required", null, 400);
}

$stmt = $conn->prepare(
    "SELECT ID, Username, Email, Password, Role, FirstName, LastName
     FROM clients
     WHERE Email = ? OR Username = ?
     LIMIT 1"
);

$stmt->bind_param("ss", $email, $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    sendResponse(false, "Invalid credentials", null, 401);
}

$user = $result->fetch_assoc();

if (!password_verify($password, $user['Password'])) {
    sendResponse(false, "Invalid credentials", null, 401);
}

$_SESSION['user_id'] = $user['ID'];
$_SESSION['role'] = $user['Role'];

sendResponse(true, "Login successful", [
    "user" => [
        "id" => $user['ID'],
        "username" => $user['Username'],
        "email" => $user['Email'],
        "role" => $user['Role']
    ],
    "redirect" => $user['Role'] === 'admin' ? "admin_dashboard.html" : "dashboard.html"
]);

    sendResponse(false, "Failed to save order", null, 500);
$stmt->close();
$conn->close();