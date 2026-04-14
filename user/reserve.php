<?php
require_once __DIR__ . '/../config/db.php';
requireLogin();

$db    = getDB();
$uid   = $_SESSION['user_id'];
$error = '';

// Load available tables
$tables = $db->query("SELECT * FROM tables WHERE isactive=1 ORDER BY tablenumber")->fetchAll();

// Process the form when submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tid   = (int)$_POST['tableid'];
    $date  = $_POST['reservationdate'];
    $time  = $_POST['reservationtime'];
    $party = (int)$_POST['partysize'];
    $notes = trim($_POST['notes'] ?? '');

    // Validate required fields
    if (!$tid || !$date || !$time || !$party) {
        $error = 'Please fill in all required fields.';
    } elseif (strtotime($date) < strtotime('today')) {
        $error = 'The date must be today or in the future.';
    } else {
        // Check if the table is already booked at that time
        $chk = $db->prepare("SELECT id FROM reservations WHERE tableid=? AND reservationdate=? AND reservationtime=?");
        $chk->execute([$tid, $date, $time]);
        if ($chk->fetch()) {
            $error = 'This table is already booked at that date and time.';
        } else {
            // Create the reservation
            $db->prepare("INSERT INTO reservations (userid, tableid, reservationdate, reservationtime, partysize, notes) VALUES (?,?,?,?,?,?)")
               ->execute([$uid, $tid, $date, $time, $party, $notes]);
            flash('success', 'Reservation made successfully!');
            header('Location: /restaurantreservation/restaurantreservation/user/my_reservations.php');
            exit;
        }
    }
}

$pageTitle = 'Make a Reservation';
include __DIR__ . '/../includes/header.php';
?>

<h1>New Reservation</h1>

<?php if ($error): ?>
    <div class="flash error"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<form method="POST" style="max-width:500px;">

    <div class="form-group">
        <label>Choose a Table</label>
        <select name="tableid" required>
            <option value="">-- Select a table --</option>
            <?php foreach ($tables as $t): ?>
                <option value="<?= $t['id'] ?>" <?= (($_POST['tableid'] ?? '') == $t['id']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($t['tablenumber'] . ' - ' . $t['location'] . ' (' . $t['capacity'] . ' seats)') ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="form-group">
        <label>Date</label>
        <input type="date" name="reservationdate"
               value="<?= htmlspecialchars($_POST['reservationdate'] ?? '') ?>"
               min="<?= date('Y-m-d') ?>" required>
    </div>

    <div class="form-group">
        <label>Time</label>
        <select name="reservationtime" required>
            <option value="">-- Select a time --</option>
            <?php
            // Available time slots
            $creneaux = ['12:00','12:30','13:00','13:30','14:00','18:00','18:30','19:00','19:30','20:00','20:30','21:00'];
            foreach ($creneaux as $c):
                $val = $c . ':00';
            ?>
                <option value="<?= $val ?>" <?= (($_POST['reservationtime'] ?? '') === $val) ? 'selected' : '' ?>>
                    <?= $c ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="form-group">
        <label>Number of Guests</label>
        <input type="number" name="partysize" min="1" max="20"
               value="<?= htmlspecialchars($_POST['partysize'] ?? '2') ?>" required>
    </div>

    <div class="form-group">
        <label>Notes / Special Requests</label>
        <textarea name="notes" placeholder="Allergies, special occasion..."><?= htmlspecialchars($_POST['notes'] ?? '') ?></textarea>
    </div>

    <button type="submit" class="btn btn-primary">Reserve</button>
    <a href="/restaurantreservation/restaurantreservation/user/dashboard.php" class="btn btn-warning">Cancel</a>
</form>

<?php include __DIR__ . '/../includes/footer.php'; ?>
