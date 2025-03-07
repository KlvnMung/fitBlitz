<?php
include_once "header.php";
include_once "function.php";

// Start session if not started
// session_start();

// Ensure user is logged in
if (!isset($_SESSION['user'])) {
    die("Error: User not logged in.");
}

$user_id = $_SESSION['user'];

// Fetch daily entries for the specific user
$stmt = $pdo->prepare("SELECT date, SUM(total_calories) AS daily_total_calories FROM food_products WHERE user = ? GROUP BY date ORDER BY date DESC");
$stmt->execute([$user_id]);
$entries = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calorie History</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        .back-button {
            margin: 20px 0;
        }
        .btn-back {
            padding: 10px 20px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .btn-back:hover {
            background-color: #45a049;
        }
        .btn-back i {
            font-size: 16px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="back-button">
            <button onclick="window.history.back()" class="btn-back">
                <i class="fas fa-arrow-left"></i> Back
            </button>
        </div>

        <!-- Display calorie history table -->
        <h3>Calorie History</h3>
        <table>
            <tr>
                <th>Date</th>
                <th>Daily Total Calories</th>
            </tr>
            <?php if (empty($entries)): ?>
            <tr>
                <td colspan="2">No records found.</td>
            </tr>
            <?php else: ?>
            <?php foreach ($entries as $entry): ?>
            <tr>
                <td><?php echo $entry['date']; ?></td>
                <td><?php echo number_format($entry['daily_total_calories'], 2); ?> kcal</td>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
        </table>
    </div>

    <?php
require_once 'footer.php';
?>
</body>
</html>
