<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth_check.php';

requireAdmin();

$eventId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($eventId <= 0) {
    setFlashMessage('danger', 'Invalid event ID.');
    header('Location: ' . getBaseUrl() . 'admin/events.php');
    exit;
}

$pdo = getDbConnection();
$stmt = $pdo->prepare("SELECT title FROM events WHERE id = ?");
$stmt->execute([$eventId]);
$event = $stmt->fetch();

if (!$event) {
    setFlashMessage('danger', 'Event record not found.');
    header('Location: ' . getBaseUrl() . 'admin/events.php');
    exit;
}

$deleteStmt = $pdo->prepare("DELETE FROM events WHERE id = ?");
$deleteStmt->execute([$eventId]);

setFlashMessage('info', 'Event "' . htmlspecialchars($event['title']) . '" and its associated registrations have been permanently deleted.');
header('Location: ' . getBaseUrl() . 'admin/events.php');
exit;
