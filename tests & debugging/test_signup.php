<?php
// Test signup endpoint directly
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Testing Signup Endpoint</h2>";
echo "<pre>";

// Simulate POST data
$_POST = [
    'FirstName' => 'Test',
    'LastName' => 'User',
    'Username' => 'testuser' . time(), // Unique username
    'Email' => 'test' . time() . '@example.com', // Unique email
    'Password' => 'password123',
    'ConfirmPassword' => 'password123',
    'Role' => 'customer'
];

echo "Simulating signup with:\n";
echo "  FirstName: " . $_POST['FirstName'] . "\n";
echo "  LastName: " . $_POST['LastName'] . "\n";
echo "  Username: " . $_POST['Username'] . "\n";
echo "  Email: " . $_POST['Email'] . "\n";
echo "  Role: " . $_POST['Role'] . "\n\n";

echo "Calling submit_signup.php...\n\n";
echo str_repeat("=", 60) . "\n";

// Capture output
ob_start();
include 'submit_signup.php';
$output = ob_get_clean();

echo "Response:\n";
echo $output . "\n";
echo str_repeat("=", 60) . "\n\n";

// Try to decode JSON
$response = json_decode($output, true);

if ($response === null) {
    echo "❌ ERROR: Response is not valid JSON\n";
    echo "JSON Error: " . json_last_error_msg() . "\n";
} else {
    echo "✅ Valid JSON response\n";
    echo "Success: " . ($response['success'] ? 'true' : 'false') . "\n";
    echo "Message: " . ($response['message'] ?? 'N/A') . "\n";
    
    if (isset($response['user'])) {
        echo "\nUser created:\n";
        print_r($response['user']);
    }
}

echo "</pre>";
?>
