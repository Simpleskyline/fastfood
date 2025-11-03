<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/php_errors.log');

// Set JSON header
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

session_start();

// Function to send JSON response
function sendResponse($success, $message = '', $data = null, $statusCode = 200) {
    http_response_code($statusCode);
    $response = ['success' => $success];
    if ($message) $response['message'] = $message;
    if ($data !== null) $response['data'] = $data;
    echo json_encode($response);
    exit();
}

// Database connection
try {
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "fastfood";

    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
        throw new Exception("Database connection failed: " . $conn->connect_error);
    }

    // Log the raw input for debugging
    $json = file_get_contents('php://input');
    error_log('Raw input received: ' . $json);
    
    // Decode JSON data
    $data = json_decode($json, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        $error = 'Invalid JSON data received: ' . json_last_error_msg();
        error_log($error);
        throw new Exception($error);
    }
    
    error_log('Decoded input data: ' . print_r($data, true));
    
    // Get credentials from JSON or form data
    $input_email = trim($data['email'] ?? ($_POST['email'] ?? ''));
    $input_password = $data['password'] ?? ($_POST['password'] ?? '');

    if (empty($input_email)) {
        throw new Exception('Email or username is required');
    }
    
    if (empty($input_password)) {
        throw new Exception('Password is required');
    }

    // Prepare statement to find user by email or username
    $query = "SELECT 
                ID, 
                Username, 
                Email, 
                Password, 
                Role, 
                FirstName, 
                LastName 
              FROM clients 
              WHERE Email = ? OR Username = ?
              LIMIT 1";
              
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        throw new Exception('Failed to prepare statement: ' . $conn->error);
    }
    
    $stmt->bind_param("ss", $input_email, $input_email);
    
    if (!$stmt->execute()) {
        throw new Exception('Database query failed: ' . $stmt->error);
    }
    
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        throw new Exception('No account found with that email or username');
    }

    $user = $result->fetch_assoc();

    // Verify password
    if (!password_verify($input_password, $user['Password'])) {
        throw new Exception('Incorrect password');
    }

    // Set session variables
    $_SESSION['user_id'] = $user['ID'];
    $_SESSION['username'] = $user['Username'];
    $_SESSION['email'] = $user['Email'];
    $_SESSION['role'] = $user['Role'];
    $_SESSION['first_name'] = $user['FirstName'];
    $_SESSION['last_name'] = $user['LastName'];

    // Update last login time (skip if LastLogin column doesn't exist)
    try {
        $updateStmt = $conn->prepare("UPDATE clients SET LastLogin = NOW() WHERE ID = ?");
        if ($updateStmt) {
            $updateStmt->bind_param("i", $user['ID']);
            $updateStmt->execute();
            $updateStmt->close();
        }
    } catch (Exception $e) {
        // Silently ignore LastLogin update errors
        error_log("Note: Could not update LastLogin - " . $e->getMessage());
    }

    // Prepare user data for response
    $responseData = [
        'success' => true,
        'message' => 'Login successful',
        'user' => [
            'ID' => $user['ID'],
            'Username' => $user['Username'],
            'Email' => $user['Email'],
            'Role' => $user['Role'],
            'FirstName' => $user['FirstName'] ?? '',
            'LastName' => $user['LastName'] ?? ''
        ],
        'redirect' => $user['Role'] === 'admin' ? 'admin_dashboard.html' : 'dashboard.html'
    ];

    $stmt->close();
    $conn->close();
    
    // Send JSON response
    header('Content-Type: application/json');
    echo json_encode($responseData);
    exit;
    
} catch (Exception $e) {
    // Log detailed error information
    $errorDetails = [
        'time' => date('Y-m-d H:i:s'),
        'error' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'trace' => $e->getTraceAsString(),
        'post_data' => $_POST,
        'session' => isset($_SESSION) ? $_SESSION : []
    ];
    
    error_log('Login Error: ' . print_r($errorDetails, true));
    
    // Save to a separate log file
    file_put_contents(__DIR__ . '/login_errors.log', 
        print_r($errorDetails, true) . "\n---\n", 
        FILE_APPEND
    );
    
    // Return error response
    $isLocal = (isset($_SERVER['REMOTE_ADDR']) && ($_SERVER['REMOTE_ADDR'] === '127.0.0.1' || $_SERVER['REMOTE_ADDR'] === '::1'));
    
    sendResponse(
        false,
        'Login failed. ' . ($isLocal ? $e->getMessage() : 'Please check your credentials and try again.'),
        $isLocal ? ['debug' => $e->getMessage()] : null,
        401
    );
}
?>
