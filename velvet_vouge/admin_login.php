<?php
session_start();
require 'includes/db.php'; // Change path as needed

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $error = "Email and password are required";
    } else {
        // Select admin user where is_admin = 1
        $stmt = $pdo->prepare("SELECT * FROM register WHERE email = ? AND is_admin = 1");
        $stmt->execute([$email]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($admin && password_verify($password, $admin['password'])) {
            // Set admin session variables
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_email'] = $admin['email'];
            $_SESSION['admin_name'] = $admin['username'];  // or 'name' if your db column is 'name'

            // Redirect to admin panel
            header("Location: admin_panal.php");
            exit();
        } else {
            $error = "Invalid admin credentials";
        }
    }
}
?>

<!-- Admin Login Frontend (admin_login.php) -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Admin Login - Velvet Vogue</title>
    <style>
        body { font-family: Arial, sans-serif; background: #bfc5d6; }
        .admin-login-container {
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
        input[type="email"], input[type="password"] {
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
    <div class="admin-login-container">
        <h2>Admin Login</h2>
        <?php if (!empty($error)): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form method="POST" action="">
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" name="email" id="email" required />
            </div>
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" name="password" id="password" required />
            </div>
            <button type="submit" class="btn">Login</button>
        </form>
    </div>
</body>
</html>