<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setting up database...</title>
</head>
<body>
    
    <?php
    require_once 'function.php';

    // Initialize $pdo in setup.php
    $host = 'localhost';
    $dbname = 'fitblitz';
    $username = 'your_username';
    $password = 'Your_pass';
    $charset = 'utf8mb4';

    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=$charset", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
        die("An error occurred: " . $e->getMessage());
    }

    // Use $pdo to create tables
    createTable($pdo, 'members', '
        user VARCHAR(16),
        pass VARCHAR(16),
        INDEX(user(6))
    ');

    createTable($pdo, 'messages', '
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        auth VARCHAR(16),
        recip VARCHAR(16),
        pm CHAR(1),
        time INT UNSIGNED,
        message VARCHAR(4096),
        INDEX(auth(6)),
        INDEX(recip(6))
    ');

    createTable($pdo, 'friends', '
        user VARCHAR(16),
        friend VARCHAR(16),
        INDEX(user(6)),
        INDEX(friend(6))
    ');

    createTable($pdo, 'profiles', '
        user VARCHAR(16),
        age INT,
        weight FLOAT,
        height INT,
        medical_history TEXT,
        goals TEXT,
        personality TEXT,
        INDEX(user(6))
    ');

    createTable($pdo, 'food_products', '
        id INT AUTO_INCREMENT PRIMARY KEY,
        product_name VARCHAR(255) NOT NULL,
        calories INT NOT NULL, 
        grams_taken INT NOT NULL, 
        total_calories FLOAT NOT NULL, 
        date DATE NOT NULL
    ');

    createTable($pdo, 'articles', '
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255),
        excerpt TEXT,
        image_url VARCHAR(255),
        source_url VARCHAR(255),
        publish_date DATE,
        author VARCHAR(100)
    ');

    createTable($pdo, 'experts', '
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        profile_picture VARCHAR(255) NOT NULL,
        credentials TEXT NOT NULL
    ');

    createTable($pdo, 'activities', '
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT,  
        steps INT,
        calories_burned FLOAT,
        date DATE
    ');
    ?>
    
</body>
</html>
