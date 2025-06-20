<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once '../config/db_config.php';
require_once '../includes/auth.php';
requireLogin();


$user_id = $_SESSION['user']['id'];

// Determine if user is acting as buyer or seller based on URL parameters
$seller_id = $_GET['seller_id'] ?? null;
$buyer_id = $_GET['buyer_id'] ?? null;

if ($seller_id) {
    // User is acting as buyer, chatting with a seller
    $buyer_id = $user_id;
    $user_role = 'buyer';
} elseif ($buyer_id) {
    // User is acting as seller, chatting with a buyer
    $seller_id = $user_id;
    $user_role = 'seller';
} else {
    // No specific chat selected, show available sellers for this user (acting as buyer)
    $buyer_id = $user_id;
    $user_role = 'buyer';
    $seller_id = null;
}

// Fetch counterpart name
$counterpart_name = "Unknown";
$counterpart_id = $user_role == 'buyer' ? $seller_id : $buyer_id;
if ($counterpart_id) {
    $stmt = $conn->prepare("SELECT name FROM users WHERE id = ?");
    $stmt->execute([$counterpart_id]);
    $counterpart = $stmt->fetch(PDO::FETCH_ASSOC);
    $counterpart_name = $counterpart ? $counterpart['name'] : "Unknown";
}

// Fetch sellers or buyers list
if ($user_role == 'buyer') {
    $stmt = $conn->prepare("SELECT DISTINCT users.id, users.name FROM users JOIN ads ON users.id = ads.seller_id");
    $stmt->execute();
    $sellers = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    $stmt = $conn->prepare("SELECT DISTINCT users.id, users.name FROM users JOIN chats ON users.id = chats.buyer_id WHERE chats.seller_id = ?");
    $stmt->execute([$seller_id]);
    $buyers = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Handle new message
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message'])) {
    $message = trim($_POST['message']);
    if (!empty($message)) {
        // Add a sender_id field to track who sent the message
        $stmt = $conn->prepare("INSERT INTO chats (buyer_id, seller_id, message, sender_id, timestamp, is_read) VALUES (?, ?, ?, ?, NOW(), 0)");
        $stmt->execute([$buyer_id, $seller_id, $message, $user_id]);
        header("Location: chat.php?" . ($user_role == 'buyer' ? "seller_id=$seller_id" : "buyer_id=$buyer_id"));
        exit;
    }
}

// Mark messages as read (when user is viewing them)
if ($buyer_id && $seller_id) {
    $stmt = $conn->prepare("UPDATE chats SET is_read = 1 WHERE buyer_id = ? AND seller_id = ? AND " . ($user_role == 'seller' ? "is_read = 0" : "1=0"));
    $stmt->execute([$buyer_id, $seller_id]);
}

