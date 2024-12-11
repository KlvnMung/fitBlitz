<?php //calory_calc.php
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

$openai = $factory->make(['api_key' => $_ENV['OPENAI_API_KEY']]);

// Enabling error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Ensure user is logged in
if (!isset($_SESSION['user'])) {
    die("User not logged in. Please log in to continue.");
}

// Retrieve user ID from the session
$user = $_SESSION['user'];

// Initializations
$errorMessage = '';
$fitnessAdvice = '';
$foodData = null;



// Handle deletion
if (isset($_POST['delete'])) {
    $delete_id = $_POST['delete_id'];
    $stmt = $pdo->prepare("DELETE FROM food_products WHERE id = ? AND user = ?");
    $stmt->execute([$delete_id, $user]);
    header("Location: calory_calc.php");
    exit();
}

// Handle search functionality
if (isset($_GET['search'])) {
    $searchTerm = sanitizeString($_GET['search']);
    $apiUrl = "https://world.openfoodfacts.org/cgi/search.pl?search_terms=" . urlencode($searchTerm) . "&search_simple=1&action=process&json=1";
    $apiResponse = file_get_contents($apiUrl);
    $foodData = json_decode($apiResponse, true);

    if (!$foodData || !isset($foodData['products']) || empty($foodData['products'])) {
        $errorMessage = "No results found for \"$searchTerm\". Please try a different search term.";
    }
}

// Handle adding item to the database
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

