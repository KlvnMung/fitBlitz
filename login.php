<?php
require_once 'header.php';

$error = $user = $pass = "";

if (isset($_POST['user'])) {
    $user = sanitizeString($_POST['user']);
    $pass = sanitizeString($_POST['pass']);

    if ($user == "" || $pass == "") {
        $error = "Please fill in all fields";
    } else {
        $result = queryMysql($pdo, "SELECT user, pass, role FROM members WHERE user=?", [$user]);

        if ($result->rowCount() == 0) {
            $error = "Username does not exist";
        } else {
            $row = $result->fetch(PDO::FETCH_ASSOC);
            $storedHash = $row['pass'];
            $role = $row['role'];

            if (password_verify($pass, $storedHash)) {
                $_SESSION['user'] = $user;
                $_SESSION['role'] = $role;

                if ($role === 'user') {
                    header("Location: information.php");
                } else {
                    header("Location: profile.php");
                }
                exit();
            } else {
                $error = "Incorrect password";
            }
        }
    }
}

echo <<<_END
<div class="form-container">
    <form method='post' action='login.php'>
        <h2>Login</h2>
_END;

if ($error != "") {
    echo "<div class='error-message'>$error</div>";
}

echo <<<_END
        <div class="form-group">
            <label for="username">Username</label>
            <input type='text' id="username" maxlength="16" name="user" value="$user">
        </div>
        <div class="form-group">
            <label for="password">Password</label>
            <input type='password' id="password" maxlength="16" name="pass" value="$pass">
            <span class="show-password" onclick="showPassword()">Show</span>
        </div>
        <div class="form-group">
            <input type='submit' value='Login'>
        </div>
        <div class="form-group">
            <p>Don't have an account? <a href='signup.php'>Sign Up</a></p>
                       <p><a href="forgot_password.php">Forgot your password?</a></p> <!-- Forgot Password Link -->
        </div>
    </form>
</div>
_END;
?>

<script>
function showPassword() {
    var password = document.getElementById('password');
    if (password.type === 'password') {
        password.type = 'text';
    } else {
        password.type = 'password';
    }
}
</script>
