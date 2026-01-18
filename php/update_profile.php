<?php
header("Content-Type: application/json");

// Connect to MySQL
$conn = new mysqli("localhost", "root", "", "fastfood");
if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "DB connection failed: " . $conn->connect_error]);
    exit;
}

// Get JSON data from the frontend
$data = json_decode(file_get_contents("php://input"), true);

// Check if data loaded
if (!$data) {
    echo json_encode(["success" => false, "message" => "No data received or invalid JSON."]);
    $conn->close();
    exit;
}

// Check for required fields
if (
    empty($data['name']) || 
    empty($data['lastname']) || 
    empty($data['email'])
) {
    echo json_encode(["success" => false, "message" => "Missing required fields."]);
    $conn->close();
    exit;
}

// Prepare statement to prevent SQL injection
$stmt = $conn->prepare("UPDATE users SET FirstName=?, LastName=?, phonenumber=?, address=? WHERE Email=?");
if (!$stmt) {
    echo json_encode(["success" => false, "message" => "Prepare failed: " . $conn->error]);
    $conn->close();
    exit;
}

$stmt->bind_param(
    "sssss",
    $data['name'],
    $data['lastname'],
    $data['phone'],
    $data['address'],
    $data['email']
);

if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        echo json_encode(["success" => true, "message" => "Profile updated successfully!"]);
    } else {
        echo json_encode(["success" => false, "message" => "No matching user found or no changes made."]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Update failed: " . $stmt->error]);
}

$stmt->close();
$conn->close();
// End of update_profile.php
?>
<?php
// Note: Ensure that the frontend sends the correct JSON structure with 'name', 'lastname',
// 'email', 'phone', and 'address' fields.
// This script updates the user's profile information in the database.
// It expects a JSON payload with the user's details.
// The script connects to the MySQL database, prepares an SQL statement to update the user's
// information, and executes it. If successful, it returns a success message; otherwise,
// it returns an error message.