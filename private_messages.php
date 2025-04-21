<?php
require_once 'header.php';
require_once 'function.php';

if (!$loggedin) die("</div></body></html>");

if (isset($_POST['recip']) && isset($_POST['text'])) {
    $recip = sanitizeString($_POST['recip']);
    $text  = sanitizeString($_POST['text']);
    $time  = time();

    $query = "INSERT INTO messages (auth, recip, pm, time, message) VALUES (?, ?, 1, ?, ?)";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$user, $recip, $time, $text]);

    if ($stmt->rowCount()) {
        echo "<script>document.addEventListener('DOMContentLoaded', function() { showAlert('Message sent successfully!', 'success'); });</script>";
    } else {
        echo "<script>document.addEventListener('DOMContentLoaded', function() { showAlert('Failed to send message.', 'error'); });</script>";
    }
}
?>

<!-- Post Private Message Section -->
<div style="margin-bottom: 30px; padding: 20px; border: 1px solid #ddd; border-radius: 8px; background-color: #f9f9f9;">
    <h3 style="font-family: Arial, sans-serif; color: #333; margin-bottom: 20px;">Send a Private Message</h3>
    <form method="post" action="private_messages.php" style="margin-top: 20px;">
        <label for="recip" style="font-family: Arial, sans-serif; font-size: 14px; color: #333; margin-bottom: 10px; display: block;">Recipient:</label>
        <select name="recip" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 8px; font-family: Arial, sans-serif; font-size: 14px; margin-bottom: 15px;">
            <?php
            $friendsQuery = "SELECT user FROM friends WHERE friend = ? UNION SELECT friend FROM friends WHERE user = ?";
            $friendsResult = queryMysql($pdo, $friendsQuery, [$user, $user]);

            while ($friend = $friendsResult->fetch()) {
                echo "<option value='" . htmlspecialchars($friend['user']) . "'>" . htmlspecialchars($friend['user']) . "</option>";
            }
            ?>
        </select>
        <textarea name="text" placeholder="Write your message here..." required style="width: 100%; height: 100px; padding: 15px; border: 1px solid #ddd; border-radius: 8px; font-family: Arial, sans-serif; font-size: 16px; margin-bottom: 15px;"></textarea>
        <input type="submit" value="Send Message" style="background-color: #007bff; color: white; border: none; padding: 15px 30px; border-radius: 8px; font-family: Arial, sans-serif; font-size: 16px; cursor: pointer;">
    </form>
</div>

<!-- Separator Line -->
<hr style="border: 1px solid #ddd; margin: 30px 0;">

<!-- Private Messages Section -->
<div>
    <h3 style="font-family: Arial, sans-serif; color: #333; margin-bottom: 20px;">Your Private Messages</h3>
    <?php
    $query = "SELECT * FROM messages WHERE (recip = ? OR auth = ?) AND pm = 1 ORDER BY time DESC";
    $result = queryMysql($pdo, $query, [$user, $user]);

    if ($result->rowCount() == 0) {
        echo "<p style='font-family: Arial, sans-serif; color: #666;'>No private messages yet.</p>";
    } else {
        while ($row = $result->fetch()) {
            $sender = ($row['auth'] === $user) ? "You" : $row['auth'];
            $recipient = ($row['recip'] === $user) ? "You" : $row['recip'];

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
    } else if (type === 'error') {
        alertDiv.style.backgroundColor = '#dc3545';
    }
    alertDiv.textContent = message;
    document.body.appendChild(alertDiv);
    setTimeout(() => { alertDiv.remove(); }, 3000);
}
</script>

<?php
require_once 'footer.php';
?>
