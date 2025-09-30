<?php
// update_profile.php - updates client profile fields
header('Content-Type: application/json');
require 'db.php';
session_start();

$data = json_decode(file_get_contents('php://input'), true);
if (!$data) {
    echo json_encode(['success'=>false,'message'=>'No data received']);
    exit;
}

$email = trim($data['email'] ?? '');
$first = trim($data['first_name'] ?? '');
$last = trim($data['last_name'] ?? '');
$phone = trim($data['phone'] ?? '');
$address = trim($data['address'] ?? '');
$city = trim($data['city'] ?? '');

if (empty($email)) {
    echo json_encode(['success'=>false,'message'=>'Email required']);
    exit;
}

$stmt = $conn->prepare('UPDATE clients SET first_name = ?, last_name = ?, phone = ?, address = ?, city = ? WHERE email = ?');
$stmt->bind_param('ssssss', $first, $last, $phone, $address, $city, $email);

if ($stmt->execute()) {
    echo json_encode(['success'=>true,'message'=>'Profile updated']);
} else {
    echo json_encode(['success'=>false,'message'=>$stmt->error]);
}
$stmt->close();
$conn->close();
?>