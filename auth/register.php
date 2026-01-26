<?php
header('Content-Type: application/json'); // Always return JSON

$conn = new mysqli("localhost", "root", "", "ronz_pizza_db");

if ($conn->connect_error) {
    echo json_encode([
        "success" => false,
        "message" => "DB connection failed: " . $conn->connect_error
    ]);
    exit();
}

// Only accept POST
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Use exact field names from your JS
    $firstName = $_POST['FirstName'] ?? '';
    $lastName  = $_POST['LastName'] ?? '';
    $username  = $_POST['Username'] ?? '';
    $email     = $_POST['Email'] ?? '';
    $password  = $_POST['Password'] ?? '';
    $role      = $_POST['Role'] ?? 'customer';

    // Basic validation
    if (!$username || !$email || !$password) {
        echo json_encode([
            "success" => false,
            "message" => "Please fill in all required fields."
        ]);
        exit();
    }

    // Hash password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Insert user
    $stmt = $conn->prepare("INSERT INTO users (first_name, last_name, username, email, password, role) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $firstName, $lastName, $username, $email, $hashedPassword, $role);

    if ($stmt->execute()) {
        echo json_encode([
            "success" => true,
            "message" => "Account created successfully!",
            "user" => [
                "firstName" => $firstName,
                "lastName"  => $lastName,
                "username"  => $username,
                "email"     => $email,
                "role"      => $role
            ]
        ]);
    } else {
        echo json_encode([
            "success" => false,
            "message" => "Signup failed: " . $stmt->error
        ]);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode([
        "success" => false,
        "message" => "Invalid request method."
    ]);
}
?>
