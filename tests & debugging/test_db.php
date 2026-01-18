<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database configuration
$config = [
    'host' => 'localhost',
    'username' => 'root',
    'password' => '',
    'database' => 'fastfood'
];

echo "<h1>Database Connection Test</h1>";

try {
    // Test connection
    $conn = new mysqli($config['host'], $config['username'], $config['password'], $config['database']);
    
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    
    echo "<p style='color: green;'>✅ Connected to database successfully!</p>";
    
    // Check if clients table exists
    $result = $conn->query("SHOW TABLES LIKE 'clients'");
    if ($result->num_rows === 0) {
        throw new Exception("❌ 'clients' table does not exist");
    }
    
    echo "<p style='color: green;'>✅ 'clients' table exists</p>";
    
    // Show table structure
    echo "<h3>Table Structure:</h3>";
    $result = $conn->query("DESCRIBE clients");
    if ($result) {
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['Field']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Type']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Null']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Key']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Default'] ?? 'NULL') . "</td>";
            echo "<td>" . htmlspecialchars($row['Extra']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // Show sample data (first 5 rows)
    echo "<h3>Sample Data (first 5 rows):</h3>";
    $result = $conn->query("SELECT * FROM clients LIMIT 5");
    if ($result && $result->num_rows > 0) {
        echo "<table border='1' cellpadding='5'>";
        // Header
        echo "<tr>";
        while ($field = $result->fetch_field()) {
            echo "<th>" . htmlspecialchars($field->name) . "</th>";
        }
        echo "</tr>";
        
        // Data
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            foreach ($row as $value) {
                echo "<td>" . htmlspecialchars(substr($value, 0, 50)) . (strlen($value) > 50 ? '...' : '') . "</td>";
            }
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>No records found in 'clients' table</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>{$e->getMessage()}</p>";
    
    // Try to create the table if it doesn't exist
    if (strpos($e->getMessage(), "Table 'fastfood.clients' doesn't exist") !== false) {
        echo "<h3>Attempting to create 'clients' table...</h3>";
        try {
            $conn = new mysqli($config['host'], $config['username'], $config['password']);
            $sql = file_get_contents(__DIR__ . '/create_clients_table.sql');
            if ($conn->multi_query($sql)) {
                echo "<p style='color: green;'>✅ Successfully created 'clients' table</p>";
            } else {
                throw new Exception("Failed to create table: " . $conn->error);
            }
        } catch (Exception $e) {
            echo "<p style='color: red;'>❌ Failed to create table: " . $e->getMessage() . "</p>";
        }
    }
}

echo "<h3>PHP Info:</h3>";
echo "<p>PHP Version: " . phpversion() . "</p>";
echo "<p>MySQLi Extension: " . (extension_loaded('mysqli') ? '✅ Loaded' : '❌ Not loaded') . "</p>";

// Show last 10 lines of error log if it exists
$error_log = ini_get('error_log');
echo "<h3>Error Log ($error_log):</h3>";
if (file_exists($error_log)) {
    $log = `tail -n 20 "$error_log"`;
    echo "<pre>" . htmlspecialchars($log) . "</pre>";
} else {
    echo "<p>Error log file not found at: " . htmlspecialchars($error_log) . "</p>";
}
?>
