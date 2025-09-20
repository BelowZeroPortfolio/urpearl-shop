-- UrPearl Shop Database Setup with Dummy Data
-- Run this in MySQL to create the complete database structure and test data

-- Create database
CREATE DATABASE IF NOT EXISTS urpearl_shop;
USE urpearl_shop;

-- Drop existing tables if they exist (in correct order to handle foreign keys)
DROP TABLE IF EXISTS cart_items;
DROP TABLE IF EXISTS order_items;
DROP TABLE IF EXISTS orders;
DROP TABLE IF EXISTS ratings;
DROP TABLE IF EXISTS notifications;
DROP TABLE IF EXISTS inventory;
DROP TABLE IF EXISTS products;
DROP TABLE IF EXISTS categories;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS failed_jobs;
DROP TABLE IF EXISTS personal_access_tokens;
DROP TABLE IF EXISTS password_reset_tokens;
DROP TABLE IF EXISTS migrations;

-- Migrations table
CREATE TABLE migrations (
    id int unsigned NOT NULL AUTO_INCREMENT,
    migration varchar(255) NOT NULL,
    batch int NOT NULL,
    PRIMARY KEY (id)
);

-- Users table
CREATE TABLE users (
    id bigint unsigned NOT NULL AUTO_INCREMENT,
    name varchar(255) NOT NULL,
    email varchar(255) NOT NULL,
    email_verified_at timestamp NULL DEFAULT NULL,
    password varchar(255) DEFAULT NULL,
    avatar varchar(255) DEFAULT NULL,
    role enum('admin','buyer') NOT NULL DEFAULT 'buyer',
    provider varchar(255) DEFAULT NULL,
    provider_id varchar(255) DEFAULT NULL,
    remember_token varchar(100) DEFAULT NULL,
    created_at timestamp NULL DEFAULT NULL,
    updated_at timestamp NULL DEFAULT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY users_email_unique (email)
);

-- Categories table
CREATE TABLE categories (
    id bigint unsigned NOT NULL AUTO_INCREMENT,
    name varchar(255) NOT NULL,
    slug varchar(255) NOT NULL,
    created_at timestamp NULL DEFAULT NULL,
    updated_at timestamp NULL DEFAULT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY categories_slug_unique (slug)
);

-- Products table
CREATE TABLE products (
    id bigint unsigned NOT NULL AUTO_INCREMENT,
    name varchar(255) NOT NULL,
    slug varchar(255) NOT NULL,
    description text,
    price decimal(10,2) NOT NULL,
    sku varchar(255) NOT NULL,
    category_id bigint unsigned NOT NULL,
    image varchar(255) DEFAULT NULL,
    created_at timestamp NULL DEFAULT NULL,
    updated_at timestamp NULL DEFAULT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY products_slug_unique (slug),
    UNIQUE KEY products_sku_unique (sku),
    KEY products_category_id_foreign (category_id),
    CONSTRAINT products_category_id_foreign FOREIGN KEY (category_id) REFERENCES categories (id) ON DELETE CASCADE
);

-- Inventory table
CREATE TABLE inventory (
    id bigint unsigned NOT NULL AUTO_INCREMENT,
    product_id bigint unsigned NOT NULL,
    quantity int NOT NULL DEFAULT '0',
    low_stock_threshold int NOT NULL DEFAULT '10',
    created_at timestamp NULL DEFAULT NULL,
    updated_at timestamp NULL DEFAULT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY inventory_product_id_unique (product_id),
    CONSTRAINT inventory_product_id_foreign FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE CASCADE
);

-- Cart Items table
CREATE TABLE cart_items (
    id bigint unsigned NOT NULL AUTO_INCREMENT,
    user_id bigint unsigned NOT NULL,
    product_id bigint unsigned NOT NULL,
    quantity int NOT NULL,
    created_at timestamp NULL DEFAULT NULL,
    updated_at timestamp NULL DEFAULT NULL,
    PRIMARY KEY (id),
    KEY cart_items_user_id_foreign (user_id),
    KEY cart_items_product_id_foreign (product_id),
    CONSTRAINT cart_items_user_id_foreign FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE,
    CONSTRAINT cart_items_product_id_foreign FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE CASCADE
);

