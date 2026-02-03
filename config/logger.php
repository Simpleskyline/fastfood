<?php
function logError($e) {
    $line = date("c") . " | " . $e . PHP_EOL;
    file_put_contents(__DIR__ . "/../logs/api.log", $line, FILE_APPEND);
}
?>