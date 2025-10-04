<?php
// create_gig.php
include 'db.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'seller') {
    echo "<script>alert('Please login as a seller to create a gig.'); redirect('login.php');</script>";
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

$categories = $pdo->query("SELECT * FROM categories")->fetchAll(PDO::FETCH_ASSOC);
if (!$categories) {
    echo "<script>alert('No categories found. Please contact support.'); redirect('index.php');</script>";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $title = filter_input(INPUT_POST, 'title', FILTER_SANITIZE_STRING);
        $description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_STRING);
        $category = filter_input(INPUT_POST, 'category', FILTER_SANITIZE_STRING);
        $price = filter_input(INPUT_POST, 'price', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
        $user_id = $_SESSION['user_id'];

        if (empty($title) || empty($description) || empty($category) || empty($price)) {
            echo "<script>alert('All fields are required.'); redirect('create_gig.php');</script>";
            exit;
        }

        $image = 'default.jpg';
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $target_dir = "uploads/";
            if (!is_dir($target_dir)) {
                mkdir($target_dir, 0755, true);
            }
            $image_name = time() . '_' . basename($_FILES['image']['name']);
            $image = $target_dir . $image_name;
            if (!move_uploaded_file($_FILES['image']['tmp_name'], $image)) {
                echo "<script>alert('Failed to upload image. Using default image.'); </script>";
                $image = 'default.jpg';
            }
        }

        $stmt = $pdo->prepare("INSERT INTO gigs (user_id, title, description, category, price, image) VALUES (?, ?, ?, ?, ?, ?)");
        if ($stmt->execute([$user_id, $title, $description, $category, $price, $image])) {
            echo "<script>alert('Gig created successfully!'); redirect('profile.php');</script>";
        } else {
            echo "<script>alert('Failed to create gig. Please try again.'); </script>";
        }
    } catch (Exception $e) {
        echo "<script>alert('Error creating gig: " . addslashes($e->getMessage()) . "'); </script>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Gig - Fiverr Clone</title>
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
            max-width: 800px;
            margin: 40px auto;
            padding: 20px;
        }
        .form-container {
            background: #fff;
            color: #333;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 6px 20px rgba(0,0,0,0.5);
            animation: slideIn 0.8s ease-out;
        }
        .form-container h2 {
            color: #ff2e63;
            margin-bottom: 20px;
            text-align: center;
            text-shadow: 0 0 5px rgba(255, 46, 99, 0.5);
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #333;
        }
        .form-group input, .form-group textarea, .form-group select {
            width: 100%;
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 10px;
            font-size: 1em;
            transition: border-color 0.3s, box-shadow 0.3s;
        }
        .form-group textarea {
            resize: vertical;
        }
        .form-group input:focus, .form-group textarea:focus, .form-group select:focus {
            border-color: #ff2e63;
            box-shadow: 0 0 8px rgba(255, 46, 99, 0.3);
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
        @keyframes slideIn {
            from { transform: translateY(-50px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        @media (max-width: 480px) {
            .form-container {
                padding: 20px;
                max-width: 90%;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="form-container">
            <h2>Create a New Gig</h2>
            <form method="POST" enctype="multipart/form-data" onsubmit="return validateForm()">
                <div class="form-group">
                    <label>Title</label>
                    <input type="text" name="title" id="title" required>
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" id="description" rows="5" required></textarea>
                </div>
                <div class="form-group">
                    <label>Category</label>
                    <select name="category" id="category" required>
                        <option value="">Select a category</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo htmlspecialchars($category['name']); ?>">
                                <?php echo htmlspecialchars($category['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Price ($)</label>
                    <input type="number" name="price" id="price" step="0.01" min="1" required>
                </div>
                <div class="form-group">
                    <label>Image (optional)</label>
                    <input type="file" name="image" id="image" accept="image/*">
                </div>
                <button type="submit" class="btn">Create Gig</button>
                <button type="button" class="btn back-btn" onclick="redirect('profile.php')">Back to Profile</button>
            </form>
        </div>
    </div>
    <script>
        function redirect(url) {
            window.location.href = url;
        }
        function validateForm() {
            const title = document.getElementById('title').value;
            const description = document.getElementById('description').value;
            const category = document.getElementById('category').value;
            const price = document.getElementById('price').value;
            if (title.length < 5) {
                alert('Title must be at least 5 characters long.');
                return false;
            }
            if (description.length < 20) {
                alert('Description must be at least 20 characters long.');
                return false;
            }
            if (!category) {
                alert('Please select a category.');
                return false;
            }
            if (price <= 0) {
                alert('Price must be greater than 0.');
                return false;
            }
            return true;
        }
    </script>
</body>
</html>
