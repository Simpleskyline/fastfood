<?php
header("Content-Type: application/json");

require_once __DIR__ . "/../../../config/db.php";
require_once __DIR__ . "/../../../config/session.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode(["error" => "Method not allowed"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

$name = trim($data["name"] ?? "");
$email = trim($data["email"] ?? "");
$password = $data["password"] ?? "";

if (!$name || !$email || !$password) {
    http_response_code(400);
    echo json_encode(["error" => "All fields are required"]);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(["error" => "Invalid email"]);
    exit;
}

// Default role = customer
$stmt = $pdo->prepare("SELECT id FROM roles WHERE name = 'customer' LIMIT 1");
$stmt->execute();
$role = $stmt->fetch();

if (!$role) {
    http_response_code(500);
    echo json_encode(["error" => "Roles not configured"]);
    exit;
}

$passwordHash = password_hash($password, PASSWORD_DEFAULT);

try {
    $stmt = $pdo->prepare(
        "INSERT INTO users (role_id, name, email, password_hash)
         VALUES (?, ?, ?, ?)"
    );
    $stmt->execute([$role["id"], $name, $email, $passwordHash]);

    echo json_encode(["status" => "success", "message" => "User registered"]);
} catch (PDOException $e) {
    if ($e->getCode() == 23000) {
        http_response_code(409);
        echo json_encode(["error" => "Email already exists"]);
    } else {
        http_response_code(500);
        echo json_encode(["error" => "Registration failed"]);
    }
}
?>