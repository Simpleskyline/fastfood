<?php
function requireFields($data, $fields) {
    foreach ($fields as $field) {
        if (!isset($data[$field]) || trim($data[$field]) === "") {
            http_response_code(400);
            echo json_encode(["error" => "Missing field: $field"]);
            exit;
        }
    }
}
