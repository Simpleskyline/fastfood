<?php
// Disable error display to prevent breaking JSON
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Set JSON header FIRST before any output
header('Content-Type: application/json');

session_start();

// Database connection
$servername = "localhost";
$username = "root";
$password = ""; // use your MySQL password if set
$dbname = "fastfood";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . $conn->connect_error]);
    exit();
}

// Collect form data
$FirstName = $_POST['FirstName'] ?? '';
$LastName = $_POST['LastName'] ?? '';
$Username = $_POST['Username'] ?? '';
$Email = $_POST['Email'] ?? '';
$Password = $_POST['Password'] ?? '';
$ConfirmPassword = $_POST['ConfirmPassword'] ?? '';
$Role = $_POST['Role'] ?? 'customer'; // Default to customer

// Validate inputs
if (empty($FirstName) || empty($LastName) || empty($Username) || empty($Email) || empty($Password)) {
    echo json_encode(['success' => false, 'message' => 'All fields are required.']);
    exit();
}

// Basic password confirmation
if ($Password !== $ConfirmPassword) {
    echo json_encode(['success' => false, 'message' => 'Passwords do not match.']);
    exit();
}

// Check if user already exists
$check_stmt = $conn->prepare("SELECT * FROM clients WHERE Email = ? OR Username = ?");
$check_stmt->bind_param("ss", $Email, $Username);
$check_stmt->execute();
$result = $check_stmt->get_result();

if ($result->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'An account with that email or username already exists.']);
    $check_stmt->close();
    $conn->close();
    exit();
}
$check_stmt->close();

// Hash password
$hashed_password = password_hash($Password, PASSWORD_DEFAULT);

// Insert new user with role
$insert_stmt = $conn->prepare("INSERT INTO clients (FirstName, LastName, Username, Email, Password, Role) VALUES (?, ?, ?, ?, ?, ?)");
$insert_stmt->bind_param("ssssss", $FirstName, $LastName, $Username, $Email, $hashed_password, $Role);
               
// Check if insert was successful
if ($insert_stmt->execute()) {
    // Set session variables
    $_SESSION['user_id'] = $insert_stmt->insert_id;
    $_SESSION['username'] = $Username;
    $_SESSION['email'] = $Email;
    $_SESSION['role'] = $Role;
    
    echo json_encode([
        'success' => true, 
        'message' => 'Registration successful!',
        'user' => [
            'username' => $Username,
            'email' => $Email,
            'firstName' => $FirstName,
            'lastName' => $LastName,
            'role' => $Role
        ]
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Registration failed: ' . $conn->error]);
}

$insert_stmt->close();
$conn->close();
