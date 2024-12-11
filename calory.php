<?php
// calory_calc.php
include_once "header.php";
include_once "function.php";
require_once 'vendor/autoload.php'; // Composer autoloader for OpenAI and dotenv

use Dotenv\Dotenv;
use OpenAI\Factory;

// Load environment variables
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Retrieve OpenAI API key from .env file
$openaiApiKey = $_ENV['OPENAI_API_KEY'];

$factory = new Factory();
$openai = $factory->make(['api_key' => $openaiApiKey]);

// Start session and ensure user is logged in
session_start();
if (!isset($_SESSION['user'])) {
    die("User not logged in. Please log in to continue.");
}

// Retrieve user ID
$user = $_SESSION['user'];

// Initialize variables
$errorMessage = $fitnessAdvice = $chatbotReply = '';
$foodData = null;

// Handle deletion
if (isset($_POST['delete'])) {
    $delete_id = $_POST['delete_id'];
    $stmt = $pdo->prepare("DELETE FROM food_products WHERE id = ? AND user = ?");
    $stmt->execute([$delete_id, $user]);
    header("Location: calory_calc.php");
    exit();
}

// Handle food search
if (isset($_GET['search'])) {
    $searchTerm = sanitizeString($_GET['search']);
    $apiUrl = "https://world.openfoodfacts.org/cgi/search.pl?search_terms=" . urlencode($searchTerm) . "&search_simple=1&action=process&json=1";
    $apiResponse = file_get_contents($apiUrl);
    $foodData = json_decode($apiResponse, true);

    if (!$foodData || !isset($foodData['products']) || empty($foodData['products'])) {
        $errorMessage = "No results found for \"$searchTerm\". Please try a different search term.";
    }
}

// Handle adding food
if (isset($_POST['add_food'])) {
    $productName = $_POST['product_name'];
    $calories = $_POST['calories'];
    $gramsTaken = $_POST['grams_taken'];
    $totalCalories = ($calories * $gramsTaken) / 100;
    $date = date('Y-m-d');

    $stmt = $pdo->prepare("INSERT INTO food_products (user, product_name, calories, grams_taken, total_calories, date)
                           VALUES (:user, :product_name, :calories, :grams_taken, :total_calories, :date)");
    $stmt->execute([
        ':user' => $user,
        ':product_name' => $productName,
        ':calories' => $calories,
        ':grams_taken' => $gramsTaken,
        ':total_calories' => $totalCalories,
        ':date' => $date
    ]);
    header("Location: calory_calc.php");
    exit();
}

// Fetch daily entries
$dateToday = date('Y-m-d');
$stmt = $pdo->prepare("SELECT id, product_name, calories, grams_taken, total_calories FROM food_products WHERE date = :date AND user = :user");
$stmt->execute([':date' => $dateToday, ':user' => $user]);
$entries = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate daily total
$dailyTotalCalories = 0;
foreach ($entries as $entry) {
    $dailyTotalCalories += $entry['total_calories'];
}

// Handle chatbot queries
if (isset($_POST['ask_chatbot'])) {
    $query = sanitizeString($_POST['chatbot_query']);
    if (empty($query)) {
        $chatbotReply = "Please enter a question.";
    }
    try {
        $response = $openai->chat()->create([
            'model' => 'gpt-3.5-turbo',
            'messages' => [
                ['role' => 'system', 'content' => 'You are a helpful assistant providing advice on fitness, nutrition, and health.'],
                ['role' => 'user', 'content' => $query],
            ],
        ]);
        $chatbotReply = $response['choices'][0]['message']['content'];
    } catch (Exception $e) {
        $chatbotReply = "Sorry, I encountered an error: " . $e->getMessage();
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calorie Calculator</title>
    <link rel="stylesheet" href="styles.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        /* Styling Chatbot */
        #chatbot-icon {
            position: fixed;
            bottom: 20px;
            right: 20px;
            width: 60px;
            height: 60px;
            background-color:whitesmoke;
            color: #ff4d4d;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.2);
            cursor: pointer;
            z-index: 1000;
        }

        #chatbot-icon i {
            font-size: 24px;
        }

        #chatbot-window {
            position: fixed;
            bottom: 90px;
            right: 20px;
            width: 300px;
            max-height: 500px;
            background: white;
            border: 1px solid #ff4d4d;
            border-radius: 10px;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.2);
            display: none;
            flex-direction: column;
            z-index: 1000;
        }

        #chatbot-header {
            background: #007bff;
            color: white;
            padding: 10px;
            border-top-left-radius: 10px;
            border-top-right-radius: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        #chatbot-body {
            padding: 10px;
            flex-grow: 1;
            overflow-y: auto;
        }

        #chatbot-input {
            display: flex;
            padding: 10px;
            border-top: 1px solid #ccc;
            background: #f9f9f9;
        }

        #chatbot-input textarea {
            flex: 1;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 5px;
            resize: none;
        }

        #chatbot-input button {
            margin-left: 10px;
            padding: 10px;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        #chatbot-input button:hover {
            background: #0056b3;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Existing Calorie Calculator Content -->

        <!-- Chatbot Icon -->
        <div id="chatbot-icon" onclick="toggleChat()">
            <i class="fas fa-comment"></i>
        </div>

        <!-- Chatbot Window -->
        <div id="chatbot-window">
            <div id="chatbot-header">
                <h4>Ask Me Anything</h4>
                <p>Fitness, Nutrition, and Health</p>
                <button onclick="toggleChat()">X</button>
            </div>
            <div id="chatbot-body">
                <div id="chatbot-messages">
                    <?php if (!empty($chatbotReply)): ?>
                        <div class="chatbot-response">
                            <p><strong>Chatbot:</strong> <?php echo nl2br(htmlspecialchars($chatbotReply)); ?></p>
                        </div>
                    <?php endif; ?>
                </div>
                <p class="disclaimer">*I can make mistakes. My data is based on articles from the internet.</p>
            </div>
            <div id="chatbot-input">
                <form id="chatbot-form" method="POST" action="calory_calc.php">
                    <textarea id="chatbot-query" name="chatbot_query" placeholder="Type your question here..." required></textarea>
                    <button type="submit" name="ask_chatbot">Send</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        function toggleChat() {
            const chatWindow = document.getElementById('chatbot-window');
            chatWindow.style.display = chatWindow.style.display === 'block' ? 'none' : 'block';
        }
    </script>
</body>
</html>
