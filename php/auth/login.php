<?php
// Enable error reporting for debugging (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set headers for JSON response
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
        $response = array_merge($response, $data);
    }
    
    echo json_encode($response);
    exit;
}

// Check if request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse(false, 'Invalid request method. Please use POST.');
}

try {
    // Get JSON input
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    // If JSON decoding failed, try to get from POST
    if ($data === null) {
        $email = isset($_POST['email']) ? trim($_POST['email']) : '';
        $password = isset($_POST['password']) ? $_POST['password'] : '';
    } else {
        $email = isset($data['email']) ? trim($data['email']) : '';
        $password = isset($data['password']) ? $data['password'] : '';
    }
    
    // Validate input
    if (empty($email) || empty($password)) {
        sendResponse(false, 'Please provide both email and password.');
    }
    
    // Get database connection
    $conn = getDBConnection();
    
    // Prepare and execute query
    $stmt = $conn->prepare("SELECT id, username, email, password, role, first_name, last_name FROM clients WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        $stmt->close();
        closeDBConnection($conn);
        sendResponse(false, 'Invalid email or password.');
    }
    
    $user = $result->fetch_assoc();
    $stmt->close();
    
    // Verify password
    if (!password_verify($password, $user['password'])) {
        closeDBConnection($conn);
        sendResponse(false, 'Invalid email or password.');
    }
    
    // Password is correct - create session
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['role'] = $user['role'];
    $_SESSION['first_name'] = $user['first_name'];
    $_SESSION['last_name'] = $user['last_name'];
    $_SESSION['logged_in'] = true;
    
    closeDBConnection($conn);
    
    // Prepare user data for response (exclude password, match frontend expectations)
    $userData = [
        'ID' => $user['id'],
        'Username' => $user['username'],
        'Email' => $user['email'],
        'Role' => $user['role'],
        'FirstName' => $user['first_name'],
        'LastName' => $user['last_name']
    ];
    
    // Determine redirect URL based on role
    // Adjust these paths to match your actual frontend structure
    $redirectUrl = ($user['role'] === 'admin') 
        ? '../../frontend/admin/admin_dashboard.html' 
        : '../../frontend/pages/dashboard.html';
    
    sendResponse(true, 'Login successful!', [
        'user' => $userData,
        'redirect' => $redirectUrl
    ]);
    
} catch (Exception $e) {
    // Log error
    error_log("Login error: " . $e->getMessage());
    sendResponse(false, 'An error occurred during login. Please try again later.');
}
?>