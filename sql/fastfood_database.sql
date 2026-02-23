-- ============================================================
--  SKYLINE TREATS – FastFood Database
--  Full schema: users, food, orders, payments, analytics
--  Engine: MySQL 8.0+
-- ============================================================

CREATE DATABASE IF NOT EXISTS fastfood
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE fastfood;

-- ============================================================
-- 1. ROLES
-- ============================================================
CREATE TABLE IF NOT EXISTS roles (
    id        TINYINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name      VARCHAR(30) NOT NULL UNIQUE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

INSERT IGNORE INTO roles (name) VALUES ('customer'), ('admin');

-- ============================================================
-- 2. USERS
-- ============================================================
CREATE TABLE IF NOT EXISTS users (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    role_id       TINYINT UNSIGNED NOT NULL DEFAULT 1,
    first_name    VARCHAR(60)  NOT NULL,
    last_name     VARCHAR(60)  NOT NULL,
    username      VARCHAR(60)  NOT NULL UNIQUE,
    email         VARCHAR(120) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    phone         VARCHAR(20)  DEFAULT NULL,
    location      VARCHAR(120) DEFAULT NULL,
    is_active     BOOLEAN      NOT NULL DEFAULT TRUE,
    created_at    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_users_role FOREIGN KEY (role_id) REFERENCES roles(id),
    INDEX idx_users_email    (email),
    INDEX idx_users_username (username),
    INDEX idx_users_role     (role_id)
) ENGINE=InnoDB;

-- ============================================================
-- 3. CATEGORIES
-- ============================================================
CREATE TABLE IF NOT EXISTS categories (
    id         SMALLINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name       VARCHAR(60)  NOT NULL UNIQUE,
    slug       VARCHAR(60)  NOT NULL UNIQUE,
    sort_order TINYINT      NOT NULL DEFAULT 0,
    created_at DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT IGNORE INTO categories (name, slug, sort_order) VALUES
  ('Mains',     'mains',     1),
  ('Sides',     'sides',     2),
  ('Snacks',    'snacks',    3),
  ('Drinks',    'drinks',    4),
  ('Milkshakes','milkshakes',5),
  ('Fresh Juice','fresh-juice',6);

-- ============================================================
-- 4. FOOD ITEMS
-- ============================================================
CREATE TABLE IF NOT EXISTS food_items (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    category_id SMALLINT UNSIGNED NOT NULL,
    name        VARCHAR(100) NOT NULL,
    description TEXT         DEFAULT NULL,
    price       DECIMAL(10,2) NOT NULL,
    image_url   VARCHAR(255) DEFAULT NULL,
    is_active   BOOLEAN      NOT NULL DEFAULT TRUE,
    created_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_food_category FOREIGN KEY (category_id) REFERENCES categories(id),
    INDEX idx_food_category (category_id),
    INDEX idx_food_active   (is_active)
) ENGINE=InnoDB;

INSERT IGNORE INTO food_items (category_id, name, description, price) VALUES
  (1, 'Burger',       'Juicy double-patty beef burger with signature sauce', 350.00),
  (1, 'Pizza',        'Wood-fired pizza loaded with toppings and mozzarella', 500.00),
  (1, 'Chicken',      'Crispy fried chicken with seasoned coating', 400.00),
  (1, 'Shawarma',     'Grilled meat wrap with garlic sauce and veggies', 350.00),
  (1, 'Cheese Wraps', 'Flour tortilla with cheese, veggies and sauce', 280.00),
  (2, 'Fries',        'Golden crispy seasoned french fries', 150.00),
  (2, 'Chapati',      'Freshly made soft layered flatbread', 30.00),
  (3, 'Kebab',        'Grilled seasoned skewered meat', 80.00),
  (3, 'Egg Rolex',    'Chapati rolled with fried egg and vegetables', 100.00),
  (3, 'Smokies',      'Grilled smokie sausage', 40.00),
  (3, 'Sausages',     'Grilled beef sausage', 50.00),
  (3, 'Samosas',      'Crispy triangular pastry with spiced filling', 50.00),
  (3, 'Bhajia',       'Spiced potato fritters, deep fried', 170.00),
  (4, 'Soda',         'Chilled carbonated soda, various flavours', 80.00),
  (4, 'Water',        'Chilled mineral water', 50.00),
  (4, 'African Tea',  'Spiced Kenyan chai', 50.00),
  (4, 'Coffee',       'Hot brewed coffee', 140.00),
  (5, 'Milkshake',    'Thick creamy milkshake, various flavours', 350.00),
  (6, 'Fresh Juice',  'Freshly squeezed juice', 120.00);

-- ============================================================
-- 5. ORDERS
-- ============================================================
CREATE TABLE IF NOT EXISTS orders (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id      INT UNSIGNED NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    status       ENUM('pending','confirmed','preparing','ready','delivered','cancelled')
                 NOT NULL DEFAULT 'pending',
    notes        TEXT         DEFAULT NULL,
    created_at   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_orders_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE RESTRICT,
    INDEX idx_orders_user      (user_id),
    INDEX idx_orders_status    (status),
    INDEX idx_orders_created   (created_at)
) ENGINE=InnoDB;

-- ============================================================
-- 6. ORDER ITEMS
-- ============================================================
CREATE TABLE IF NOT EXISTS order_items (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id     INT UNSIGNED NOT NULL,
    food_item_id INT UNSIGNED NOT NULL,
    quantity     SMALLINT UNSIGNED NOT NULL DEFAULT 1,
    unit_price   DECIMAL(10,2) NOT NULL,
    line_total   DECIMAL(10,2) GENERATED ALWAYS AS (quantity * unit_price) STORED,

    CONSTRAINT fk_oi_order FOREIGN KEY (order_id)     REFERENCES orders(id)     ON DELETE CASCADE,
    CONSTRAINT fk_oi_food  FOREIGN KEY (food_item_id) REFERENCES food_items(id) ON DELETE RESTRICT,
    INDEX idx_oi_order (order_id),
    INDEX idx_oi_food  (food_item_id)
) ENGINE=InnoDB;

-- ============================================================
-- 7. PAYMENTS
-- ============================================================
CREATE TABLE IF NOT EXISTS payments (
    id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id       INT UNSIGNED NOT NULL,
    method         ENUM('M-Pesa','Card','Crypto') NOT NULL,
    amount         DECIMAL(10,2) NOT NULL,
    status         ENUM('pending','completed','failed','refunded') NOT NULL DEFAULT 'pending',
    reference      VARCHAR(120) DEFAULT NULL  COMMENT 'Transaction ref / wallet address',
    provider_response JSON       DEFAULT NULL COMMENT 'Raw provider payload',
    created_at     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_payments_order FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE RESTRICT,
    INDEX idx_payments_order  (order_id),
    INDEX idx_payments_status (status)
) ENGINE=InnoDB;

-- ============================================================
-- 8. SESSIONS  (server-side session store – optional)
-- ============================================================
CREATE TABLE IF NOT EXISTS sessions (
    id         VARCHAR(128) PRIMARY KEY,
    user_id    INT UNSIGNED NOT NULL,
    ip_address VARCHAR(45)  DEFAULT NULL,
    user_agent TEXT         DEFAULT NULL,
    payload    TEXT         NOT NULL,
    expires_at DATETIME     NOT NULL,
    created_at DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_sessions_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_sessions_user    (user_id),
    INDEX idx_sessions_expires (expires_at)
) ENGINE=InnoDB;

-- ============================================================
-- 9. API LOGS  (every request logged for analytics)
-- ============================================================
CREATE TABLE IF NOT EXISTS api_logs (
    id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id     INT UNSIGNED    DEFAULT NULL,
    method      VARCHAR(10)     NOT NULL,
    endpoint    VARCHAR(255)    NOT NULL,
    status_code SMALLINT        NOT NULL,
    duration_ms SMALLINT        DEFAULT NULL,
    ip_address  VARCHAR(45)     DEFAULT NULL,
    created_at  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_logs_user     (user_id),
    INDEX idx_logs_endpoint (endpoint),
    INDEX idx_logs_created  (created_at)
) ENGINE=InnoDB;

-- ============================================================
-- 10. RATE LIMITS  (login / register throttle)
-- ============================================================
CREATE TABLE IF NOT EXISTS rate_limits (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    ip_address VARCHAR(45)  NOT NULL,
    action     VARCHAR(60)  NOT NULL,
    attempts   SMALLINT     NOT NULL DEFAULT 1,
    window_start DATETIME   NOT NULL DEFAULT CURRENT_TIMESTAMP,

    UNIQUE KEY uk_rl (ip_address, action),
    INDEX idx_rl_window (window_start)
) ENGINE=InnoDB;

-- ============================================================
-- 11. CONTACT MESSAGES
-- ============================================================
CREATE TABLE IF NOT EXISTS contact_messages (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name       VARCHAR(120) NOT NULL,
    email      VARCHAR(120) NOT NULL,
    subject    VARCHAR(200) DEFAULT NULL,
    message    TEXT         NOT NULL,
    is_read    BOOLEAN      NOT NULL DEFAULT FALSE,
    created_at DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_cm_email (email),
    INDEX idx_cm_read  (is_read)
) ENGINE=InnoDB;

-- ============================================================
-- 12. ANALYTICS VIEWS  (pre-built for Python analytics layer)
-- ============================================================

-- Daily revenue summary
CREATE OR REPLACE VIEW vw_daily_revenue AS
    SELECT
        DATE(o.created_at)       AS order_date,
        COUNT(DISTINCT o.id)     AS total_orders,
        SUM(o.total_amount)      AS total_revenue,
        COUNT(DISTINCT o.user_id) AS unique_customers
    FROM orders o
    WHERE o.status NOT IN ('cancelled')
    GROUP BY DATE(o.created_at);

-- Top selling food items
CREATE OR REPLACE VIEW vw_top_items AS
    SELECT
        f.id,
        f.name,
        c.name                   AS category,
        SUM(oi.quantity)         AS units_sold,
        SUM(oi.line_total)       AS revenue
    FROM order_items oi
    JOIN food_items f ON oi.food_item_id = f.id
    JOIN categories c ON f.category_id  = c.id
    JOIN orders o ON oi.order_id = o.id
    WHERE o.status NOT IN ('cancelled')
    GROUP BY f.id, f.name, c.name
    ORDER BY units_sold DESC;

-- User order summary
CREATE OR REPLACE VIEW vw_user_summary AS
    SELECT
        u.id,
        CONCAT(u.first_name, ' ', u.last_name) AS full_name,
        u.email,
        COUNT(DISTINCT o.id)     AS total_orders,
        COALESCE(SUM(o.total_amount), 0) AS total_spent,
        MAX(o.created_at)        AS last_order_at
    FROM users u
    LEFT JOIN orders o ON o.user_id = u.id AND o.status != 'cancelled'
    GROUP BY u.id, full_name, u.email;

-- Payment summary
CREATE OR REPLACE VIEW vw_payment_summary AS
    SELECT
        method,
        status,
        COUNT(*)        AS count,
        SUM(amount)     AS total_amount
    FROM payments
    GROUP BY method, status;

-- ============================================================
-- 13. STORED PROCEDURE: place_order
--     Called by Python backend for atomic order creation
-- ============================================================
DELIMITER $$

CREATE PROCEDURE IF NOT EXISTS place_order(
    IN  p_user_id      INT UNSIGNED,
    IN  p_items_json   JSON,
    OUT p_order_id     INT UNSIGNED,
    OUT p_total        DECIMAL(10,2),
    OUT p_error        VARCHAR(255)
)
BEGIN
    DECLARE v_food_id     INT UNSIGNED;
    DECLARE v_qty         SMALLINT;
    DECLARE v_price       DECIMAL(10,2);
    DECLARE v_line        DECIMAL(10,2);
    DECLARE v_running     DECIMAL(10,2) DEFAULT 0;
    DECLARE v_idx         INT DEFAULT 0;
    DECLARE v_count       INT;
    DECLARE v_found       TINYINT;

    SET p_error = NULL;

    SELECT JSON_LENGTH(p_items_json) INTO v_count;

    IF v_count = 0 THEN
        SET p_error = 'Cart is empty';
        LEAVE;
    END IF;

    START TRANSACTION;

    -- Insert order shell
    INSERT INTO orders (user_id, total_amount, status)
    VALUES (p_user_id, 0, 'pending');
    SET p_order_id = LAST_INSERT_ID();

    -- Loop items
    WHILE v_idx < v_count DO
        SET v_food_id = JSON_UNQUOTE(JSON_EXTRACT(p_items_json, CONCAT('$[', v_idx, '].food_id')));
        SET v_qty     = JSON_UNQUOTE(JSON_EXTRACT(p_items_json, CONCAT('$[', v_idx, '].quantity')));

        -- Price from DB (never trust frontend)
        SELECT COUNT(*), COALESCE(price, 0)
        INTO v_found, v_price
        FROM food_items
        WHERE id = v_food_id AND is_active = 1;

        IF v_found = 0 THEN
            ROLLBACK;
            SET p_error = CONCAT('Food item not found: ', v_food_id);
            LEAVE;
        END IF;

        SET v_line = v_price * v_qty;
        SET v_running = v_running + v_line;

        INSERT INTO order_items (order_id, food_item_id, quantity, unit_price)
        VALUES (p_order_id, v_food_id, v_qty, v_price);

        SET v_idx = v_idx + 1;
    END WHILE;

    -- Update real total
    UPDATE orders SET total_amount = v_running WHERE id = p_order_id;
    SET p_total = v_running;

    COMMIT;
END$$

DELIMITER ;

-- ============================================================
-- Done.
-- ============================================================
