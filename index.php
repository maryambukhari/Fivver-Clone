<?php
// index.php
// Enable error reporting for debugging
ini_set('display_errors', 0); // Disable display to avoid exposing sensitive info
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);
error_log("Starting index.php"); // Log start of script

// Include database connection
try {
    include 'db.php';
    error_log("db.php included successfully");
} catch (Exception $e) {
    error_log("Failed to include db.php: " . $e->getMessage());
    echo "<script>alert('Database connection failed. Showing sample gigs.'); </script>";
    $pdo = null; // Set PDO to null to use sample gigs
}

// Session timeout
$timeout = 30 * 60;
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $timeout)) {
    session_unset();
    session_destroy();
    echo "<script>alert('Session timed out. Please login again.'); window.location.href='login.php';</script>";
    exit;
}
$_SESSION['last_activity'] = time();
error_log("Session checked, user_id: " . (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'none'));

// Fetch gigs from database if connection exists
$gigs = [];
$categories = [];
if ($pdo) {
    try {
        $stmt = $pdo->query("SELECT gigs.*, users.username FROM gigs JOIN users ON gigs.user_id = users.id ORDER BY gigs.created_at DESC LIMIT 12");
        $gigs = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $categories = $pdo->query("SELECT * FROM categories")->fetchAll(PDO::FETCH_ASSOC);
        error_log("Gigs fetched from DB: " . count($gigs));
    } catch (Exception $e) {
        error_log("Database query error: " . $e->getMessage());
        echo "<script>alert('Error loading gigs from database. Showing sample gigs.'); </script>";
    }
}

// Hardcoded sample gigs as fallback
$sample_gigs = [
    [
        'id' => 0,
        'title' => 'Professional Logo Design',
        'username' => 'CreativeDesigner',
        'category' => 'Graphic Design',
        'description' => 'Get a stunning, custom logo for your brand with unlimited revisions and high-quality formats.',
        'price' => 50.00,
        'rating' => 4.8,
        'rating_count' => 120,
        'image' => 'Uploads/sample_logo_design.jpg'
    ],
    [
        'id' => 0,
        'title' => 'Custom Website Development',
        'username' => 'WebWizard',
        'category' => 'Web Development',
        'description' => 'Build a responsive, modern website with HTML, CSS, and JavaScript, tailored to your needs.',
        'price' => 200.00,
        'rating' => 4.9,
        'rating_count' => 85,
        'image' => 'Uploads/sample_web_development.jpg'
    ],
    [
        'id' => 0,
        'title' => 'SEO Content Writing',
        'username' => 'ContentMaster',
        'category' => 'Writing',
        'description' => 'High-quality, SEO-optimized articles to boost your website’s ranking and engagement.',
        'price' => 30.00,
        'rating' => 4.7,
        'rating_count' => 65,
        'image' => 'Uploads/sample_content_writing.jpg'
    ]
];

// Use database gigs if available; otherwise, use sample gigs
$gigs = !empty($gigs) ? $gigs : $sample_gigs;
error_log("Final gigs count: " . count($gigs));

