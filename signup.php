<?php
require_once 'header.php';

$error = $user = $pass = $email = '';  // Initialize email

if (isset($_SESSION['user'])) {
    destroySession();
}

if (isset($_POST['user'])) {
    $user = sanitizeString($_POST['user']);
    $pass = sanitizeString($_POST['pass']);
    $email = sanitizeString($_POST['email']);  // Get the email from the form
    $role = isset($_POST['role']) && $_POST['role'] === 'admin' ? 'admin' : 'user'; // Role selection

    // Validate inputs
    if ($user == "" || $pass == "" || $email == "") {
        $error = "All fields are required!";
    } else {
        // Check if the email already exists
        $result = queryMysql($pdo, "SELECT * FROM members WHERE user='$user' OR email='$email'");
        if ($result->rowCount()) {
            $error = "That username or email already exists, please choose another one.";
        } else {
            // Encrypt password
            $hashedPass = password_hash($pass, PASSWORD_BCRYPT);

            // Insert into database
            queryMysql($pdo, "INSERT INTO members(user, pass, email, role) VALUES ('$user', '$hashedPass', '$email', '$role')");
            die('<h4>Account created successfully</h4> Please proceed to log in.</div></body></html>');
        }
    }
}

echo '<div class="form-container">';
echo '<form method="post" action="signup.php">';
if ($error) {
    echo '<div class="error-message">' . $error . '</div>';
}
echo '<h2>Sign Up</h2>';
echo '<div class="form-group">
  <label for="username">Username</label>
  <input type="text" id="username" maxlength="16" name="user" value="' . $user . '" required>
</div>
<div class="form-group">
  <label for="password">Password</label>
  <input type="password" id="password" maxlength="16" name="pass" value="' . $pass . '" required>
  <span class="show-password" onclick="showPassword()">Show</span>
</div>
<div class="form-group">
  <label for="email">Email</label>
  <input type="email" id="email" name="email" value="' . $email . '" required>
</div>
<div class="form-group">
  <label for="role">Role</label>
  <select id="role" name="role">
    <option value="user" selected>User</option>
    <option value="admin">Admin</option>
  </select>
</div>
<div class="form-group">
  <input type="submit" value="Sign Up">
</div>';
echo '</form>';
echo '</div>';
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
