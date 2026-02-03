<?php
function success($data = []) {
    echo json_encode(["success" => true, "data" => $data]);
    exit;
}

function fail($msg, $code = 400) {
    http_response_code($code);
    echo json_encode(["success" => false, "error" => $msg]);
    exit;
}