// Check for new signup
$new_signup = isset($_SESSION['new_signup']) && $_SESSION['new_signup'] === true;
if ($new_signup) {
    unset($_SESSION['new_signup']);
    error_log("New signup detected for user: " . ($_SESSION['username'] ?? 'unknown'));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fiverr Clone - Home</title>
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
            overflow-x: hidden;
        }
        .header {
            background: #1a1a2e;
            padding: 20px;
            text-align: center;
            box-shadow: 0 4px 12px rgba(0,0,0,0.5);
            position: sticky;
            top: 0;
            z-index: 100;
        }
        .header h1 {
            font-size: 2.8em;
            color: #ff2e63;
            text-shadow: 0 0 10px rgba(255, 46, 99, 0.7);
            animation: neonGlow 1.5s ease-in-out infinite alternate;
        }
        .nav a {
            color: #08d9d6;
            margin: 0 20px;
            text-decoration: none;
            font-size: 1.2em;
            transition: color 0.3s, transform 0.3s;
        }
        .nav a:hover {
            color: #ff2e63;
            transform: scale(1.1);
        }
        .container {
            max-width: 1400px;
            margin: 40px auto;
            padding: 0 20px;
        }
        .hero {
            text-align: center;
            padding: 60px 20px;
            background: linear-gradient(45deg, #ff2e63, #08d9d6);
            border-radius: 15px;
            margin-bottom: 40px;
            animation: slideIn 1s ease-out;
        }
        .hero h2 {
            font-size: 2.7em;
            margin-bottom: 15px;
            text-shadow: 0 0 5px rgba(255, 255, 255, 0.5);
        }
        .hero input {
            padding: 14px;
            width: 70%;
            max-width: 600px;
            border: none;
            border-radius: 25px;
            font-size: 1.2em;
            box-shadow: 0 4px 12px rgba(0,0,0,0.3);
            transition: box-shadow 0.3s;
        }
        .hero input:focus {
            box-shadow: 0 0 10px rgba(255, 46, 99, 0.5);
            outline: none;
        }
        .welcome-message {
            text-align: center;
            margin-bottom: 30px;
            background: #fff;
            color: #333;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.3);
            animation: fadeIn 1s ease-out;
        }
        .gig-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 30px;
        }
        .gig-card {
            background: #fff;
            color: #333;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 6px 15px rgba(0,0,0,0.3);
            transition: transform 0.3s, box-shadow 0.3s;
            position: relative;
            overflow: hidden;
        }
        .gig-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 8px 20px rgba(255, 46, 99, 0.5);
        }
        .gig-card img {
            width: 100%;
            height: 220px;
            object-fit: cover;
            border-radius: 10px;
            transition: transform 0.3s;
        }
        .gig-card:hover img {
            transform: scale(1.05);
        }
        .gig-card h3 {
            margin: 15px 0 10px;
            color: #ff2e63;
            font-size: 1.6em;
        }
        .gig-card p {
            font-size: 1em;
            margin-bottom: 12px;
            color: #555;
        }
        .gig-card .description {
            font-size: 0.95em;
            color: #666;
            line-height: 1.4;
        }
        .gig-card .rating {
            color: #f4a261;
            font-size: 1.1em;
            margin-bottom: 10px;
        }
        .gig-card .price {
            font-size: 1.2em;
            font-weight: bold;
            color: #333;
        }
        .view-gig-btn {
            background: #ff2e63;
            color: #fff;
            padding: 12px 25px;
            border: none;
            border-radius: 25px;
            cursor: pointer;
            font-size: 1.1em;
            transition: background 0.3s, transform 0.3s;
            width: 100%;
            text-align: center;
        }
        .view-gig-btn:hover {
            background: #08d9d6;
            transform: scale(1.05);
        }
        .categories {
            margin: 50px 0;
            text-align: center;
        }
        .category-btn {
            background: #08d9d6;
            color: #fff;
            padding: 12px 25px;
            margin: 8px;
            border: none;
            border-radius: 25px;
            cursor: pointer;
            font-size: 1.1em;
            transition: background 0.3s, transform 0.3s;
        }
        .category-btn:hover {
            background: #ff2e63;
            transform: scale(1.1);
        }
        .no-gigs {
            text-align: center;
            color: #666;
            font-style: italic;
            margin: 20px 0;
            font-size: 1.2em;
        }
        @keyframes neonGlow {
            from { text-shadow: 0 0 5px #ff2e63, 0 0 10px #ff2e63; }
            to { text-shadow: 0 0 10px #ff2e63, 0 0 20px #ff2e63; }
        }
        @keyframes slideIn {
            from { transform: translateY(-50px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        @media (max-width: 768px) {
            .gig-grid {
                grid-template-columns: 1fr;
            }
            .hero input {
                width: 90%;
            }
            .gig-card img {
                height: 180px;
            }
        }
        @media (max-width: 480px) {
            .container {
                padding: 0 10px;
            }
            .hero {
                padding: 40px 10px;
            }
            .gig-card {
                padding: 15px;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Fiverr Clone</h1>
        <div class="nav">
            <a href="#" onclick="redirect('index.php')">Home</a>
            <?php if (!isset($_SESSION['user_id'])): ?>
                <a href="#" onclick="redirect('signup.php')">Signup</a>
                <a href="#" onclick="redirect('login.php')">Login</a>
            <?php else: ?>
                <a href="#" onclick="redirect('profile.php')">Profile</a>
                <?php if (isset($_SESSION['role']) && $_SESSION['role'] == 'seller'): ?>
                    <a href="#" onclick="redirect('create_gig.php')">Create Gig</a>
                <?php endif; ?>
                <a href="#" onclick="redirect('messages.php')">Messages</a>
                <a href="#" onclick="redirect('logout.php')">Logout</a>
            <?php endif; ?>
        </div>
    </div>
    <div class="container">
        <?php if ($new_signup && isset($_SESSION['role']) && $_SESSION['role'] == 'buyer'): ?>
            <div class="welcome-message">
                <h3>Welcome, <?php echo htmlspecialchars($_SESSION['username'] ?? 'Guest'); ?>!</h3>
                <p>Explore top freelance services below. Find the perfect gig for your project!</p>
            </div>
        <?php endif; ?>
        <div class="hero">
            <h2>Find Your Perfect Freelancer</h2>
            <input type="text" placeholder="Search for services (e.g., logo design, web development)..." onkeydown="if(event.key === 'Enter') redirect('search.php?q=' + encodeURIComponent(this.value))">
        </div>
        <h2>Explore Top Gigs</h2>
        <?php if (empty($gigs)): ?>
            <p class="no-gigs">No gigs available at the moment. <a href="#" onclick="redirect('signup.php')" style="color: #ff2e63;">Become a seller</a> to create one!</p>
        <?php else: ?>
            <div class="gig-grid">
                <?php foreach ($gigs as $gig): ?>
                    <div class="gig-card">
                        <img src="<?php echo htmlspecialchars($gig['image'] ?: 'Uploads/default.jpg'); ?>" alt="Gig Image" onerror="this.src='Uploads/default.jpg'">
                        <h3><?php echo htmlspecialchars($gig['title'] ?? 'Untitled Gig'); ?></h3>
                        <p><strong>By:</strong> <?php echo htmlspecialchars($gig['username'] ?? 'Unknown Seller'); ?></p>
                        <p><strong>Category:</strong> <?php echo htmlspecialchars($gig['category'] ?? 'Uncategorized'); ?></p>
                        <p class="description"><?php echo htmlspecialchars(substr($gig['description'] ?? 'No description available', 0, 150)) . (strlen($gig['description'] ?? '') > 150 ? '...' : ''); ?></p>
                        <p class="rating">★ <?php echo number_format($gig['rating'] ?? 0, 1); ?> (<?php echo $gig['rating_count'] ?? 0; ?> reviews)</p>
                        <p class="price">$<?php echo number_format($gig['price'] ?? 0, 2); ?></p>
                        <?php if ($gig['id'] != 0): ?>
                            <button class="view-gig-btn" onclick="redirect('gig_details.php?id=<?php echo $gig['id']; ?>')">View Gig</button>
                        <?php else: ?>
                            <button class="view-gig-btn" onclick="alert('This is a sample gig. Please signup as a seller to create real gigs!'); redirect('signup.php')">View Sample</button>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <div class="categories">
            <h2>Browse by Category</h2>
            <?php if (empty($categories)): ?>
                <p class="no-gigs">No categories available. Try: Graphic Design, Web Development, Writing.</p>
            <?php else: ?>
                <?php foreach ($categories as $category): ?>
                    <button class="category-btn" onclick="redirect('search.php?category=<?php echo urlencode($category['name']); ?>')">
                        <?php echo htmlspecialchars($category['name']); ?>
                    </button>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
    <script>
        function redirect(url) {
            window.location.href = url;
        }
    </script>
</body>
</html>
