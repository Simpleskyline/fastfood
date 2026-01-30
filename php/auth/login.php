<?php
require_once __DIR__ . "/../config/database.php";
session_start();

header("Content-Type: application/json");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode(["error" => "Method not allowed"]);
    exit;
}

$email    = trim($_POST["email"] ?? "");
$password = $_POST["password"] ?? "";

if ($email === "" || $password === "") {
    http_response_code(400);
    echo json_encode(["error" => "Email and password required"]);
    exit;
}

$stmt = $pdo->prepare(
    "SELECT id, name, email, password FROM clients WHERE email = ?"
);
$stmt->execute([$email]);

$client = $stmt->fetch();

if (!$client || !password_verify($password, $client["password"])) {
    http_response_code(401);
    echo json_encode(["error" => "Invalid credentials"]);
    exit;
}

$_SESSION["client_id"]   = $client["id"];
$_SESSION["client_name"] = $client["name"];

echo json_encode([
    "status" => "success",
    "client" => [
        "id"    => $client["id"],
        "name"  => $client["name"],
        "email" => $client["email"]
    ]
]);