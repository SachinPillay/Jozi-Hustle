<?php
session_start();
require_once 'db_config.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$user = $_SESSION['user'];

// Fetch list of chat conversations
try {
    $stmt = $conn->prepare("
        SELECT DISTINCT c.chat_id, u.name, u.email, c.last_message
        FROM chats c
        JOIN users u ON (c.sender_id = u.id OR c.receiver_id = u.id)
        WHERE (c.sender_id = ? OR c.receiver_id = ?)
        ORDER BY c.last_message_time DESC
    ");
    $stmt->execute([$user['id'], $user['id']]);
    $conversations = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error fetching chats: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Chat - Jozi Hustle</title>
    <link rel="stylesheet" href="StyleSheet.css">
    <style>
        body {
            background: #EDF4F2;
            font-family: 'Poppins', sans-serif;
        }

        .chat-container {
            display: flex;
            height: 80vh;
            max-width: 900px;
            margin: auto;
            border-radius: 12px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        /* Left Side: Chat List */
        .chat-list {
            flex: 1;
            background: white;
            padding: 15px;
            overflow-y: auto;
        }

        .chat-item {
            padding: 12px;
            border-bottom: 1px solid #ddd;
            cursor: pointer;
            transition: 0.3s;
        }

        .chat-item:hover {
            background: #f0f0f0;
        }

        .chat-item strong {
            display: block;
            font-size: 16px;
        }

        .chat-item p {
            font-size: 14px;
            color: gray;
            margin: 4px 0 0;
        }

        /* Right Side: User Account Panel */
        .account-panel {
            width: 250px;
            background: white;
            padding: 20px;
            text-align: center;
        }

        .account-icon {
            font-size: 40px;
            color: #31473A;
            margin-bottom: 10px;
        }

        .account-panel h3 {
            font-size: 18px;
            margin-bottom: 5px;
        }

        .account-panel p {
            font-size: 14px;
            color: gray;
        }

        /* Chat Display */
        .chat-display {
            flex: 2;
            display: flex;
            flex-direction: column;
            background: white;
            padding: 15px;
        }

        .messages {
            flex: 1;
            overflow-y: auto;
            padding: 10px;
            border-radius: 8px;
            background: #f9f9f9;
        }

        .message {
            padding: 8px 12px;
            margin-bottom: 10px;
            border-radius: 6px;
            max-width: 70%;
        }

        .sent {
            background: #31473A;
            color: white;
            margin-left: auto;
        }

        .received {
            background: #ddd;
            color: black;
        }

        .input-box {
            display: flex;
            gap: 10px;
            margin-top: 10px;
        }

        .input-box input {
            flex: 1;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 6px;
        }

        .input-box button {
            background: #31473A;
            color: white;
            border: none;
            padding: 10px;
            border-radius: 6px;
            cursor: pointer;
        }
    </style>
</head>
<body>

<div class="chat-container">
    <!-- Chat List -->
    <div class="chat-list">
        <h3>Conversations</h3>
        <?php foreach ($conversations as $chat): ?>
            <div class="chat-item" onclick="openChat('<?php echo $chat['chat_id']; ?>')">
                <strong><?php echo htmlspecialchars($chat['name']); ?></strong>
                <p><?php echo htmlspecialchars($chat['last_message']); ?></p>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Chat Display -->
    <div class="chat-display">
        <div class="messages">
            <div class="message received">Hello! How can I help?</div>
            <div class="message sent">I'm interested in your listing.</div>
        </div>

        <div class="input-box">
            <input type="text" placeholder="Type a message..." id="messageInput">
            <button onclick="sendMessage()">Send</button>
        </div>
    </div>

    <!-- User Account Panel -->
    <div class="account-panel">
        <div class="account-icon">👤</div>
        <h3><?php echo htmlspecialchars($user['name']); ?></h3>
        <p><?php echo htmlspecialchars($user['email']); ?></p>
    </div>
</div>

<script>
    function openChat(chatId) {
        alert("Opening chat with ID: " + chatId);
    }

    function sendMessage() {
        let input = document.getElementById("messageInput");
        if (input.value.trim()) {
            let message = document.createElement("div");
            message.classList.add("message", "sent");
            message.innerText = input.value;
            document.querySelector(".messages").appendChild(message);
            input.value = "";
        }
    }
</script>

</body>
</html>
