<?php
session_start();

// Destroy all session data
$_SESSION = [];
session_unset();
session_destroy();

// Optionally redirect the user to login or homepage
header("Location: login.php");
exit();
?>
        <div class="form-group">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" required>
        </div>
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>
        </div>
        <button type="submit" class="btn">Login</button>
        <p style="text-align: center; margin-top: 12px;">Don't have an account? <a href="signup.php">Sign Up</a></p>
    </form>
    </div>
    <!-- logout.html -->
<!DOCTYPE html>
<html>
<head>
    <title>Logout</title>
</head>
<body>

    <h2>Welcome, User!</h2>

    <!-- Logout button -->
    <form action="logout.php" method="post">
        <button type="submit">Logout</button>
    </form>

</body>
</html>