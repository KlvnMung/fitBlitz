<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setting up database...</title>
</head>
<body>
    <h3>Setting up</h3>
    <?php
    require_once 'function.php';
    require_once 'database_setup.php';

    if (!$pdo) {
        die("Database connection failed.");
    }

    createTable($pdo, 'members',
'user VARCHAR(255) PRIMARY KEY,
pass VARCHAR(255),
email VARCHAR(255) NOT NULL,
role ENUM(\'user\', \'admin\') DEFAULT \'user\',
otp VARCHAR(6),
otp_expiration TIMESTAMP');



    createTable($pdo, 'messages',
    'id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    auth VARCHAR(16),
    recip VARCHAR(16),
    pm CHAR(1),
    time INT UNSIGNED,
    message VARCHAR(4096)');

    createTable($pdo, 'friends',
    'user VARCHAR(16),
    friend VARCHAR(16)');

    createTable($pdo, 'profiles',
    'user VARCHAR(16),
    age INT,
    weight FLOAT,
    height INT,
    medical_history TEXT,
    goals TEXT,
    personality TEXT');

    createTable($pdo, 'food_products',
    'id INT AUTO_INCREMENT PRIMARY KEY,
    product_name VARCHAR(255) NOT NULL,
    calories INT NOT NULL, 
    grams_taken INT NOT NULL, 
    total_calories FLOAT NOT NULL, 
    date DATE NOT NULL');

    createTable($pdo, 'articles', 
    'id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255),
    excerpt TEXT,
    image_url VARCHAR(255),
    source_url VARCHAR(255),
    publish_date DATE,
    author VARCHAR(100)');

    createTable($pdo, 'experts',
    'id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    profile_picture VARCHAR(255) NOT NULL,
    credentials TEXT NOT NULL');

    createTable($pdo, 'activities',
    'id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,  
    steps INT,
    calories_burned FLOAT,
    date DATE');

    ?>
    <br>...done;
</body>
</html>
