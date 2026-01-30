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

    // Get all clients
    $result = $conn->query("SELECT ID, Username, Email, Role, FirstName, LastName, CreatedAt FROM clients");
    
    echo "<h2>clients in Database:</h2>";
    
    if ($result->num_rows > 0) {
        echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
        echo "<tr><th>ID</th><th>Username</th><th>Email</th><th>Name</th><th>Role</th><th>Created</th></tr>";
        
        while($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['ID']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Username']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Email']) . "</td>";
            echo "<td>" . htmlspecialchars($row['FirstName'] . ' ' . $row['LastName']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Role']) . "</td>";
            echo "<td>" . htmlspecialchars($row['CreatedAt']) . "</td>";
            echo "</tr>";
        }
        
        echo "</table>";
    } else {
        echo "No clients found in the database.";
    }
    
    $conn->close();
    
} catch (Exception $e) {
    echo "<div style='color:red;'>Error: " . htmlspecialchars($e->getMessage()) . "</div>";
}
?>
