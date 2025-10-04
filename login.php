<?php
// login.php
include 'db.php';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    echo "<script>redirect('index.php');</script>";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['last_activity'] = time(); // Track session activity
        echo "<script>alert('Login successful!'); redirect('index.php');</script>";
    } else {
        echo "<script>alert('Invalid email or password!');</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Fiverr Clone</title>
    <style>
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: linear-gradient(135deg, #0f2027, #203a43, #2c5364);
            color: #fff;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .form-container {
            background: #fff;
            color: #333;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 6px 20px rgba(0,0,0,0.5);
            width: 100%;
            max-width: 450px;
            animation: slideIn 0.8s ease-out;
        }
        .form-container h2 {
            text-align: center;
            color: #ff2e63;
            margin-bottom: 20px;
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
        .form-group input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 25px;
            font-size: 1em;
            transition: border-color 0.3s, box-shadow 0.3s;
        }
        .form-group input:focus {
            border-color: #ff2e63;
            box-shadow: 0 0 8px rgba(255, 46, 99, 0.3);
            outline: none;
        }
        .btn {
            background: #ff2e63;
            color: #fff;
            padding: 12px;
            border: none;
            border-radius: 25px;
            width: 100%;
            cursor: pointer;
            font-size: 1.1em;
            transition: background 0.3s, transform 0.3s;
        }
        .btn:hover {
            background: #08d9d6;
            transform: scale(1.05);
        }
        .form-group .toggle-password {
            cursor: pointer;
            position: absolute;
            right: 20px;
            top: 50%;
            transform: translateY(-50%);
            color: #ff2e63;
        }
        .form-group .password-container {
            position: relative;
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
    <div class="form-container">
        <h2>Login to Your Account</h2>
        <form method="POST">
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" required>
            </div>
            <div class="form-group password-container">
                <label>Password</label>
                <input type="password" name="password" id="password" required>
                <span class="toggle-password" onclick="togglePassword()">👁️</span>
            </div>
            <button type="submit" class="btn">Login</button>
        </form>
        <p style="text-align: center; margin-top: 15px;">
            Don't have an account? <a href="#" onclick="redirect('signup.php')" style="color: #ff2e63;">Signup</a>
        </p>
    </div>
    <script>
        function redirect(url) {
            window.location.href = url;
        }
        function togglePassword() {
            const password = document.getElementById('password');
            password.type = password.type === 'password' ? 'text' : 'password';
        }
    </script>
</body>
</html>
