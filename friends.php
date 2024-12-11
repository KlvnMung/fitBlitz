<?php
require_once 'header.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

if (isset($_GET['view'])) $view = sanitizeString($_GET['view']);
else                      $view = $user;

$name1 = $view == $user ? "Your" : "<a href='members.php?view=$view&r=$randstr'>$view</a>'s";
$name2 = $view == $user ? "Your" : "$view's";
$name3 = $view == $user ? "You are" : "$view is";

echo "<div class='friends-container'>";
echo "<h3>$name2 Friends</h3>";

$followers = [];
$following = [];

// Fetch followers
$result = queryMysql($pdo, "SELECT friend FROM friends WHERE user = ?", [$view]);
while ($row = $result->fetch()) {
    $followers[] = $row['friend'];
}

// Fetch following
$result = queryMysql($pdo, "SELECT user FROM friends WHERE friend = ?", [$view]);
while ($row = $result->fetch()) {
    $following[] = $row['user'];
}

// Find mutual friends
$mutual = array_intersect($followers, $following);
$followers = array_diff($followers, $mutual);
$following = array_diff($following, $mutual);

// Display mutual friends
if (count($mutual) > 0) {
    echo "<span class='subhead'>$name2 Mutual Friends</span><ul>";
    foreach ($mutual as $friend) {
        echo "<li><a href='members.php?view=$friend&r=$randstr'>$friend</a> - <a href='private_messages.php?with=$friend&r=$randstr'>Message</a></li>";
    }
    echo "</ul>";
}

// Display followers
if (count($followers) > 0) {
    echo "<span class='subhead'>$name2 Followers</span><ul>";
    foreach ($followers as $follower) {
        echo "<li><a href='members.php?view=$follower&r=$randstr'>$follower</a></li>";
    }
    echo "</ul>";
}

// Display following
if (count($following) > 0) {
    echo "<span class='subhead'>$name3 Following</span><ul>";
    foreach ($following as $followee) {
        echo "<li><a href='members.php?view=$followee&r=$randstr'>$followee</a></li>";
    }
    echo "</ul>";
}

if (empty($mutual) && empty($followers) && empty($following)) {
    echo "<p>You don't have any friends yet!</p>";
}

echo "</div>";
require_once 'footer.php';
?>

