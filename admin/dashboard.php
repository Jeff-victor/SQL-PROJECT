<?php
require_once __DIR__ . '/../config/db.php';
requireAdmin(); // Only admin can access this page

$db = getDB();

// General statistics using COUNT, SUM, AVG
$stats = $db->query("
    SELECT
        COUNT(*)                    AS total,
        COALESCE(AVG(partysize), 0) AS avg_guests,
        COALESCE(SUM(partysize), 0) AS total_guests,
        SUM(CASE WHEN status='pending'   THEN 1 ELSE 0 END) AS pending,
        SUM(CASE WHEN status='confirmed' THEN 1 ELSE 0 END) AS confirmed,
        SUM(CASE WHEN status='cancelled' THEN 1 ELSE 0 END) AS cancelled,
        SUM(CASE WHEN status='completed' THEN 1 ELSE 0 END) AS completed
    FROM reservations
")->fetch();

// Number of registered customers
$nb_users = $db->query("SELECT COUNT(*) FROM users WHERE role='user'")->fetchColumn();

// Number of active tables
$nb_tables = $db->query("SELECT COUNT(*) FROM tables WHERE isactive=1")->fetchColumn();

// Today's reservations
$aujourd_hui = $db->query("
    SELECT COUNT(*) FROM reservations
    WHERE reservationdate = CURDATE()
    AND status IN ('pending','confirmed')
")->fetchColumn();

// Most booked tables (uses GROUP BY)
$tables_populaires = $db->query("
    SELECT dt.tablenumber, dt.location, COUNT(r.id) AS nb_reservations
    FROM tables dt
    LEFT JOIN reservations r ON r.tableid = dt.id
    GROUP BY dt.id
    ORDER BY nb_reservations DESC
    LIMIT 5
")->fetchAll();

// Upcoming reservations (next 7 days)
$prochaines = $db->query("
    SELECT r.id, u.name AS client, dt.tablenumber,
           r.reservationdate, r.reservationtime, r.partysize, r.status
    FROM reservations r
    JOIN users u   ON u.id  = r.userid
    JOIN tables dt ON dt.id = r.tableid
    WHERE r.reservationdate BETWEEN CURDATE() AND CURDATE() + INTERVAL 7 DAY
    AND r.status IN ('pending','confirmed')
    ORDER BY r.reservationdate, r.reservationtime
    LIMIT 8
")->fetchAll();

$pageTitle = 'Admin Dashboard';
include __DIR__ . '/../includes/header.php';
?>

<h1>Dashboard</h1>

<!-- Statistics cards -->
<div class="stats">
    <div class="stat-box">
        <h3><?= $stats['total'] ?></h3>
        <p>Total reservations</p>
    </div>
    <div class="stat-box">
        <h3><?= $aujourd_hui ?></h3>
        <p>Today's reservations</p>
    </div>
    <div class="stat-box">
        <h3><?= number_format((float)$stats['avg_guests'], 1) ?></h3>
        <p>Average guests</p>
    </div>
    <div class="stat-box">
        <h3><?= $stats['total_guests'] ?></h3>
        <p>Total guests</p>
    </div>
    <div class="stat-box">
        <h3><?= $nb_users ?></h3>
        <p>Registered customers</p>
    </div>
    <div class="stat-box">
        <h3><?= $nb_tables ?></h3>
        <p>Active tables</p>
    </div>
</div>

<!-- Reservations by status -->
<h2>Reservations by Status</h2>
<table>
    <tr>
        <th>Status</th>
        <th>Count</th>
    </tr>
    <tr><td>Pending</td>   <td><?= $stats['pending'] ?></td></tr>
    <tr><td>Confirmed</td> <td><?= $stats['confirmed'] ?></td></tr>
    <tr><td>Completed</td> <td><?= $stats['completed'] ?></td></tr>
    <tr><td>Cancelled</td> <td><?= $stats['cancelled'] ?></td></tr>
</table>

<!-- Most booked tables -->
<h2>Most Booked Tables</h2>
<table>
    <tr>
        <th>Table</th>
        <th>Location</th>
        <th>Total Bookings</th>
    </tr>
    <?php foreach ($tables_populaires as $t): ?>
    <tr>
        <td><?= htmlspecialchars($t['tablenumber']) ?></td>
        <td><?= htmlspecialchars($t['location']) ?></td>
        <td><?= $t['nb_reservations'] ?></td>
    </tr>
    <?php endforeach; ?>
</table>

<!-- Upcoming reservations -->
<h2>Upcoming Reservations (next 7 days)</h2>
<?php if (empty($prochaines)): ?>
    <p>No reservations in the next 7 days.</p>
<?php else: ?>
<table>
    <tr>
        <th>Guest</th>
        <th>Table</th>
        <th>Date</th>
        <th>Time</th>
        <th>Guests</th>
        <th>Status</th>
        <th>Action</th>
    </tr>
    <?php foreach ($prochaines as $r): ?>
    <tr>
        <td><?= htmlspecialchars($r['client']) ?></td>
        <td><?= htmlspecialchars($r['tablenumber']) ?></td>
        <td><?= date('m/d/Y', strtotime($r['reservationdate'])) ?></td>
        <td><?= date('H:i', strtotime($r['reservationtime'])) ?></td>
        <td><?= $r['partysize'] ?></td>
        <td><span class="badge badge-<?= $r['status'] ?>"><?= $r['status'] ?></span></td>
        <td>
            <a href="/restaurantreservation/restaurantreservation/admin/reservations.php?action=edit&id=<?= $r['id'] ?>"
               class="btn btn-warning">Edit</a>
        </td>
    </tr>
    <?php endforeach; ?>
</table>
<?php endif; ?>

<?php include __DIR__ . '/../includes/footer.php'; ?>
