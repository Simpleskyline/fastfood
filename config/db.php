<?php
$env = parse_ini_file(__DIR__ . "/../.env");

$host = $env["DB_HOST"];
$db   = $env["DB_NAME"];
$user = $env["DB_USER"];
$pass = $env["DB_PASS"];

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$db;charset=utf8mb4",
        $user,
        $pass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => "Database connection failed"]);
    exit;
}
