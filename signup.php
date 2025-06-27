<?php
// Enable debugging (for development, disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

// Database connection details
$servername = "localhost";
$username = "root";
$password = ""; // Use your MySQL password if set
$dbname = "fastfood"; // Ensure this matches your database name

// Create database connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    // Return a JSON response for AJAX calls (like from auth.js)
    header('Content-Type: application/json');
    echo json_encode(["success" => false, "message" => "Database connection failed: " . $conn->connect_error]);
    exit();
}

// Check if the request method is POST
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Get raw JSON data for AJAX (auth.js uses JSON.stringify)
    $input_json = file_get_contents('php://input');
    $data = json_decode($input_json, true);

    // If JSON decoding failed or data is not an array, it might be a traditional form post
    if (json_last_error() !== JSON_ERROR_NONE || !is_array($data)) {
        // Fallback for traditional form submission (from your HTML)
        $FirstName       = $_POST['FirstName'] ?? '';
        $LastName        = $_POST['LastName'] ?? '';
        $Username        = $_POST['Username'] ?? '';
        $Email           = $_POST['Email'] ?? '';
        $PhoneNumber     = $_POST['Phone_Number'] ?? ''; // Corrected name for consistency
        $Address         = $_POST['address'] ?? '';
        $Password        = $_POST['Password'] ?? ''; // Expecting 'Password' from signup.html
        $ConfirmPassword = $_POST['ConfirmPassword'] ?? ''; // From signup.html
    } else {
        // Data from AJAX (auth.js)
        $FirstName       = $data['firstName'] ?? '';
        $LastName        = $data['lastName'] ?? '';
        $Email           = $data['email'] ?? '';
        $Password        = $data['password'] ?? '';
        $ConfirmPassword = $data['confirmPassword'] ?? '';
        // AJAX from auth.js doesn't send Username, PhoneNumber, Address directly,
        // so these would be empty or require updates to auth.js/signup.html if needed.
        $Username = ''; // No username from auth.js by default
        $PhoneNumber = '';
        $Address = '';
    }

    // Basic server-side validation
    if (empty($FirstName) || empty($LastName) || empty($Email) || empty($Password) || empty($ConfirmPassword)) {
        sendJsonResponse(false, 'All required fields must be filled.');
    }

    if ($Password !== $ConfirmPassword) {
        sendJsonResponse(false, 'Passwords do not match.');
    }

    if (strlen($Password) < 6) {
        sendJsonResponse(false, 'Password must be at least 6 characters long.');
    }

    // Check if user (email or username) already exists
    $check_stmt = $conn->prepare("SELECT client_id FROM clients WHERE Email = ? OR Username = ?");
    if (!$check_stmt) {
        sendJsonResponse(false, "Prepare statement failed: " . $conn->error);
    }
    $check_stmt->bind_param("ss", $Email, $Username);
    $check_stmt->execute();
    $check_stmt->store_result();

    if ($check_stmt->num_rows > 0) {
        sendJsonResponse(false, 'An account with that email or username already exists. Please sign in instead.');
    }
    $check_stmt->close();

    // Hash password
    $hashed_password = password_hash($Password, PASSWORD_DEFAULT);

    // Prepare and bind for inserting new user
    // Make sure 'Username', 'phonenumber', 'address' columns exist and match types
    $insert_sql = "INSERT INTO clients (FirstName, LastName, Username, Email, Password, phonenumber, address) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($insert_sql);

    if (!$stmt) {
        sendJsonResponse(false, "Prepare statement failed: " . $conn->error);
    }

    // 'sssssss' -> s for string. Adjust types if 'phonenumber' or 'address' are different.
    $stmt->bind_param("sssssss", $FirstName, $LastName, $Username, $Email, $hashed_password, $PhoneNumber, $Address);

    if ($stmt->execute()) {
        // Registration successful
        sendJsonResponse(true, 'Registration successful! You can now sign in.');
    } else {
        // Error during insertion
        sendJsonResponse(false, 'Registration failed: ' . $stmt->error);
    }

    $stmt->close();
} else {
    // If not a POST request, redirect or show an error
    sendJsonResponse(false, 'Invalid request method.');
}

$conn->close();

function sendJsonResponse($success, $message) {
    header('Content-Type: application/json');
    echo json_encode(["success" => $success, "message" => $message]);
    exit();
}
?>