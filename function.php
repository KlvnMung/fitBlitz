<?php
$host='localhost';
$data='fitblitz';
$user='Admin';
$pass='fitBlitz';
$chr='utf8mb4';
$attr="mysql:host=localhost;dbname=$data;port=3306;charset=$chr";

$opts =
  [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
  ];

  //error handling
  try{
      $pdo= new PDO($attr,$user,$pass,$opts);
  }
  catch (\PDOException $e) {
  switch ($e->getCode()) {
      case 1049: // Error code for "Unknown database"
          echo 'Database not found';
          break;
      case 2002: // Error code for "Can't connect to local MySQL server through socket"
          echo 'Cannot connect to the database server';
          break;
      default:
          echo 'An error occurred: ' . $e->getMessage();
          break;
  }
  }

// Define the createTable function with $pdo as a parameter
function createTable($pdo, $name, $query)
{
    $sanitized_name = sanitizeString($name);
    $sanitized_query = sanitizeString($query);

    try {
        $stmt = $pdo->prepare("CREATE TABLE IF NOT EXISTS `$sanitized_name` ($sanitized_query)");
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            return "Table created successfully";
        } else {
            return "Table creation failed";
        }
    } catch (PDOException $e) {
        return "Error creating table: " . $e->getMessage();
    }
}

// Define the queryMysql function with $pdo as a parameter
function queryMysql($pdo, $query, $params = []) {
    try {
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        return $stmt; // Return PDOStatement
    } catch (PDOException $e) {
        // Log or display error
        error_log("Database Error: " . $e->getMessage());
        return false;
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

// Define the sanitizeString function
function sanitizeString($var)
{
    $var = strip_tags($var);
    $var = htmlentities($var);
    return $var;
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
?>