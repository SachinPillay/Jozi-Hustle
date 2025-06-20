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

// Get messages with user details (simplified)
$query = "SELECT c.*, 
          buyer.name as buyer_name, buyer.surname as buyer_surname, buyer.email as buyer_email,
          seller.name as seller_name, seller.surname as seller_surname, seller.email as seller_email
          FROM chats c
          JOIN users buyer ON c.buyer_id = buyer.id
          JOIN users seller ON c.seller_id = seller.id
          ORDER BY c.timestamp DESC
          LIMIT 50";
$stmt = $pdo->query($query);
$messages = $stmt->fetchAll();

// Simple statistics
$total_messages = count($messages);
$unread_messages = 0;
$today_messages = 0;

foreach ($messages as $message) {
    if (!$message['is_read']) $unread_messages++;
    if (date('Y-m-d', strtotime($message['timestamp'])) == date('Y-m-d')) $today_messages++;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages - Admin Panel</title>
    <link href="admin-styles.css" rel="stylesheet">
</head>
<body>
    <!-- Simple Navigation -->
    <nav class="admin-nav">
        <a href="index.php">Dashboard</a>
        <a href="users.php">Users</a>
        <a href="ads.php">Ads</a>
        <a href="moderation.php">Moderation</a>
        <a href="messages.php" class="active">Messages</a>
        <a href="../index.php">Back to Site</a>
        <a href="../auth/logout.php">Logout</a>
    </nav>

    <div class="container">
        <h1>Messages</h1>
        <p style="text-align: center; color: #666;">View all user messages and communications</p>

        <!-- Simple Statistics -->
        <div class="stats-row">
            <div class="stat-box">
                <div class="stat-number"><?php echo $total_messages; ?></div>
                <div class="stat-label">Total Messages</div>
            </div>
            
            <div class="stat-box">
                <div class="stat-number"><?php echo $today_messages; ?></div>
                <div class="stat-label">Today's Messages</div>
            </div>
            
            <div class="stat-box">
                <div class="stat-number"><?php echo $unread_messages; ?></div>
                <div class="stat-label">Unread Messages</div>
            </div>
        </div>

        <!-- Messages Table -->
        <div class="card">
            <h3>Recent Messages (Last 50)</h3>
            
            <?php if (empty($messages)): ?>
                <p style="text-align: center; color: #666; padding: 20px;">No messages found.</p>
            <?php else: ?>
                <table class="simple-table">
                    <tr>
                        <th>ID</th>
                        <th>From (Buyer)</th>
                        <th>To (Seller)</th>
                        <th>Message</th>
                        <th>Date & Time</th>
                        <th>Status</th>
                    </tr>
                    <?php foreach ($messages as $message): ?>
                    <tr style="<?php echo !$message['is_read'] ? 'background-color: #fff3cd;' : ''; ?>">
                        <td><?php echo $message['id']; ?></td>
                        <td>
                            <strong><?php echo htmlspecialchars($message['buyer_name'] . ' ' . $message['buyer_surname']); ?></strong><br>
                            <small style="color: #666;"><?php echo htmlspecialchars($message['buyer_email']); ?></small>
                        </td>
                        <td>
                            <strong><?php echo htmlspecialchars($message['seller_name'] . ' ' . $message['seller_surname']); ?></strong><br>
                            <small style="color: #666;"><?php echo htmlspecialchars($message['seller_email']); ?></small>
                        </td>
                        <td>
                            <div style="max-width: 300px; word-wrap: break-word;">
                                <?php 
                                $msg = htmlspecialchars($message['message']);
                                echo strlen($msg) > 100 ? substr($msg, 0, 100) . '...' : $msg;
                                ?>
                            </div>
                        </td>
                        <td><?php echo date('Y-m-d H:i:s', strtotime($message['timestamp'])); ?></td>
                        <td>
                            <span class="badge badge-<?php echo $message['is_read'] ? 'success' : 'warning'; ?>">
                                <?php echo $message['is_read'] ? 'Read' : 'Unread'; ?>
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </table>
            <?php endif; ?>
        </div>

        <!-- Information Card -->
        <div class="card">
            <h3>About Messages</h3>
            <ul>
                <li>This shows the last 50 messages between buyers and sellers</li>
                <li>Yellow highlighted rows are unread messages</li>
                <li>Messages are automatically generated when users communicate about ads</li>
                <li>Use this to monitor user interactions and identify any issues</li>
                <li>Messages help users negotiate prices and arrange meetups</li>
            </ul>
        </div>

        <!-- Quick Stats -->
        <div class="card">
            <h3>Message Statistics</h3>
            <div style="display: flex; gap: 20px; flex-wrap: wrap;">
                <div>
                    <strong>Total Messages:</strong> <?php echo $total_messages; ?>
                </div>
                <div>
                    <strong>Messages Today:</strong> <?php echo $today_messages; ?>
                </div>
                <div>
                    <strong>Unread Messages:</strong> <?php echo $unread_messages; ?>
                </div>
                <div>
                    <strong>Read Messages:</strong> <?php echo $total_messages - $unread_messages; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
