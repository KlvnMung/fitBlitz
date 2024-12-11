<?php
// require_once 'header.php';

// Check if the user is logged in and has an admin role
if (!isset($_SESSION['user'])) {
    header("Location: information.php");
    exit();
}

$user = $_SESSION['user'];

// Fetch the role of the logged-in user
$roleResult = queryMysql($pdo, "SELECT role FROM members WHERE user=?", [$user]);
$roleRow = $roleResult->fetch(PDO::FETCH_ASSOC);

if ($roleRow['role'] !== 'admin') {
    die("Access Denied: You do not have admin privileges.");
}

// Fetch all user data
$result = queryMysql($pdo, "SELECT user, pass, role FROM members");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="admin-dashboard">
        <h2>Admin Dashboard</h2>
        <table>
            <thead>
                <tr>
                    <th>Username</th>
                    <th>Password (Hashed)</th>
                    <th>Role</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch(PDO::FETCH_ASSOC)): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['user']) ?></td>
                        <td><?= htmlspecialchars($row['pass']) ?></td>
                        <td><?= htmlspecialchars($row['role']) ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
