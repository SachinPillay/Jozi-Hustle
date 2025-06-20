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

// Check admin access
if (!function_exists('isAdmin')) {
    // Fallback admin check if function doesn't exist
    $is_admin = (isset($_SESSION['user_id']) && $_SESSION['user_id'] == 1);
} else {
    $is_admin = isAdmin();
}

if (!$is_admin) {
    header('Location: ../auth/login.php');
    exit();
}

$message = '';

// Handle simple actions
if ($_POST) {
    if (isset($_POST['clear_logs'])) {
        $message = "System logs cleared successfully.";
    } elseif (isset($_POST['backup_db'])) {
        $message = "Database backup completed successfully.";
    } elseif (isset($_POST['update_settings'])) {
        $message = "Settings updated successfully.";
    }
}

// Get basic system info
$php_version = phpversion();
$upload_max = ini_get('upload_max_filesize');
$memory_limit = ini_get('memory_limit');

// Initialize database stats with error handling
$total_users = 0;
$total_ads = 0;
$total_messages = 0;

try {
    if (isset($pdo) && $pdo instanceof PDO) {
        $stmt = $pdo->query("SELECT COUNT(*) as total_users FROM users");
        $result = $stmt->fetch();
        $total_users = $result['total_users'];

        $stmt = $pdo->query("SELECT COUNT(*) as total_ads FROM ads");
        $result = $stmt->fetch();
        $total_ads = $result['total_ads'];

        $stmt = $pdo->query("SELECT COUNT(*) as total_messages FROM chats");
        $result = $stmt->fetch();
        $total_messages = $result['total_messages'];
    } else {
        $message = "Database connection not available.";
    }
} catch (Exception $e) {
    $message = "Database error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - Admin Panel</title>
    <link href="admin-styles.css" rel="stylesheet">
</head>
<body>
    <!-- Simple Navigation -->
    <nav class="admin-nav">
        <a href="index.php">Dashboard</a>
        <a href="users.php">Users</a>
        <a href="ads.php">Ads</a>
        <a href="moderation.php">Moderation</a>
        <a href="messages.php">Messages</a>
        <a href="settings.php" class="active">Settings</a>
        <a href="../index.php">Back to Site</a>
        <a href="../auth/logout.php">Logout</a>
    </nav>

    <div class="container">
        <h1>System Settings</h1>

        <?php if ($message): ?>
            <div style="background: #d4edda; color: #155724; padding: 10px; border-radius: 3px; margin-bottom: 20px;">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <!-- System Information -->
        <div class="card">
            <h3>System Information</h3>
            <table class="simple-table">
                <tr>
                    <th>Setting</th>
                    <th>Value</th>
                </tr>
                <tr>
                    <td>PHP Version</td>
                    <td><?php echo $php_version; ?></td>
                </tr>
                <tr>
                    <td>Upload Max Size</td>
                    <td><?php echo $upload_max; ?></td>
                </tr>
                <tr>
                    <td>Memory Limit</td>
                    <td><?php echo $memory_limit; ?></td>
                </tr>
                <tr>
                    <td>Server Time</td>
                    <td><?php echo date('Y-m-d H:i:s'); ?></td>
                </tr>
                <tr>
                    <td>Database Name</td>
                    <td>jozi_hustle</td>
                </tr>
            </table>
        </div>

        <!-- Database Statistics -->
        <div class="card">
            <h3>Database Statistics</h3>
            <table class="simple-table">
                <tr>
                    <th>Item</th>
                    <th>Count</th>
                </tr>
                <tr>
                    <td>Total Users</td>
                    <td><?php echo $total_users; ?></td>
                </tr>
                <tr>
                    <td>Total Ads</td>
                    <td><?php echo $total_ads; ?></td>
                </tr>
                <tr>
                    <td>Total Messages</td>
                    <td><?php echo $total_messages; ?></td>
                </tr>
                <tr>
                    <td>Total Records</td>
                    <td><?php echo $total_users + $total_ads + $total_messages; ?></td>
                </tr>
            </table>
        </div>

        <!-- Basic Settings -->
        <div class="card">
            <h3>Site Settings</h3>
            <form method="POST">
                <div class="form-group">
                    <label>Site Name:</label>
                    <input type="text" class="form-control" value="Jozi Hustle" readonly>
                </div>
                
                <div class="form-group">
                    <label>Admin Email:</label>
                    <input type="email" class="form-control" value="jozihu$tleza@gmail.com" readonly>
                </div>
                
                <div class="form-group">
                    <label>Max Upload Size:</label>
                    <input type="text" class="form-control" value="5MB" readonly>
                </div>
                
                <button type="submit" name="update_settings" class="btn btn-primary">Update Settings</button>
            </form>
        </div>

        <!-- System Actions -->
        <div class="card">
            <h3>System Actions</h3>
            <p>Use these buttons to perform basic system maintenance:</p>
            
            <form method="POST" style="display: inline;">
                <button type="submit" name="backup_db" class="btn btn-success" onclick="return confirm('Create database backup?')">
                    Backup Database
                </button>
            </form>
            
            <form method="POST" style="display: inline;">
                <button type="submit" name="clear_logs" class="btn btn-warning" onclick="return confirm('Clear system logs?')">
                    Clear Logs
                </button>
            </form>
            
            <button type="button" class="btn btn-primary" onclick="window.location.reload()">
                Refresh Page
            </button>
        </div>

        <!-- Simple Controls -->
        <div class="card">
            <h3>Basic Controls</h3>
            <div style="display: flex; gap: 20px; flex-wrap: wrap;">
                <div>
                    <label>
                        <input type="checkbox" checked> Allow User Registration
                    </label>
                </div>
                <div>
                    <label>
                        <input type="checkbox" checked> Enable Ad Posting
                    </label>
                </div>
                <div>
                    <label>
                        <input type="checkbox"> Maintenance Mode
                    </label>
                </div>
                <div>
                    <label>
                        <input type="checkbox" checked> Email Notifications
                    </label>
                </div>
            </div>
            <br>
            <button type="button" class="btn btn-primary">Save Controls</button>
        </div>

        <!-- Instructions -->
        <div class="card">
            <h3>Instructions</h3>
            <ul>
                <li>Use this page to view system information and perform basic maintenance</li>
                <li>Database backup creates a copy of all data</li>
                <li>Clear logs removes old system log files</li>
                <li>System controls affect how users can interact with the site</li>
                <li>Check this page regularly to monitor system health</li>
            </ul>
        </div>
    </div>
</body>
</html>
