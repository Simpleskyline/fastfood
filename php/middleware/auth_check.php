<?php
session_start();

if (!isset($_SESSION["client_id"])) {
    http_response_code(401);
    echo json_encode(["error" => "Unauthorized"]);
    exit;
}