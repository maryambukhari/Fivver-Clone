<?php
// profile.php
include 'db.php';
if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('Please login to view your profile.'); redirect('login.php');</script>";
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
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$gigs = $pdo->prepare("SELECT * FROM gigs WHERE user_id = ?");
$gigs->execute([$user_id]);
$gigs = $gigs->fetchAll(PDO::FETCH_ASSOC);

$orders = $pdo->prepare("SELECT orders.*, gigs.title FROM orders JOIN gigs ON orders.gig_id = gigs.id WHERE orders.buyer_id = ? OR orders.seller_id = ?");
$orders->execute([$user_id, $user_id]);
$orders = $orders->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $bio = filter_input(INPUT_POST, 'bio', FILTER_SANITIZE_STRING);
    $stmt = $pdo->prepare("UPDATE users SET bio = ? WHERE id = ?");
    $stmt->execute([$bio, $user_id]);
    echo "<script>alert('Profile updated!'); redirect('profile.php');</script>";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - Fiverr Clone</title>
    <style>
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: linear-gradient(135deg, #0f2027, #203a43, #2c5364);
            color: #fff;
            margin: 0;
        }
        .header {
            background: #1a1a2e;
            padding: 20px;
            text-align: center;
            box-shadow: 0 4px 12px rgba(0,0,0,0.5);
        }
        .header h1 {
            font-size: 2.5em;
            color: #ff2e63;
            text-shadow: 0 0 10px rgba(255, 46, 99, 0.7);
        }
        .container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }
        .profile-card {
            background: #fff;
            color: #333;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 6px 15px rgba(0,0,0,0.3);
            animation: slideIn 0.8s ease-out;
        }
        .profile-card img {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 15px;
        }
        .profile-card h2 {
            color: #ff2e63;
            margin-bottom: 15px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
        }
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 10px;
            resize: vertical;
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
        .section {
            margin-top: 40px;
        }
        .section h3 {
            color: #08d9d6;
            margin-bottom: 20px;
        }
        .gig-grid, .order-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 25px;
        }
        .gig-card, .order-card {
            background: #fff;
            color: #333;
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 6px 15px rgba(0,0,0,0.3);
            transition: transform 0.3s;
        }
        .gig-card:hover, .order-card:hover {
            transform: translateY(-10px);
        }
        .gig-card img {
            width: 100%;
            height: 150px;
            object-fit: cover;
            border-radius: 10px;
        }
        @keyframes slideIn {
            from { transform: translateY(-50px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Your Profile</h1>
    </div>
    <div class="container">
        <div class="profile-card">
            <img src="<?php echo $user['profile_image'] ?: 'default.jpg'; ?>" alt="Profile Image">
            <h2><?php echo htmlspecialchars($user['username']); ?></h2>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
            <p><strong>Role:</strong> <?php echo ucfirst($user['role']); ?></p>
            <form method="POST">
                <div class="form-group">
                    <label>Bio</label>
                    <textarea name="bio" rows="4"><?php echo htmlspecialchars($user['bio']); ?></textarea>
                </div>
                <button type="submit" class="btn">Update Profile</button>
            </form>
        </div>
        <?php if ($user['role'] == 'seller'): ?>
            <div class="section">
                <h3>Your Gigs</h3>
                <div class="gig-grid">
                    <?php foreach ($gigs as $gig): ?>
                        <div class="gig-card">
                            <img src="<?php echo $gig['image'] ?: 'default.jpg'; ?>" alt="Gig Image">
                            <h4><?php echo htmlspecialchars($gig['title']); ?></h4>
                            <p>$<?php echo number_format($gig['price'], 2); ?></p>
                            <button class="btn" onclick="redirect('edit_gig.php?id=<?php echo $gig['id']; ?>')">Edit</button>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
        <div class="section">
            <h3>Your Orders</h3>
            <div class="order-grid">
                <?php foreach ($orders as $order): ?>
                    <div class="order-card">
                        <h4><?php echo htmlspecialchars($order['title']); ?></h4>
                        <p><strong>Status:</strong> <?php echo ucfirst($order['status']); ?></p>
                        <p><strong>Total:</strong> $<?php echo number_format($order['total_price'], 2); ?></p>
                        <button class="btn" onclick="redirect('order.php?id=<?php echo $order['id']; ?>')">View Order</button>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <script>
        function redirect(url) {
            window.location.href = url;
        }
    </script>
</body>
</html>
