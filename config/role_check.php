<?php
require_once __DIR__ . "/session.php";

if (!isset($_SESSION["user_id"])) {
    http_response_code(401);
    echo json_encode([
        "error" => "Unauthorized. Please log in."
    ]);
    exit;
}
?>