<?php
session_start();
require 'includes/db.php'; // Make sure this path is correct

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    // Fetch user from register table
    $stmt = $pdo->prepare("SELECT id, password FROM register WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        header('Location: index.php'); // Change from payment.php to index.php
        exit;
    } else {
        $error = 'Invalid username or password';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login - Velvet Vogue</title>
    <style>
        body { font-family: Arial, sans-serif; background: #bfc5d6; }
        .login-container {
            max-width: 350px;
            margin: 80px auto;
            background: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 16px rgba(44,62,80,0.08);
        }
        h2 { text-align: center; margin-bottom: 24px; }
        .form-group { margin-bottom: 18px; }
        label { display: block; margin-bottom: 6px; }
        input[type="text"], input[type="password"] {
            width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;
        }
        .btn {
            width: 100%; padding: 10px; background: #3498db; color: #fff;
            border: none; border-radius: 4px; cursor: pointer; font-size: 16px;
        }
        .btn:hover { background: #2980b9; }
        .error { color: #e74c3c; text-align: center; margin-bottom: 12px; }
    </style>
</head>
<body>
    <div class="login-container">
        <h2>Login</h2>
        <?php if ($error): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required autofocus>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit" class="btn">Login</button>
        </form>
        <p style="text-align:center;margin-top:16px;">
            Don't have an account? <a href="register.php">Register</a>
        </p>
    </div>
</body>
</html>