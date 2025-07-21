<?php
session_start();
// FIX: Use the correct path for your database config file
require_once __DIR__ . '/config/db.php'; // This will always use the correct absolute path

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if (!$username || !$email || !$password || !$confirm) {
        $msg = 'All fields are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $msg = 'Invalid email address.';
    } elseif ($password !== $confirm) {
        $msg = 'Passwords do not match.';
    } else {
        // Check if username or email already exists
        $stmt = $pdo->prepare("SELECT account_id FROM accounts WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        if ($stmt->fetch()) {
            $msg = 'Username or email already exists.';
        } else {
            // Hash the password
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            // Insert into accounts table
            $stmt = $pdo->prepare("INSERT INTO accounts (username, email, password_hash) VALUES (?, ?, ?)");
            if ($stmt->execute([$username, $email, $password_hash])) {
                $_SESSION['user'] = [
                    'username' => $username,
                    'email' => $email
                ];
                $msg = 'Registration successful!';
            } else {
                $msg = 'Registration failed. Please try again.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - Velvet Vogue</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f8f9fa; }
        .register-container {
            max-width: 400px;
            margin: 60px auto;
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.08);
            padding: 32px 28px;
        }
        h2 { text-align: center; color: #333; margin-bottom: 24px; }
        .form-group { margin-bottom: 18px; }
        label { display: block; margin-bottom: 6px; color: #555; font-weight: 500; }
        input[type="text"], input[type="email"], input[type="password"] {
            width: 100%; padding: 10px 12px; border: 1px solid #ccc; border-radius: 8px; font-size: 1rem;
        }
        .btn {
            width: 100%; background: #667eea; color: #fff; border: none; padding: 12px; border-radius: 8px;
            font-size: 1.1rem; font-weight: 600; cursor: pointer; transition: background 0.2s;
        }
        .btn:hover { background: #4f46e5; }
        .msg { text-align: center; margin-bottom: 16px; color: #e74c3c; font-weight: 500; }
        .msg.success { color: #27ae60; }
    </style>
</head>
<body>
    <div class="register-container">
        <h2>Create Account</h2>
        <?php if ($msg): ?>
            <div class="msg<?php echo ($msg === 'Registration successful!') ? ' success' : ''; ?>"><?php echo htmlspecialchars($msg); ?></div>
        <?php endif; ?>
        <form method="post" autocomplete="off">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
            </div>

            <button type="submit" class="btn" style="display: flex; align-items: center; justify-content: center; gap: 10px;">
                <span style="display: flex; align-items: center; justify-content: center;">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" style="margin-right: 6px;"><circle cx="12" cy="12" r="10" fill="#fff"/><path d="M8 12.5l2.5 2.5L16 9.5" stroke="#667eea" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </span>
                Sign Up
            </button>
        </form>
        <div style="text-align:center; margin-top:18px;">
            <a href="login.php" style="color:#667eea; text-decoration:none;">Already have an account? Login</a>
        </div>
    </div>
</body>
</html>
