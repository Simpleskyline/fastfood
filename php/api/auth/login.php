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

$email = trim($data["email"] ?? "");
$password = $data["password"] ?? "";

if (!$email || !$password) {
    http_response_code(400);
    echo json_encode(["error" => "Email and password required"]);
    exit;
}

$stmt = $pdo->prepare(
    "SELECT users.id, users.name, users.password_hash, roles.name AS role
     FROM users
     JOIN roles ON users.role_id = roles.id
     WHERE users.email = ?
     LIMIT 1"
);
$stmt->execute([$email]);
$user = $stmt->fetch();

if (!$user || !password_verify($password, $user["password_hash"])) {
    http_response_code(401);
    echo json_encode(["error" => "Invalid credentials"]);
    exit;
}

// Store session
$_SESSION["user_id"] = $user["id"];
$_SESSION["user_role"] = $user["role"];
$_SESSION["user_name"] = $user["name"];

echo json_encode([
    "status" => "success",
    "message" => "Login successful",
    "user" => [
        "id" => $user["id"],
        "name" => $user["name"],
        "role" => $user["role"]
    ]
]);
?>