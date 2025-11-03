-- Drop the database if it exists and create a new one
DROP DATABASE IF EXISTS fastfood;
CREATE DATABASE fastfood;
USE fastfood;

-- Create clients table
CREATE TABLE IF NOT EXISTS clients (
    ID INT AUTO_INCREMENT PRIMARY KEY,
    FirstName VARCHAR(50) NOT NULL,
    LastName VARCHAR(50) NOT NULL,
    Username VARCHAR(50) NOT NULL UNIQUE,
    Email VARCHAR(100) NOT NULL UNIQUE,
    Password VARCHAR(255) NOT NULL,
    Role ENUM('customer', 'admin') DEFAULT 'customer',
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    LastLogin TIMESTAMP NULL DEFAULT NULL,
    INDEX idx_email (Email),
    INDEX idx_username (Username)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert a test admin user (password: admin123)
INSERT INTO clients (FirstName, LastName, Username, Email, Password, Role) 
VALUES ('Admin', 'User', 'admin', 'admin@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Insert a test customer (password: customer123)
INSERT INTO clients (FirstName, LastName, Username, Email, Password) 
VALUES ('John', 'Doe', 'johndoe', 'john@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');
