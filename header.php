<?php 
session_start(); // Start the session
require_once "setup.php";
// Get the current page name
$current_page = basename($_SERVER['PHP_SELF']); 

require_once 'function.php';

$userstr = "";
$randstr = substr(md5(rand()), 0, 7);
$loggedin = false;
// $user = '';

if (isset($_SESSION['user'])) {
    $user = $_SESSION['user'];
    $loggedin = true;
    $userstr = "Logged in, welcome " . htmlspecialchars($user);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset='utf-8'>
<meta name='viewport' content='width=device-width, initial-scale=1'>
    
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" />
    <script>
        function toggleTheme() {
            document.body.classList.toggle('dark-theme');
        }
    </script>
</head>
<body>
    <div class="container">
       

        <main class="content">
    
<?php
if (!$loggedin) {
    echo <<<__MAIN
    <div class="logo">
        <h1>FitBl<span class="bold-icon"><i class="fas fa-bolt"></i></span>tz</h1>
        <p>$userstr</p>
    </div>
    <div class="nav-horizontal">
        <ul>
            <li><a href="index.php">Home</a></li>
            <li><a href="signup.php">Sign Up</a></li>
            <li><a href="login.php">Log In</a></li>
        </ul>
    </div>
__MAIN;
} else {
    $profile_pic = file_exists("$user.jpg") ? "$user.jpg" : "default_profile.jpg";
    echo <<<__LOGGEDIN
    <div class="logo">
    <!-- Dark Theme Toggle -->
    <div class="theme-toggle">
        <button onclick="toggleTheme()" title="Toggle Dark Mode">
            <i class="fas fa-moon"></i>
        </button>
    </div>
        <h1>FitBl<span class="bold-icon"><i class="fas fa-bolt"></i></span>tz</h1>
        <div class="user-profile-header">
            <img src="$profile_pic" class="profile-pic-header" alt="Profile Picture">
            <span class="username">$user</span>
        </div>
    </div>
    <div class="nav-horizontal">
        <ul>
            <li><a href="information.php?r=$randstr"><i class="fa fa-home"></i> Home</a></li>
            <li><a href="members.php?view=$user&r=$randstr"><i class="fa fa-user-friends"></i> Members</a></li>
            <li><a href="friends.php?view=$user&r=$randstr"><i class="fa fa-heart"></i> Friends</a></li>
            <li><a href="calory_calc.php?view=$user&r=$randstr"><i class="fa fa-calculator"></i> Calory Calculator</a></li> 
            <li class="dropdown">
                <a href="javascript:void(0)" class="dropbtn">Messages</a>
                <div class="dropdown-content">
                    <a href="messages.php?view=$user&r=$randstr">Public Messages</a>
                    <a href="private_messages.php?r=$randstr">Private Messages</a>
                </div>
            </li>
            <li><a href="profile.php?view=$user&r=$randstr"><i class="fa fa-user"></i> Profile</a></li>
            <li><a href="logout.php"><i class="fa fa-sign-out-alt"></i> Log Out</a></li>
        </ul>
    </div>
__LOGGEDIN;
}
?>

<script>
    // dark theme toggle
    function toggleTheme() {
        document.body.classList.toggle('dark-theme');
        localStorage.setItem('theme', document.body.classList.contains('dark-theme') ? 'dark' : 'light');
    }

    // Load theme preference from localStorage
    document.addEventListener('DOMContentLoaded', () => {
        if (localStorage.getItem('theme') === 'dark') {
            document.body.classList.add('dark-theme');
        }
    });
document.addEventListener('DOMContentLoaded', (event) => {
    var dropdowns = document.getElementsByClassName("dropdown");
    for (var i = 0; i < dropdowns.length; i++) {
        var openDropdown = dropdowns[i];
        openDropdown.addEventListener('click', function(event) {
            var dropdownContent = this.getElementsByClassName("dropdown-content")[0];
            dropdownContent.style.display = dropdownContent.style.display === "block" ? "none" : "block";
        });
    }

    window.onclick = function(event) {
        if (!event.target.matches('.dropbtn')) {
            var dropdowns = document.getElementsByClassName("dropdown-content");
            for (var i = 0; i < dropdowns.length; i++) {
                var openDropdown = dropdowns[i];
                if (openDropdown.style.display === "block") {
                    openDropdown.style.display = "none";
                }
            }
        }
    }
});
</script>
</body>
</html>
