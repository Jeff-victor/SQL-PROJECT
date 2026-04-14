<?php
require_once __DIR__ . '/../config/db.php';
requireAdmin();

$db     = getDB();
$action = $_GET['action'] ?? 'list';
$id     = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// ── DELETE ─────────────────────────────────────────────
if ($action === 'delete' && $id) {
    $db->prepare("DELETE FROM tables WHERE id = ?")->execute([$id]);
    flash('success', 'Table deleted.');
    header('Location: /restaurantreservation/restaurantreservation/admin/tables.php');
    exit;
}

// ── SAVE (create or update) ────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tid      = (int)($_POST['id'] ?? 0);
    $number   = trim($_POST['tablenumber']);
    $capacity = (int)$_POST['capacity'];
    $location = trim($_POST['location']);
    $active   = isset($_POST['isactive']) ? 1 : 0;

    if ($tid) {
        // Update existing table
        $db->prepare("UPDATE tables SET tablenumber=?, capacity=?, location=?, isactive=? WHERE id=?")
           ->execute([$number, $capacity, $location, $active, $tid]);
        flash('success', 'Table updated.');
    } else {
        // Create new table
        $db->prepare("INSERT INTO tables (tablenumber, capacity, location, isactive) VALUES (?,?,?,?)")
           ->execute([$number, $capacity, $location, $active]);
        flash('success', 'Table created.');
    }
    header('Location: /restaurantreservation/restaurantreservation/admin/tables.php');
    exit;
}

// ── LOAD for editing ───────────────────────────────────
$table = null;
if ($action === 'edit' && $id) {
    $stmt = $db->prepare("SELECT * FROM tables WHERE id = ?");
    $stmt->execute([$id]);
    $table = $stmt->fetch();
}

// ── LIST ───────────────────────────────────────────────
$liste = $db->query("SELECT * FROM tables ORDER BY tablenumber")->fetchAll();

$pageTitle = 'Manage Tables';
include __DIR__ . '/../includes/header.php';
?>

<h1>Restaurant Tables</h1>

<?php if ($action === 'new' || $action === 'edit'): ?>
<!-- Form -->
<h2><?= $action === 'new' ? 'New Table' : 'Edit Table' ?></h2>
<form method="POST" style="max-width:450px;">
    <input type="hidden" name="id" value="<?= $table['id'] ?? 0 ?>">

    <div class="form-group">
        <label>Table Number</label>
        <input type="text" name="tablenumber" value="<?= htmlspecialchars($table['tablenumber'] ?? '') ?>" placeholder="e.g. T01" required>
    </div>

    <div class="form-group">
        <label>Capacity (number of seats)</label>
        <input type="number" name="capacity" min="1" value="<?= $table['capacity'] ?? '' ?>" required>
    </div>

    <div class="form-group">
        <label>Location</label>
        <input type="text" name="location" value="<?= htmlspecialchars($table['location'] ?? 'Main Hall') ?>">
    </div>

    <div class="form-group">
        <label>
            <input type="checkbox" name="isactive" value="1" <?= ($table['isactive'] ?? 1) ? 'checked' : '' ?>>
            Table is active (available for booking)
        </label>
    </div>

    <button type="submit" class="btn btn-primary">
        <?= $action === 'new' ? 'Create' : 'Save' ?>
    </button>
    <a href="/restaurantreservation/restaurantreservation/admin/tables.php" class="btn btn-warning">Cancel</a>
</form>

<?php else: ?>
<!-- List -->
<a href="?action=new" class="btn btn-primary" style="margin-bottom:15px;">+ Add Table</a>

<table>
    <tr>
        <th>Number</th>
        <th>Location</th>
        <th>Capacity</th>
        <th>Status</th>
        <th>Actions</th>
    </tr>
    <?php foreach ($liste as $t): ?>
    <tr>
        <td><?= htmlspecialchars($t['tablenumber']) ?></td>
        <td><?= htmlspecialchars($t['location']) ?></td>
        <td><?= $t['capacity'] ?> seats</td>
        <td><?= $t['isactive'] ? '✅ Active' : '❌ Inactive' ?></td>
        <td>
            <a href="?action=edit&id=<?= $t['id'] ?>" class="btn btn-warning">Edit</a>
            <a href="?action=delete&id=<?= $t['id'] ?>" class="btn btn-danger"
               onclick="return confirm('Delete table <?= htmlspecialchars($t['tablenumber']) ?>?')">Delete</a>
        </td>
    </tr>
    <?php endforeach; ?>
</table>
<?php endif; ?>

<?php include __DIR__ . '/../includes/footer.php'; ?>
