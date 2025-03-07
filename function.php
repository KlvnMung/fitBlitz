<?php
// Load .env file manually without external libraries
function loadEnv($path = __DIR__ . '/.env') {
    if (!file_exists($path)) {
        die(".env file not found!");
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $env = [];

    foreach ($lines as $line) {
        // Ignore comments and empty lines
        if (strpos(trim($line), '#') === 0 || empty(trim($line))) {
            continue;
        }

        // Ensure the line contains an '='
        if (strpos($line, '=') === false) {
            continue; // Skip invalid lines
        }

        list($key, $value) = explode('=', $line, 2);

        // Ensure $value is set and trim it safely
        $env[$key] = isset($value) ? trim($value) : '';
    }

    return $env;
}

$env = loadEnv();

$host = $env['DB_HOST'] ?? 'localhost';
$data = $env['DB_NAME'] ?? 'fitblitz';
$user = $env['DB_USER'] ?? 'root';
$pass = $env['DB_PASS'] ?? '';
$chr  = $env['DB_CHARSET'] ?? 'utf8mb4';
$port = $env['DB_PORT'] ?? '3306';

$attr = "mysql:host=$host;dbname=$data;port=$port;charset=$chr";

$opts = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($attr, $user, $pass, $opts);
} catch (PDOException $e) {
    if ($e->getCode() == 1049) { // Database not found
        echo "Database '$data' not found. Creating it now...<br>";

        try {
            // Connect without specifying the database and create it
            $pdo = new PDO("mysql:host=$host;port=$port;charset=$chr", $user, $pass, $opts);
            $pdo->exec("CREATE DATABASE `$data`");
            echo "Database '$data' created successfully.<br>";

            // Reconnect to the newly created database
            $pdo = new PDO($attr, $user, $pass, $opts);
        } catch (PDOException $e) {
            die("Database creation failed: " . $e->getMessage());
        }
    } else {
        die("Database connection failed: " . $e->getMessage());
    }
}

function createTable($pdo, $name, $query)
{
    try {
        $sql = "CREATE TABLE IF NOT EXISTS `$name` ($query)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        echo "Table '$name' checked/created successfully.<br>";
    } catch (PDOException $e) {
        echo "Error creating table '$name': " . $e->getMessage() . "<br>";
    }
}


function sanitizeString($var)
{
    return htmlspecialchars(strip_tags($var));
}

// Define the queryMysql function with $pdo as a parameter
function queryMysql($pdo, $query, $params = []) {
    try {
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        return $stmt;
    } catch (PDOException $e) {
        die("Database Error: " . $e->getMessage());  // Debugging output
    }
}


// Define the destroySession function
function destroySession()
{
    $_SESSION=array();

    if (session_id() != "" || isset($_COOKIE[session_name()]))
      setcookie(session_name(), '', time()-2592000, '/');

    session_destroy();
}

// Define the showProfile function with $pdo as a parameter
// In function.php, update the showProfile function:

