<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    require_once '../includes/config.php';
} catch (Exception $e) {
    die("Config file error: " . $e->getMessage());
}

// Check if user is logged in first
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit();
}

// Check admin access - use direct check to avoid function issues
if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] != 1) {
    header('Location: ../auth/login.php');
    exit();
}

// Handle user actions
if ($_POST) {
    if (isset($_POST['action']) && isset($_POST['user_id'])) {
        $user_id = (int)$_POST['user_id'];
        $action = $_POST['action'];
        
        if ($action == 'block') {
            $stmt = $pdo->prepare("UPDATE users SET blocked = 1 WHERE id = ?");
            $stmt->execute([$user_id]);
            $message = "User blocked successfully.";
        } elseif ($action == 'unblock') {
            $stmt = $pdo->prepare("UPDATE users SET blocked = 0 WHERE id = ?");
            $stmt->execute([$user_id]);
            $message = "User unblocked successfully.";
        } elseif ($action == 'delete') {
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$user_id]);
            $message = "User deleted successfully.";
        }
    }
}

// Get search term
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Get users
if (!empty($search)) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE name LIKE ? OR surname LIKE ? OR email LIKE ? ORDER BY created_at DESC");
    $stmt->execute(["%$search%", "%$search%", "%$search%"]);
} else {
    $stmt = $pdo->query("SELECT * FROM users ORDER BY created_at DESC");
}
$users = $stmt->fetchAll();

// Count statistics
$total_users = count($users);
$blocked_users = 0;
foreach ($users as $user) {
    if ($user['blocked']) $blocked_users++;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - Admin Panel</title>
    <link href="admin-styles.css" rel="stylesheet">
</head>
<body>
    <!-- Simple Navigation -->
    <nav class="admin-nav">
        <a href="index.php">Dashboard</a>
        <a href="users.php" class="active">Users</a>
        <a href="ads.php">Ads</a>
        <a href="moderation.php">Moderation</a>
        <a href="messages.php">Messages</a>
        <a href="../index.php">Back to Site</a>
        <a href="../auth/logout.php">Logout</a>
    </nav>

    <div class="container">
        <h1>User Management</h1>

        <?php if (isset($message)): ?>
            <div style="background: #d4edda; color: #155724; padding: 10px; border-radius: 3px; margin-bottom: 20px;">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <!-- Simple Statistics -->
        <div class="stats-row">
            <div class="stat-box">
                <div class="stat-number"><?php echo $total_users; ?></div>
                <div class="stat-label">Total Users</div>
            </div>
            
            <div class="stat-box">
                <div class="stat-number"><?php echo $blocked_users; ?></div>
                <div class="stat-label">Blocked Users</div>
            </div>
            
            <div class="stat-box">
                <div class="stat-number"><?php echo $total_users - $blocked_users; ?></div>
                <div class="stat-label">Active Users</div>
            </div>
        </div>

        <!-- Search Form -->
        <div class="card">
            <h3>Search Users</h3>
            <form method="GET">
                <input type="text" name="search" placeholder="Search by name or email..." 
                       value="<?php echo htmlspecialchars($search); ?>" class="form-control" style="width: 300px; display: inline-block;">
                <button type="submit" class="btn btn-primary">Search</button>
                <a href="users.php" class="btn btn-secondary">Clear</a>
            </form>
        </div>

        <!-- Users Table -->
        <div class="card">
            <h3>All Users (<?php echo count($users); ?> found)</h3>
            
            <?php if (empty($users)): ?>
                <p style="text-align: center; color: #666; padding: 20px;">No users found.</p>
            <?php else: ?>
                <table class="simple-table">
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Gender</th>
                        <th>Joined Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                    <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?php echo $user['id']; ?></td>
                        <td><?php echo htmlspecialchars($user['name'] . ' ' . $user['surname']); ?></td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td><?php echo htmlspecialchars($user['phone'] ?? 'Not provided'); ?></td>
                        <td><?php echo htmlspecialchars($user['gender'] ?? 'Not specified'); ?></td>
                        <td><?php echo date('Y-m-d', strtotime($user['created_at'])); ?></td>
                        <td>
                            <span class="badge badge-<?php echo $user['blocked'] ? 'danger' : 'success'; ?>">
                                <?php echo $user['blocked'] ? 'Blocked' : 'Active'; ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($user['id'] != 1): // Don't allow actions on admin user ?>
                                <?php if ($user['blocked']): ?>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                        <input type="hidden" name="action" value="unblock">
                                        <button type="submit" class="btn btn-success" onclick="return confirm('Unblock this user?')">
                                            Unblock
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                        <input type="hidden" name="action" value="block">
                                        <button type="submit" class="btn btn-warning" onclick="return confirm('Block this user?')">
                                            Block
                                        </button>
                                    </form>
                                <?php endif; ?>
                                
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                    <input type="hidden" name="action" value="delete">
                                    <button type="submit" class="btn btn-danger" onclick="return confirm('Delete this user? This cannot be undone!')">
                                        Delete
                                    </button>
                                </form>
                            <?php else: ?>
                                <span class="badge badge-secondary">Admin</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </table>
            <?php endif; ?>
        </div>

        <!-- Simple instructions -->
        <div class="card">
            <h3>Instructions</h3>
            <ul>
                <li>Use the search box to find specific users by name or email</li>
                <li>Block users to prevent them from logging in</li>
                <li>Delete users to permanently remove them from the system</li>
                <li>Admin user (ID: 1) cannot be modified</li>
            </ul>
        </div>
    </div>
</body>
</html>
