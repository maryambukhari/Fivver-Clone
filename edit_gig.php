<?php
// edit_gig.php
session_start();
include 'db.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'seller') {
    echo "<script>redirect('login.php');</script>";
    exit;
}
$gig_id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
$stmt = $pdo->prepare("SELECT * FROM gigs WHERE id = ? AND user_id = ?");
$stmt->execute([$gig_id, $_SESSION['user_id']]);
$gig = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$gig) {
    echo "<script>redirect('profile.php');</script>";
    exit;
}
$categories = $pdo->query("SELECT * FROM categories")->fetchAll(PDO::FETCH_ASSOC);
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = filter_input(INPUT_POST, 'title', FILTER_SANITIZE_STRING);
    $description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_STRING);
    $category = $_POST['category'];
    $price = filter_input(INPUT_POST, 'price', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    
    $image = $gig['image'];
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $target_dir = "uploads/";
        if (!is_dir($target_dir)) mkdir($target_dir, 0755, true);
        $image = $target_dir . basename($_FILES['image']['name']);
        move_uploaded_file($_FILES['image']['tmp_name'], $image);
    }
    
    $stmt = $pdo->prepare("UPDATE gigs SET title = ?, description = ?, category = ?, price = ?, image = ? WHERE id = ?");
    if ($stmt->execute([$title, $description, $category, $price, $image, $gig_id])) {
        echo "<script>alert('Gig updated successfully!'); redirect('profile.php');</script>";
    } else {
        echo "<script>alert('Failed to update gig!');</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Gig - Fiverr Clone</title>
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
        .form-container {
            background: #fff;
            color: #333;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 6px 20px rgba(0,0,0,0.5);
            animation: slideIn 0.8s ease-out;
        }
        .form-container h2 {
            color: #ff2e63;
            margin-bottom: 20px;
            text-align: center;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
        }
        .form-group input, .form-group textarea, .form-group select {
            width: 100%;
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 10px;
            font-size: 1em;
            transition: border-color 0.3s;
        }
        .form-group textarea {
            resize: vertical;
        }
        .form-group input:focus, .form-group textarea:focus, .form-group select:focus {
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
        <div class="form-container">
            <h2>Edit Gig</h2>
            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label>Title</label>
                    <input type="text" name="title" value="<?php echo htmlspecialchars($gig['title']); ?>" required>
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" rows="5" required><?php echo htmlspecialchars($gig['description']); ?></textarea>
                </div>
                <div class="form-group">
                    <label>Category</label>
                    <select name="category" required>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo htmlspecialchars($category['name']); ?>" <?php echo $category['name'] == $gig['category'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($category['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Price ($)</label>
                    <input type="number" name="price" step="0.01" value="<?php echo $gig['price']; ?>" required>
                </div>
                <div class="form-group">
                    <label>Image</label>
                    <input type="file" name="image" accept="image/*">
                    <p>Current: <img src="<?php echo $gig['image']; ?>" width="100" alt="Current Image"></p>
                </div>
                <button type="submit" class="btn">Update Gig</button>
            </form>
        </div>
    </div>
    <script>
        function redirect(url) {
            window.location.href = url;
        }
    </script>
</body>
</html>
