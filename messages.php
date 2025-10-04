<?php
// messages.php
include 'db.php';
if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('Please login to view messages.'); redirect('login.php');</script>";
    exit;
}
// Session timeout
$timeout = 30 * 60;
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $timeout)) {
    session_unset();
    session_destroy();
    echo "<script>alert('Session timed out. Please login again.'); redirect('login.php');</script>";
    exit;
}
$_SESSION['last_activity'] = time();

$user_id = $_SESSION['user_id'];
$order_id = filter_input(INPUT_GET, 'order_id', FILTER_SANITIZE_NUMBER_INT);

try {
    // Fetch user's orders for selection
    $orders_stmt = $pdo->prepare("
        SELECT orders.*, gigs.title, users.username AS seller_name 
        FROM orders 
        JOIN gigs ON orders.gig_id = gigs.id 
        JOIN users ON orders.seller_id = users.id 
        WHERE orders.buyer_id = ? OR orders.seller_id = ?
    ");
    $orders_stmt->execute([$user_id, $user_id]);
    $orders = $orders_stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($order_id && is_numeric($order_id)) {
        // Validate order and user access
        $stmt = $pdo->prepare("
            SELECT orders.*, users.username AS seller_name 
            FROM orders 
            JOIN users ON orders.seller_id = users.id 
            WHERE orders.id = ? 
        ");
        $stmt->execute([$order_id]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$order || ($order['buyer_id'] != $user_id && $order['seller_id'] != $user_id)) {
            echo "<script>alert('You do not have access to this order or it does not exist.'); </script>";
            $order_id = null; // Reset to show order list
        } else {
            // Fetch messages for valid order
            $messages_stmt = $pdo->prepare("
                SELECT messages.*, users.username 
                FROM messages 
                JOIN users ON messages.sender_id = users.id 
                WHERE messages.order_id = ? 
                ORDER BY created_at
            ");
            $messages_stmt->execute([$order_id]);
            $messages = $messages_stmt->fetchAll(PDO::FETCH_ASSOC);

            // Handle message sending
            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                $message = filter_input(INPUT_POST, 'message', FILTER_SANITIZE_STRING);
                if (empty($message)) {
                    echo "<script>alert('Message cannot be empty.'); </script>";
                } else {
                    $receiver_id = ($user_id == $order['buyer_id']) ? $order['seller_id'] : $order['buyer_id'];
                    $stmt = $pdo->prepare("
                        INSERT INTO messages (order_id, sender_id, receiver_id, message) 
                        VALUES (?, ?, ?, ?)
                    ");
                    if ($stmt->execute([$order_id, $user_id, $receiver_id, $message])) {
                        echo "<script>redirect('messages.php?order_id=$order_id');</script>";
                    } else {
                        echo "<script>alert('Failed to send message. Please try again.'); </script>";
                    }
                }
            }
        }
    }
} catch (Exception $e) {
    echo "<script>alert('Database error: " . addslashes($e->getMessage()) . "'); </script>";
    $order_id = null; // Show order list on error
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages - Fiverr Clone</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: linear-gradient(135deg, #0f2027, #203a43, #2c5364);
            color: #fff;
            margin: 0;
        }
        .container {
            max-width: 900px;
            margin: 40px auto;
            padding: 20px;
        }
        .message-container {
            background: #fff;
            color: #333;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 6px 20px rgba(0,0,0,0.5);
            animation: slideIn 0.8s ease-out;
        }
        .message-container h2 {
            color: #ff2e63;
            margin-bottom: 20px;
            text-shadow: 0 0 5px rgba(255, 46, 99, 0.5);
        }
        .order-list {
            margin-bottom: 20px;
        }
        .order-card {
            background: #f9f9f9;
            padding: 15px;
            margin-bottom: 10px;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
            transition: transform 0.3s;
        }
        .order-card:hover {
            transform: translateY(-5px);
        }
        .order-card a {
            color: #ff2e63;
            text-decoration: none;
            font-weight: bold;
        }
        .order-card a:hover {
            color: #08d9d6;
        }
        .message-list {
            max-height: 400px;
            overflow-y: auto;
            margin-bottom: 20px;
            padding: 15px;
            border: 1px solid #ccc;
            border-radius: 10px;
            background: #f9f9f9;
        }
        .message {
            padding: 12px;
            margin-bottom: 15px;
            border-radius: 10px;
            animation: fadeIn 0.5s ease-in;
        }
        .message.sent {
            background: #ff2e63;
            color: #fff;
            margin-left: 20%;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }
        .message.received {
            background: #08d9d6;
            color: #fff;
            margin-right: 20%;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }
        .message small {
            font-size: 0.8em;
            opacity: 0.8;
        }
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 10px;
            resize: vertical;
            font-size: 1em;
            transition: border-color 0.3s;
        }
        .form-group textarea:focus {
            border-color: #ff2e63;
            outline: none;
        }
        .btn {
            background: #ff2e63;
            color: #fff;
            padding: 12px 25px;
            border: none;
            border-radius: 25px;
            cursor: pointer;
            font-size: 1.1em;
            transition: background 0.3s, transform 0.3s;
        }
        .btn:hover {
            background: #08d9d6;
            transform: scale(1.05);
        }
        .back-btn {
            background: #08d9d6;
            margin-top: 15px;
        }
        .back-btn:hover {
            background: #ff2e63;
        }
        .no-messages, .no-orders {
            text-align: center;
            color: #666;
            font-style: italic;
        }
        @keyframes slideIn {
            from { transform: translateY(-50px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @media (max-width: 480px) {
            .message-container {
                padding: 20px;
                max-width: 90%;
            }
            .message.sent, .message.received {
                margin-left: 10px;
                margin-right: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="message-container">
            <h2>Messages</h2>
            <?php if (!$order_id || !isset($order)): ?>
                <h3>Select an Order to Message</h3>
                <?php if (empty($orders)): ?>
                    <p class="no-orders">You have no orders. <a href="#" onclick="redirect('index.php')">Explore gigs</a> to get started!</p>
                <?php else: ?>
                    <div class="order-list">
                        <?php foreach ($orders as $order): ?>
                            <div class="order-card">
                                <p><strong>Order #<?php echo htmlspecialchars($order['id']); ?>:</strong> <?php echo htmlspecialchars($order['title']); ?></p>
                                <p><strong>Seller:</strong> <?php echo htmlspecialchars($order['seller_name']); ?></p>
                                <p><strong>Status:</strong> <?php echo ucfirst($order['status']); ?></p>
                                <p><a href="#" onclick="redirect('messages.php?order_id=<?php echo $order['id']; ?>')">Message</a></p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <button class="btn back-btn" onclick="redirect('profile.php')">Back to Profile</button>
                <?php endif; ?>
            <?php else: ?>
                <h3>Messages for Order #<?php echo htmlspecialchars($order_id); ?> (<?php echo htmlspecialchars($order['seller_name']); ?>)</h3>
                <div class="message-list">
                    <?php if (empty($messages)): ?>
                        <p class="no-messages">No messages yet. Start the conversation!</p>
                    <?php else: ?>
                        <?php foreach ($messages as $message): ?>
                            <div class="message <?php echo $message['sender_id'] == $user_id ? 'sent' : 'received'; ?>">
                                <p><strong><?php echo htmlspecialchars($message['username']); ?>:</strong> <?php echo htmlspecialchars($message['message']); ?></p>
                                <p><small><?php echo $message['created_at']; ?></small></p>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <form method="POST" onsubmit="return validateMessage()">
                    <div class="form-group">
                        <textarea name="message" id="message" rows="4" placeholder="Type your message..." required></textarea>
                    </div>
                    <button type="submit" class="btn">Send Message</button>
                    <button type="button" class="btn back-btn" onclick="redirect('order.php?id=<?php echo $order_id; ?>')">Back to Order</button>
                    <button type="button" class="btn back-btn" onclick="redirect('messages.php')">View All Orders</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
    <script>
        function redirect(url) {
            window.location.href = url;
        }
        function validateMessage() {
            const message = document.getElementById('message');
            if (message && message.value.trim().length === 0) {
                alert('Message cannot be empty.');
                return false;
            }
            return true;
        }
        // Auto-scroll to latest message
        const messageList = document.querySelector('.message-list');
        if (messageList) {
            messageList.scrollTop = messageList.scrollHeight;
        }
    </script>
</body>
</html>
