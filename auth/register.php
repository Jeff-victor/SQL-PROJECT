<?php
require_once __DIR__ . '/../config/db.php';

// If already logged in, redirect
if (isLoggedIn()) {
    header('Location: /restaurantreservation/restaurantreservation/user/dashboard.php');
    exit;
}

$error   = '';
$success = false;

// Process the form when submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = trim($_POST['name']);
    $email    = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm  = $_POST['confirm'];

    // Basic validations
    if (!$name || !$email || !$password) {
        $error = 'All fields are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email address.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } else {
        $db = getDB();

        // Check if the email is already used
        $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = 'This email is already in use.';
        } else {
            // Create the account
            $hash = password_hash($password, PASSWORD_BCRYPT);
            $db->prepare("INSERT INTO users (name, email, password_hash, role) VALUES (?, ?, ?, 'user')")
               ->execute([$name, $email, $hash]);
            $success = true;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register - The HOUSE</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f5f5f5; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; }
        .box { background: white; padding: 30px; border-radius: 6px; width: 370px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h2 { color: #5C1A2E; text-align: center; margin-bottom: 20px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; font-size: 14px; }
        input { width: 100%; padding: 8px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        button { width: 100%; padding: 10px; background: #5C1A2E; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 15px; }
        button:hover { background: #3D0F1E; }
        .error   { background: #f8d7da; color: #721c24; padding: 10px; border-radius: 4px; margin-bottom: 15px; font-size: 14px; }
        .success { background: #d4edda; color: #155724; padding: 15px; border-radius: 4px; text-align: center; }
        .links { text-align: center; margin-top: 15px; font-size: 13px; }
        .links a { color: #5C1A2E; }
    </style>
</head>
<body>
<div class="box">
    <h2>🍽️ Create an Account</h2>

    <?php if ($success): ?>
        <div class="success">
            Account created successfully!<br><br>
            <a href="/restaurantreservation/restaurantreservation/auth/login.php">Sign in →</a>
        </div>
    <?php else: ?>

        <?php if ($error): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST">
            <label>Full Name</label>
            <input type="text" name="name" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required>

            <label>Email</label>
            <input type="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>

            <label>Password (min. 6 characters)</label>
            <input type="password" name="password" required>

            <label>Confirm Password</label>
            <input type="password" name="confirm" required>

            <button type="submit">Create Account</button>
        </form>

        <div class="links">
            Already have an account? <a href="/restaurantreservation/restaurantreservation/auth/login.php">Sign in</a>
        </div>

    <?php endif; ?>
</div>
</body>
</html>
