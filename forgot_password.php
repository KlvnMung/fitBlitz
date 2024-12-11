<?php
require_once 'header.php';
require_once 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

session_start();

$error = $success = '';
$email = isset($_SESSION['email']) ? $_SESSION['email'] : ''; // Store email across steps
$step = isset($_POST['step']) ? sanitizeString($_POST['step']) : (isset($_GET['step']) ? $_GET['step'] : '1');

// Step 1: Send OTP
if ($step === '1' && isset($_POST['email'])) {
    $email = sanitizeString($_POST['email']);
    if (empty($email)) {
        $error = "Please enter your email address.";
    } else {
        $result = queryMysql($pdo, "SELECT user, email FROM members WHERE email=?", [$email]);
        if ($result->rowCount() === 0) {
            $error = "No user found with that email address.";
        } else {
            $otp = rand(100000, 999999);
            $expiration = date('Y-m-d H:i:s', strtotime('+10 minutes'));
            queryMysql($pdo, "UPDATE members SET otp=?, otp_expiration=? WHERE email=?", [$otp, $expiration, $email]);

            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'kelvinmun7@gmail.com';
                $mail->Password = 'dnrz moqu xjzy cbqa';
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;

                $mail->setFrom('kelvinmun7@gmail.com', 'FitBlitz');
                $mail->addAddress($email);

                $mail->isHTML(true);
                $mail->Subject = "Your OTP for Password Reset";
                $mail->Body = nl2br("Your OTP is: $otp\nIt will expire in 10 minutes.");
                $mail->send();

                $success = "OTP sent to your email address. Please check your inbox.";
                $_SESSION['email'] = $email;
                header("Location: forgot_password.php?step=2");
                exit();
            } catch (Exception $e) {
                $error = "Mailer Error: {$mail->ErrorInfo}";
            }
        }
    }
}

// Step 2: Validate OTP and Reset Password
if ($step === '2' && isset($_POST['otp'])) {
    $otp = sanitizeString($_POST['otp']);
    $new_pass = sanitizeString($_POST['new_pass']);
    $confirm_pass = sanitizeString($_POST['confirm_pass']);

    if (empty($otp) || empty($new_pass) || empty($confirm_pass)) {
        $error = "Please fill all fields.";
    } elseif ($new_pass !== $confirm_pass) {
        $error = "Passwords do not match.";
    } else {
        $result = queryMysql($pdo, "SELECT user, otp, otp_expiration FROM members WHERE otp=?", [$otp]);
        if ($result->rowCount() === 0) {
            $error = "Invalid OTP.";
        } else {
            $row = $result->fetch(PDO::FETCH_ASSOC);
            if (strtotime($row['otp_expiration']) < time()) {
                $error = "OTP has expired. Please request a new one.";
            } else {
                $hashedPass = password_hash($new_pass, PASSWORD_BCRYPT);
                queryMysql($pdo, "UPDATE members SET pass=?, otp=NULL, otp_expiration=NULL WHERE otp=?", [$hashedPass, $otp]);
                $success = "Your password has been reset successfully.";
                header("Location: login.php");
                exit();
            }
        }
    }
}
?>
<style>
    .toggle-password {
    cursor: pointer;
    color: #007bff;
    font-size: 0.9em;
    margin-left: 10px;
}

.toggle-password:hover {
    text-decoration: underline;
}

</style>
<!-- HTML Form Rendering -->
<div class="form-container">
    <?php if ($step === '1'): ?>
        <form method="POST" action="forgot_password.php">
            <h2>Forgot Password</h2>
            <?php if ($error) echo "<div class='error-message'>$error</div>"; ?>
            <?php if ($success) echo "<div class='success-message'>$success</div>"; ?>
            <div class="form-group">
                <label for="email">Enter your email:</label>
                <input type="email" id="email" name="email" value="<?= htmlspecialchars($email) ?>" required>
            </div>
            <input type="hidden" name="step" value="1">
            <div class="form-group">
                <input type="submit" value="Send OTP">
            </div>
        </form>
    <?php elseif ($step === '2'): ?>
        <form method="POST" action="forgot_password.php">
            <h2>Reset Password</h2>
            <?php if ($error) echo "<div class='error-message'>$error</div>"; ?>
            <?php if ($success) echo "<div class='success-message'>$success</div>"; ?>
            <div class="form-group">
                <label for="otp">Enter OTP:</label>
                <input type="text" id="otp" name="otp" required>
            </div>
            <div class="form-group">
                <label for="new_pass">New Password:</label>
                <input type="password" id="new_pass" name="new_pass" required>
                <span class= "toggle-password" onclick="showPassword()">Show</span>
            </div>
            <div class="form-group">
                <label for="confirm_pass">Confirm Password:</label>
                <input type="password" id="confirm_pass" name="confirm_pass" required>
                <span class= "toggle-password" onclick="showPassword()">Show</span>
            </div>
            <input type="hidden" name="step" value="2">
            <div class="form-group">
                <input type="submit" value="Reset Password">
            </div>
        </form>
    <?php endif; ?>
</div>
<script>
    function showPassword(){
      let password=document.getElementById('new_pass');
       let confirm_pass=document.getElementById('confirm_pass');
      password.type=password.type=== 'password'?'text':'password';
     
      confirm_pass.type=confirm_pass.type === 'password'?'text':'password';
    }
</script>
