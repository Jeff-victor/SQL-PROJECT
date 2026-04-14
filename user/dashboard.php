<?php
require_once __DIR__ . '/../config/db.php';
requireLogin();

$db  = getDB();
$uid = $_SESSION['user_id'];

// Personal statistics for the logged-in user
$stats = $db->prepare("
    SELECT
        COUNT(*) AS total,
        SUM(CASE WHEN status='pending'   THEN 1 ELSE 0 END) AS pending,
        SUM(CASE WHEN status='confirmed' THEN 1 ELSE 0 END) AS confirmed,
        SUM(CASE WHEN status='completed' THEN 1 ELSE 0 END) AS completed
    FROM reservations WHERE userid = ?
");
$stats->execute([$uid]);
$stats = $stats->fetch();

// Next upcoming reservation
$prochaine = $db->prepare("
    SELECT r.*, dt.tablenumber, dt.location
    FROM reservations r
    JOIN tables dt ON dt.id = r.tableid
    WHERE r.userid = ?
    AND r.reservationdate >= CURDATE()
    AND r.status IN ('pending','confirmed')
    ORDER BY r.reservationdate, r.reservationtime
    LIMIT 1
");
$prochaine->execute([$uid]);
$prochaine = $prochaine->fetch();

// Last 5 reservations
$dernieres = $db->prepare("
    SELECT r.*, dt.tablenumber
    FROM reservations r
    JOIN tables dt ON dt.id = r.tableid
    WHERE r.userid = ?
    ORDER BY r.created_at DESC
    LIMIT 5
");
$dernieres->execute([$uid]);
$dernieres = $dernieres->fetchAll();

$pageTitle = 'My Dashboard';
include __DIR__ . '/../includes/header.php';
?>

<h1>Hello, <?= htmlspecialchars($_SESSION['name'] ?? 'Guest') ?> 👋</h1>

<!-- Statistics -->
<div class="stats">
    <div class="stat-box">
        <h3><?= $stats['total'] ?></h3>
        <p>Total reservations</p>
    </div>
    <div class="stat-box">
        <h3><?= $stats['pending'] ?></h3>
        <p>Pending</p>
    </div>
    <div class="stat-box">
        <h3><?= $stats['confirmed'] ?></h3>
        <p>Confirmed</p>
    </div>
    <div class="stat-box">
        <h3><?= $stats['completed'] ?></h3>
        <p>Completed</p>
    </div>
</div>

<!-- Next upcoming reservation -->
<?php if ($prochaine): ?>
<div style="background:white;border-left:4px solid #C9A84C;padding:15px 20px;margin-bottom:25px;border-radius:4px;">
    <strong>Next Reservation:</strong>
    Table <?= htmlspecialchars($prochaine['tablenumber']) ?> (<?= htmlspecialchars($prochaine['location']) ?>)
    — <?= date('m/d/Y', strtotime($prochaine['reservationdate'])) ?>
    at <?= date('H:i', strtotime($prochaine['reservationtime'])) ?>
    — <?= $prochaine['partysize'] ?> guests
    — <span class="badge badge-<?= $prochaine['status'] ?>"><?= $prochaine['status'] ?></span>
</div>
<?php endif; ?>

<!-- Recent reservations -->
<h2>My Recent Reservations</h2>
<?php if (empty($dernieres)): ?>
    <p>No reservations yet. <a href="/restaurantreservation/restaurantreservation/user/reserve.php">Make a reservation →</a></p>
<?php else: ?>
<table>
    <tr>
        <th>Date</th>
        <th>Time</th>
        <th>Table</th>
        <th>Guests</th>
        <th>Status</th>
        <th>Action</th>
    </tr>
    <?php foreach ($dernieres as $r): ?>
    <tr>
        <td><?= date('m/d/Y', strtotime($r['reservationdate'])) ?></td>
        <td><?= date('H:i',   strtotime($r['reservationtime'])) ?></td>
        <td><?= htmlspecialchars($r['tablenumber']) ?></td>
        <td><?= $r['partysize'] ?></td>
        <td><span class="badge badge-<?= $r['status'] ?>"><?= $r['status'] ?></span></td>
        <td>
            <?php if (in_array($r['status'], ['pending','confirmed']) && strtotime($r['reservationdate']) >= strtotime('today')): ?>
                <a href="/restaurantreservation/restaurantreservation/user/my_reservations.php?action=cancel&id=<?= $r['id'] ?>"
                   class="btn btn-danger"
                   onclick="return confirm('Cancel this reservation?')">Cancel</a>
            <?php endif; ?>
        </td>
    </tr>
    <?php endforeach; ?>
</table>
<a href="/restaurantreservation/restaurantreservation/user/my_reservations.php">View all my reservations →</a>
<?php endif; ?>

<br><br>
<a href="/restaurantreservation/restaurantreservation/user/reserve.php" class="btn btn-primary">+ New Reservation</a>

<?php include __DIR__ . '/../includes/footer.php'; ?>
