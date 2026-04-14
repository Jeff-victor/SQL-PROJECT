<?php
// header.php - Included at the top of every page
$pageTitle = $pageTitle ?? 'The HOUSE';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($pageTitle) ?> - The HOUSE</title>
    <style>
        /* General style */
        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            padding: 0;
        }

        /* Navigation bar */
        nav {
            background-color: #5C1A2E;
            padding: 10px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        nav a {
            color: white;
            text-decoration: none;
            margin-right: 15px;
            font-size: 14px;
        }
        nav a:hover { text-decoration: underline; }
        .nav-brand {
            font-size: 20px;
            font-weight: bold;
            color: #C9A84C;
        }

        /* Main content */
        .container {
            max-width: 1100px;
            margin: 30px auto;
            padding: 0 20px;
        }

        /* Page title */
        h1 {
            color: #5C1A2E;
            border-bottom: 2px solid #C9A84C;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }

        /* Flash messages */
        .flash {
            padding: 10px 15px;
            margin-bottom: 15px;
            border-radius: 4px;
        }
        .success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error   { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }

        /* Table */
        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            margin-bottom: 20px;
        }
        table th {
            background-color: #5C1A2E;
            color: white;
            padding: 10px;
            text-align: left;
        }
        table td {
            padding: 10px;
            border-bottom: 1px solid #ddd;
        }
        table tr:hover { background-color: #f9f9f9; }

        /* Buttons */
        .btn {
            display: inline-block;
            padding: 8px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            font-size: 13px;
        }
        .btn-primary { background-color: #5C1A2E; color: white; }
        .btn-danger  { background-color: #dc3545; color: white; }
        .btn-warning { background-color: #ffc107; color: black; }
        .btn-success { background-color: #28a745; color: white; }
        .btn:hover   { opacity: 0.85; }

        /* Form */
        .form-group { margin-bottom: 15px; }
        .form-group label {
            display: block;
            font-weight: bold;
            margin-bottom: 5px;
            color: #333;
        }
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 14px;
            box-sizing: border-box;
        }
        .form-group textarea { height: 80px; }

        /* Stat cards */
        .stats {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            margin-bottom: 25px;
        }
        .stat-box {
            background: white;
            border-left: 4px solid #C9A84C;
            padding: 15px 20px;
            border-radius: 4px;
            flex: 1;
            min-width: 150px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .stat-box h3 { margin: 0; font-size: 28px; color: #5C1A2E; }
        .stat-box p  { margin: 5px 0 0; font-size: 13px; color: #666; }

        /* Status badge */
        .badge {
            padding: 3px 8px;
            border-radius: 10px;
            font-size: 12px;
            font-weight: bold;
        }
        .badge-pending   { background: #fff3cd; color: #856404; }
        .badge-confirmed { background: #d4edda; color: #155724; }
        .badge-cancelled { background: #f8d7da; color: #721c24; }
        .badge-completed { background: #d1ecf1; color: #0c5460; }

        /* Pagination */
        .pagination { margin-top: 15px; }
        .pagination a {
            display: inline-block;
            padding: 6px 12px;
            margin: 2px;
            border: 1px solid #ddd;
            border-radius: 4px;
            text-decoration: none;
            color: #5C1A2E;
            background: white;
        }
        .pagination a.active { background: #5C1A2E; color: white; }
        .pagination a:hover  { background: #f0e0e5; }

        /* Footer */
        footer {
            background-color: #5C1A2E;
            color: #ccc;
            text-align: center;
            padding: 15px;
            margin-top: 40px;
            font-size: 13px;
        }
    </style>
</head>
<body>

<!-- Navigation bar -->
<nav>
    <a class="nav-brand" href="/restaurantreservation/restaurantreservation/">🍽️ The HOUSE</a>
    <div>
        <?php if (isLoggedIn()): ?>
            <?php if (isAdmin()): ?>
                <a href="/restaurantreservation/restaurantreservation/admin/dashboard.php">Dashboard</a>
                <a href="/restaurantreservation/restaurantreservation/admin/reservations.php">Reservations</a>
                <a href="/restaurantreservation/restaurantreservation/admin/tables.php">Tables</a>
                <a href="/restaurantreservation/restaurantreservation/admin/users.php">Users</a>
            <?php else: ?>
                <a href="/restaurantreservation/restaurantreservation/user/dashboard.php">Home</a>
                <a href="/restaurantreservation/restaurantreservation/user/reserve.php">Reserve</a>
                <a href="/restaurantreservation/restaurantreservation/user/my_reservations.php">My Reservations</a>
            <?php endif; ?>
            <a href="/restaurantreservation/restaurantreservation/auth/logout.php">Logout (<?= htmlspecialchars($_SESSION['name'] ?? 'Account') ?>)</a>
        <?php else: ?>
            <a href="/restaurantreservation/restaurantreservation/auth/login.php">Login</a>
            <a href="/restaurantreservation/restaurantreservation/auth/register.php">Register</a>
        <?php endif; ?>
    </div>
</nav>

<!-- Main content -->
<div class="container">
<?php showFlash(); ?>
