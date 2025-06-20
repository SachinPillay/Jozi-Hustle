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

// Handle ad actions
if ($_POST) {
    if (isset($_POST['action']) && isset($_POST['ad_id'])) {
        $ad_id = (int)$_POST['ad_id'];
        $action = $_POST['action'];
        
        if ($action == 'block') {
            $stmt = $pdo->prepare("UPDATE ads SET blocked = 1 WHERE id = ?");
            $stmt->execute([$ad_id]);
            $message = "Ad blocked successfully.";
        } elseif ($action == 'unblock') {
            $stmt = $pdo->prepare("UPDATE ads SET blocked = 0 WHERE id = ?");
            $stmt->execute([$ad_id]);
            $message = "Ad unblocked successfully.";
        } elseif ($action == 'delete') {
            $stmt = $pdo->prepare("DELETE FROM ads WHERE id = ?");
            $stmt->execute([$ad_id]);
            $message = "Ad deleted successfully.";
        }
    }
}

// Get search term
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$category = isset($_GET['category']) ? $_GET['category'] : 'all';

// Get ads
if (!empty($search)) {
    if ($category != 'all') {
        $stmt = $pdo->prepare("SELECT a.*, u.name, u.surname FROM ads a JOIN users u ON a.seller_id = u.id WHERE (a.title LIKE ? OR a.description LIKE ?) AND a.category = ? ORDER BY a.created_at DESC");
        $stmt->execute(["%$search%", "%$search%", $category]);
    } else {
        $stmt = $pdo->prepare("SELECT a.*, u.name, u.surname FROM ads a JOIN users u ON a.seller_id = u.id WHERE a.title LIKE ? OR a.description LIKE ? ORDER BY a.created_at DESC");
        $stmt->execute(["%$search%", "%$search%"]);
    }
} else {
    if ($category != 'all') {
        $stmt = $pdo->prepare("SELECT a.*, u.name, u.surname FROM ads a JOIN users u ON a.seller_id = u.id WHERE a.category = ? ORDER BY a.created_at DESC");
        $stmt->execute([$category]);
    } else {
        $stmt = $pdo->query("SELECT a.*, u.name, u.surname FROM ads a JOIN users u ON a.seller_id = u.id ORDER BY a.created_at DESC");
    }
}
$ads = $stmt->fetchAll();

// Count statistics
$total_ads = count($ads);
$blocked_ads = 0;
$active_ads = 0;
$sold_ads = 0;

foreach ($ads as $ad) {
    if ($ad['blocked']) {
        $blocked_ads++;
    } elseif ($ad['status'] == 'available') {
        $active_ads++;
    } elseif ($ad['status'] == 'sold') {
        $sold_ads++;
    }
}

