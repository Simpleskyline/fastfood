-- FastFood Database Setup Script
-- Run this in phpMyAdmin SQL tab or MySQL command line

-- Create database if it doesn't exist
CREATE DATABASE IF NOT EXISTS fastfood;
USE fastfood;

-- 1. Clients/Users Table
CREATE TABLE IF NOT EXISTS clients (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. Items/Menu Table
CREATE TABLE IF NOT EXISTS items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    available TINYINT(1) DEFAULT 1,
    category VARCHAR(50),
    description TEXT,
    image_url VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Orders Table
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    items TEXT NOT NULL,
    total DECIMAL(10, 2) NOT NULL,
    status VARCHAR(50) DEFAULT 'Pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES clients(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. Payments Table
CREATE TABLE IF NOT EXISTS payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    payment_method VARCHAR(50) NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    status VARCHAR(50) DEFAULT 'Pending',
    transaction_id VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert sample menu items
INSERT INTO items (name, price, available, category, description) VALUES
('Classic Burger', 8.99, 1, 'burger', 'Juicy beef patty with lettuce, tomato, and special sauce'),
('Cheese Burger', 9.99, 1, 'burger', 'Classic burger with melted cheddar cheese'),
('Chicken Burger', 9.49, 1, 'burger', 'Crispy chicken breast with mayo and pickles'),
('Pepperoni Pizza', 12.99, 1, 'pizza', 'Classic pepperoni with mozzarella cheese'),
('Margherita Pizza', 10.99, 1, 'pizza', 'Fresh tomatoes, mozzarella, and basil'),
('BBQ Chicken Pizza', 13.99, 1, 'pizza', 'BBQ sauce, grilled chicken, and red onions'),
('Chicken Wings', 7.99, 1, 'chicken', '8 pieces of crispy chicken wings'),
('Chicken Tenders', 8.49, 1, 'chicken', '5 pieces of breaded chicken tenders'),
('French Fries', 3.99, 1, 'fries', 'Crispy golden french fries'),
('Cheese Fries', 4.99, 1, 'fries', 'Fries topped with melted cheese'),
('Coca Cola', 1.99, 1, 'soda', 'Classic Coca Cola'),
('Sprite', 1.99, 1, 'soda', 'Refreshing lemon-lime soda'),
('Orange Juice', 2.99, 1, 'juice', 'Fresh squeezed orange juice'),
('Apple Juice', 2.99, 1, 'juice', 'Pure apple juice'),
('Chocolate Milkshake', 4.99, 1, 'milkshake', 'Rich chocolate milkshake'),
('Vanilla Milkshake', 4.99, 1, 'milkshake', 'Creamy vanilla milkshake'),
('Bottled Water', 1.49, 1, 'water', 'Pure bottled water');

-- Insert a sample admin user (password: admin123)
INSERT INTO clients (FirstName, LastName, Username, Email, Password, Role) VALUES
('Admin', 'User', 'admin', 'admin@fastfood.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Insert a sample customer user (password: customer123)
INSERT INTO clients (FirstName, LastName, Username, Email, Password, Role) VALUES
('John', 'Doe', 'johndoe', 'john@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer');

SELECT 'Database setup completed successfully!' AS message;