// Handle BMR Calculation and Fitness Advice
if (isset($_POST['calculate_bmr'])) {
    $weight = sanitizeString($_POST['weight']);
    $height = sanitizeString($_POST['height']);
    $age = sanitizeString($_POST['age']);
    $gender = sanitizeString($_POST['gender']);
    $activityLevel = sanitizeString($_POST['activity_level']);

    // Calculate BMR
    if ($gender == 'male') {
        $bmr = (10 * $weight) + (6.25 * $height) - (5 * $age) + 5;
    } else {
        $bmr = (10 * $weight) + (6.25 * $height) - (5 * $age) - 161;
    }

    // Determine activity multiplier
    switch ($activityLevel) {
        case 'sedentary':
            $activityMultiplier = 1.2;
            break;
        case 'lightly_active':
            $activityMultiplier = 1.375;
            break;
        case 'moderately_active':
            $activityMultiplier = 1.55;
            break;
        case 'very_active':
            $activityMultiplier = 1.725;
            break;
        case 'super_active':
            $activityMultiplier = 1.9;
            break;
        default:
            $activityMultiplier = 1.2; // Default to sedentary
    }

    // Calculate TDEE
    $tdee = $bmr * $activityMultiplier;

    // Fetch user's goals from the profile
    $stmt = $pdo->prepare("SELECT goals FROM profiles WHERE user = ?");
    $stmt->execute([$user]);
    $userGoals = $stmt->fetchColumn();

    // Check if the user has goals related to weight loss or gain
    if (strpos($userGoals, 'lose weight') !== false && $dailyTotalCalories > $tdee) {
        $fitnessAdvice = "Warning: Your Total Daily Energy Expenditure (TDEE) of " . number_format($tdee, 2) . " kcal is lower than your intake of " . number_format($dailyTotalCalories, 2) . " kcal. Make sure to maintain a calorie deficit to lose weight.";
    } elseif (strpos($userGoals, 'gain weight') !== false && $dailyTotalCalories < $tdee) {
        $fitnessAdvice = "Warning: Your TDEE of " . number_format($tdee, 2) . " kcal is higher than your intake of " . number_format($dailyTotalCalories, 2) . " kcal. You should consume more calories to gain weight.";
    } else {
        $fitnessAdvice = "Your calorie intake seems to be aligned with your goals. Keep up the good work!";
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
        /* Add some basic styling to the form */
        form {
            max-width: 500px;
            margin: 40px auto;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        label {
            display: block;
            margin-bottom: 10px;
        }
        input[type="number"], input[type="text"], select {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
        }
        button[type="submit"] {
            background-color: #4CAF50;
            color: #fff;
            padding: 10px 20px;
            border: none;
            border-radius: 10px;
            cursor: pointer;
        }
        button[type="submit"]:hover {
            background-color: #3e8e41;
        }
        .search-container {
            width: 100%;
            max-width: 500px;
            margin: 20px auto;
        }
        .search-container form {
            display: flex;
        }
        .search-container input[type="text"] {
            flex: 1;
            padding: 10px;
            border-right: none;
            border-radius: 4px 0 0 4px;
        }
        .search-container button {
            border: none;
            cursor: pointer;
        }
        .search-container button:hover {
            background: #45a049;
        }
        .delete-btn {
            background: none;
            border: none;
            color: #ff4444;
            cursor: pointer;
            padding: 5px;
        }
        .delete-btn:hover {
            color: #cc0000;
        }
         /* Styling tables */
    table {
        width: 100%;
        border-collapse: collapse;
        margin: 20px 0;
        font-size: 1em;
        font-family: Arial, sans-serif;
        background-color: #fff; /* White background for the table */
        text-align: left;
        border: 2px solid #000; /* Black border around the table */
    }

    table th, table td {
        padding: 12px 15px;
        border: 1px solid #000; /* Black borders for cells */
    }

    table th {
        background-color: #b30000; /* Dark red background for header */
        color: #fff; /* White text for header */
    }

    table tr:nth-child(even) {
        background-color: #f9f9f9; /* Light gray for even rows */
    }

    table tr:nth-child(odd) {
        background-color: #fff; /* White for odd rows */
    }

    table tr:hover {
        background-color: #ffe6e6; /* Light red background on hover */
    }

    .delete-btn {
        color: #b30000; /* Red for delete button */
        border: none;
        background: none;
        cursor: pointer;
        padding: 5px;
        font-size: 1em;
    }

    .delete-btn:hover {
        color: #ff0000; /* Brighter red on hover */
    }

    /* Styling the links under the tables */
    .history-link, .activity-link {
        margin-top: 20px;
        text-align: center;
    }

    .history-link a, .activity-link a {
        color: #b30000; /* Dark red for links */
        text-decoration: none;
        font-weight: bold;
        font-size: 1.2em;
    }

    .history-link a:hover, .activity-link a:hover {
        text-decoration: underline;
    }
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
        <div class="search-container">
            <form action="calory_calc.php" method="get">
                <input type="text" placeholder="Search food in database..." name="search">
                <button type="submit"><i class="fas fa-search"></i></button>
            </form>
        </div>

        <!-- Display error message if no results found -->
        <?php if (!empty($errorMessage)): ?>
            <div class="error-message"><?php echo htmlspecialchars($errorMessage); ?></div>
        <?php endif; ?>

        <!-- Display search results -->
        <?php if (isset($foodData) && isset($foodData['products'])): ?>
            <table>
                <tr>
                    <th>Product Name</th>
                    <th>Calories (per 100g)</th>
                    <th>Add</th>
                </tr>
                <?php foreach ($foodData['products'] as $product): ?>
                    <?php
                    $productName = isset($product['product_name']) ? htmlspecialchars($product['product_name']) : 'N/A';
                    $calories = isset($product['nutriments']['energy-kcal_100g']) ? htmlspecialchars($product['nutriments']['energy-kcal_100g']) : 'N/A';
                    ?>
                    <tr>
                        <td><?php echo $productName; ?></td>
                        <td><?php echo $calories; ?></td>
                        <td>
                            <form method="post" action="calory_calc.php">
                                <input type="hidden" name="product_name" value="<?php echo $productName ; ?>">
                                <input type="hidden" name="calories" value="<?php echo $calories; ?>">
                                <input type="number" name="grams_taken" placeholder="Grams" required style="width: 80px;">
                                <button type="submit" name="add_food">Add</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>
        <?php endif; ?>

        <!-- Display graph -->
        <canvas id="calorieChart" width="400" height="400"></canvas>
        <p><strong>Daily Total Calories:</strong> <?php echo number_format($dailyTotalCalories, 2); ?> kcal</p>

        <!-- Display foods taken table -->
        <h3>Today's Entries</h3>
        <table>
            <tr>
                <th>Product Name</th>
                <th>Calories (per 100g)</th>
                <th>Grams Taken</th>
                <th>Total Calories</th>
                <th>Action</th>
            </tr>
            <?php foreach ($entries as $entry): ?>
            <tr>
                <td><?php echo htmlspecialchars($entry['product_name']); ?></td>
                <td><?php echo htmlspecialchars($entry['calories']); ?></td>
                <td><?php echo htmlspecialchars($entry['grams_taken']); ?></td>
                <td><?php echo htmlspecialchars(number_format($entry['total_calories'], 2)); ?></td>
                <td>
                    <form method="post" style="display: inline;">
                        <input type="hidden" name="delete_id" value="<?php echo $entry['id']; ?>">
                        <button type="submit" name="delete" class="delete-btn">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
        <div class="history-link">
            <a href="calory_history.php">View Calorie History</a>
        </div>

        <!-- Display fitness advice -->
        <h3>Fitness Advice</h3>

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


        <p><?php echo $fitnessAdvice; ?></p>

        <!-- BMR and TDEE calculator form -->
        <h3>Calculate BMR and TDEE</h3>
        <form method="post" action="calory_calc.php">
            <label>Weight (kg):
                <input type="number" name="weight" required>
            </label>
            <label>Height (cm):
                <input type="number" name="height" required>
            </label>
            <label>Age (years):
                <input type="number" name="age" required>
            </label>
            <label>Gender:
                <select name="gender" required>
                    <option value="male">Male</option>
                    <option value="female">Female</option>
                </select>
            </label>
            <label>Activity Level:
                <select name="activity_level" required>
                    <option value="sedentary">Sedentary (little or no exercise)</option>
                    <option value="lightly_active">Lightly active (light exercise/sports 1-3 days/week)</option>
                    <option value="moderately_active">Moderately active (moderate exercise/sports 3-5 days/week)</option>
                    <option value="very_active">Very active (hard exercise/sports 6-7 days a week)</option>
                    <option value="super_active">Super active (very hard exercise/sports & physical job or 2x training)</option>
                </select>
            </label>
            <button type="submit" name="calculate_bmr">Calculate BMR and TDEE</button>
        </form>
    </div>
    <div class="activity-link">
    <a href="activity_tracker.php">Go to Activity Tracker</a>
</div>

    <?php
    $chartData = [];
    $chartLabels = [];
    foreach ($entries as $entry) {
        $chartData[] = $entry['total_calories'];
        $chartLabels[] = $entry['product_name'];
    }
    ?>
    <script>
        var ctx = document.getElementById('calorieChart').getContext('2d');
        var myChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode($chartLabels); ?>,
                datasets: [{
                    data: <?php echo json_encode($chartData); ?>,
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.8)',
                        'rgba(54, 162, 235, 0.8)',
                        'rgba(255, 206, 86, 0.8)',
                        'rgba(75, 192, 192 , 0.8)',
                        'rgba(153, 102, 255, 0.8)',
                        'rgba(255, 159, 64, 0.8)'
                    ],
                    borderColor: [
                        'rgba(255, 99, 132, 1)',
                        'rgba(54, 162, 235, 1)',
                        'rgba(255, 206, 86, 1)',
                        'rgba(75, 192, 192, 1)',
                        'rgba(153, 102, 255, 1)',
                        'rgba(255, 159, 64, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    title: {
                        display: true,
                        text: 'Calorie Distribution'
                    }
                }
            }
        });
        
        function toggleChat() {
    const chatWindow = document.getElementById('chatbot-window');
    if (chatWindow.style.display === 'block') {
        chatWindow.style.display = 'none';
    } else {
        chatWindow.style.display = 'block';
    }
}

    </script>

    <?php
    require_once 'footer.php';
    ?>
</body>
</html>