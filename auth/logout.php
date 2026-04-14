<?php
require_once __DIR__ . '/../config/db.php';

// Clear the session and log the user out
$_SESSION = [];
session_destroy();

// Redirect to the login page
header('Location: /restaurantreservation/restaurantreservation/auth/login.php');
exit;