// Fetch chat messages
$messages = [];
if ($buyer_id && $seller_id) {
    $stmt = $conn->prepare("SELECT chats.*, 
                            buyer_user.name AS buyer_name, 
                            seller_user.name AS seller_name,
                            sender_user.name AS sender_name
                            FROM chats 
                            JOIN users AS buyer_user ON chats.buyer_id = buyer_user.id 
                            JOIN users AS seller_user ON chats.seller_id = seller_user.id 
                            LEFT JOIN users AS sender_user ON chats.sender_id = sender_user.id
                            WHERE buyer_id = ? AND seller_id = ? 
                            ORDER BY timestamp ASC");
    $stmt->execute([$buyer_id, $seller_id]);
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat - Jozi Hustle</title>
    <link rel="stylesheet" href="../assets/css/StyleSheet.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .chat-container {
            display: flex;
            height: calc(100vh - 200px);
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            overflow: hidden;
            margin: 2rem auto;
            max-width: 1200px;
        }

        .chat-list {
            width: 30%;
            border-right: 1px solid #e0e0e0;
            background: #f8f9fa;
            display: flex;
            flex-direction: column;
        }

        .chat-list-header {
            padding: 1.5rem;
            background: #667eea;
            color: white;
        }

        .chat-list-header h3 {
            margin: 0;
            font-size: 1.2rem;
            font-weight: 600;
        }

        .chat-users {
            padding: 1.5rem;
            overflow-y: auto;
            flex-grow: 1;
        }

        .chat-list ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .chat-list li {
            margin-bottom: 0.8rem;
        }

        .chat-list a {
            display: flex;
            align-items: center;
            padding: 1rem;
            background: white;
            border-radius: 10px;
            text-decoration: none;
            color: #333;
            transition: all 0.3s ease;
            border: 1px solid #e0e0e0;
        }

        .chat-list a:hover {
            background: #667eea;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
        }

        .chat-list a.active {
            background: #667eea;
            color: white;
            border-color: #667eea;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            background: #e9ecef;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 1rem;
            font-size: 1.2rem;
            color: #6c757d;
        }

        .chat-list a:hover .user-avatar {
            background: rgba(255, 255, 255, 0.2);
            color: white;
        }

        .chat-list a.active .user-avatar {
            background: rgba(255, 255, 255, 0.2);
            color: white;
        }
        
        .chat-window {
            width: 70%;
            display: flex;
            flex-direction: column;
            background: white;
        }

        .chat-header {
            padding: 1.5rem;
            background: white;
            border-bottom: 1px solid #e0e0e0;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .chat-header h3 {
            margin: 0;
            color: #333;
            font-size: 1.3rem;
            font-weight: 600;
            display: flex;
            align-items: center;
        }

        .chat-header h3 i {
            margin-right: 0.5rem;
            color: #667eea;
        }

        .chat-box {
            flex-grow: 1;
            padding: 1.5rem;
            overflow-y: auto;
            background: #f8f9fa;
            display: flex;
            flex-direction: column;
        }

        .message-group {
            margin-bottom: 1rem;
            display: flex;
            flex-direction: column;
        }

        .message-group:last-child {
            margin-bottom: 0;
        }
        
        .message-bubble {
            max-width: 70%;
            padding: 1rem 1.5rem;
            margin: 0.3rem 0;
            border-radius: 20px;
            position: relative;
            word-wrap: break-word;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            animation: fadeIn 0.3s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .buyer {
            background: #667eea;
            color: white;
            align-self: flex-start;
            margin-right: auto;
            border-bottom-left-radius: 5px;
        }

        .seller {
            background: #28a745;
            color: white;
            align-self: flex-end;
            margin-left: auto;
            border-bottom-right-radius: 5px;
        }

        .sender-name {
            font-size: 0.8rem;
            margin-bottom: 0.3rem;
            opacity: 0.8;
        }
        
        .timestamp {
            font-size: 0.7rem;
            opacity: 0.8;
            margin-top: 0.3rem;
            font-weight: 500;
        }

        .message-status {
            display: flex;
            align-items: center;
            gap: 0.3rem;
            margin-top: 0.2rem;
            font-size: 0.7rem;
        }

        .unread::after {
            content: "●";
            color: #ff4757;
            font-size: 12px;
            animation: pulse 2s infinite;
            margin-left: 0.3rem;
        }

        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.5; }
            100% { opacity: 1; }
        }

        .message-form-container {
            padding: 1rem;
            background: white;
            border-top: 1px solid #e0e0e0;
        }

        .message-form {
            display: flex;
            gap: 1rem;
            background: white;
            border-radius: 10px;
            padding: 0.5rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .message-form input[type="text"] {
            flex: 1;
            padding: 1rem;
            border: 1px solid #e0e0e0;
            border-radius: 25px;
            font-size: 1rem;
            outline: none;
            transition: all 0.3s ease;
        }

        .message-form input[type="text"]:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .message-form button {
            padding: 1rem 2rem;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 25px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .message-form button:hover {
            background: #5a6eea;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }

        .message-form button i {
            font-size: 1.1rem;
        }

        .empty-chat {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100%;
            color: #6c757d;
            padding: 2rem;
            text-align: center;
        }

        .empty-chat i {
            font-size: 3rem;
            margin-bottom: 1rem;
            color: #e9ecef;
        }
        
        @media (max-width: 768px) {
            .chat-container {
                flex-direction: column;
                height: auto;
            }
            
            .chat-list, .chat-window {
                width: 100%;
            }
            
            .chat-list {
                max-height: 200px;
            }
        }
    </style>
</head>
<body>

<nav class="navbar">
    <div class="nav-container">
        <a href="../index.php" class="logo">
            <i class="fas fa-store"></i> Jozi Hustle
        </a>
        <ul class="nav-links">
            <li><a href="../index.php"><i class="fas fa-home"></i> Home</a></li>
            <li><a href="categories.php"><i class="fas fa-list"></i> Categories</a></li>
            <li><a href="chat.php" class="active"><i class="fas fa-comments"></i> Chat</a></li>
            <li><a href="../ads/ad.php"><i class="fas fa-plus"></i> Post Ad</a></li>
            <li><a href="wishlist.php"><i class="fas fa-heart"></i> Wishlist</a></li>
            <li><a href="../user/user_account.php"><i class="fas fa-user"></i> My Account</a></li>
            <li><a href="../auth/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </div>
</nav>

<div class="container main-content">
    <div class="text-center mb-4">
        <h1 style="color: #667eea; margin-bottom: 0.5rem;">Chat Center</h1>
        <p style="color: #666;">Connect with buyers and sellers</p>
    </div>
    
<div class="chat-container">
    <!-- Sidebar -->
    <div class="chat-list">
        <div class="chat-list-header">
            <h3><i class="fas fa-users"></i> <?= $user_role == 'buyer' ? 'Available Sellers' : 'Interested Buyers' ?></h3>
        </div>
        <div class="chat-users">
            <ul>
                <?php foreach (($user_role == 'buyer' ? $sellers : $buyers) as $person): ?>
                    <li>
                        <a href="chat.php?<?= $user_role == 'buyer' ? "seller_id={$person['id']}" : "buyer_id={$person['id']}" ?>"
                           class="<?= ($counterpart_id == $person['id']) ? 'active' : '' ?>">
                            <div class="user-avatar">
                                <i class="fas fa-user"></i>
                            </div>
                            <span><?= htmlspecialchars($person['name']) ?></span>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>

    <!-- Chat Window -->
    <div class="chat-window">
        <div class="chat-header">
            <h3>
                <i class="fas fa-comment"></i>
                Chat with <?= htmlspecialchars($counterpart_name) ?>
            </h3>
        </div>
        <div class="chat-box">
            <?php if (empty($messages)): ?>
                <div class="empty-chat">
                    <i class="fas fa-comments"></i>
                    <h3>No messages yet</h3>
                    <p>Start the conversation by sending a message!</p>
                </div>
            <?php else: ?>
                <?php foreach ($messages as $msg): ?>
                    <?php 
                    // Use sender_id to determine who sent the message
                    $is_my_message = ($msg['sender_id'] == $user_id);
                    $sender_name = $msg['sender_name'] ?? ($is_my_message ? $_SESSION['user']['name'] : $counterpart_name);
                    ?>
                    <div class="message-bubble <?= $is_my_message ? 'seller' : 'buyer' ?>">
                        <strong><?= htmlspecialchars($sender_name) ?></strong><br>
                        <?= htmlspecialchars($msg['message']) ?>
                        <div class="timestamp">
                            <?= date('g:i A', strtotime($msg['timestamp'])) ?>
                            <?php if ($msg['is_read'] == 0 && !$is_my_message): ?>
                                <span class="unread"></span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Message Input -->
        <div class="message-form-container">
            <form method="post" class="message-form">
                <input type="hidden" name="receiver_id" value="<?= htmlspecialchars($user_role == 'buyer' ? $seller_id : $buyer_id); ?>">
                <input type="text" name="message" placeholder="Type your message here..." required>
                <button type="submit">
                    <i class="fas fa-paper-plane"></i> Send
                </button>
            </form>
        </div>
    </div>
</div>
</div>

<script src="../assets/js/script.js"></script>
<script>
// Scroll chat to bottom on load
function scrollChatToBottom() {
    const chatBox = document.querySelector('.chat-box');
    chatBox.scrollTop = chatBox.scrollHeight;
}

// Scroll on page load
document.addEventListener('DOMContentLoaded', scrollChatToBottom);

// Format time to 12-hour format with AM/PM
function formatTime(timestamp) {
    const date = new Date(timestamp);
    return date.toLocaleTimeString('en-US', { 
        hour: 'numeric', 
        minute: '2-digit',
        hour12: true 
    });
}

// Update all timestamps
document.querySelectorAll('.timestamp').forEach(timestamp => {
    const time = timestamp.textContent.trim();
    if (time.match(/^\d{2}:\d{2}$/)) {
        const today = new Date().toISOString().split('T')[0];
        timestamp.textContent = formatTime(today + 'T' + time);
    }
});

// Auto focus message input when chat loads
if (document.querySelector('.message-form input[type="text"]')) {
    document.querySelector('.message-form input[type="text"]').focus();
}

// Scroll to bottom when new message is sent
document.querySelector('.message-form').addEventListener('submit', () => {
    setTimeout(scrollChatToBottom, 100);
});
</script>
</body>
</html>
