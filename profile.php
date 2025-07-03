<?php
// update_profile.php - Handles user profile update from profile.html

header('Content-Type: application/json');

// Connect to database
$conn = new mysqli("localhost", "root", "", "fastfood");

if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Database connection failed: " . $conn->connect_error]);
    exit();
}

// Read and decode JSON input
$data = json_decode(file_get_contents("php://input"), true);

// Sample validation
if (!isset($data['email'])) {
    echo json_encode(["success" => false, "message" => "Email is required."]);
    exit();
}

$email = $conn->real_escape_string($data['email']);
$name = $conn->real_escape_string($data['name']);
$avatar = $conn->real_escape_string($data['avatar']);
$phone = $conn->real_escape_string($data['phone']);
$address = $conn->real_escape_string($data['address']);
$favFood = $conn->real_escape_string($data['favFood']);

// Update query
$sql = "UPDATE users SET FirstName='$name', Avatar='$avatar', phonenumber='$phone', Address='$address', fav_food='$favFood' WHERE Email='$email'";

if ($conn->query($sql) === TRUE) {
    echo json_encode(["success" => true, "message" => "Profile updated successfully!"]);
} else {
    echo json_encode(["success" => false, "message" => "Error updating profile: " . $conn->error]);
}

$conn->close();
?>
