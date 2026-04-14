<?php
require_once __DIR__ . '/../config/db.php';
requireLogin();

$db  = getDB();
$uid = $_SESSION['user_id'];

// Cancel a reservation
if (isset($_GET['action']) && $_GET['action'] === 'cancel' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    // Make sure the reservation belongs to this user and is still cancellable
    $stmt = $db->prepare("SELECT id FROM reservations WHERE id=? AND userid=? AND status IN ('pending','confirmed') AND reservationdate >= CURDATE()");
    $stmt->execute([$id, $uid]);
    if ($stmt->fetch()) {
        $db->prepare("UPDATE reservations SET status='cancelled' WHERE id=?")->execute([$id]);
        flash('success', 'Reservation cancelled.');
    }
    header('Location: /restaurantreservation/restaurantreservation/user/my_reservations.php');
    exit;
}

// Pagination setup
$perPage    = 8;
$page       = max(1, (int)($_GET['page'] ?? 1));
$offset     = ($page - 1) * $perPage;

// Count total reservations for this user
$total      = $db->prepare("SELECT COUNT(*) FROM reservations WHERE userid=?");
$total->execute([$uid]);
$total      = (int)$total->fetchColumn();
$totalPages = ceil($total / $perPage);

// Get the reservations for this page
$stmt = $db->prepare("
    SELECT r.*, dt.tablenumber, dt.location
    FROM reservations r
    JOIN tables dt ON dt.id = r.tableid
    WHERE r.userid = ?
    ORDER BY r.reservationdate DESC, r.reservationtime DESC
    LIMIT $perPage OFFSET $offset
");
$stmt->execute([$uid]);
$liste = $stmt->fetchAll();

$pageTitle = 'My Reservations';
include __DIR__ . '/../includes/header.php';
?>

<h1>My Reservations</h1>

<a href="/restaurantreservation/restaurantreservation/user/reserve.php" class="btn btn-primary" style="margin-bottom:15px;">+ New Reservation</a>

<?php if (empty($liste)): ?>
    <p>No reservations yet.</p>
<?php else: ?>
<table>
    <tr>
        <th>Date</th>
        <th>Time</th>
        <th>Table</th>
        <th>Location</th>
        <th>Guests</th>
        <th>Status</th>
        <th>Notes</th>
        <th>Action</th>
    </tr>
    <?php foreach ($liste as $r): ?>
    <tr>
        <td><?= date('m/d/Y', strtotime($r['reservationdate'])) ?></td>
        <td><?= date('H:i',   strtotime($r['reservationtime'])) ?></td>
        <td><?= htmlspecialchars($r['tablenumber']) ?></td>
        <td><?= htmlspecialchars($r['location']) ?></td>
        <td><?= $r['partysize'] ?></td>
        <td><span class="badge badge-<?= $r['status'] ?>"><?= $r['status'] ?></span></td>
        <td><?= htmlspecialchars($r['notes'] ?? '-') ?></td>
        <td>
            <?php if (in_array($r['status'], ['pending','confirmed']) && strtotime($r['reservationdate']) >= strtotime('today')): ?>
                <a href="?action=cancel&id=<?= $r['id'] ?>" class="btn btn-danger"
                   onclick="return confirm('Cancel this reservation?')">Cancel</a>
            <?php else: ?>
                -
            <?php endif; ?>
        </td>
    </tr>
    <?php endforeach; ?>
</table>

<!-- Pagination -->
<?php if ($totalPages > 1): ?>
<div class="pagination">
    <?php for ($p = 1; $p <= $totalPages; $p++): ?>
        <a href="?page=<?= $p ?>" class="<?= $p == $page ? 'active' : '' ?>"><?= $p ?></a>
    <?php endfor; ?>
</div>
<?php endif; ?>

<?php endif; ?>

<?php include __DIR__ . '/../includes/footer.php'; ?>
