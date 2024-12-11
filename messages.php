<?php 
require_once 'header.php';

if (!$loggedin) die("</div></body></html>");

if (isset($_GET['view'])) $view = sanitizeString($_GET['view']);
else                      $view = $user;

if (isset($_POST['text'])) {
    $text = sanitizeString($_POST['text']);
    $pm   = 0; // Always set to public (community)
    $time = time();
    if ($text != "") {
        queryMysql($pdo, "INSERT INTO messages VALUES(NULL, '$user', '$view', '$pm', $time, '$text', 0)");
    }
}

echo "<h3>Community Comments</h3>";

echo <<<_END
  <form method='post' action='messages.php?view=$view&r=$randstr'>
    <div>
      <textarea name='text' placeholder='Type here to leave a comment'></textarea>
    </div>
    <input type='submit' value='Post Comment'>
  </form><br>
_END;

if (isset($_GET['erase'])) {
    $erase = sanitizeString($_GET['erase']);
    queryMysql($pdo, "DELETE FROM messages WHERE id='$erase' AND auth='$user'");
}

$query = "SELECT * FROM messages WHERE pm = 0 ORDER BY time DESC LIMIT 100";
$result = queryMysql($pdo, $query);

while ($row = $result->fetch()) {
    if ($row['pm'] == 0) {
        echo date('M jS \'y g:ia:', $row['time']);
        echo " <a href='messages.php?view=" . $row['auth'] . "&r=$randstr'>" . $row['auth'] . "</a> ";
        echo "commented: &quot;" . $row['message'] . "&quot; ";

        if ($row['auth'] == $user) {
            echo "[<a href='messages.php?view=$view&erase=" . $row['id'] . "&r=$randstr'>erase</a>]";
        }
        echo "<br>";
    }
}

// Function to fetch mutual friends
function getMutualFriends($pdo, $user) {
    $followers = [];
    $following = [];

    // Fetch users who follow the current user
    $result = queryMysql($pdo, "SELECT friend FROM friends WHERE user = ?", [$user]);
    if ($result) {
        while ($row = $result->fetch()) {
            $followers[] = $row['friend'];
        }
    }

    // Fetch users the current user is following
    $result = queryMysql($pdo, "SELECT user FROM friends WHERE friend = ?", [$user]);
    if ($result) {
        while ($row = $result->fetch()) {
            $following[] = $row['user'];
        }
    }

    // Return mutual friends (those who follow each other)
    return array_intersect($followers, $following);
}
require_once 'footer.php';
?>

