<?php
require_once 'header.php';

$error = $success = '';

if (isset($_POST['otp']) && isset($_POST['new_pass']) && isset($_POST['confirm_pass'])) {
    $otp = sanitizeString($_POST['otp']);
    $new_pass = sanitizeString($_POST['new_pass']);
    $confirm_pass = sanitizeString($_POST['confirm_pass']);

    // Check if OTP and passwords match
    if (empty($otp) || empty($new_pass) || empty($confirm_pass)) {
        $error = "Please fill all fields.";
    } elseif ($new_pass !== $confirm_pass) {
        $error = "Passwords do not match.";
    } else {
        // Verify OTP
        $result = queryMysql($pdo, "SELECT user, otp, otp_expiration FROM members WHERE otp=?", [$otp]);
        if ($result->rowCount() == 0) {
            $error = "Invalid OTP.";
        } else {
            $row = $result->fetch(PDO::FETCH_ASSOC);

            // Check if OTP is expired
            if (strtotime($row['otp_expiration']) < time()) {
                $error = "OTP has expired. Please request a new one.";
            } else {
                // Update the password
                $hashedPass = password_hash($new_pass, PASSWORD_BCRYPT);
                queryMysql($pdo, "UPDATE members SET pass=?, otp=NULL, otp_expiration=NULL WHERE otp=?", [$hashedPass, $otp]);

                $success = "Your password has been reset successfully.";
            }
        }
    }
}
?>

<div class="form-container">
    <form method="POST" action="reset_password.php">
        <h2>Reset Password</h2>

        <?php
        if ($error) {
            echo "<div class='error-message'>$error</div>";
        }

        if ($success) {
            echo "<div class='success-message'>$success</div>";
        }
        ?>

        <div class="form-group">
            <label for="otp">Enter OTP:</label>
            <input type="text" id="otp" name="otp" required>
        </div>

        <div class="form-group">
            <label for="new_pass">New Password:</label>
            <input type="password" id="new_pass" name="new_pass" required>
        </div>

        <div class="form-group">
            <label for="confirm_pass">Confirm New Password:</label>
            <input type="password" id="confirm_pass" name="confirm_pass" required>
        </div>

        <div class="form-group">
            <input type="submit" value="Reset Password">
        </div>
    </form>
</div>
