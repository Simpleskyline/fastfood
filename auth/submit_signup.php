<?php
// Enable full error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/php_errors.log');

// Log the start of the script
error_log("=== Starting submit_signup.php at " . date('Y-m-d H:i:s') . " ===");
error_log("POST data: " . print_r($_POST, true));

// Set JSON header FIRST before any output
header('Content-Type: application/json');

session_start();

// Database connection
try {
    $servername = "localhost";
    $username = "root";
    $password = ""; // use your MySQL password if set
    $dbname = "fastfood";

    // Log connection attempt
    error_log("Attempting to connect to database: $dbname on $servername");
    
    // Create connection
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Check connection
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    // Set charset to ensure proper encoding
    if (!$conn->set_charset("utf8mb4")) {
        error_log("Error loading character set utf8mb4: " . $conn->error);
    }
    
    error_log("Database connection successful");
    
} catch (Exception $e) {
    $errorMsg = 'Database connection failed: ' . $e->getMessage();
    error_log($errorMsg);
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => 'Database connection error. Please try again later.',
        'error' => $errorMsg
    ]);
    exit();
}

// Collect form data
$FirstName = trim($_POST['FirstName'] ?? '');
$LastName = trim($_POST['LastName'] ?? '');
$Username = trim($_POST['Username'] ?? '');
$Email = trim($_POST['Email'] ?? '');
$Password = $_POST['Password'] ?? '';
$ConfirmPassword = $_POST['ConfirmPassword'] ?? '';
$Role = $_POST['Role'] ?? 'customer'; // Default to customer

// Validate inputs
$errors = [];

if (empty($FirstName)) $errors[] = 'First name is required';
if (empty($LastName)) $errors[] = 'Last name is required';
if (empty($Username)) $errors[] = 'Username is required';
if (empty($Email)) {
    $errors[] = 'Email is required';
} elseif (!filter_var($Email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Invalid email format';
}
if (empty($Password)) {
    $errors[] = 'Password is required';
} elseif (strlen($Password) < 6) {
    $errors[] = 'Password must be at least 6 characters';
}
if ($Password !== $ConfirmPassword) {
    $errors[] = 'Passwords do not match';
}

if (!empty($errors)) {
    http_response_code(400);
    echo json_encode([
        'success' => false, 
        'message' => 'Please fix the following errors:',
        'errors' => $errors
    ]);
    exit();
}

// Password confirmation is now handled in the validation block above

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
