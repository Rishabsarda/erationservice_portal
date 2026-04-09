-- E-Ration Portal Database Schema and Sample Data

CREATE DATABASE IF NOT EXISTS erationportal;
USE erationportal;

-- 1. admin (shopkeeper)
CREATE TABLE IF NOT EXISTS admin (
    admin_id INT PRIMARY KEY AUTO_INCREMENT,
    admin_name VARCHAR(100) NOT NULL,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL
);

-- 2. users (citizens)
CREATE TABLE IF NOT EXISTS users (
    user_id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    ration_card_no VARCHAR(20) UNIQUE NOT NULL,
    mobile_no VARCHAR(15),
    address VARCHAR(255),
    village VARCHAR(100),
    family_members INT NOT NULL,
    password VARCHAR(255) NOT NULL,
    card_type ENUM('APL', 'BPL', 'AAY') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 3. ration_shop
CREATE TABLE IF NOT EXISTS ration_shop (
    shop_id INT PRIMARY KEY AUTO_INCREMENT,
    shop_name VARCHAR(100) NOT NULL,
    shop_address VARCHAR(255),
    village VARCHAR(100),
    shop_timings VARCHAR(100),
    shopkeeper_name VARCHAR(100),
    mobile_no VARCHAR(15),
    is_open TINYINT(1) DEFAULT 0
);

-- 4. ration_stock
CREATE TABLE IF NOT EXISTS ration_stock (
    stock_id INT PRIMARY KEY AUTO_INCREMENT,
    shop_id INT,
    rice_qty INT DEFAULT 0,
    wheat_qty INT DEFAULT 0,
    sugar_qty INT DEFAULT 0,
    kerosene_qty INT DEFAULT 0,
    last_updated DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (shop_id) REFERENCES ration_shop(shop_id) ON DELETE CASCADE
);

-- 5. ration_distribution
CREATE TABLE IF NOT EXISTS ration_distribution (
    distribution_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    shop_id INT,
    rice_given INT DEFAULT 0,
    wheat_given INT DEFAULT 0,
    sugar_given INT DEFAULT 0,
    kerosene_given INT DEFAULT 0,
    distribution_date DATE NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (shop_id) REFERENCES ration_shop(shop_id) ON DELETE CASCADE
);

-- 6. complaints
CREATE TABLE IF NOT EXISTS complaints (
    complaint_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    complaint_text TEXT NOT NULL,
    complaint_date DATE NOT NULL,
    status ENUM('Pending', 'Resolved') DEFAULT 'Pending',
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- 7. slot_bookings
CREATE TABLE IF NOT EXISTS slot_bookings (
    booking_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    shop_id INT,
    slot_date DATE NOT NULL,
    time_slot VARCHAR(50) NOT NULL,
    booked_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (shop_id) REFERENCES ration_shop(shop_id) ON DELETE CASCADE
);

-- SAMPLE DATA --

-- Insert Shopkeeper Admin (password is 'admin123' hashed)
INSERT INTO admin (admin_name, username, password) VALUES 
('Ramesh Kumar', 'admin', '$2y$10$247Em8Xq.TRgiEQYBwte2OW5Ih3e50h5NfFfLuWAvB1jzVVU16oLa');

-- Insert Ration Shop
INSERT INTO ration_shop (shop_name, shop_address, village, shop_timings, shopkeeper_name, mobile_no, is_open) VALUES 
('Govt FPS - 001', 'Main Market', 'Rampur', '09:00 AM - 05:00 PM', 'Ramesh Kumar', '9876543210', 0);

-- Insert Initial Ration Stock for the shop
INSERT INTO ration_stock (shop_id, rice_qty, wheat_qty, sugar_qty, kerosene_qty, last_updated) VALUES 
(1, 500, 300, 100, 50, NOW());

-- Insert Sample Citizens (password is 'password' hashed)
INSERT INTO users (name, ration_card_no, mobile_no, address, village, family_members, password, card_type) VALUES 
('Suresh Das', 'RC1234567890', '9988776655', 'House No 12, West Lane', 'Rampur', 4, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'BPL'),
('Amit Patel', 'RC0987654321', '8877665544', 'House No 45, East Lane', 'Rampur', 2, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'AAY'),
('Priya Singh', 'RC1122334455', '7766554433', 'House No 8', 'Rampur', 6, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'APL');

-- Insert Sample Complaint
INSERT INTO complaints (user_id, complaint_text, complaint_date, status) VALUES 
(1, 'Rice quality is not good this month.', CURDATE() - INTERVAL 2 DAY, 'Pending');

-- Insert Sample Slot Booking for Today (to test the new dashboard feature)
INSERT INTO slot_bookings (user_id, shop_id, slot_date, time_slot) VALUES 
(1, 1, CURDATE(), '09:00 AM - 11:00 AM');
