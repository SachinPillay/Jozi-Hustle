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

// Get basic statistics
$stats = [];

// Total users
$stmt = $pdo->query("SELECT COUNT(*) as total FROM users");
$stats['total_users'] = $stmt->fetch()['total'];

// Total ads
$stmt = $pdo->query("SELECT COUNT(*) as total FROM ads");
$stats['total_ads'] = $stmt->fetch()['total'];

// Active ads
$stmt = $pdo->query("SELECT COUNT(*) as total FROM ads WHERE status = 'available'");
$stats['active_ads'] = $stmt->fetch()['total'];

// Blocked users
$stmt = $pdo->query("SELECT COUNT(*) as total FROM users WHERE blocked = 1");
$stats['blocked_users'] = $stmt->fetch()['total'];

// Recent ads
$stmt = $pdo->query("SELECT a.*, u.name, u.surname FROM ads a JOIN users u ON a.seller_id = u.id ORDER BY a.created_at DESC LIMIT 5");
$recent_ads = $stmt->fetchAll();

// Recent users
$stmt = $pdo->query("SELECT * FROM users ORDER BY created_at DESC LIMIT 5");
$recent_users = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Jozi Hustle</title>
    <link href="admin-styles.css" rel="stylesheet">
</head>
<body>
    <!-- Simple Navigation -->
    <nav class="admin-nav">
        <a href="index.php" class="active">Dashboard</a>
        <a href="users.php">Users</a>
        <a href="ads.php">Ads</a>
        <a href="moderation.php">Moderation</a>
        <a href="messages.php">Messages</a>
        <a href="../index.php">Back to Site</a>
        <a href="../auth/logout.php">Logout</a>
    </nav>

    <div class="container">
        <h1>Admin Dashboard</h1>
        <p style="text-align: center; color: #666;">Welcome to the admin panel. Here you can manage users and ads.</p>

        <!-- Simple Statistics -->
        <div class="stats-row">
            <div class="stat-box">
                <div class="stat-number"><?php echo $stats['total_users']; ?></div>
                <div class="stat-label">Total Users</div>
                <?php if ($stats['blocked_users'] > 0): ?>
                    <div style="color: red; font-size: 12px;"><?php echo $stats['blocked_users']; ?> blocked</div>
                <?php endif; ?>
            </div>
            
            <div class="stat-box">
                <div class="stat-number"><?php echo $stats['total_ads']; ?></div>
                <div class="stat-label">Total Ads</div>
                <div style="color: green; font-size: 12px;"><?php echo $stats['active_ads']; ?> active</div>
            </div>
            
            <div class="stat-box">
                <div class="stat-number"><?php echo $stats['active_ads']; ?></div>
                <div class="stat-label">Active Ads</div>
                <div style="color: blue; font-size: 12px;">Available for sale</div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="card">
            <h3>Quick Actions</h3>
            <a href="users.php" class="btn btn-primary">Manage Users</a>
            <a href="ads.php" class="btn btn-primary">View All Ads</a>
            <a href="moderation.php" class="btn btn-warning">Moderate Content</a>
            <a href="messages.php" class="btn btn-success">View Messages</a>
        </div>

        <!-- Recent Activity -->
        <div style="display: flex; gap: 20px; flex-wrap: wrap;">
            <!-- Recent Ads -->
            <div class="card" style="flex: 1; min-width: 300px;">
                <h3>Recent Ads</h3>
                <table class="simple-table">
                    <tr>
                        <th>Title</th>
                        <th>Seller</th>
                        <th>Price</th>
                        <th>Status</th>
                    </tr>
                    <?php foreach ($recent_ads as $ad): ?>
                    <tr>
                        <td>
                            <strong><?php echo htmlspecialchars($ad['title']); ?></strong><br>
                            <small style="color: #666;"><?php echo $ad['category']; ?></small>
                        </td>
                        <td><?php echo htmlspecialchars($ad['name'] . ' ' . $ad['surname']); ?></td>
                        <td>R<?php echo number_format($ad['price'], 2); ?></td>
                        <td>
                            <span class="badge badge-<?php echo $ad['status'] == 'available' ? 'success' : 'secondary'; ?>">
                                <?php echo ucfirst($ad['status']); ?>
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </table>
            </div>
            
            <!-- Recent Users -->
            <div class="card" style="flex: 1; min-width: 300px;">
                <h3>Recent Users</h3>
                <table class="simple-table">
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Joined</th>
                        <th>Status</th>
                    </tr>
                    <?php foreach ($recent_users as $user): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($user['name'] . ' ' . $user['surname']); ?></td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td><?php echo date('M d', strtotime($user['created_at'])); ?></td>
                        <td>
                            <span class="badge badge-<?php echo $user['blocked'] ? 'danger' : 'success'; ?>">
                                <?php echo $user['blocked'] ? 'Blocked' : 'Active'; ?>
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </table>
            </div>
        </div>

        <!-- Simple footer -->
        <div style="text-align: center; margin-top: 40px; padding: 20px; color: #666; border-top: 1px solid #ddd;">
            <p>Jozi Hustle Admin Panel - Last updated: <?php echo date('Y-m-d H:i:s'); ?></p>
        </div>
    </div>
</body>
</html>
