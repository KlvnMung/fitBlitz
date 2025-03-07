<?php
require_once 'function.php'; // Include DB connection function

try {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS members (
            user VARCHAR(255) PRIMARY KEY,
            pass VARCHAR(255) NOT NULL,
            email VARCHAR(255) NOT NULL UNIQUE,
            role ENUM('user', 'admin') DEFAULT 'user',
            otp VARCHAR(6),
            otp_expiration TIMESTAMP
        );

        CREATE TABLE IF NOT EXISTS messages (
            id INT AUTO_INCREMENT PRIMARY KEY,
            auth VARCHAR(255) NOT NULL,
            recip VARCHAR(255) NOT NULL,
            pm TINYINT NOT NULL,
            time INT NOT NULL,
            message TEXT NOT NULL,
            seen TINYINT NOT NULL DEFAULT 0
        );

        CREATE TABLE IF NOT EXISTS calorie_entries (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            food VARCHAR(255) NOT NULL,
            calories INT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE IF NOT EXISTS food_products (
            user VARCHAR(255) NOT NULL,
            product_name VARCHAR(255) NOT NULL,
            calories INT NOT NULL, 
            grams_taken INT NOT NULL, 
            total_calories FLOAT NOT NULL, 
            date DATE NOT NULL,
            PRIMARY KEY (user, product_name),
            FOREIGN KEY (user) REFERENCES members(user)
        );
    ");
    echo "Database setup completed successfully.";
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>