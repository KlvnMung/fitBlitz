<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once "header.php";

// Check if user is logged in
if (!isset($_SESSION['user'])) {
    // Redirect to login page if not logged in
    header("Location: login.php");
    exit();
}


// Display the user's profile if a specific user is selected
if (isset($_GET['view'])) {
    $view = sanitizeString($_GET['view']);

    if ($view == $user) {
        $name = "your";
    } else {
        $name = $view;
    }

    echo "<div class='members-container'>";
echo "<h3>Members</h3>";

    // echo "<h3>$name's profile</h3>";
    // showProfile($pdo, $view); // Uncomment this once the function is working

    //echo "<a href='messages.php?view=$view&r=$randstr'>View $name's messages</a>";
    
    
}

// Handle follow request
if (isset($_GET['add'])) {
    $add = sanitizeString($_GET['add']);
    
    // Check if user is already followed
    $result = queryMysql($pdo, "SELECT * FROM friends WHERE user = ? AND friend = ?", [$add, $user]);
    
    if ($result && $result->rowCount() == 0) {
        // Add the user to friends if not followed yet
        queryMysql($pdo, "INSERT INTO friends (user, friend) VALUES (?, ?)", [$add, $user]);
    }
}

// Handle unfollow request
if (isset($_GET['remove'])) {
    $remove = sanitizeString($_GET['remove']);
    
    // Remove the user from friends
    queryMysql($pdo, "DELETE FROM friends WHERE user = ? AND friend = ?", [$remove, $user]);
}

// Fetch and display all members
$result = queryMysql($pdo, "SELECT user FROM members ORDER BY user");

if ($result) {
    $num = $result->rowCount();

    echo "<ul>";
    if ($num > 0) {
        while ($row = $result->fetch()) {
            if ($row['user'] == $user) continue; // Skip current user
            
            $username = htmlspecialchars($row['user']);
            echo "<li><a href='members.php?view=$username&r=$randstr'>$username</a>";

            // Check if current user is following this member
            $result1 = queryMysql($pdo, "SELECT * FROM friends WHERE user = ? AND friend = ?", [$username, $user]);
            $t1 = $result1 ? $result1->rowCount() : 0;

            // Check if this member is following the current user
            $result2 = queryMysql($pdo, "SELECT * FROM friends WHERE user = ? AND friend = ?", [$user, $username]);
            $t2 = $result2 ? $result2->rowCount() : 0;

            $follow = "Follow";

            // Display the follow status
            if ($t1 + $t2 > 0) {
                echo " &harr; Mutual friends";
            } elseif ($t1) {
                echo " &larr; You are following";
            } elseif ($t2) {
                echo " &rarr; Is following you";
                $follow = "Reciprocate";
            }

            // Follow or unfollow link
            if ($t1 == 0) {
                echo " <a href='members.php?add=$username&r=$randstr'>$follow</a>";
            } else {
                echo " <a href='members.php?remove=$username&r=$randstr'>Unfollow</a>";
            }

            echo "</li>";
        }
    } else {
        echo "<li>No members found.</li>";
    }
    echo "</ul>";
} else {
    echo "Error fetching members list.";
}

echo "</div>";
require_once 'footer.php';
?>
