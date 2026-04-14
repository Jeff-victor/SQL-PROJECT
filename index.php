<?php
// Home page - redirects based on whether the user is logged in or not
require_once __DIR__ . '/config/db.php';

if (!isLoggedIn()) {
    header('Location: /restaurantreservation/restaurantreservation/auth/login.php');
} elseif (isAdmin()) {
    header('Location: /restaurantreservation/restaurantreservation/admin/dashboard.php');
} else {
    header('Location: /restaurantreservation/restaurantreservation/user/dashboard.php');
}
exit;
