<?php
session_start();

// Use client_id to match login.php
if (!isset($_SESSION["client_id"])) {
    http_response_code(401);
    echo json_encode(["error" => "Unauthorized"]);
    exit;
}