CREATE DATABASE IF NOT EXISTS hotel_management;
USE hotel_management;

CREATE TABLE users (
    id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'user') DEFAULT 'user',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    is_deleted TINYINT(1) DEFAULT 0
);

CREATE TABLE room_types (
    id INT AUTO_INCREMENT PRIMARY KEY,
    type_name VARCHAR(50) UNIQUE NOT NULL,
    description TEXT
);

    CREATE TABLE rooms (
    id INT AUTO_INCREMENT PRIMARY KEY,
    room_type_id INT,
    price DECIMAL(10,2),
    image VARCHAR(255),
    availability TINYINT(1),
    max_guests INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (room_type_id) REFERENCES room_types(id)
);

CREATE TABLE payment_methods (
    id INT AUTO_INCREMENT PRIMARY KEY,
    method_name VARCHAR(50) UNIQUE NOT NULL
);

CREATE TABLE bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    room_id INT NOT NULL,
    check_in DATE NOT NULL,
    check_out DATE NOT NULL,
    status ENUM('pending', 'confirmed', 'cancelled') DEFAULT 'pending',
    payment_method_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE CASCADE,
    FOREIGN KEY (payment_method_id) REFERENCES payment_methods(id)
);

INSERT INTO users (full_name, email, password, role, is_deleted)
VALUES (
    'admin',                          
    'admin@example.com',
    '$2y$10$Wz3R5TxGyz7XfX6c4Kdrd.0aW6VGQbVbRYUI5qD0R9PHZn7vwLtB6',  
    'admin',
    0
);

INSERT INTO room_types (type_name, description) VALUES
('Single Room', 'A small room with one bed'),
('Double Room', 'A room with two beds'),
('Deluxe Room', 'Spacious room with premium amenities');

INSERT INTO payment_methods (method_name) VALUES
('Cash'),
('Credit Card'),
('Gcash'),
('Paymaya');

UPDATE users SET is_deleted = 0 WHERE is_deleted = 1;
