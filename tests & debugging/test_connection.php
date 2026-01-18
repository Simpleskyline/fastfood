<?php
// Test database connection
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Database Connection Test</h2>";
echo "<pre>";

echo "Testing connection to MySQL...\n\n";

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "fastfood";

// Test 1: Connect to MySQL server
echo "1. Connecting to MySQL server...\n";
$conn = new mysqli($servername, $username, $password);

if ($conn->connect_error) {
    echo "   ✗ FAILED: " . $conn->connect_error . "\n";
    die();
} else {
    echo "   ✓ SUCCESS: Connected to MySQL server\n\n";
}

// Test 2: Check if database exists
echo "2. Checking if 'fastfood' database exists...\n";
$result = $conn->query("SHOW DATABASES LIKE 'fastfood'");

if ($result->num_rows == 0) {
    echo "   ✗ FAILED: Database 'fastfood' does not exist\n";
    echo "   → Creating database...\n";
    
    if ($conn->query("CREATE DATABASE fastfood")) {
        echo "   ✓ SUCCESS: Database 'fastfood' created\n\n";
    } else {
        echo "   ✗ FAILED: Could not create database\n";
        die();
    }
} else {
    echo "   ✓ SUCCESS: Database 'fastfood' exists\n\n";
}

// Test 3: Connect to fastfood database
echo "3. Connecting to 'fastfood' database...\n";
$conn->select_db($dbname);

if ($conn->error) {
    echo "   ✗ FAILED: " . $conn->error . "\n";
    die();
} else {
    echo "   ✓ SUCCESS: Connected to 'fastfood' database\n\n";
}

// Test 4: Check tables
echo "4. Checking tables...\n";
$tables = ['clients', 'items', 'orders', 'payments'];
$missing_tables = [];

foreach ($tables as $table) {
    $result = $conn->query("SHOW TABLES LIKE '$table'");
    if ($result->num_rows == 0) {
        echo "   ✗ Table '$table' does not exist\n";
        $missing_tables[] = $table;
    } else {
        echo "   ✓ Table '$table' exists\n";
    }
}

if (count($missing_tables) > 0) {
    echo "\n⚠️  MISSING TABLES: " . implode(', ', $missing_tables) . "\n";
    echo "\n📋 ACTION REQUIRED:\n";
    echo "   Run the setup script: http://localhost/fastfood/setup_database.php\n";
} else {
    echo "\n✅ ALL TESTS PASSED!\n";
    echo "\nYour database is properly configured.\n";
}

echo "\n" . str_repeat("=", 60) . "\n";
echo "Connection Details:\n";
echo "  Server: $servername\n";
echo "  Username: $username\n";
echo "  Password: " . (empty($password) ? "(empty)" : "(set)") . "\n";
echo "  Database: $dbname\n";
echo "  MySQL Version: " . $conn->server_info . "\n";
echo str_repeat("=", 60) . "\n";

$conn->close();
echo "</pre>";
?>
