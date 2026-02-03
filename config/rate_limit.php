<?php
    function rateLimit($key, $limit = 5, $seconds = 600) {
    if (!isset($_SESSION[$key])) {
        $_SESSION[$key] = ["count" => 1, "time" => time()];
        return;
    }

    if (time() - $_SESSION[$key]["time"] > $seconds) {
        $_SESSION[$key] = ["count" => 1, "time" => time()];
        return;
    }

    $_SESSION[$key]["count"]++;
    if ($_SESSION[$key]["count"] > $limit) {
        http_response_code(429);
        echo json_encode(["error" => "Too many attempts. Try later."]);
        exit;
    }
}