-- Orders table
CREATE TABLE orders (
    id bigint unsigned NOT NULL AUTO_INCREMENT,
    user_id bigint unsigned NOT NULL,
    order_number varchar(255) NOT NULL,
    status enum('pending','processing','shipped','delivered','cancelled') NOT NULL DEFAULT 'pending',
    total_amount decimal(10,2) NOT NULL,
    shipping_address text NOT NULL,
    payment_method varchar(255) NOT NULL,
    payment_status enum('pending','paid','failed','refunded') NOT NULL DEFAULT 'pending',
    notes text,
    created_at timestamp NULL DEFAULT NULL,
    updated_at timestamp NULL DEFAULT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY orders_order_number_unique (order_number),
    KEY orders_user_id_foreign (user_id),
    CONSTRAINT orders_user_id_foreign FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
);

-- Order Items table
CREATE TABLE order_items (
    id bigint unsigned NOT NULL AUTO_INCREMENT,
    order_id bigint unsigned NOT NULL,
    product_id bigint unsigned NOT NULL,
    quantity int NOT NULL,
    price decimal(10,2) NOT NULL,
    created_at timestamp NULL DEFAULT NULL,
    updated_at timestamp NULL DEFAULT NULL,
    PRIMARY KEY (id),
    KEY order_items_order_id_foreign (order_id),
    KEY order_items_product_id_foreign (product_id),
    CONSTRAINT order_items_order_id_foreign FOREIGN KEY (order_id) REFERENCES orders (id) ON DELETE CASCADE,
    CONSTRAINT order_items_product_id_foreign FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE CASCADE
);

-- Ratings table
CREATE TABLE ratings (
    id bigint unsigned NOT NULL AUTO_INCREMENT,
    user_id bigint unsigned NOT NULL,
    product_id bigint unsigned NOT NULL,
    rating int NOT NULL,
    review text,
    created_at timestamp NULL DEFAULT NULL,
    updated_at timestamp NULL DEFAULT NULL,
    PRIMARY KEY (id),
    KEY ratings_user_id_foreign (user_id),
    KEY ratings_product_id_foreign (product_id),
    CONSTRAINT ratings_user_id_foreign FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE,
    CONSTRAINT ratings_product_id_foreign FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE CASCADE,
    CONSTRAINT ratings_rating_check CHECK ((rating >= 1) and (rating <= 5))
);

-- Notifications table
CREATE TABLE notifications (
    id bigint unsigned NOT NULL AUTO_INCREMENT,
    user_id bigint unsigned NOT NULL,
    title varchar(255) NOT NULL,
    message text NOT NULL,
    type enum('info','success','warning','error') NOT NULL DEFAULT 'info',
    is_read tinyint(1) NOT NULL DEFAULT '0',
    created_at timestamp NULL DEFAULT NULL,
    updated_at timestamp NULL DEFAULT NULL,
    PRIMARY KEY (id),
    KEY notifications_user_id_foreign (user_id),
    CONSTRAINT notifications_user_id_foreign FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
);

-- Password reset tokens table
CREATE TABLE password_reset_tokens (
    email varchar(255) NOT NULL,
    token varchar(255) NOT NULL,
    created_at timestamp NULL DEFAULT NULL,
    PRIMARY KEY (email)
);

-- Personal access tokens table
CREATE TABLE personal_access_tokens (
    id bigint unsigned NOT NULL AUTO_INCREMENT,
    tokenable_type varchar(255) NOT NULL,
    tokenable_id bigint unsigned NOT NULL,
    name varchar(255) NOT NULL,
    token varchar(64) NOT NULL,
    abilities text,
    last_used_at timestamp NULL DEFAULT NULL,
    expires_at timestamp NULL DEFAULT NULL,
    created_at timestamp NULL DEFAULT NULL,
    updated_at timestamp NULL DEFAULT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY personal_access_tokens_token_unique (token),
    KEY personal_access_tokens_tokenable_type_tokenable_id_index (tokenable_type,tokenable_id)
);

