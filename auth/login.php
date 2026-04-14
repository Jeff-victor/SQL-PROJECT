<?php
require_once __DIR__ . '/../config/db.php';

// If already logged in, redirect
if (isLoggedIn()) {
    header('Location: /restaurantreservation/restaurantreservation/user/dashboard.php');
    exit;
}

$error = '';

// Process the form when submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = $_POST['email'];
    $password = $_POST['password'];

    // Search for the user in the database
    $db   = getDB();
    $stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    // Verify the password
    if ($user && password_verify($password, $user['password_hash'])) {
        // Login successful - save data in the session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['name']    = $user['name'];
        $_SESSION['email']   = $user['email'];
        $_SESSION['role']    = $user['role'];

        // Redirect based on role
        if ($user['role'] === 'admin') {
            header('Location: /restaurantreservation/restaurantreservation/admin/dashboard.php');
        } else {
            header('Location: /restaurantreservation/restaurantreservation/user/dashboard.php');
        }
        exit;
    } else {
        $error = 'Incorrect email or password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login - The HOUSE</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f5f5f5; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .box { background: white; padding: 30px; border-radius: 6px; width: 350px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h2 { color: #5C1A2E; text-align: center; margin-bottom: 20px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; font-size: 14px; }
        input { width: 100%; padding: 8px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        button { width: 100%; padding: 10px; background: #5C1A2E; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 15px; }
        button:hover { background: #3D0F1E; }
        .error { background: #f8d7da; color: #721c24; padding: 10px; border-radius: 4px; margin-bottom: 15px; font-size: 14px; }
        .links { text-align: center; margin-top: 15px; font-size: 13px; }
        .links a { color: #5C1A2E; }
    </style>
</head>
<body>
<div class="box">
    <h2>🍽️ The HOUSE</h2>

    <?php if ($error): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST">
        <label>Email</label>
        <input type="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>

        <label>Password</label>
        <input type="password" name="password" required>

        <button type="submit">Sign In</button>
    </form>

    <div class="links">
        No account yet? <a href="/restaurantreservation/restaurantreservation/auth/register.php">Register</a>
    </div>
</div>
</body>
</html>
