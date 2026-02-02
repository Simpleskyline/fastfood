<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '../config/db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed'
    ]);
    exit;
}

/**
 * MATCH FRONTEND FormData KEYS (case-sensitive!)
 */
$firstName = trim($_POST["FirstName"] ?? "");
$lastName  = trim($_POST["LastName"] ?? "");
$username  = trim($_POST["Username"] ?? "");
$email     = trim($_POST["Email"] ?? "");
$password  = $_POST["Password"] ?? "";
$role      = trim($_POST["Role"] ?? "");

/**
 * Backend validation
 */
$errors = [];

if ($firstName === "" || $lastName === "" || $username === "" || $email === "" || $password === "") {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "All fields are required"]);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Invalid email format';
}

if (strlen($password) < 6) {
    $errors[] = 'Password must be at least 6 characters';
}

if (!empty($errors)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'errors' => $errors
    ]);
    exit;
}

$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

try {
    $stmt = $pdo->prepare("
        INSERT INTO clients (first_name, last_name, username, email, password, role)
        VALUES (?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
        $firstName,
        $lastName,
        $username,
        $email,
        password_hash($password, PASSWORD_DEFAULT),
        $role
    ]);


    echo json_encode([
        'success' => true,
        'message' => 'Account created successfully',
        'user' => [
            'username' => $username,
            'email' => $email,
            'role' => $role
        ]
    ]);

} catch (PDOException $e) {
    http_response_code(400);

    if ($e->getCode() === '23000') {
        echo json_encode([
            'success' => false,
            'message' => 'Username or email already exists'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Registration failed',
            'debug' => $e->getMessage() // remove in production
        ]);
    }
}