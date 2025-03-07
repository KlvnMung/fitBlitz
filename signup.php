<?php
require_once 'header.php';

$error = "";
$user = "";
$pass = "";
$email = "";
$role = "user";

if (isset($_SESSION['user'])) {
    destroySession();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user = sanitizeString($_POST['user']);
    $pass = sanitizeString($_POST['pass']);
    $email = sanitizeString($_POST['email']);
    $role = isset($_POST['role']) && $_POST['role'] === 'admin' ? 'admin' : 'user';

    if (empty($user) || empty($pass) || empty($email)) {
        $error = "All fields are required!";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM members WHERE user = ? OR email = ?");
        $stmt->execute([$user, $email]);
        
        if ($stmt->rowCount() > 0) {
            $error = "That username or email already exists, please choose another one.";
        } else {
            $hashedPass = password_hash($pass, PASSWORD_BCRYPT);
            $stmt = $pdo->prepare("INSERT INTO members (user, pass, email, role) VALUES (?, ?, ?, ?)");
            $stmt->execute([$user, $hashedPass, $email, $role]);
            echo '<h4>Account created successfully</h4> Please proceed to <a href="login.php">log in</a>.';
            exit();
        }
    }
}
?>

<div class="form-container">
    <form method="post" action="signup.php">
        <h2>Sign Up</h2>
        <?php if (!empty($error)) echo "<div class='error-message'>$error</div>"; ?>
        <div class="form-group">
            <label for="username">Username</label>
            <input type="text" id="username" name="user" value="<?= htmlspecialchars($user) ?>" required>
        </div>
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="pass" required>
        </div>
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="<?= htmlspecialchars($email) ?>" required>
        </div>
        <div class="form-group">
            <label for="role">Role</label>
            <select id="role" name="role">
                <option value="user" <?= $role === 'user' ? 'selected' : '' ?>>User</option>
                <option value="admin" <?= $role === 'admin' ? 'selected' : '' ?>>Admin</option>
            </select>
        </div>
        <div class="form-group">
            <input type="submit" value="Sign Up">
        </div>
    </form>
</div>