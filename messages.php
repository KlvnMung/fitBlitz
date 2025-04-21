<?php 
require_once 'header.php';
require_once 'function.php';

if (!$loggedin) die("</div></body></html>");

if (isset($_POST['text'])) {
    $text = sanitizeString($_POST['text']);
    $pm   = 0; // Always set to public (community)
    $time = time();

    if ($text != "") {
        $stmt = $pdo->prepare("INSERT INTO messages VALUES(NULL, ?, ?, ?, ?, ?)");
        $stmt->execute([$user, $user, $pm, $time, $text]);

        if ($stmt->rowCount()) {
            $_SESSION['alert'] = ['message' => 'Message posted successfully!', 'type' => 'success'];
        } else {
            $_SESSION['alert'] = ['message' => 'Failed to post message.', 'type' => 'error'];
        }
    }

    header("Location: messages.php");
    exit();
}

// Show alert if exists after page reload
if (isset($_SESSION['alert'])) {
    $msg = $_SESSION['alert']['message'];
    $type = $_SESSION['alert']['type'];
    echo "<script>document.addEventListener('DOMContentLoaded', function() { showAlert('$msg', '$type'); });</script>";
    unset($_SESSION['alert']);
}
?>
    <h3 style="font-family: Arial, sans-serif; color: #333; margin-bottom: 20px;">Post a Comment</h3>
    <form method="post" action="messages.php" style="display: flex; align-items: center; border: 1px solid #ddd; border-radius: 30px; padding: 10px 15px; background-color: #f9f9f9; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);">
        <textarea name="text" placeholder="Write your comment here..." required style="flex: 1; border: none; outline: none; font-family: Arial, sans-serif; font-size: 14px; resize: none; background-color: transparent; padding: 5px;"></textarea>
        <button type="submit" style="background-color: #007bff; color: white; border: none; padding: 8px 15px; border-radius: 20px; font-family: Arial, sans-serif; font-size: 14px; cursor: pointer; margin-left: 10px;">Post</button>
    </form>


<!-- Separator Line -->
<hr style="border: 1px solid #ddd; margin: 30px 0;">

<!-- Public Messages Section -->
<div>
    <h3 style="font-family: Arial, sans-serif; color: #333; margin-bottom: 20px;">Community Comments</h3>
    <?php
    $query = "SELECT * FROM messages WHERE pm = 0 ORDER BY time DESC LIMIT 100";
    $result = queryMysql($pdo, $query);

    if ($result->rowCount() == 0) {
        echo "<p style='font-family: Arial, sans-serif; color: #666;'>No comments yet. Be the first to comment!</p>";
    } else {
        while ($row = $result->fetch()) {
            $sender = ($row['auth'] === $user) ? "You" : $row['auth'];

            echo "<div style='border: 1px solid #ddd; border-radius: 8px; padding: 15px; margin-bottom: 15px; background-color: #f9f9f9;'>";
            echo "<div style='display: flex; align-items: center; margin-bottom: 10px;'>";
            echo "<div style='width: 40px; height: 40px; border-radius: 50%; background-color: #ccc; display: inline-block;'></div>";
            echo "<div style='margin-left: 10px;'>";
            echo "<strong style='font-family: Arial, sans-serif; color: #333;'>" . htmlspecialchars($sender) . "</strong>";
            echo "<p style='font-family: Arial, sans-serif; color: #999; font-size: 12px; margin: 0;'>" . date('M jS \'y g:ia', $row['time']) . "</p>";
            echo "</div>";
            echo "</div>";
            echo "<p style='font-family: Arial, sans-serif; color: #555;'>" . htmlspecialchars($row['message']) . "</p>";
            echo "</div>";
        }
    }
    ?>
</div>

<?php

if (isset($_GET['erase'])) {
    $erase = sanitizeString($_GET['erase']);
    queryMysql($pdo, "DELETE FROM messages WHERE id='$erase' AND auth='$user'");
}

// Function to fetch mutual friends
function getMutualFriends($pdo, $user) {
    $followers = [];
    $following = [];

    $result = queryMysql($pdo, "SELECT friend FROM friends WHERE user = ?", [$user]);
    if ($result) {
        while ($row = $result->fetch()) {
            $followers[] = $row['friend'];
        }
    }

    $result = queryMysql($pdo, "SELECT user FROM friends WHERE friend = ?", [$user]);
    if ($result) {
        while ($row = $result->fetch()) {
            $following[] = $row['user'];
        }
    }

    return array_intersect($followers, $following);
}

require_once 'footer.php';
?>

<script>
function showAlert(message, type) {
    const alertDiv = document.createElement('div');
    alertDiv.style.position = 'fixed';
    alertDiv.style.top = '20px';
    alertDiv.style.left = '50%';
    alertDiv.style.transform = 'translateX(-50%)';
    alertDiv.style.padding = '12px 20px';
    alertDiv.style.color = '#fff';
    alertDiv.style.borderRadius = '5px';
    alertDiv.style.fontFamily = 'Arial, sans-serif';
    alertDiv.style.fontSize = '14px';
    alertDiv.style.boxShadow = '0 0 10px rgba(0,0,0,0.3)';
    alertDiv.style.zIndex = '1000';

    if (type === 'success') {
        alertDiv.style.backgroundColor = '#28a745';
    } else {
        alertDiv.style.backgroundColor = '#dc3545';
    }

    alertDiv.textContent = message;
    document.body.appendChild(alertDiv);

    setTimeout(() => {
        alertDiv.remove();
    }, 3000);
}
</script>
