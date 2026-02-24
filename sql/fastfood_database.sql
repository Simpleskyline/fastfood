-- ============================================================
-- Skyline Treats – Full Database Schema v3.0
-- Features: Google OAuth, password reset, combos, pizza/coffee/
--           milkshake flavours, delivery, admin analytics
-- ============================================================

CREATE DATABASE IF NOT EXISTS fastfood
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE fastfood;

-- ============================================================
-- 1. ROLES
-- ============================================================
CREATE TABLE IF NOT EXISTS roles (
    id         TINYINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name       VARCHAR(30) NOT NULL UNIQUE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT IGNORE INTO roles (name) VALUES ('customer'), ('admin');

-- ============================================================
-- 2. USERS  (Google OAuth + password reset tokens)
-- ============================================================
CREATE TABLE IF NOT EXISTS users (
    id                 INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    role_id            TINYINT UNSIGNED NOT NULL DEFAULT 1,
    first_name         VARCHAR(60)  NOT NULL,
    last_name          VARCHAR(60)  NOT NULL DEFAULT '',
    username           VARCHAR(60)  NOT NULL UNIQUE,
    email              VARCHAR(120) NOT NULL UNIQUE,
    password_hash      VARCHAR(255) DEFAULT NULL,
    phone              VARCHAR(20)  DEFAULT NULL,
    location           VARCHAR(255) DEFAULT NULL,
    avatar_url         VARCHAR(500) DEFAULT NULL,
    google_id          VARCHAR(100) DEFAULT NULL,
    auth_provider      ENUM('local','google') NOT NULL DEFAULT 'local',
    reset_token        VARCHAR(128) DEFAULT NULL,
    reset_token_expiry DATETIME    DEFAULT NULL,
    email_verified     BOOLEAN      NOT NULL DEFAULT FALSE,
    is_active          BOOLEAN      NOT NULL DEFAULT TRUE,
    created_at         DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at         DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_users_role FOREIGN KEY (role_id) REFERENCES roles(id),
    INDEX idx_users_email    (email),
    INDEX idx_users_username (username),
    INDEX idx_users_role     (role_id),
    INDEX idx_users_google   (google_id),
    INDEX idx_users_reset    (reset_token)
) ENGINE=InnoDB;

-- ============================================================
-- 3. CATEGORIES
-- ============================================================
CREATE TABLE IF NOT EXISTS categories (
    id         SMALLINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name       VARCHAR(60)  NOT NULL UNIQUE,
    slug       VARCHAR(60)  NOT NULL UNIQUE,
    icon       VARCHAR(10)  DEFAULT '🍽️',
    sort_order TINYINT      NOT NULL DEFAULT 0,
    created_at DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT IGNORE INTO categories (name, slug, icon, sort_order) VALUES
  ('Mains',       'mains',       '🍔', 1),
  ('Sides',       'sides',       '🍟', 2),
  ('Snacks',      'snacks',      '🥪', 3),
  ('Drinks',      'drinks',      '🥤', 4),
  ('Milkshakes',  'milkshakes',  '🥛', 5),
  ('Fresh Juice', 'fresh-juice', '🍊', 6),
  ('Combos',      'combos',      '🎁', 7),
  ('Coffee',      'coffee',      '☕', 8),
  ('Tea',         'tea',         '🍵', 9),
  ('Pizza',       'pizza',       '🍕', 10);

-- ============================================================
-- 4. FOOD ITEMS
-- variants: JSON array [{label, price_add}]
-- has_sugar_option: for coffee/tea
-- combo_items: JSON array of food_item_ids making the combo
-- ============================================================
CREATE TABLE IF NOT EXISTS food_items (
    id               INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    category_id      SMALLINT UNSIGNED NOT NULL,
    name             VARCHAR(100) NOT NULL,
    description      TEXT         DEFAULT NULL,
    price            DECIMAL(10,2) NOT NULL,
    image_url        VARCHAR(500) DEFAULT NULL,
    variants         JSON         DEFAULT NULL,
    has_sugar_option BOOLEAN      NOT NULL DEFAULT FALSE,
    combo_items      JSON         DEFAULT NULL,
    is_featured      BOOLEAN      NOT NULL DEFAULT FALSE,
    is_active        BOOLEAN      NOT NULL DEFAULT TRUE,
    created_at       DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at       DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_food_category FOREIGN KEY (category_id) REFERENCES categories(id),
    INDEX idx_food_category (category_id),
    INDEX idx_food_active   (is_active),
    INDEX idx_food_featured (is_featured)
) ENGINE=InnoDB;

-- Mains
INSERT IGNORE INTO food_items (category_id, name, description, price, image_url, is_featured) VALUES
  (1, 'Burger',       'Juicy double-patty beef burger with signature sauce, fresh lettuce and cheese', 350.00, 'https://www.kitchensanctuary.com/wp-content/uploads/2021/05/Double-Cheeseburger-square-FS-42.jpg', TRUE),
  (1, 'Chicken',      'Crispy fried chicken with secret seasoned coating', 400.00, 'https://hips.hearstapps.com/hmg-prod/images/roast-chicken-recipe-2-66b231ac9a8fb.jpg?resize=1200:*', FALSE),
  (1, 'Shawarma',     'Slow-roasted meat wrapped in flatbread with garlic sauce and veggies', 350.00, 'https://www.munatycooking.com/wp-content/uploads/2023/12/chicken-shawarma-image-feature-2023.jpg', FALSE),
  (1, 'Cheese Wraps', 'Grilled cheese-filled wraps packed with flavour and crunch', 280.00, 'https://life-in-the-lofthouse.com/wp-content/uploads/2022/08/Grilled-Cheeseburger-Wraps282-500x500.jpg', FALSE);

-- Pizza with flavours (cat 10)
INSERT IGNORE INTO food_items (category_id, name, description, price, image_url, variants, is_featured) VALUES
  (10, 'Margherita Pizza', 'Classic tomato sauce, fresh mozzarella, basil', 500.00, 'https://images.unsplash.com/photo-1574071318508-1cdbab80d002?w=600', NULL, TRUE),
  (10, 'Pepperoni Pizza',  'Loaded with pepperoni and melted cheese', 600.00, 'https://images.unsplash.com/photo-1565299624946-b28f40a0ae38?w=600', NULL, FALSE),
  (10, 'BBQ Chicken Pizza','Smoky BBQ sauce, grilled chicken, red onion', 650.00, 'https://images.unsplash.com/photo-1513104890138-7c749659a591?w=600', NULL, FALSE),
  (10, 'Veggie Supreme',   'Roasted peppers, mushrooms, olives, spinach', 550.00, 'https://images.unsplash.com/photo-1571997478779-2adcbbe9ab2f?w=600', NULL, FALSE),
  (10, 'Meat Lovers Pizza','Beef, sausage, bacon, ham — the ultimate meat feast', 700.00, 'https://images.unsplash.com/photo-1628840042765-356cda07504e?w=600', NULL, FALSE);

-- Sides
INSERT IGNORE INTO food_items (category_id, name, description, price, image_url) VALUES
  (2, 'Fries',   'Golden crispy seasoned french fries', 150.00, 'https://www.allrecipes.com/thmb/8_B6OD1w6h1V0zPi8KAGzD41Kzs=/1500x0/filters:no_upscale():max_bytes(150000):strip_icc()/50223-homemade-crispy-seasoned-french-fries-VAT-Beauty-4x3-789ecb2eaed34d6e879b9a93dd56a50a.jpg'),
  (2, 'Chapati', 'Freshly made soft layered East African flatbread', 30.00, 'https://toasterding.com/wp-content/uploads/2024/04/Screenshot-2025-04-16-at-12.46.56.webp');

-- Snacks
INSERT IGNORE INTO food_items (category_id, name, description, price, image_url) VALUES
  (3, 'Kebab',    'Spiced minced beef skewers grilled over charcoal', 80.00,  'https://dukamajuu.com/wp-content/uploads/2024/06/Fm-Beef-Kebabs-160G_2.jpg'),
  (3, 'Egg Rolex','A classic street egg omelette rolled in fresh chapati', 100.00, 'https://www.thefooddictator.com/wp-content/uploads/2019/09/rolex1-907x1024.jpg'),
  (3, 'Smokies',  'Bite-sized smoky sausage bites, great street snack', 40.00,  'https://littlesunnykitchen.com/wp-content/uploads/2020/11/Crockpot-Little-Smokies-10.jpg'),
  (3, 'Sausages', 'Grilled beef sausages served hot with ketchup', 50.00,  'https://cuetopiatexas.com/wp-content/uploads/2015/12/Beef-Texas-Sausage-1.jpg'),
  (3, 'Samosas',  'Crispy triangle pastries with spiced potatoes or beef', 50.00,  'https://www.awesomecuisine.com/wp-content/uploads/2014/11/vegetable-samosa.jpg'),
  (3, 'Bhajia',   'Deep-fried spiced potato fritters — crispy outside', 170.00, 'https://keeshaskitchen.com/wp-content/uploads/2023/11/KENYAN-BHAJIAS-2.jpg');

-- Drinks
INSERT IGNORE INTO food_items (category_id, name, description, price, image_url) VALUES
  (4, 'Soda',  'Chilled carbonated soda — Fanta, Coke, Sprite & more', 80.00, 'https://media.istockphoto.com/id/537699621/photo/fanta-drink-in-can-on-ice-isolated-on-white-background.jpg?s=612x612&w=0&k=20&c=SYrfyTjTukaR6_IsWusmOX-2-ZjCF5UyuvFZEAqTChs='),
  (4, 'Water', 'Chilled mineral water — the perfect companion', 50.00, 'https://olkeriliquor.com/wp-content/uploads/2023/06/DASANI-500ML-MINERAL-WATER.jpg');

-- Milkshakes with flavours (cat 5)
INSERT IGNORE INTO food_items (category_id, name, description, price, image_url, variants) VALUES
  (5, 'Milkshake', 'Thick creamy handcrafted milkshake — pick your flavour',
   350.00,
   'https://images.unsplash.com/photo-1568901346375-23c9450c58cd?w=600',
   '[{"label":"Chocolate","price_add":0},{"label":"Vanilla","price_add":0},{"label":"Strawberry","price_add":0},{"label":"Mango","price_add":0},{"label":"Oreo","price_add":50},{"label":"Banana","price_add":0},{"label":"Caramel","price_add":30}]');

-- Fresh Juice
INSERT IGNORE INTO food_items (category_id, name, description, price, image_url) VALUES
  (6, 'Fresh Juice', 'Freshly squeezed — mango, passion, watermelon & more', 120.00, 'https://www.vfi.co.ke/wp-content/uploads/2020/02/Fresh-Juice-1-1024x988.jpg');

-- Coffee with varieties (cat 8)
INSERT IGNORE INTO food_items (category_id, name, description, price, image_url, variants, has_sugar_option) VALUES
  (8, 'Espresso',    'Strong pure espresso shot, rich and bold', 100.00, 'https://images.unsplash.com/photo-1510591509098-f4fdc6d0ff04?w=600', NULL, TRUE),
  (8, 'Americano',   'Espresso diluted with hot water, smooth and clean', 120.00, 'https://images.unsplash.com/photo-1521302080334-4bebac2763a6?w=600', NULL, TRUE),
  (8, 'Cappuccino',  'Espresso with equal parts steamed and frothed milk', 150.00, 'https://images.unsplash.com/photo-1572442388796-11668a67e53d?w=600', NULL, TRUE),
  (8, 'Latte',       'Espresso with generous steamed milk, mild and creamy', 160.00, 'https://images.unsplash.com/photo-1561882468-9110e03e0f78?w=600', NULL, TRUE),
  (8, 'Mocha',       'Espresso, chocolate, and steamed milk — a sweet treat', 180.00, 'https://images.unsplash.com/photo-1578314675249-a6910f80cc4e?w=600', NULL, TRUE),
  (8, 'Cold Brew',   'Smooth cold-brewed Kenyan coffee, served over ice', 200.00, 'https://images.unsplash.com/photo-1461023058943-07fcbe16d735?w=600',
   '[{"label":"Original","price_add":0},{"label":"Vanilla Cold Brew","price_add":30},{"label":"Salted Caramel Cold Brew","price_add":40}]', FALSE);

-- Tea with varieties (cat 9)
INSERT IGNORE INTO food_items (category_id, name, description, price, image_url, variants, has_sugar_option) VALUES
  (9, 'African Chai',     'Spiced milk tea brewed the East African way — warm and comforting', 50.00, 'https://www.teaforturmeric.com/wp-content/uploads/2021/11/Masala-Chai-Tea-Recipe-Card.jpg', NULL, TRUE),
  (9, 'Green Tea',        'Light refreshing green tea, served hot or iced', 80.00, 'https://images.unsplash.com/photo-1556679343-c7306c1976bc?w=600',
   '[{"label":"Plain","price_add":0},{"label":"Lemon & Ginger","price_add":20},{"label":"Mint","price_add":20},{"label":"Honey","price_add":30}]', TRUE),
  (9, 'Black Tea',        'Classic strong black tea, served with milk on the side', 60.00, 'https://images.unsplash.com/photo-1544787219-7f47ccb76574?w=600', NULL, TRUE),
  (9, 'Chamomile Tea',    'Calming chamomile floral tea, perfect to wind down', 90.00, 'https://images.unsplash.com/photo-1517942616069-6f5a3ce0dfcb?w=600', NULL, TRUE),
  (9, 'Ginger Lemon Tea', 'Zesty ginger and lemon tea — great for immunity', 80.00, 'https://images.unsplash.com/photo-1571934811356-5cc061b6821f?w=600', NULL, TRUE);

-- Combos (cat 7) — combo_items JSON references other food item IDs (approximate)
INSERT IGNORE INTO food_items (category_id, name, description, price, image_url, combo_items, is_featured) VALUES
  (7, 'Burger Combo',  'Burger + Fries + Soda — the classic combo deal', 500.00,
   'https://images.unsplash.com/photo-1594212699903-ec8a3eca50f5?w=600',
   '[1,11,15]', TRUE),
  (7, 'Chicken Combo', 'Crispy Chicken + Fries + Water — a satisfying meal', 520.00,
   'https://images.unsplash.com/photo-1562967914-608f82629710?w=600',
   '[2,11,16]', FALSE),
  (7, 'Shawarma Combo','Shawarma + Chapati + Soda — the wrap lovers deal', 450.00,
   'https://images.unsplash.com/photo-1599487488170-d11ec9c172f0?w=600',
   '[3,12,15]', FALSE),
  (7, 'Snack Box',     'Kebab + Samosas + Bhajia + Soda — ultimate snack platter', 300.00,
   'https://images.unsplash.com/photo-1626645738196-c2a7c87a8f58?w=600',
   '[7,11,13,15]', TRUE),
  (7, 'Pizza Party',   'Any Pizza + 2 Sodas — great for sharing', 680.00,
   'https://images.unsplash.com/photo-1513104890138-7c749659a591?w=600',
   '[5,15,15]', FALSE),
  (7, 'Morning Boost', 'Egg Rolex + Chai + Fresh Juice — perfect breakfast', 220.00,
   'https://images.unsplash.com/photo-1504754524776-8f4f37790ca0?w=600',
   '[8,26,25]', FALSE);

-- ============================================================
-- 5. ORDERS  (with delivery support)
-- ============================================================
CREATE TABLE IF NOT EXISTS orders (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id         INT UNSIGNED NOT NULL,
    total_amount    DECIMAL(10,2) NOT NULL,
    status          ENUM('pending','confirmed','preparing','ready','out_for_delivery','delivered','cancelled')
                    NOT NULL DEFAULT 'pending',
    -- Delivery fields
    delivery_type   ENUM('pickup','delivery') NOT NULL DEFAULT 'pickup',
    delivery_address TEXT         DEFAULT NULL,
    delivery_lat    DECIMAL(10,7) DEFAULT NULL,
    delivery_lng    DECIMAL(10,7) DEFAULT NULL,
    delivery_distance_km DECIMAL(6,2) DEFAULT NULL,
    delivery_fee    DECIMAL(8,2) DEFAULT 0.00,
    notes           TEXT         DEFAULT NULL,
    created_at      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_orders_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE RESTRICT,
    INDEX idx_orders_user      (user_id),
    INDEX idx_orders_status    (status),
    INDEX idx_orders_created   (created_at),
    INDEX idx_orders_delivery  (delivery_type)
) ENGINE=InnoDB;

-- ============================================================
-- 6. ORDER ITEMS  (variant choice stored as text)
-- ============================================================
CREATE TABLE IF NOT EXISTS order_items (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id     INT UNSIGNED NOT NULL,
    food_item_id INT UNSIGNED NOT NULL,
    quantity     SMALLINT UNSIGNED NOT NULL DEFAULT 1,
    unit_price   DECIMAL(10,2) NOT NULL,
    variant      VARCHAR(100)  DEFAULT NULL,  -- chosen flavour/variant
    sugar_option VARCHAR(20)   DEFAULT NULL,  -- 'sugared' | 'unsugared' | null
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
    id                INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id          INT UNSIGNED NOT NULL,
    method            ENUM('M-Pesa','Card','Crypto') NOT NULL,
    amount            DECIMAL(10,2) NOT NULL,
    status            ENUM('pending','completed','failed','refunded') NOT NULL DEFAULT 'pending',
    reference         VARCHAR(120) DEFAULT NULL,
    provider_response JSON         DEFAULT NULL,
    created_at        DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at        DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_payments_order FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE RESTRICT,
    INDEX idx_payments_order  (order_id),
    INDEX idx_payments_status (status),
    INDEX idx_payments_method (method)
) ENGINE=InnoDB;

-- ============================================================
-- 8. SESSIONS
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
-- 9. API LOGS
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
-- 10. RATE LIMITS
-- ============================================================
CREATE TABLE IF NOT EXISTS rate_limits (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    ip_address   VARCHAR(45)  NOT NULL,
    action       VARCHAR(60)  NOT NULL,
    attempts     SMALLINT     NOT NULL DEFAULT 1,
    window_start DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,

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
-- 12. PASSWORD RESET TOKENS (standalone table for cleaner mgmt)
-- ============================================================
CREATE TABLE IF NOT EXISTS password_resets (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id    INT UNSIGNED NOT NULL,
    email      VARCHAR(120) NOT NULL,
    token      VARCHAR(128) NOT NULL UNIQUE,
    used       BOOLEAN NOT NULL DEFAULT FALSE,
    expires_at DATETIME NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_pr_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_pr_token   (token),
    INDEX idx_pr_email   (email),
    INDEX idx_pr_expires (expires_at)
) ENGINE=InnoDB;

-- ============================================================
-- 13. ANALYTICS VIEWS
-- ============================================================

CREATE OR REPLACE VIEW vw_daily_revenue AS
    SELECT
        DATE(o.created_at)       AS order_date,
        COUNT(DISTINCT o.id)     AS total_orders,
        SUM(o.total_amount)      AS total_revenue,
        SUM(o.delivery_fee)      AS total_delivery_fees,
        COUNT(DISTINCT o.user_id) AS unique_customers
    FROM orders o
    WHERE o.status NOT IN ('cancelled')
    GROUP BY DATE(o.created_at);

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

CREATE OR REPLACE VIEW vw_user_summary AS
    SELECT
        u.id,
        CONCAT(u.first_name, ' ', u.last_name) AS full_name,
        u.email,
        u.auth_provider,
        COUNT(DISTINCT o.id)              AS total_orders,
        COALESCE(SUM(o.total_amount), 0)  AS total_spent,
        MAX(o.created_at)                 AS last_order_at
    FROM users u
    LEFT JOIN orders o ON o.user_id = u.id AND o.status != 'cancelled'
    GROUP BY u.id, full_name, u.email, u.auth_provider;

CREATE OR REPLACE VIEW vw_payment_summary AS
    SELECT
        method,
        status,
        COUNT(*)        AS count,
        SUM(amount)     AS total_amount
    FROM payments
    GROUP BY method, status;

CREATE OR REPLACE VIEW vw_delivery_summary AS
    SELECT
        delivery_type,
        COUNT(*)                         AS total_orders,
        AVG(delivery_distance_km)        AS avg_distance_km,
        SUM(delivery_fee)                AS total_delivery_revenue,
        AVG(delivery_fee)                AS avg_delivery_fee
    FROM orders
    WHERE status NOT IN ('cancelled')
    GROUP BY delivery_type;

-- ============================================================
-- 14. STORED PROCEDURE: place_order (updated for delivery)
-- ============================================================
DROP PROCEDURE IF EXISTS place_order;

DELIMITER $$

CREATE PROCEDURE place_order(
    IN  p_user_id       INT UNSIGNED,
    IN  p_items_json    JSON,
    IN  p_delivery_type VARCHAR(20),
    IN  p_delivery_addr TEXT,
    IN  p_delivery_lat  DECIMAL(10,7),
    IN  p_delivery_lng  DECIMAL(10,7),
    IN  p_delivery_km   DECIMAL(6,2),
    IN  p_delivery_fee  DECIMAL(8,2),
    OUT p_order_id      INT UNSIGNED,
    OUT p_total         DECIMAL(10,2),
    OUT p_error         VARCHAR(255)
)
BEGIN
    DECLARE v_food_id  INT UNSIGNED;
    DECLARE v_qty      SMALLINT;
    DECLARE v_price    DECIMAL(10,2);
    DECLARE v_running  DECIMAL(10,2) DEFAULT 0;
    DECLARE v_idx      INT DEFAULT 0;
    DECLARE v_count    INT;
    DECLARE v_found    TINYINT;

    SET p_error = NULL;
    SELECT JSON_LENGTH(p_items_json) INTO v_count;

    IF v_count = 0 THEN
        SET p_error = 'Cart is empty';
        LEAVE place_order;
    END IF;

    START TRANSACTION;

    INSERT INTO orders (user_id, total_amount, status, delivery_type, delivery_address,
                        delivery_lat, delivery_lng, delivery_distance_km, delivery_fee)
    VALUES (p_user_id, 0, 'pending', COALESCE(p_delivery_type,'pickup'),
            p_delivery_addr, p_delivery_lat, p_delivery_lng, p_delivery_km, COALESCE(p_delivery_fee,0));
    SET p_order_id = LAST_INSERT_ID();

    WHILE v_idx < v_count DO
        SET v_food_id = JSON_UNQUOTE(JSON_EXTRACT(p_items_json, CONCAT('$[', v_idx, '].food_id')));
        SET v_qty     = JSON_UNQUOTE(JSON_EXTRACT(p_items_json, CONCAT('$[', v_idx, '].quantity')));

        SELECT COUNT(*), COALESCE(price, 0)
        INTO v_found, v_price
        FROM food_items
        WHERE id = v_food_id AND is_active = 1;

        IF v_found = 0 THEN
            ROLLBACK;
            SET p_error = CONCAT('Food item not found: ', v_food_id);
            LEAVE place_order;
        END IF;

        SET v_running = v_running + (v_price * v_qty);

        INSERT INTO order_items (order_id, food_item_id, quantity, unit_price)
        VALUES (p_order_id, v_food_id, v_qty, v_price);

        SET v_idx = v_idx + 1;
    END WHILE;

    SET v_running = v_running + COALESCE(p_delivery_fee, 0);
    UPDATE orders SET total_amount = v_running WHERE id = p_order_id;
    SET p_total = v_running;

    COMMIT;
END$$

DELIMITER ;