-- Failed jobs table
CREATE TABLE failed_jobs (
    id bigint unsigned NOT NULL AUTO_INCREMENT,
    uuid varchar(255) NOT NULL,
    connection text NOT NULL,
    queue text NOT NULL,
    payload longtext NOT NULL,
    exception longtext NOT NULL,
    failed_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY failed_jobs_uuid_unique (uuid)
);

-- Insert migration records
INSERT INTO migrations (migration, batch) VALUES
('2014_10_12_000000_create_users_table', 1),
('2014_10_12_100000_create_password_reset_tokens_table', 1),
('2019_08_19_000000_create_failed_jobs_table', 1),
('2019_12_14_000001_create_personal_access_tokens_table', 1),
('2024_01_01_000001_create_categories_table', 1),
('2024_01_01_000002_create_products_table', 1),
('2024_01_01_000003_create_inventory_table', 1),
('2024_01_01_000004_create_cart_items_table', 1),
('2024_01_01_000005_create_orders_table', 1),
('2024_01_01_000006_create_order_items_table', 1),
('2024_01_01_000007_create_ratings_table', 1),
('2024_01_01_000008_create_notifications_table', 1);

-- Insert dummy data

-- Users (password is 'password' hashed)
INSERT INTO users (id, name, email, email_verified_at, password, role, created_at, updated_at) VALUES
(1, 'Admin User', 'admin@urpearl.com', NOW(), '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', NOW(), NOW()),
(2, 'John Doe', 'john@example.com', NOW(), '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'buyer', NOW(), NOW()),
(3, 'Jane Smith', 'jane@example.com', NOW(), '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'buyer', NOW(), NOW()),
(4, 'Mike Johnson', 'mike@example.com', NOW(), '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'buyer', NOW(), NOW()),
(5, 'Sarah Wilson', 'sarah@example.com', NOW(), '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'buyer', NOW(), NOW());

-- Categories
INSERT INTO categories (id, name, slug, created_at, updated_at) VALUES
(1, 'Pearl Necklaces', 'pearl-necklaces', NOW(), NOW()),
(2, 'Pearl Earrings', 'pearl-earrings', NOW(), NOW()),
(3, 'Pearl Bracelets', 'pearl-bracelets', NOW(), NOW()),
(4, 'Pearl Rings', 'pearl-rings', NOW(), NOW()),
(5, 'Pearl Sets', 'pearl-sets', NOW(), NOW()),
(6, 'Cultured Pearls', 'cultured-pearls', NOW(), NOW()),
(7, 'Freshwater Pearls', 'freshwater-pearls', NOW(), NOW()),
(8, 'Saltwater Pearls', 'saltwater-pearls', NOW(), NOW());

-- Products
INSERT INTO products (id, name, slug, description, price, sku, category_id, created_at, updated_at) VALUES
(1, 'Classic White Pearl Necklace', 'classic-white-pearl-necklace', 'Elegant 18-inch strand of lustrous white freshwater pearls. Perfect for formal occasions and everyday elegance.', 299.99, 'PEARL-1001', 1, NOW(), NOW()),
(2, 'Tahitian Black Pearl Earrings', 'tahitian-black-pearl-earrings', 'Stunning black Tahitian pearl drop earrings with sterling silver settings. A sophisticated choice for any outfit.', 459.99, 'PEARL-1002', 2, NOW(), NOW()),
(3, 'Rose Gold Pearl Bracelet', 'rose-gold-pearl-bracelet', 'Delicate rose gold bracelet featuring 7-8mm cultured pearls. Adjustable length for perfect fit.', 189.99, 'PEARL-1003', 3, NOW(), NOW()),
(4, 'Vintage Pearl Ring', 'vintage-pearl-ring', 'Antique-inspired pearl ring with intricate gold band design. Features a single 9mm cultured pearl.', 149.99, 'PEARL-1004', 4, NOW(), NOW()),
(5, 'Bridal Pearl Set', 'bridal-pearl-set', 'Complete bridal set including necklace, earrings, and bracelet. Perfect for weddings and special occasions.', 799.99, 'PEARL-1005', 5, NOW(), NOW()),
(6, 'Akoya Pearl Strand', 'akoya-pearl-strand', 'Premium Japanese Akoya cultured pearls with exceptional luster. 16-inch strand with 14k gold clasp.', 899.99, 'PEARL-1006', 6, NOW(), NOW()),
(7, 'Freshwater Pearl Choker', 'freshwater-pearl-choker', 'Modern choker style necklace with multiple strands of small freshwater pearls. Contemporary and chic.', 129.99, 'PEARL-1007', 7, NOW(), NOW()),
(8, 'South Sea Pearl Pendant', 'south-sea-pearl-pendant', 'Luxurious South Sea pearl pendant on 18k white gold chain. Features a 12mm golden pearl.', 1299.99, 'PEARL-1008', 8, NOW(), NOW()),
(9, 'Pearl Stud Earrings', 'pearl-stud-earrings', 'Classic pearl stud earrings with 6-7mm cultured pearls. Sterling silver posts with secure backs.', 79.99, 'PEARL-1009', 2, NOW(), NOW()),
(10, 'Baroque Pearl Necklace', 'baroque-pearl-necklace', 'Unique baroque pearl necklace with irregular shaped pearls. Each piece is one-of-a-kind.', 349.99, 'PEARL-1010', 1, NOW(), NOW()),
(11, 'Pearl Tennis Bracelet', 'pearl-tennis-bracelet', 'Elegant tennis bracelet featuring uniform cultured pearls. Secure clasp with safety chain.', 259.99, 'PEARL-1011', 3, NOW(), NOW()),
(12, 'Pearl Cocktail Ring', 'pearl-cocktail-ring', 'Statement cocktail ring with large cultured pearl surrounded by cubic zirconia accents.', 199.99, 'PEARL-1012', 4, NOW(), NOW()),
(13, 'Mother of Pearl Necklace', 'mother-of-pearl-necklace', 'Beautiful mother of pearl disc necklace with adjustable cord. Lightweight and versatile.', 89.99, 'PEARL-1013', 1, NOW(), NOW()),
(14, 'Pearl Drop Earrings', 'pearl-drop-earrings', 'Elegant drop earrings with cascading pearls. Perfect for evening wear and special events.', 169.99, 'PEARL-1014', 2, NOW(), NOW()),
(15, 'Cultured Pearl Charm Bracelet', 'cultured-pearl-charm-bracelet', 'Charming bracelet with pearl and gold charms. Includes heart, star, and flower charms.', 219.99, 'PEARL-1015', 3, NOW(), NOW());

-- Inventory
INSERT INTO inventory (id, product_id, quantity, low_stock_threshold, created_at, updated_at) VALUES
(1, 1, 25, 5, NOW(), NOW()),
(2, 2, 15, 3, NOW(), NOW()),
(3, 3, 30, 8, NOW(), NOW()),
(4, 4, 20, 5, NOW(), NOW()),
(5, 5, 8, 2, NOW(), NOW()),
(6, 6, 12, 3, NOW(), NOW()),
(7, 7, 35, 10, NOW(), NOW()),
(8, 8, 5, 2, NOW(), NOW()),
(9, 9, 50, 15, NOW(), NOW()),
(10, 10, 18, 5, NOW(), NOW()),
(11, 11, 22, 6, NOW(), NOW()),
(12, 12, 14, 4, NOW(), NOW()),
(13, 13, 40, 12, NOW(), NOW()),
(14, 14, 16, 5, NOW(), NOW()),
(15, 15, 28, 8, NOW(), NOW());

-- Sample cart items for testing
INSERT INTO cart_items (id, user_id, product_id, quantity, created_at, updated_at) VALUES
(1, 2, 1, 2, NOW(), NOW()),
(2, 2, 3, 1, NOW(), NOW()),
(3, 3, 2, 1, NOW(), NOW()),
(4, 3, 5, 1, NOW(), NOW()),
(5, 4, 9, 3, NOW(), NOW());

-- Sample orders
INSERT INTO orders (id, user_id, order_number, status, total_amount, shipping_address, payment_method, payment_status, created_at, updated_at) VALUES
(1, 2, 'ORD-2024-001', 'delivered', 489.98, '123 Main St, Anytown, AT 12345', 'stripe', 'paid', DATE_SUB(NOW(), INTERVAL 7 DAY), DATE_SUB(NOW(), INTERVAL 5 DAY)),
(2, 3, 'ORD-2024-002', 'shipped', 1259.98, '456 Oak Ave, Another City, AC 67890', 'stripe', 'paid', DATE_SUB(NOW(), INTERVAL 3 DAY), DATE_SUB(NOW(), INTERVAL 2 DAY)),
(3, 4, 'ORD-2024-003', 'processing', 239.97, '789 Pine Rd, Somewhere, SW 54321', 'stripe', 'paid', DATE_SUB(NOW(), INTERVAL 1 DAY), NOW());

-- Sample order items
INSERT INTO order_items (id, order_id, product_id, quantity, price, created_at, updated_at) VALUES
(1, 1, 1, 1, 299.99, DATE_SUB(NOW(), INTERVAL 7 DAY), DATE_SUB(NOW(), INTERVAL 7 DAY)),
(2, 1, 3, 1, 189.99, DATE_SUB(NOW(), INTERVAL 7 DAY), DATE_SUB(NOW(), INTERVAL 7 DAY)),
(3, 2, 2, 1, 459.99, DATE_SUB(NOW(), INTERVAL 3 DAY), DATE_SUB(NOW(), INTERVAL 3 DAY)),
(4, 2, 5, 1, 799.99, DATE_SUB(NOW(), INTERVAL 3 DAY), DATE_SUB(NOW(), INTERVAL 3 DAY)),
(5, 3, 9, 3, 79.99, DATE_SUB(NOW(), INTERVAL 1 DAY), DATE_SUB(NOW(), INTERVAL 1 DAY));

-- Sample ratings
INSERT INTO ratings (id, user_id, product_id, rating, review, created_at, updated_at) VALUES
(1, 2, 1, 5, 'Beautiful necklace! The pearls have amazing luster and the quality is exceptional.', DATE_SUB(NOW(), INTERVAL 5 DAY), DATE_SUB(NOW(), INTERVAL 5 DAY)),
(2, 3, 2, 4, 'Love these earrings! They are elegant and go with everything. Slightly heavy but worth it.', DATE_SUB(NOW(), INTERVAL 2 DAY), DATE_SUB(NOW(), INTERVAL 2 DAY)),
(3, 4, 9, 5, 'Perfect everyday pearl studs. Great quality for the price!', NOW(), NOW()),
(4, 2, 3, 5, 'The rose gold bracelet is gorgeous! Fits perfectly and looks expensive.', DATE_SUB(NOW(), INTERVAL 4 DAY), DATE_SUB(NOW(), INTERVAL 4 DAY)),
(5, 3, 5, 5, 'Amazing bridal set! Used it for my wedding and received so many compliments.', DATE_SUB(NOW(), INTERVAL 1 DAY), DATE_SUB(NOW(), INTERVAL 1 DAY));

-- Sample notifications
INSERT INTO notifications (id, user_id, title, message, type, is_read, created_at, updated_at) VALUES
(1, 1, 'Low Stock Alert', 'Product "South Sea Pearl Pendant" is running low on stock (5 remaining)', 'warning', 0, NOW(), NOW()),
(2, 1, 'New Order', 'New order #ORD-2024-003 has been placed', 'info', 0, DATE_SUB(NOW(), INTERVAL 1 DAY), DATE_SUB(NOW(), INTERVAL 1 DAY)),
(3, 2, 'Order Shipped', 'Your order #ORD-2024-001 has been shipped', 'success', 1, DATE_SUB(NOW(), INTERVAL 5 DAY), DATE_SUB(NOW(), INTERVAL 4 DAY)),
(4, 3, 'Order Delivered', 'Your order #ORD-2024-002 has been delivered', 'success', 0, DATE_SUB(NOW(), INTERVAL 2 DAY), DATE_SUB(NOW(), INTERVAL 2 DAY));

-- Display summary
SELECT 'Database setup completed successfully!' as Status;
SELECT COUNT(*) as 'Total Users' FROM users;
SELECT COUNT(*) as 'Total Categories' FROM categories;
SELECT COUNT(*) as 'Total Products' FROM products;
SELECT COUNT(*) as 'Total Cart Items' FROM cart_items;
SELECT COUNT(*) as 'Total Orders' FROM orders;
SELECT COUNT(*) as 'Total Ratings' FROM ratings;