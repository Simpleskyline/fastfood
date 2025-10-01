<?php
/**
 * Database Setup Script
 * Run this file once to create all necessary tables
 * Access via: http://localhost/fastfood/setup_database.php
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

$servername = "localhost";
$username = "root";
$password = "";

// Connect to MySQL server (without selecting database first)
$conn = new mysqli($servername, $username, $password);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "<h2>FastFood Database Setup</h2>";
echo "<pre>";

// Create database
$sql = "CREATE DATABASE IF NOT EXISTS fastfood";
if ($conn->query($sql) === TRUE) {
    echo "✓ Database 'fastfood' created or already exists\n";
} else {
    echo "✗ Error creating database: " . $conn->error . "\n";
}

// Select database
$conn->select_db("fastfood");

// Create clients table
$sql = "CREATE TABLE IF NOT EXISTS clients (
    id INT AUTO_INCREMENT PRIMARY KEY,
    FirstName VARCHAR(100) NOT NULL,
    LastName VARCHAR(100) NOT NULL,
    Username VARCHAR(50) NOT NULL UNIQUE,
    Email VARCHAR(100) NOT NULL UNIQUE,
    Password VARCHAR(255) NOT NULL,
    Role VARCHAR(20) DEFAULT 'customer',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_email (Email),
    INDEX idx_username (Username)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if ($conn->query($sql) === TRUE) {
    echo "✓ Table 'clients' created successfully\n";
} else {
    echo "✗ Error creating clients table: " . $conn->error . "\n";
}

// Create items table
$sql = "CREATE TABLE IF NOT EXISTS items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    available TINYINT(1) DEFAULT 1,
    category VARCHAR(50),
    description TEXT,
    image_url VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if ($conn->query($sql) === TRUE) {
    echo "✓ Table 'items' created successfully\n";
} else {
    echo "✗ Error creating items table: " . $conn->error . "\n";
}

// Create orders table
$sql = "CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    items TEXT NOT NULL,
    total DECIMAL(10, 2) NOT NULL,
    status VARCHAR(50) DEFAULT 'Pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES clients(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if ($conn->query($sql) === TRUE) {
    echo "✓ Table 'orders' created successfully\n";
} else {
    echo "✗ Error creating orders table: " . $conn->error . "\n";
}

// Create payments table
$sql = "CREATE TABLE IF NOT EXISTS payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    payment_method VARCHAR(50) NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    status VARCHAR(50) DEFAULT 'Pending',
    transaction_id VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if ($conn->query($sql) === TRUE) {
    echo "✓ Table 'payments' created successfully\n";
} else {
    echo "✗ Error creating payments table: " . $conn->error . "\n";
}

// Check if items table is empty and insert sample data
$result = $conn->query("SELECT COUNT(*) as count FROM items");
$row = $result->fetch_assoc();

if ($row['count'] == 0) {
    echo "\n--- Inserting sample menu items ---\n";
    
    $items = [
        ['Classic Burger', 8.99, 'burger', 'Juicy beef patty with lettuce, tomato, and special sauce'],
        ['Cheese Burger', 9.99, 'burger', 'Classic burger with melted cheddar cheese'],
        ['Chicken Burger', 9.49, 'burger', 'Crispy chicken breast with mayo and pickles'],
        ['Pepperoni Pizza', 12.99, 'pizza', 'Classic pepperoni with mozzarella cheese'],
        ['Margherita Pizza', 10.99, 'pizza', 'Fresh tomatoes, mozzarella, and basil'],
        ['BBQ Chicken Pizza', 13.99, 'pizza', 'BBQ sauce, grilled chicken, and red onions'],
        ['Chicken Wings', 7.99, 'chicken', '8 pieces of crispy chicken wings'],
        ['Chicken Tenders', 8.49, 'chicken', '5 pieces of breaded chicken tenders'],
        ['French Fries', 3.99, 'fries', 'Crispy golden french fries'],
        ['Cheese Fries', 4.99, 'fries', 'Fries topped with melted cheese'],
        ['Coca Cola', 1.99, 'soda', 'Classic Coca Cola'],
        ['Sprite', 1.99, 'soda', 'Refreshing lemon-lime soda'],
        ['Orange Juice', 2.99, 'juice', 'Fresh squeezed orange juice'],
        ['Apple Juice', 2.99, 'juice', 'Pure apple juice'],
        ['Chocolate Milkshake', 4.99, 'milkshake', 'Rich chocolate milkshake'],
        ['Vanilla Milkshake', 4.99, 'milkshake', 'Creamy vanilla milkshake'],
        ['Bottled Water', 1.49, 'water', 'Pure bottled water']
    ];
    
    $stmt = $conn->prepare("INSERT INTO items (name, price, category, description, available) VALUES (?, ?, ?, ?, 1)");
    
    foreach ($items as $item) {
        $stmt->bind_param("sdss", $item[0], $item[1], $item[2], $item[3]);
        if ($stmt->execute()) {
            echo "  ✓ Added: {$item[0]}\n";
        }
    }
    $stmt->close();
} else {
    echo "\n✓ Items table already has data ({$row['count']} items)\n";
}

// Check if admin user exists
$result = $conn->query("SELECT COUNT(*) as count FROM clients WHERE Role = 'admin'");
$row = $result->fetch_assoc();

if ($row['count'] == 0) {
    echo "\n--- Creating default admin user ---\n";
    // Password: admin123
    $admin_password = password_hash('admin123', PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO clients (FirstName, LastName, Username, Email, Password, Role) VALUES (?, ?, ?, ?, ?, ?)");
    $fname = 'Admin';
    $lname = 'User';
    $uname = 'admin';
    $email = 'admin@fastfood.com';
    $role = 'admin';
    $stmt->bind_param("ssssss", $fname, $lname, $uname, $email, $admin_password, $role);
    
    if ($stmt->execute()) {
        echo "✓ Admin user created\n";
        echo "  Username: admin\n";
        echo "  Email: admin@fastfood.com\n";
        echo "  Password: admin123\n";
    }
    $stmt->close();
} else {
    echo "\n✓ Admin user already exists\n";
}

echo "\n" . str_repeat("=", 50) . "\n";
echo "DATABASE SETUP COMPLETED SUCCESSFULLY!\n";
echo str_repeat("=", 50) . "\n";
echo "\nYou can now:\n";
echo "1. Sign up at: http://localhost/fastfood/auth.html\n";
echo "2. Or login with admin credentials above\n";
echo "\n⚠️  For security, consider deleting this setup file after use.\n";
echo "</pre>";

$conn->close();
?>
