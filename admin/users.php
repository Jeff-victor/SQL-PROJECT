<?php
require_once __DIR__ . '/../config/db.php';
requireAdmin();

$db     = getDB();
$action = $_GET['action'] ?? 'list';
$id     = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// ── DELETE ─────────────────────────────────────────────
if ($action === 'delete' && $id) {
    // Prevent deleting your own account
    if ($id === (int)$_SESSION['user_id']) {
        flash('error', 'You cannot delete your own account.');
    } else {
        $db->prepare("DELETE FROM users WHERE id = ?")->execute([$id]);
        flash('success', 'User deleted.');
    }
    header('Location: /restaurantreservation/restaurantreservation/admin/users.php');
    exit;
}

// ── SAVE (create or update) ────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $uid      = (int)($_POST['id'] ?? 0);
    $name     = trim($_POST['name']);
    $email    = trim($_POST['email']);
    $role     = $_POST['role'];
    $password = $_POST['password'] ?? '';

    if ($uid) {
        // Update with or without a new password
        if ($password) {
            $hash = password_hash($password, PASSWORD_BCRYPT);
            $db->prepare("UPDATE users SET name=?, email=?, role=?, password_hash=? WHERE id=?")
               ->execute([$name, $email, $role, $hash, $uid]);
        } else {
            $db->prepare("UPDATE users SET name=?, email=?, role=? WHERE id=?")
               ->execute([$name, $email, $role, $uid]);
        }
        flash('success', 'User updated.');
    } else {
        // Create new user
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $db->prepare("INSERT INTO users (name, email, password_hash, role) VALUES (?,?,?,?)")
           ->execute([$name, $email, $hash, $role]);
        flash('success', 'User created.');
    }
    header('Location: /restaurantreservation/restaurantreservation/admin/users.php');
    exit;
}

// ── LOAD for editing ───────────────────────────────────
$user = null;
if ($action === 'edit' && $id) {
    $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$id]);
    $user = $stmt->fetch();
}

// ── LIST ───────────────────────────────────────────────
$liste = $db->query("SELECT * FROM users ORDER BY role, name")->fetchAll();

$pageTitle = 'Manage Users';
include __DIR__ . '/../includes/header.php';
?>

<h1>Users</h1>

<?php if ($action === 'new' || $action === 'edit'): ?>
<!-- Form -->
<h2><?= $action === 'new' ? 'New User' : 'Edit User' ?></h2>
<form method="POST" style="max-width:450px;">
    <input type="hidden" name="id" value="<?= $user['id'] ?? 0 ?>">

    <div class="form-group">
        <label>Full Name</label>
        <input type="text" name="name" value="<?= htmlspecialchars($user['name'] ?? '') ?>" required>
    </div>

    <div class="form-group">
        <label>Email</label>
        <input type="email" name="email" value="<?= htmlspecialchars($user['email'] ?? '') ?>" required>
    </div>

    <div class="form-group">
        <label>Role</label>
        <select name="role">
            <option value="user"  <?= (($user['role'] ?? 'user') === 'user')  ? 'selected' : '' ?>>User</option>
            <option value="admin" <?= (($user['role'] ?? 'user') === 'admin') ? 'selected' : '' ?>>Administrator</option>
        </select>
    </div>

    <div class="form-group">
        <label>Password <?= $user ? '(leave blank to keep current)' : '' ?></label>
        <input type="password" name="password" <?= !$user ? 'required' : '' ?>>
    </div>

    <button type="submit" class="btn btn-primary">
        <?= $action === 'new' ? 'Create' : 'Save' ?>
    </button>
    <a href="/restaurantreservation/restaurantreservation/admin/users.php" class="btn btn-warning">Cancel</a>
</form>

<?php else: ?>
<!-- List -->
<a href="?action=new" class="btn btn-primary" style="margin-bottom:15px;">+ Add User</a>

<table>
    <tr>
        <th>Name</th>
        <th>Email</th>
        <th>Role</th>
        <th>Joined</th>
        <th>Actions</th>
    </tr>
    <?php foreach ($liste as $u): ?>
    <tr>
        <td><?= htmlspecialchars($u['name']) ?></td>
        <td><?= htmlspecialchars($u['email']) ?></td>
        <td><?= $u['role'] === 'admin' ? '🔑 Admin' : '👤 User' ?></td>
        <td><?= date('m/d/Y', strtotime($u['created_at'])) ?></td>
        <td>
            <a href="?action=edit&id=<?= $u['id'] ?>" class="btn btn-warning">Edit</a>
            <?php if ($u['id'] !== (int)$_SESSION['user_id']): ?>
                <a href="?action=delete&id=<?= $u['id'] ?>" class="btn btn-danger"
                   onclick="return confirm('Delete <?= htmlspecialchars($u['name']) ?>?')">Delete</a>
            <?php endif; ?>
        </td>
    </tr>
    <?php endforeach; ?>
</table>
<?php endif; ?>

<?php include __DIR__ . '/../includes/footer.php'; ?>
