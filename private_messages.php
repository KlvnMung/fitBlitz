<?php
require_once 'header.php';

if (!$loggedin) die("</div></body></html>");

if (isset($_POST['recip']) && isset($_POST['text'])) {
    $recip = sanitizeString($_POST['recip']);
    $text  = sanitizeString($_POST['text']);
    $time  = time();
    queryMysql($pdo,"INSERT INTO messages VALUES(NULL, '$user', '$recip', 1, $time, '$text', 0)"); // 1 for 'private'
    echo "<div class='success'>Message sent successfully!</div>";
}

echo "<h3>Your Private Messages</h3>";

// Display private messages
$query  = "SELECT * FROM messages WHERE (recip='$user' OR auth='$user') AND pm=1 ORDER BY time DESC";
$result = queryMysql($pdo,$query);

while ($row = $result->fetch()) {
    $sender = ($row['auth'] == $user) ? "You" : $row['auth'];
    $recipient = ($row['recip'] == $user) ? "You" : $row['recip'];
    
    echo "<div class='message-private'>";
    echo date('M jS \'y g:ia:', $row['time']);
    echo " <strong>$sender</strong> to <strong>$recipient</strong>: ";
    echo "&quot;" . $row['message'] . "&quot;<br>";
    echo "</div>";
}

// Form to send new private message
echo <<<_END
  <form method='post' action='private_messages.php?r=$randstr'>
    <h4>Send a Private Message</h4>
    <label for='recip'>To:</label>
    <select name='recip'>
_END;

// Display friends list
$friendsQuery = "SELECT user FROM friends WHERE friend='$user' UNION SELECT friend FROM friends WHERE user='$user'";
$friendsResult = queryMysql($pdo,$friendsQuery);
while ($friend = $friendsResult->fetch()) {
    echo "<option value='" . $friend['user'] . "'>" . $friend['user'] . "</option>";
}

echo <<<_END
    </select>
    <textarea name='text' placeholder='Type your message'></textarea>
    <input type='submit' value='Send'>
  </form>
_END;
require_once 'footer.php';
?>