function showProfile($pdo, $user)
{
    if (file_exists("$user.jpg")) {
        echo "<img src='$user.jpg' style='float:left; margin-right: 10px;'>";
    }

    $result = queryMysql($pdo, "SELECT * FROM profiles WHERE user='$user'");

    if ($result->rowCount()) {
        $row = $result->fetch();
        echo "<div class='profile-info'>";
        
        // Display user name as header
        echo "<h2>" . htmlspecialchars($user) . "'s Profile</h2>";
        
        // Display profile information if it exists
        echo "<div class='profile-details'>";
        
        // Age
        if (!empty($row['age'])) {
            echo "<p><strong>Age:</strong> " . htmlspecialchars($row['age']) . " years</p>";
        }
        
        // Weight
        if (!empty($row['weight'])) {
            echo "<p><strong>Weight:</strong> " . htmlspecialchars($row['weight']) . " kg</p>";
        }
        
        // Height
        if (!empty($row['height'])) {
            echo "<p><strong>Height:</strong> " . htmlspecialchars($row['height']) . " cm</p>";
        }
        
        // Medical History
        if (!empty($row['medical_history'])) {
            echo "<div class='profile-section'>";
            echo "<h3>Medical History</h3>";
            echo "<p>" . nl2br(htmlspecialchars($row['medical_history'])) . "</p>";
            echo "</div>";
        }
        
        // Goals
        if (!empty($row['goals'])) {
            echo "<div class='profile-section'>";
            echo "<h3>Fitness Goals</h3>";
            echo "<p>" . nl2br(htmlspecialchars($row['goals'])) . "</p>";
            echo "</div>";
        }
        
        // Personality/Bio
        if (!empty($row['personality'])) {
            echo "<div class='profile-section'>";
            echo "<h3>About Me</h3>";
            echo "<p>" . nl2br(htmlspecialchars($row['personality'])) . "</p>";
            echo "</div>";
        }
        
        echo "</div>"; // Close profile-details
        echo "</div>"; // Close profile-info
        echo "<br style='clear:left;'><br>";
    } else {
        echo "<p>No profile information available yet.</p><br>";
    }
}

function fetchExercisesFromAPI($apiUrl, $apiKey) {
    $options = [
        'http' => [
            'header' => [
                "x-rapidapi-host: exercisedb.p.rapidapi.com",
                "x-rapidapi-key: $apiKey"
            ],
            'method' => 'GET',
        ],
    ];

    $context = stream_context_create($options);
    $response = @file_get_contents($apiUrl, false, $context);

    return $response ? json_decode($response, true) : [];
}

function categorizeExercises($exercises) {
    $groupedExercises = ['machine' => [], 'non-machine' => []];

    foreach ($exercises as $exercise) {
        if (isset($exercise['target'], $exercise['equipment'])) {
            $equipmentType = strtolower($exercise['equipment']);
            if (in_array($equipmentType, ['machine', 'cable', 'smith machine'])) {
                $groupedExercises['machine'][$exercise['target']][] = $exercise;
            } else {
                $groupedExercises['non-machine'][$exercise['target']][] = $exercise;
            }
        }
    }

    return $groupedExercises;
}
// User authentication
function loginUser($pdo, $user, $pass) {
    $stmt = $pdo->prepare("SELECT * FROM members WHERE user = :user");
    $stmt->execute([':user' => $user]);
    $result = $stmt->fetch();

    echo ":User  " . $user . "\n";
    echo "Password: " . $pass . "\n";

    if ($result) {
        echo "User  found: " . $result['user'] . "\n";
        echo "Password: " . $result['pass'] . "\n";
        echo "Input password: " . $pass . "\n";
        if (password_verify($pass, $result['pass'])) {
            echo "Password verified\n";
            return true;
        } else {
            echo "Password not verified\n";
            return false;
        }
    } else {
        echo "User  not found\n";
        return false;
    }
}


// Message handling
function insertMessage($pdo, $user, $view, $text) {
    $stmt = $pdo->prepare("INSERT INTO messages (auth, recip, pm, time, message) VALUES (?, ?, 0, ?, ?)");
    return $stmt->execute([$user, $view, time(), $text]);
}

function deleteMessage($pdo, $messageId, $user) {
    $stmt = $pdo->prepare("DELETE FROM messages WHERE id=? AND auth=?");
    return $stmt->execute([$messageId, $user]);
}

// Calorie tracker
function addCalorieEntry($pdo, $user, $product_name, $calories) {
    $stmt = $pdo->prepare("INSERT INTO food_products (user, product_name, calories, date) VALUES (?, ?, ?, NOW())");
    return $stmt->execute([$user, $product_name, $calories]);
}

function getCalorieEntries($pdo, $user) {
    $stmt = $pdo->prepare("SELECT product_name, calories FROM food_products WHERE user=?");
    $stmt->execute([$user]);
    return $stmt->fetchAll();
}


?>