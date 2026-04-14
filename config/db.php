<?php
// Start the session (required for login/logout)
session_start();

// Database connection information
$host = 'localhost';
$user = 'root';
$pass = '';
$db   = 'restaurantreservation';

// Connect using PDO
try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Connection error: " . $e->getMessage());
}

// Returns the PDO connection
function getDB() {
    global $pdo;
    return $pdo;
}

// Check if the user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Check if the user is an admin
function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

// Redirect to login if not logged in
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: /restaurantreservation/restaurantreservation/auth/login.php');
        exit;
    }
}

// Redirect if not admin
function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        header('Location: /restaurantreservation/restaurantreservation/user/dashboard.php');
        exit;
    }
}

// Save a flash message (temporary message shown once)
function flash($type, $msg) {
    $_SESSION['flash'] = ['type' => $type, 'msg' => $msg];
}

// Display the flash message if it exists
function showFlash() {
    if (!empty($_SESSION['flash'])) {
        $f = $_SESSION['flash'];
        unset($_SESSION['flash']);
        echo '<div class="flash ' . $f['type'] . '">' . htmlspecialchars($f['msg']) . '</div>';
    }
}
