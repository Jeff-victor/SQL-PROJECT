<?php
require_once __DIR__ . '/../config/db.php';
requireAdmin();

$db     = getDB();
$action = $_GET['action'] ?? 'list';
$id     = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// ── DELETE ─────────────────────────────────────────────
if ($action === 'delete' && $id) {
    $db->prepare("DELETE FROM reservations WHERE id = ?")->execute([$id]);
    flash('success', 'Reservation deleted.');
    header('Location: /restaurantreservation/restaurantreservation/admin/reservations.php');
    exit;
}

// ── SAVE (create or update) ────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rid   = (int)($_POST['id'] ?? 0);
    $uid   = (int)$_POST['userid'];
    $tid   = (int)$_POST['tableid'];
    $date  = $_POST['reservationdate'];
    $time  = $_POST['reservationtime'];
    $party = (int)$_POST['partysize'];
    $stat  = $_POST['status'];
    $notes = trim($_POST['notes'] ?? '');

    if ($rid) {
        // Update existing reservation
        $db->prepare("UPDATE reservations SET userid=?, tableid=?, reservationdate=?, reservationtime=?, partysize=?, status=?, notes=? WHERE id=?")
           ->execute([$uid, $tid, $date, $time, $party, $stat, $notes, $rid]);
        flash('success', 'Reservation updated.');
    } else {
        // Create new reservation
        $db->prepare("INSERT INTO reservations (userid, tableid, reservationdate, reservationtime, partysize, status, notes) VALUES (?,?,?,?,?,?,?)")
           ->execute([$uid, $tid, $date, $time, $party, $stat, $notes]);
        flash('success', 'Reservation created.');
    }
    header('Location: /restaurantreservation/restaurantreservation/admin/reservations.php');
    exit;
}

// ── LOAD for editing ───────────────────────────────────
$res = null;
if ($action === 'edit' && $id) {
    $stmt = $db->prepare("SELECT * FROM reservations WHERE id = ?");
    $stmt->execute([$id]);
    $res = $stmt->fetch();
}

// ── LIST with pagination ───────────────────────────────
$perPage = 10;
$page    = max(1, (int)($_GET['page'] ?? 1));
$offset  = ($page - 1) * $perPage;

$total      = $db->query("SELECT COUNT(*) FROM reservations")->fetchColumn();
$totalPages = ceil($total / $perPage);

// Get reservations with guest name and table number
$liste = $db->query("
    SELECT r.*, u.name AS client, dt.tablenumber
    FROM reservations r
    JOIN users u   ON u.id  = r.userid
    JOIN tables dt ON dt.id = r.tableid
    ORDER BY r.reservationdate DESC, r.reservationtime DESC
    LIMIT $perPage OFFSET $offset
")->fetchAll();

// Load users and tables for the form dropdowns
$users  = $db->query("SELECT id, name FROM users WHERE role='user' ORDER BY name")->fetchAll();
$tables = $db->query("SELECT id, tablenumber, capacity, location FROM tables WHERE isactive=1 ORDER BY tablenumber")->fetchAll();

$pageTitle = 'Manage Reservations';
include __DIR__ . '/../includes/header.php';
?>

<h1>Reservations</h1>

<?php if ($action === 'new' || $action === 'edit'): ?>
<!-- Create / Edit form -->
<h2><?= $action === 'new' ? 'New Reservation' : 'Edit Reservation' ?></h2>
<form method="POST" style="max-width:550px;">
    <input type="hidden" name="id" value="<?= $res['id'] ?? 0 ?>">

    <div class="form-group">
        <label>Guest</label>
        <select name="userid" required>
            <option value="">-- Select --</option>
            <?php foreach ($users as $u): ?>
                <option value="<?= $u['id'] ?>" <?= (($res['userid'] ?? '') == $u['id']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($u['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="form-group">
        <label>Table</label>
        <select name="tableid" required>
            <option value="">-- Select --</option>
            <?php foreach ($tables as $t): ?>
                <option value="<?= $t['id'] ?>" <?= (($res['tableid'] ?? '') == $t['id']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($t['tablenumber'] . ' - ' . $t['location'] . ' (' . $t['capacity'] . ' seats)') ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="form-group">
        <label>Date</label>
        <input type="date" name="reservationdate" value="<?= $res['reservationdate'] ?? '' ?>" required>
    </div>

    <div class="form-group">
        <label>Time</label>
        <input type="time" name="reservationtime" value="<?= $res['reservationtime'] ?? '' ?>" required>
    </div>

    <div class="form-group">
        <label>Number of Guests</label>
        <input type="number" name="partysize" min="1" value="<?= $res['partysize'] ?? 2 ?>" required>
    </div>

    <div class="form-group">
        <label>Status</label>
        <select name="status">
            <?php foreach (['pending','confirmed','cancelled','completed'] as $s): ?>
                <option value="<?= $s ?>" <?= (($res['status'] ?? 'pending') === $s) ? 'selected' : '' ?>>
                    <?= ucfirst($s) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="form-group">
        <label>Notes</label>
        <textarea name="notes"><?= htmlspecialchars($res['notes'] ?? '') ?></textarea>
    </div>

    <button type="submit" class="btn btn-primary">
        <?= $action === 'new' ? 'Create' : 'Save' ?>
    </button>
    <a href="/restaurantreservation/restaurantreservation/admin/reservations.php" class="btn btn-warning">Cancel</a>
</form>

<?php else: ?>
<!-- Reservations list -->
<a href="?action=new" class="btn btn-primary" style="margin-bottom:15px;">+ New Reservation</a>

<table>
    <tr>
        <th>#</th>
        <th>Guest</th>
        <th>Table</th>
        <th>Date</th>
        <th>Time</th>
        <th>Guests</th>
        <th>Status</th>
        <th>Actions</th>
    </tr>
    <?php foreach ($liste as $r): ?>
    <tr>
        <td><?= $r['id'] ?></td>
        <td><?= htmlspecialchars($r['client']) ?></td>
        <td><?= htmlspecialchars($r['tablenumber']) ?></td>
        <td><?= date('m/d/Y', strtotime($r['reservationdate'])) ?></td>
        <td><?= date('H:i', strtotime($r['reservationtime'])) ?></td>
        <td><?= $r['partysize'] ?></td>
        <td><span class="badge badge-<?= $r['status'] ?>"><?= $r['status'] ?></span></td>
        <td>
            <a href="?action=edit&id=<?= $r['id'] ?>" class="btn btn-warning">Edit</a>
            <a href="?action=delete&id=<?= $r['id'] ?>" class="btn btn-danger"
               onclick="return confirm('Delete this reservation?')">Delete</a>
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
