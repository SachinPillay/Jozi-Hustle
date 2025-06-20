<?php
session_start();
require_once 'db_config.php';

// Ensure only the admin can access this page
if (!isset($_SESSION['user']) || $_SESSION['user']['email'] !== 'jozihu$tleza@gmail.com') {
    header("Location: login.php");
    exit;
}

// Handle user deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_user'])) {
    try {
        $user_id = intval($_POST['delete_user']);
        $role = $_POST['role'];
        $table = ($role === 'buyer') ? 'buyers' : 'sellers';

        $stmt = $conn->prepare("DELETE FROM $table WHERE id = ?");
        $stmt->execute([$user_id]);
    } catch (PDOException $e) {
        die("Error deleting user: " . $e->getMessage());
    }
}

// Handle user blocking/unblocking
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['block_user'])) {
    try {
        $user_id = intval($_POST['block_user']);
        $role = $_POST['role'];
        $table = ($role === 'buyer') ? 'buyers' : 'sellers';

        $stmt = $conn->prepare("UPDATE $table SET blocked = NOT blocked WHERE id = ?");
        $stmt->execute([$user_id]);
    } catch (PDOException $e) {
        die("Error blocking user: " . $e->getMessage());
    }
}

// Fetch all buyers and sellers
try {
    $stmt = $conn->prepare("
        SELECT id, name, email, 'buyer' AS role, blocked FROM buyers 
        UNION 
        SELECT id, name, email, 'seller' AS role, blocked FROM sellers 
        ORDER BY role ASC
    ");
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error fetching users: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Manage Users - Jozi Hustle</title>
  <link rel="stylesheet" href="StyleSheet.css">
</head>
<body>

<div class="nav">
<a href="admin_dashboard.php" class="active">Dashboard</a>
    <a href="user_management.php">🛠 User Management</a>
    <a href="ad_moderation.php">📢 Moderate Ads</a>
    <span style="float:right;">
      <a href="logout.php">Logout</a>
    </span>
</div>

<div class="container">
    <h2>User Management</h2>
    <p>Admin  is able to view, block, or delete users.</p>

    <?php if (empty($users)): ?>
        <p style="color: red;">No users found.</p>
    <?php else: ?>
        <table class="user-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($user['name']); ?></td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td><?php echo ucfirst($user['role']); ?></td>
                        <td><?php echo $user['blocked'] ? "<span style='color:red;'>Blocked</span>" : "<span style='color:green;'>Active</span>"; ?></td>
                        <td>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="delete_user" value="<?php echo $user['id']; ?>">
                                <input type="hidden" name="role" value="<?php echo htmlspecialchars($user['role']); ?>">
                                <button type="submit" class="action-btn delete-btn">Delete</button>
                            </form>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="block_user" value="<?php echo $user['id']; ?>">
                                <input type="hidden" name="role" value="<?php echo htmlspecialchars($user['role']); ?>">
                                <button type="submit" class="action-btn block-btn"><?php echo $user['blocked'] ? "Unblock" : "Block"; ?></button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<script src="script.js"></script>
</body>
</html>
