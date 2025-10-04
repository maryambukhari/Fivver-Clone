<?php
// signup.php
include 'db.php';
if (isset($_SESSION['user_id'])) {
    echo "<script>redirect('index.php');</script>";
    exit;
}
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $_POST['role'];
    
    $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
    if ($stmt->execute([$username, $email, $password, $role])) {
        echo "<script>alert('Signup successful! Please login.'); redirect('login.php');</script>";
    } else {
        echo "<script>alert('Signup failed! Username or email already exists.');</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Signup - Fiverr Clone</title>
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
        .form-group input, .form-group select {
            width: 100%;
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 25px;
            font-size: 1em;
            transition: border-color 0.3s, box-shadow 0.3s;
        }
        .form-group input:focus, .form-group select:focus {
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
        <h2>Create Your Account</h2>
        <form method="POST" onsubmit="return validateForm()">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" id="username" required>
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" id="email" required>
            </div>
            <div class="form-group password-container">
                <label>Password</label>
                <input type="password" name="password" id="password" required>
                <span class="toggle-password" onclick="togglePassword()">👁️</span>
            </div>
            <div class="form-group">
                <label>Role</label>
                <select name="role" id="role">
                    <option value="buyer">Buyer</option>
                    <option value="seller">Seller</option>
                </select>
            </div>
            <button type="submit" class="btn">Signup</button>
        </form>
        <p style="text-align: center; margin-top: 15px;">
            Already have an account? <a href="#" onclick="redirect('login.php')" style="color: #ff2e63;">Login</a>
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
        function validateForm() {
            const username = document.getElementById('username').value;
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            if (username.length < 3) {
                alert('Username must be at least 3 characters long.');
                return false;
            }
            if (!email.includes('@') || !email.includes('.')) {
                alert('Please enter a valid email address.');
                return false;
            }
            if (password.length < 6) {
                alert('Password must be at least 6 characters long.');
                return false;
            }
            return true;
        }
    </script>
</body>
</html>
