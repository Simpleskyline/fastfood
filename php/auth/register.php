<?php
// Enable error reporting for debugging (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set headers for JSON response and CORS
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Start session
session_start();

// Include database configuration
require_once __DIR__ . '/../config/db.php';

// Function to send JSON response
function sendResponse($success, $message, $data = null) {
    $response = [
        'success' => $success,
        'message' => $message
    ];
    
    if ($data !== null) {
        $response['user'] = $data;
    }
    
    echo json_encode($response);
    exit;
}

// Function to validate email
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

// Function to validate password strength
function isValidPassword($password) {
    // At least 6 characters
    return strlen($password) >= 6;
}

// Check if request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse(false, 'Invalid request method. Please use POST.');
}

try {
    // Get form data
    $firstName = isset($_POST['FirstName']) ? trim($_POST['FirstName']) : '';
    $lastName = isset($_POST['LastName']) ? trim($_POST['LastName']) : '';
    $username = isset($_POST['Username']) ? trim($_POST['Username']) : '';
    $email = isset($_POST['Email']) ? trim($_POST['Email']) : '';
    $password = isset($_POST['Password']) ? $_POST['Password'] : '';
    $confirmPassword = isset($_POST['ConfirmPassword']) ? $_POST['ConfirmPassword'] : '';
    $role = isset($_POST['Role']) ? $_POST['Role'] : 'client';
    
    // Validation array
    $errors = [];
    
    // Validate required fields
    if (empty($firstName)) {
        $errors[] = 'First name is required.';
    }
    
    if (empty($lastName)) {
        $errors[] = 'Last name is required.';
    }
    
    if (empty($username)) {
        $errors[] = 'Username is required.';
    } elseif (strlen($username) < 3) {
        $errors[] = 'Username must be at least 3 characters long.';
    }
    
    if (empty($email)) {
        $errors[] = 'Email is required.';
    } elseif (!isValidEmail($email)) {
        $errors[] = 'Please enter a valid email address.';
    }
    
    if (empty($password)) {
        $errors[] = 'Password is required.';
    } elseif (!isValidPassword($password)) {
        $errors[] = 'Password must be at least 6 characters long.';
    }
    
    if ($password !== $confirmPassword) {
        $errors[] = 'Passwords do not match.';
    }
    
    // Validate role
    if (!in_array($role, ['client', 'admin'])) {
        $errors[] = 'Invalid role selected.';
    }
    
    // If there are validation errors, return them
    if (!empty($errors)) {
        sendResponse(false, implode(' ', $errors), null);
    }
    
    // Get database connection
    $conn = getDBConnection();
    
    // Check if username already exists
    $stmt = $conn->prepare("SELECT id FROM clients WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();
    
    if ($stmt->num_rows > 0) {
        $stmt->close();
        closeDBConnection($conn);
        sendResponse(false, 'Username already exists. Please choose another one.');
    }
    $stmt->close();
    
    // Check if email already exists
    $stmt = $conn->prepare("SELECT id FROM clients WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    
    if ($stmt->num_rows > 0) {
        $stmt->close();
        closeDBConnection($conn);
        sendResponse(false, 'Email already registered. Please use another email or sign in.');
    }
    $stmt->close();
    
    // Hash the password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    // Insert new user into database
    $stmt = $conn->prepare("INSERT INTO clients (username, email, password, role, first_name, last_name) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $username, $email, $hashedPassword, $role, $firstName, $lastName);
    
    if ($stmt->execute()) {
        $userId = $stmt->insert_id;
        $stmt->close();
        
        // Create user session
        $_SESSION['user_id'] = $userId;
        $_SESSION['username'] = $username;
        $_SESSION['email'] = $email;
        $_SESSION['role'] = $role;
        $_SESSION['first_name'] = $firstName;
        $_SESSION['last_name'] = $lastName;
        $_SESSION['logged_in'] = true;
        
        // Prepare user data for response (matching frontend expectations)
        $userData = [
            'id' => $userId,
            'username' => $username,
            'email' => $email,
            'role' => $role,
            'firstName' => $firstName,
            'lastName' => $lastName
        ];
        
        closeDBConnection($conn);
        
        sendResponse(true, 'Account created successfully!', $userData);
    } else {
        $stmt->close();
        closeDBConnection($conn);
        sendResponse(false, 'Registration failed. Please try again.');
    }
    
} catch (Exception $e) {
    // Log error
    error_log("Registration error: " . $e->getMessage());
    sendResponse(false, 'An error occurred during registration. Please try again later.');
}
?>