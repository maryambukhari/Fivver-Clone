<?php
// search.php
session_start();
include 'db.php';
$query = filter_input(INPUT_GET, 'q', FILTER_SANITIZE_STRING) ?: '';
$category = filter_input(INPUT_GET, 'category', FILTER_SANITIZE_STRING) ?: '';
$min_price = filter_input(INPUT_GET, 'min_price', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) ?: 0;
$max_price = filter_input(INPUT_GET, 'max_price', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) ?: 10000;
$min_rating = filter_input(INPUT_GET, 'min_rating', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) ?: 0;

$sql = "SELECT gigs.*, users.username FROM gigs JOIN users ON gigs.user_id = users.id WHERE 1=1";
$params = [];
if ($query) {
    $sql .= " AND gigs.title LIKE ?";
    $params[] = "%$query%";
}
if ($category) {
    $sql .= " AND gigs.category = ?";
    $params[] = $category;
}
$sql .= " AND gigs.price BETWEEN ? AND ?";
$params[] = $min_price;
$params[] = $max_price;
$sql .= " AND gigs.rating >= ?";
$params[] = $min_rating;

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$gigs = $stmt->fetchAll(PDO::FETCH_ASSOC);
$categories = $pdo->query("SELECT * FROM categories")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search - Fiverr Clone</title>
    <style>
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: linear-gradient(135deg, #0f2027, #203a43, #2c5364);
            color: #fff;
            margin: 0;
        }
        .container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 20px;
        }
        .filter-form {
            background: #fff;
            color: #333;
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 6px 20px rgba(0,0,0,0.5);
            margin-bottom: 30px;
            animation: slideIn 0.8s ease-out;
        }
        .filter-form .form-group {
            display: inline-block;
            margin: 10px;
        }
        .filter-form label {
            margin-right: 10px;
            font-weight: bold;
        }
        .filter-form input, .filter-form select {
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 10px;
        }
        .gig-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 25px;
        }
        .gig-card {
            background: #fff;
            color: #333;
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 6px 15px rgba(0,0,0,0.3);
            transition: transform 0.3s;
        }
        .gig-card:hover {
            transform: translateY(-10px);
        }
        .gig-card img {
            width: 100%;
            height: 150px;
            object-fit: cover;
            border-radius: 10px;
        }
        .gig-card h3 {
            color: #ff2e63;
            margin: 15px 0;
        }
        .gig-card .rating {
            color: #f4a261;
        }
        .btn {
            background: #ff2e63;
            color: #fff;
            padding: 10px 20px;
            border: none;
            border-radius: 25px;
            cursor: pointer;
            transition: background 0.3s, transform 0.3s;
        }
        .btn:hover {
            background: #08d9d6;
            transform: scale(1.05);
        }
        @keyframes slideIn {
            from { transform: translateY(-50px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="filter-form">
            <form method="GET">
                <div class="form-group">
                    <label>Search</label>
                    <input type="text" name="q" value="<?php echo htmlspecialchars($query); ?>" placeholder="Search gigs...">
                </div>
                <div class="form-group">
                    <label>Category</label>
                    <select name="category">
                        <option value="">All</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo htmlspecialchars($cat['name']); ?>" <?php echo $cat['name'] == $category ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Min Price</label>
                    <input type="number" name="min_price" value="<?php echo $min_price; ?>">
                </div>
                <div class="form-group">
                    <label>Max Price</label>
                    <input type="number" name="max_price" value="<?php echo $max_price; ?>">
                </div>
                <div class="form-group">
                    <label>Min Rating</label>
                    <input type="number" name="min_rating" step="0.1" value="<?php echo $min_rating; ?>">
                </div>
                <button type="submit" class="btn">Apply Filters</button>
            </form>
        </div>
        <div class="gig-grid">
            <?php foreach ($gigs as $gig): ?>
                <div class="gig-card">
                    <img src="<?php echo $gig['image'] ?: 'default.jpg'; ?>" alt="Gig Image">
                    <h3><?php echo htmlspecialchars($gig['title']); ?></h3>
                    <p>by <?php echo htmlspecialchars($gig['username']); ?></p>
                    <p class="rating">★ <?php echo $gig['rating']; ?> (<?php echo $gig['rating_count']; ?> reviews)</p>
                    <p>$<?php echo number_format($gig['price'], 2); ?></p>
                    <button class="btn" onclick="redirect('gig_details.php?id=<?php echo $gig['id']; ?>')">View Gig</button>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <script>
        function redirect(url) {
            window.location.href = url;
        }
    </script>
</body>
</html>
