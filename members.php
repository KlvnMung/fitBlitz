<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
// session_start();
require_once "header.php";
require_once "setup.php";

// Ensure user is logged in
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$user = $_SESSION['user'];


// Handle follow request
if (isset($_GET['add'])) {
    $add = sanitizeString($_GET['add']);

    // Check if user is already followed
    $stmt = $pdo->prepare("SELECT * FROM friends WHERE user = ? AND friend = ?");
    $stmt->execute([$user, $add]);

    if ($stmt->rowCount() == 0) {
        // Add friend
        $stmt = $pdo->prepare("INSERT INTO friends (user, friend) VALUES (?, ?)");
        $stmt->execute([$user, $add]);
    }

    // Redirect to refresh the page and show the updated status
    header("Location: members.php");
    exit();
}

// Handle unfollow request
if (isset($_GET['remove'])) {
    $remove = sanitizeString($_GET['remove']);

    $stmt = $pdo->prepare("DELETE FROM friends WHERE user = ? AND friend = ?");
    $stmt->execute([$user, $remove]);

    // Redirect to refresh the page
    header("Location: members.php");
    exit();
}

// Fetch members list
$stmt = $pdo->query("SELECT user FROM members ORDER BY user");

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Members</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="members-container">
        <h3>Members</h3>
        <ul>
            <?php
            while ($row = $stmt->fetch()) {
                $username = htmlspecialchars($row['user']);
                if ($username == $user) continue; // Skip current user

                echo "<li><a href='profile.php?view=$username'>$username</a>";

                // Check follow status
                $stmt1 = $pdo->prepare("SELECT * FROM friends WHERE user = ? AND friend = ?");
                $stmt1->execute([$user, $username]);
                $following = $stmt1->rowCount();

                $stmt2 = $pdo->prepare("SELECT * FROM friends WHERE user = ? AND friend = ?");
                $stmt2->execute([$username, $user]);
                $follower = $stmt2->rowCount();

                if ($following && $follower) {
                    echo " &harr; Mutual friends";
                } elseif ($following) {
                    echo " &larr; You are following";
                } elseif ($follower) {
                    echo " &rarr; Is following you";
                }

                // Display follow/unfollow button
                if (!$following) {
                    echo " <a href='members.php?add=$username' class='follow-btn'>Follow</a>";
                } else {
                    echo " <a href='members.php?remove=$username' class='unfollow-btn'>Unfollow</a>";
                }

                echo "</li>";
            }
            ?>
        </ul>
    </div>
    <script>
        // Optional: Add AJAX for instant updates (if needed)
    </script>
        <?php
    require_once 'footer.php';
    ?>
</body>
</html>
