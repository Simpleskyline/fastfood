<?php
// Test PHP error logging
$log_message = "=== Testing PHP Error Log ===\n";
$log_message .= "Current directory: " . __DIR__ . "\n";
$log_message .= "PHP Version: " . phpversion() . "\n";
$log_message .= "Error log path: " . ini_get('error_log') . "\n";

// Test writing to a custom log file
$custom_log = __DIR__ . '/custom_php_errors.log';
file_put_contents($custom_log, date('Y-m-d H:i:s') . " - Test log entry\n", FILE_APPEND);

// Test error_log function
error_log("This is a test error message from error_log()");

// Show success message
echo "<h1>Log Test Complete</h1>";
echo "<p>Check these locations for log files:</p>";
echo "<ul>";
echo "<li><code>C:\\xampp\\php\\logs\\php_error_log</code></li>";
echo "<li><code>C:\\xampp\\apache\\logs\\error.log</code></li>";
echo "<li><a href='custom_php_errors.log' target='_blank'>custom_php_errors.log</a> (in this folder)</li>";
echo "</ul>";

echo "<h3>PHP Configuration</h3>";
echo "<pre>" . htmlspecialchars($log_message) . "</pre>";

// Show PHP info if needed
echo "<p><a href='?phpinfo=1'>Show PHP Info</a></p>";
if (isset($_GET['phpinfo'])) {
    phpinfo();
}
?>
