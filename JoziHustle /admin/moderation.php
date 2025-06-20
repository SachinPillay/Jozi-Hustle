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

$message = '';

// Handle moderation actions
if ($_POST) {
    if (isset($_POST['approve_ad'])) {
        $ad_id = (int)$_POST['ad_id'];
        $stmt = $pdo->prepare("UPDATE ads SET blocked = 0 WHERE id = ?");
        $stmt->execute([$ad_id]);
        $message = "Ad approved successfully.";
    } elseif (isset($_POST['reject_ad'])) {
        $ad_id = (int)$_POST['ad_id'];
        $stmt = $pdo->prepare("UPDATE ads SET blocked = 1 WHERE id = ?");
        $stmt->execute([$ad_id]);
        $message = "Ad blocked successfully.";
    }
}

// Get recent ads for review
$recent_ads = $pdo->query("SELECT a.*, u.name, u.surname FROM ads a 
                          JOIN users u ON a.seller_id = u.id 
                          ORDER BY a.created_at DESC LIMIT 10")->fetchAll();

// Get blocked content
$blocked_ads = $pdo->query("SELECT a.*, u.name, u.surname FROM ads a 
                           JOIN users u ON a.seller_id = u.id 
                           WHERE a.blocked = 1 LIMIT 10")->fetchAll();

$blocked_users = $pdo->query("SELECT * FROM users WHERE blocked = 1 LIMIT 10")->fetchAll();

// Simple counts
$blocked_ads_count = count($blocked_ads);
$blocked_users_count = count($blocked_users);
$recent_ads_count = count($recent_ads);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Content Moderation - Admin Panel</title>
    <link href="admin-styles.css" rel="stylesheet">
</head>
<body>
    <!-- Simple Navigation -->
    <nav class="admin-nav">
        <a href="index.php">Dashboard</a>
        <a href="users.php">Users</a>
        <a href="ads.php">Ads</a>
        <a href="moderation.php" class="active">Moderation</a>
        <a href="messages.php">Messages</a>
        <a href="../index.php">Back to Site</a>
        <a href="../auth/logout.php">Logout</a>
    </nav>

    <div class="container">
        <h1>Content Moderation</h1>

        <?php if ($message): ?>
            <div style="background: #d4edda; color: #155724; padding: 10px; border-radius: 3px; margin-bottom: 20px;">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <!-- Simple Statistics -->
        <div class="stats-row">
            <div class="stat-box">
                <div class="stat-number"><?php echo $blocked_ads_count; ?></div>
                <div class="stat-label">Blocked Ads</div>
            </div>
            
            <div class="stat-box">
                <div class="stat-number"><?php echo $blocked_users_count; ?></div>
                <div class="stat-label">Blocked Users</div>
            </div>
            
            <div class="stat-box">
                <div class="stat-number"><?php echo $recent_ads_count; ?></div>
                <div class="stat-label">Recent Ads</div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="card">
            <h3>Quick Actions</h3>
            <a href="ads.php" class="btn btn-primary">Review All Ads</a>
            <a href="users.php" class="btn btn-primary">Review All Users</a>
            <a href="messages.php" class="btn btn-success">View Messages</a>
        </div>

        <!-- Recent Ads for Review -->
        <div class="card">
            <h3>Recent Ads for Review</h3>
            
            <?php if (empty($recent_ads)): ?>
                <p style="color: #666;">No recent ads to review.</p>
            <?php else: ?>
                <table class="simple-table">
                    <tr>
                        <th>ID</th>
                        <th>Title</th>
                        <th>Seller</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                    <?php foreach ($recent_ads as $ad): ?>
                    <tr>
                        <td><?php echo $ad['id']; ?></td>
                        <td><?php echo htmlspecialchars($ad['title']); ?></td>
                        <td><?php echo htmlspecialchars($ad['name'] . ' ' . $ad['surname']); ?></td>
                        <td><?php echo $ad['category']; ?></td>
                        <td>R<?php echo number_format($ad['price'], 2); ?></td>
                        <td>
                            <span class="badge badge-<?php echo $ad['blocked'] ? 'danger' : 'success'; ?>">
                                <?php echo $ad['blocked'] ? 'Blocked' : 'Active'; ?>
                            </span>
                        </td>
                        <td><?php echo date('Y-m-d', strtotime($ad['created_at'])); ?></td>
                        <td>
                            <?php if (!$ad['blocked']): ?>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="ad_id" value="<?php echo $ad['id']; ?>">
                                    <button type="submit" name="reject_ad" class="btn btn-danger" onclick="return confirm('Block this ad?')">
                                        Block
                                    </button>
                                </form>
                            <?php else: ?>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="ad_id" value="<?php echo $ad['id']; ?>">
                                    <button type="submit" name="approve_ad" class="btn btn-success" onclick="return confirm('Approve this ad?')">
                                        Approve
                                    </button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </table>
            <?php endif; ?>
        </div>

        <!-- Blocked Content -->
        <?php if (!empty($blocked_ads)): ?>
        <div class="card">
            <h3>Blocked Ads (<?php echo count($blocked_ads); ?>)</h3>
            <table class="simple-table">
                <tr>
                    <th>Title</th>
                    <th>Seller</th>
                    <th>Category</th>
                    <th>Date Blocked</th>
                    <th>Action</th>
                </tr>
                <?php foreach ($blocked_ads as $ad): ?>
                <tr>
                    <td><?php echo htmlspecialchars($ad['title']); ?></td>
                    <td><?php echo htmlspecialchars($ad['name'] . ' ' . $ad['surname']); ?></td>
                    <td><?php echo $ad['category']; ?></td>
                    <td><?php echo date('Y-m-d', strtotime($ad['created_at'])); ?></td>
                    <td>
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="ad_id" value="<?php echo $ad['id']; ?>">
                            <button type="submit" name="approve_ad" class="btn btn-success" onclick="return confirm('Approve this ad?')">
                                Approve
                            </button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>
        <?php endif; ?>

        <!-- Blocked Users -->
        <?php if (!empty($blocked_users)): ?>
        <div class="card">
            <h3>Blocked Users (<?php echo count($blocked_users); ?>)</h3>
            <table class="simple-table">
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Joined Date</th>
                    <th>Action</th>
                </tr>
                <?php foreach ($blocked_users as $user): ?>
                <tr>
                    <td><?php echo htmlspecialchars($user['name'] . ' ' . $user['surname']); ?></td>
                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                    <td><?php echo date('Y-m-d', strtotime($user['created_at'])); ?></td>
                    <td>
                        <a href="users.php" class="btn btn-primary">Review in User Management</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>
        <?php endif; ?>

        <!-- Instructions -->
        <div class="card">
            <h3>Moderation Instructions</h3>
            <ul>
                <li>Review recent ads to ensure they meet community guidelines</li>
                <li>Block inappropriate content or spam</li>
                <li>Approve blocked content if it was incorrectly flagged</li>
                <li>Use the User Management page to handle blocked users</li>
                <li>Check messages for any user reports or complaints</li>
            </ul>
        </div>
    </div>
</body>
</html>
