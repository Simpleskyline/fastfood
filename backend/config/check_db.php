<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection
try {
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "fastfood";

    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    echo "<h2>Database Connection: OK</h2>";

    // Check if clients table exists
    $tables = $conn->query("SHOW TABLES LIKE 'clients'");
    
    if ($tables->num_rows === 0) {
        echo "<div style='color:red;'>ERROR: 'clients' table does not exist in the database.</div>";
    } else {
        echo "<h3>Table 'clients' exists</h3>";
        
        // Show table structure
        $result = $conn->query("DESCRIBE clients");
        echo "<h4>Table Structure:</h4>";
        echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
        echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
        while($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['Field']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Type']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Null']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Key']) . "</td>";
            echo "<td>" . (is_null($row['Default']) ? 'NULL' : htmlspecialchars($row['Default'])) . "</td>";
            echo "<td>" . htmlspecialchars($row['Extra']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        // Show sample data (first 5 rows)
        $users = $conn->query("SELECT ID, Username, Email, Role, FirstName, LastName FROM clients LIMIT 5");
        if ($users->num_rows > 0) {
            echo "<h4>Sample Users (first 5):</h4>";
            echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
            $first = true;
            while($row = $users->fetch_assoc()) {
                if ($first) {
                    echo "<tr>";
                    foreach(array_keys($row) as $key) {
                        echo "<th>" . htmlspecialchars($key) . "</th>";
                    }
                    echo "</tr>";
                    $first = false;
                }
                echo "<tr>";
                foreach($row as $value) {
                    echo "<td>" . htmlspecialchars($value) . "</td>";
                }
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<div style='color:red;'>No users found in the clients table.</div>";
        }
    }
    
    $conn->close();
    
} catch (Exception $e) {
    echo "<div style='color:red;'>Error: " . htmlspecialchars($e->getMessage()) . "</div>";
}
?>
