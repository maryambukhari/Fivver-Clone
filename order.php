<?php
// order.php
session_start();
include 'db.php';
if (!isset($_SESSION['user_id'])) {
    echo "<script>redirect('login.php');</script>";
    exit;
}
$order_id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
$stmt = $pdo->prepare("SELECT orders.*, gigs.title, users.username AS seller_name FROM orders JOIN gigs ON orders.gig_id = gigs.id JOIN users ON orders.seller_id = users.id WHERE orders.id = ?");
$stmt->execute([$order_id]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$order || ($order['buyer_id'] != $_SESSION['user_id'] && $order['seller_id'] != $_SESSION['user_id'])) {
    echo "<script>redirect('index.php');</script>";
    exit;
}
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_SESSION['role'] == 'seller') {
    $status = $_POST['status'];
    $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $stmt->execute([$status, $order_id]);
    echo "<script>alert('Order status updated!'); redirect('order.php?id=$order_id');</script>";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Details - Fiverr Clone</title>
    <style>
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: linear-gradient(135deg, #0f2027, #203a43, #2c5364);
            color: #fff;
            margin: 0;
        }
        .container {
            max-width: 800px;
            margin: 40px auto;
            padding: 20px;
        }
        .order-details {
            background: #fff;
            color: #333;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 6px 20px rgba(0,0,0,0.5);
            animation: slideIn 0.8s ease-out;
        }
        .order-details h2 {
            color: #ff2e63;
            margin-bottom: 20px;
        }
        .btn {
            background: #ff2e63;
            color: #fff;
            padding: 12px 25px;
            border: none;
            border-radius: 25px;
            cursor: pointer;
            transition: background 0.3s, transform 0.3s;
        }
        .btn:hover {
            background: #08d9d6;
            transform: scale(1.05);
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group select {
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 10px;
            width: 200px;
        }
        @keyframes slideIn {
            from { transform: translateY(-50px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="order-details">
            <h2>Order Details</h2>
            <p><strong>Gig:</strong> <?php echo htmlspecialchars($order['title']); ?></p>
            <p><strong>Seller:</strong> <?php echo htmlspecialchars($order['seller_name']); ?></p>
            <p><strong>Total Price:</strong> $<?php echo number_format($order['total_price'], 2); ?></p>
            <p><strong>Status:</strong> <?php echo ucfirst($order['status']); ?></p>
            <p><strong>Order Date:</strong> <?php echo $order['created_at']; ?></p>
            <?php if ($_SESSION['role'] == 'seller' && $order['seller_id'] == $_SESSION['user_id']): ?>
                <form method="POST">
                    <div class="form-group">
                        <label>Update Status</label>
                        <select name="status">
                            <option value="pending" <?php echo $order['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="accepted" <?php echo $order['status'] == 'accepted' ? 'selected' : ''; ?>>Accepted</option>
                            <option value="in_progress" <?php echo $order['status'] == 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
                            <option value="completed" <?php echo $order['status'] == 'completed' ? 'selected' : ''; ?>>Completed</option>
                            <option value="rejected" <?php echo $order['status'] == 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                        </select>
                    </div>
                    <button type="submit" class="btn">Update Status</button>
                </form>
            <?php endif; ?>
            <button class="btn" onclick="redirect('messages.php?order_id=<?php echo $order['id']; ?>')">Message Seller</button>
        </div>
    </div>
    <script>
        function redirect(url) {
            window.location.href = url;
        }
    </script>
</body>
</html>
