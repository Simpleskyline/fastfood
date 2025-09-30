<?php
header('Content-Type: application/json');
require 'db.php';
session_start();

// Ensure user is logged in
if (!isset($_SESSION['client_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

// Read JSON input
$input = json_decode(file_get_contents('php://input'), true);

// Use lowercase keys from the HTML form
$firstName = trim($input['name'] ?? '');
$lastName  = trim($input['lastname'] ?? '');
$email     = trim($input['email'] ?? '');
$phone     = trim($input['phone'] ?? '');

if ($email === '') {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Email is required']);
    exit;
}

$client_id = $_SESSION['client_id'];

// Update query matches your DB schema (`First_Name`, `Last_Name`, `Email`, `Phone`)
$stmt = $conn->prepare("
    UPDATE clients 
    SET First_Name = ?, Last_Name = ?, Email = ?, Phone = ? 
    WHERE client_id = ?
");

if (!$stmt) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Prepare failed: ' . $conn->error]);
    exit;
}

$stmt->bind_param('ssssi', $firstName, $lastName, $email, $phone, $client_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Profile updated successfully']);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $stmt->error]);
}

$stmt->close();
$conn->close();
?>