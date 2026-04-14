<?php
// Checks if a table is available at a given date and time
require_once __DIR__ . '/../config/db.php';
requireLogin();

header('Content-Type: application/json');

$tableid = (int)($_GET['tableid'] ?? 0);
$date    = $_GET['date'] ?? '';
$time    = $_GET['time'] ?? '';

$db   = getDB();
$stmt = $db->prepare("SELECT id FROM reservations WHERE tableid=? AND reservationdate=? AND reservationtime=?");
$stmt->execute([$tableid, $date, $time]);

// Returns true if available, false if already booked
echo json_encode(['available' => !$stmt->fetch()]);
