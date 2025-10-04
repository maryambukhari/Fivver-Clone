<?php
// gig_details.php
session_start();
include 'db.php';
$gig_id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
$stmt = $pdo->prepare("SELECT gigs.*, users.username FROM gigs JOIN users ON gigs.user_id = users.id WHERE gigs.id = ?");
$stmt->execute([$gig_id]);
$gig = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$gig) {
    echo "<script>redirect('index.php');</script>";
    exit;
}
$ratings = $pdo->prepare("SELECT * FROM ratings WHERE gig_id = ?");
$ratings->execute([$gig_id]);
$ratings = $ratings->fetchAll(PDO::FETCH_ASSOC);
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['user_id'])) {
    $buyer_id = $_SESSION['user_id'];
    $stmt = $pdo->prepare("INSERT INTO orders (gig_id, buyer_id, seller_id, total_price, status) VALUES (?, ?, ?, ?, 'pending')");
    $stmt->execute([$gig_id, $buyer_id, $gig['user_id'], $gig['price']]);
    echo "<script>alert('Order placed successfully!'); redirect('order.php?id=' + " . $pdo->lastInsertId() . ");</script>";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gig Details - Fiverr Clone</title>
    <style>
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: linear-gradient(135deg, #0f2027, #203a43, #2c5364);
            color: #fff;
            margin: 0;
        }
        .container {
            max-width: 1000px;
            margin: 40px auto;
            padding: 20px;
        }
        .gig-details {
            background: #fff;
            color: #333;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 6px 20px rgba(0,0,0,0.5);
            animation: slideIn 0.8s ease-out;
        }
        .gig-details img {
            width: 100%;
            max-width: 400px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        .gig-details h2 {
            color: #ff2e63;
            margin-bottom: 15px;
        }
        .gig-details .rating {
            color: #f4a261;
            font-size: 1.2em;
            margin-bottom: 15px;
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
        .reviews {
            margin-top: 30px;
        }
        .review-card {
            background: #f9f9f9;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 15px;
        }
        @keyframes slideIn {
            from { transform: translateY(-50px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="gig-details">
            <img src="<?php echo $gig['image'] ?: 'default.jpg'; ?>" alt="Gig Image">
            <h2><?php echo htmlspecialchars($gig['title']); ?></h2>
            <p><strong>by</strong> <?php echo htmlspecialchars($gig['username']); ?></p>
            <p class="rating">★ <?php echo $gig['rating']; ?> (<?php echo $gig['rating_count']; ?> reviews)</p>
            <p><strong>Category:</strong> <?php echo htmlspecialchars($gig['category']); ?></p>
            <p><?php echo htmlspecialchars($gig['description']); ?></p>
            <p><strong>Price:</strong> $<?php echo number_format($gig['price'], 2); ?></p>
            <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] != $gig['user_id']): ?>
                <form method="POST">
                    <button type="submit" class="btn">Order Now</button>
                </form>
            <?php endif; ?>
            <div class="reviews">
                <h3>Reviews</h3>
                <?php foreach ($ratings as $rating): ?>
                    <div class="review-card">
                        <p><strong>Rating:</strong> ★ <?php echo $rating['rating']; ?></p>
                        <p><?php echo htmlspecialchars($rating['review']); ?></p>
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
