<?php
// Start session
if (!extension_loaded('gd')) {
    die('GD library is not enabled. Please contact your server administrator.');
}

require_once 'header.php';

if (!$loggedin) die("You need to be logged in to view this page.");

echo "<div class='objective'>";

// Check if we're in edit mode
$edit_mode = isset($_GET['edit']) && $_GET['edit'] === 'true';

try {
    $result = queryMysql($pdo, "SELECT * FROM profiles WHERE user='$user'");
} catch (PDOException $e) {
    echo "Error retrieving profile data: " . $e->getMessage();
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $age = sanitizeString($_POST['age']);
    $weight = sanitizeString($_POST['weight']);
    $height = sanitizeString($_POST['height']);
    $medical_history = sanitizeString($_POST['medical_history']);
    $goals = sanitizeString($_POST['goals']);
    $personality = sanitizeString($_POST['personality']);

    if ($result->rowCount()) {
        try {
            queryMysql($pdo, "UPDATE profiles SET 
                age = ?, weight = ?, height = ?, 
                medical_history = ?, goals = ?, personality = ? 
                WHERE user = ?", 
                [$age, $weight, $height, $medical_history, $goals, $personality, $user]);
            echo "<div class='success'>Profile updated successfully!</div>";
        } catch (PDOException $e) {
            echo "<div class='error'>Error updating profile: " . $e->getMessage() . "</div>";
            exit;
        }
    } else {
        try {
            queryMysql($pdo, "INSERT INTO profiles 
                (user, age, weight, height, medical_history, goals, personality) 
                VALUES (?, ?, ?, ?, ?, ?, ?)",
                [$user, $age, $weight, $height, $medical_history, $goals, $personality]);
            echo "<div class='success'>Profile created successfully!</div>";
        } catch (PDOException $e) {
            echo "<div class='error'>Error creating profile: " . $e->getMessage() . "</div>";
            exit;
        }
    }
    $edit_mode = false; // Exit edit mode after saving
} 

// Fetch current profile data
if ($result->rowCount()) {
    $row = $result->fetch();
    $age = $row['age'];
    $weight = $row['weight'];
    $height = $row['height'];
    $medical_history = $row['medical_history'];
    $goals = $row['goals'];
    $personality = $row['personality'];
} else {
    $age = $weight = $height = $medical_history = $goals = $personality = '';
}

// Handle image upload code here (keep your existing code)

// Show the profile or edit form based on mode
if (!$edit_mode) {
    // Show profile with edit button
    echo "<div class='profile-header'>";
    echo "<h3>Your Profile</h3>";
    echo "<a href='profile.php?edit=true&r=$randstr' class='edit-button'>";
    echo "<i class='fas fa-edit'></i> Edit Profile";
    echo "</a>";
    echo "</div>";
    
    showProfile($pdo, $user);
} else {
    // Show edit form
    echo "<div class='profile-header'>";
    echo "<h3>Edit Your Profile</h3>";
    echo "</div>";
    
    echo <<<_END
    <form method='post' action='profile.php?r=$randstr' enctype='multipart/form-data' class='profile-form'>
        <div class='form-group'>
            <label>Age:</label>
            <input type='number' name='age' value='$age' class='form-control'>
        </div>
        
        <div class='form-group'>
            <label>Weight (kg):</label>
            <input type='number' step='0.1' name='weight' value='$weight' class='form-control'>
        </div>
        
        <div class='form-group'>
            <label>Height (cm):</label>
            <input type='number' name='height' value='$height' class='form-control'>
        </div>
        
        <div class='form-group'>
            <label>Medical History:</label>
            <textarea name='medical_history' class='form-control'>$medical_history</textarea>
        </div>
        
        <div class='form-group'>
            <label>Fitness Goals:</label>
            <textarea name='goals' class='form-control'>$goals</textarea>
        </div>
        
        <div class='form-group'>
            <label>About Me:</label>
            <textarea name='personality' class='form-control'>$personality</textarea>
        </div>
        
        <div class='form-group'>
            <label>Profile Picture:</label>
            <input type='file' name='image' class='form-control'>
        </div>
        
        <div class='button-group'>
            <input type='submit' value='Save Changes' class='submit-button'>
            <a href='profile.php' class='cancel-button'>Cancel</a>
        </div>
    </form>
    
_END;
}

echo "</div>";
require_once 'footer.php';
?>