<?php
require_once 'header.php';
require_once 'setup.php';
$error = $user = $pass = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user = sanitizeString($_POST['user']);
    $pass = sanitizeString($_POST['pass']);

    if (empty($user) || empty($pass)) {
        $error = "Please fill in all fields";
    } else {
        $stmt = $pdo->prepare("SELECT user, pass, role FROM members WHERE user = ?");
        $stmt->execute([$user]);

        if ($stmt->rowCount() == 0) {
            $error = "Username does not exist";
        } else {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if (password_verify($pass, $row['pass'])) {
                $_SESSION['user'] = $user;
                $_SESSION['role'] = $row['role'];
                header("Location: " . ($row['role'] === 'admin' ? 'profile.php' : 'information.php'));
                exit();
            } else {
                $error = "Incorrect password";
            }
        }
    }
}
?>

<div class="form-container">
    <form method="post" action="login.php">
        <h2>Login</h2>
        <?php if (!empty($error)) echo "<div class='error-message'>$error</div>"; ?>
        <div class="form-group">
            <label for="username">Username</label>
            <input type="text" id="username" name="user" value="<?= htmlspecialchars($user) ?>">
        </div>
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="pass">
        </div>
        <div class="form-group">
            <input type="submit" value="Login">
        </div>
        <div class="form-group">
            <p>Don't have an account? <a href='signup.php'>Sign Up</a></p>
            <p><a href="forgot_password.php">Forgot your password?</a></p>
        </div>
    </form>
</div>

<script>
function togglePassword() {
    var password = document.getElementById('password');
    password.type = password.type === 'password' ? 'text' : 'password';
}
</script>