$categories = ['Food', 'Electronics', 'Clothing', 'Beverages', 'Household Contents', 'Other'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ad Management - Admin Panel</title>
    <link href="admin-styles.css" rel="stylesheet">
</head>
<body>
    <!-- Simple Navigation -->
    <nav class="admin-nav">
        <a href="index.php">Dashboard</a>
        <a href="users.php">Users</a>
        <a href="ads.php" class="active">Ads</a>
        <a href="moderation.php">Moderation</a>
        <a href="messages.php">Messages</a>
        <a href="../index.php">Back to Site</a>
        <a href="../auth/logout.php">Logout</a>
    </nav>

    <div class="container">
        <h1>Ad Management</h1>

        <?php if (isset($message)): ?>
            <div style="background: #d4edda; color: #155724; padding: 10px; border-radius: 3px; margin-bottom: 20px;">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <!-- Simple Statistics -->
        <div class="stats-row">
            <div class="stat-box">
                <div class="stat-number"><?php echo $total_ads; ?></div>
                <div class="stat-label">Total Ads</div>
            </div>
            
            <div class="stat-box">
                <div class="stat-number"><?php echo $active_ads; ?></div>
                <div class="stat-label">Active Ads</div>
            </div>
            
            <div class="stat-box">
                <div class="stat-number"><?php echo $sold_ads; ?></div>
                <div class="stat-label">Sold Ads</div>
            </div>
            
            <div class="stat-box">
                <div class="stat-number"><?php echo $blocked_ads; ?></div>
                <div class="stat-label">Blocked Ads</div>
            </div>
        </div>

        <!-- Search Form -->
        <div class="card">
            <h3>Search Ads</h3>
            <form method="GET">
                <input type="text" name="search" placeholder="Search by title or description..." 
                       value="<?php echo htmlspecialchars($search); ?>" class="form-control" style="width: 300px; display: inline-block;">
                
                <select name="category" class="form-control" style="width: 200px; display: inline-block; margin-left: 10px;">
                    <option value="all">All Categories</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo $cat; ?>" <?php echo $category == $cat ? 'selected' : ''; ?>><?php echo $cat; ?></option>
                    <?php endforeach; ?>
                </select>
                
                <button type="submit" class="btn btn-primary">Search</button>
                <a href="ads.php" class="btn btn-secondary">Clear</a>
            </form>
        </div>

        <!-- Ads Table -->
        <div class="card">
            <h3>All Ads (<?php echo count($ads); ?> found)</h3>
            
            <?php if (empty($ads)): ?>
                <p style="text-align: center; color: #666; padding: 20px;">No ads found.</p>
            <?php else: ?>
                <table class="simple-table">
                    <tr>
                        <th>ID</th>
                        <th>Image</th>
                        <th>Title</th>
                        <th>Seller</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Status</th>
                        <th>Created Date</th>
                        <th>Actions</th>
                    </tr>
                    <?php foreach ($ads as $ad): ?>
                    <tr>
                        <td><?php echo $ad['id']; ?></td>
                        <td>
                            <?php if ($ad['image']): ?>
                                <img src="../uploads/<?php echo basename($ad['image']); ?>" style="width: 50px; height: 50px; object-fit: cover;" alt="Ad image">
                            <?php else: ?>
                                <div style="width: 50px; height: 50px; background: #f0f0f0; display: flex; align-items: center; justify-content: center; color: #666;">No Image</div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <strong><?php echo htmlspecialchars($ad['title']); ?></strong><br>
                            <small style="color: #666;"><?php echo htmlspecialchars(substr($ad['description'], 0, 50)) . '...'; ?></small>
                        </td>
                        <td><?php echo htmlspecialchars($ad['name'] . ' ' . $ad['surname']); ?></td>
                        <td><?php echo $ad['category']; ?></td>
                        <td>R<?php echo number_format($ad['price'], 2); ?></td>
                        <td>
                            <?php if ($ad['blocked']): ?>
                                <span class="badge badge-danger">Blocked</span>
                            <?php else: ?>
                                <span class="badge badge-<?php echo $ad['status'] == 'available' ? 'success' : 'secondary'; ?>">
                                    <?php echo ucfirst($ad['status']); ?>
                                </span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo date('Y-m-d', strtotime($ad['created_at'])); ?></td>
                        <td>
                            <?php if ($ad['blocked']): ?>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="ad_id" value="<?php echo $ad['id']; ?>">
                                    <input type="hidden" name="action" value="unblock">
                                    <button type="submit" class="btn btn-success" onclick="return confirm('Unblock this ad?')">
                                        Unblock
                                    </button>
                                </form>
                            <?php else: ?>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="ad_id" value="<?php echo $ad['id']; ?>">
                                    <input type="hidden" name="action" value="block">
                                    <button type="submit" class="btn btn-warning" onclick="return confirm('Block this ad?')">
                                        Block
                                    </button>
                                </form>
                            <?php endif; ?>
                            
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="ad_id" value="<?php echo $ad['id']; ?>">
                                <input type="hidden" name="action" value="delete">
                                <button type="submit" class="btn btn-danger" onclick="return confirm('Delete this ad? This cannot be undone!')">
                                    Delete
                                </button>
                            </form>
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
                <li>Use the search box to find specific ads by title or description</li>
                <li>Filter ads by category using the dropdown</li>
                <li>Block ads to hide them from users</li>
                <li>Delete ads to permanently remove them from the system</li>
            </ul>
        </div>
    </div>
</body>
</html>
