DROP DATABASE IF EXISTS ecommerce_db;

CREATE DATABASE ecommerce_db;
USE ecommerce_db;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'seller', 'customer', 'delivery') NOT NULL DEFAULT 'customer',
    phone VARCHAR(20),
    address VARCHAR(255),
    status ENUM('active', 'blocked') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE
);

CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    seller_id INT NOT NULL,
    category_id INT,
    name VARCHAR(150) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    stock INT NOT NULL DEFAULT 0,
    image VARCHAR(255) DEFAULT 'no-image.png',
    status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (seller_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
);

CREATE TABLE cart (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    UNIQUE KEY unique_cart_item (customer_id, product_id)
);

CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    shipping_address VARCHAR(255) NOT NULL,
    payment_method ENUM('COD', 'Card') NOT NULL DEFAULT 'COD',
    order_status ENUM('pending', 'confirmed', 'cancelled') NOT NULL DEFAULT 'pending',
    delivery_agent_id INT DEFAULT NULL,
    delivery_status ENUM(
        'unassigned',
        'assigned',
        'accepted',
        'rejected',
        'out_for_delivery',
        'delivered',
        'failed'
    ) NOT NULL DEFAULT 'unassigned',
    delivery_note VARCHAR(255) DEFAULT NULL,
    delivery_otp VARCHAR(10) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (delivery_agent_id) REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NULL,
    seller_id INT NULL,
    product_name VARCHAR(150) NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    item_status ENUM('pending', 'shipped', 'delivered') NOT NULL DEFAULT 'pending',
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL,
    FOREIGN KEY (seller_id) REFERENCES users(id) ON DELETE SET NULL
);

INSERT INTO users (name, email, password, role, phone, address) VALUES
('Admin', 'admin@shopease.com', '$2y$10$ZScV3XypZHKsltKInH1Vaewhvu/WQBmXX4g2brY6vzeKIxsKNTBTO', 'admin', '01700000000', 'Head Office, Dhaka'),
('BD Traders', 'seller@shopease.com', '$2y$10$ZScV3XypZHKsltKInH1Vaewhvu/WQBmXX4g2brY6vzeKIxsKNTBTO', 'seller', '01711111111', 'Gulshan, Dhaka'),
('Alex', 'customer@shopease.com', '$2y$10$ZScV3XypZHKsltKInH1Vaewhvu/WQBmXX4g2brY6vzeKIxsKNTBTO', 'customer', '01722222222', 'Mirpur, Dhaka'),
('Delivery Boy', 'delivery@shopease.com', '$2y$10$ZScV3XypZHKsltKInH1Vaewhvu/WQBmXX4g2brY6vzeKIxsKNTBTO', 'delivery', '01733333333', 'Mohammadpur, Dhaka');

INSERT INTO categories (name) VALUES
('Electronics'),
('Fashion'),
('Home & Living'),
('Books'),
('Sports');

INSERT INTO products (seller_id, category_id, name, description, price, stock, image) VALUES
(2, 1, 'Wireless Bluetooth Headphones', 'Over-ear headphones with 20 hours battery life and noise isolation.', 2499.00, 25, 'no-image.png'),
(2, 1, 'Smart Watch Series X', 'Fitness tracking smartwatch with heart-rate monitor.', 3999.00, 15, 'no-image.png'),
(2, 2, 'Men Casual Cotton Shirt', 'Breathable cotton shirt, available in multiple sizes.', 899.00, 40, 'no-image.png'),
(2, 3, 'Ceramic Coffee Mug Set (6pcs)', 'Elegant ceramic mug set, dishwasher safe.', 1200.00, 30, 'no-image.png'),
(2, 4, 'The Pragmatic Programmer', 'Classic software engineering book.', 1500.00, 10, 'no-image.png');

SELECT 'Database created successfully. Delete operations are configured correctly.' AS status;