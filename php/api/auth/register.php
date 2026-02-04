<?php
header("Content-Type: application/json");

require_once __DIR__ . "/../../../config/db.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Method not allowed"]);
    exit;
}

// Read JSON input
$data = json_decode(file_get_contents("php://input"), true);

// Extract fields safely
$firstName = trim($data["FirstName"] ?? "");
$lastName  = trim($data["LastName"] ?? "");
$username  = trim($data["Username"] ?? "");
$email     = trim($data["Email"] ?? "");
$password  = $data["Password"] ?? "";
$confirm   = $data["ConfirmPassword"] ?? "";
$roleName  = trim($data["Role"] ?? "");

// =======================
// VALIDATION
// =======================
if (!$firstName || !$lastName || !$username || !$email || !$password || !$confirm || !$roleName) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "All fields are required"]);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Invalid email format"]);
    exit;
}

if ($password !== $confirm) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Passwords do not match"]);
    exit;
}

if (strlen($password) < 6) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Password must be at least 6 characters"]);
    exit;
}

// =======================
// ROLE VALIDATION
// =======================
$stmt = $pdo->prepare("SELECT id, name FROM roles WHERE name = ? LIMIT 1");
$stmt->execute([$roleName]);
$role = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$role) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Invalid role selected"]);
    exit;
}

// =======================
// CHECK DUPLICATES
// =======================
$check = $pdo->prepare("SELECT id FROM users WHERE email = ? OR username = ? LIMIT 1");
$check->execute([$email, $username]);

if ($check->fetch()) {
    http_response_code(409);
    echo json_encode(["success" => false, "message" => "Email or username already exists"]);
    exit;
}

// =======================
// CREATE USER
// =======================
$passwordHash = password_hash($password, PASSWORD_DEFAULT);

try {
    $stmt = $pdo->prepare(
        "INSERT INTO users (role_id, first_name, last_name, username, email, password_hash)
         VALUES (?, ?, ?, ?, ?, ?)"
    );

    $stmt->execute([
        $role["id"],
        $firstName,
        $lastName,
        $username,
        $email,
        $passwordHash
    ]);

    echo json_encode([
        "success" => true,
        "message" => "Account created successfully",
        "user" => [
            "id" => $pdo->lastInsertId(),
            "username" => $username,
            "email" => $email,
            "role" => $role["name"],
            "firstName" => $firstName,
            "lastName" => $lastName
        ]
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Registration failed",
        "debug" => $e->getMessage() // Remove this in production
    ]);
}
?>
