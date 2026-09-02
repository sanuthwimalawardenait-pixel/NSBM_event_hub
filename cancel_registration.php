<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth_check.php';

requireStudent();

$regId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($regId <= 0) {
    setFlashMessage('danger', 'Invalid registration ID.');
    header('Location: ' . getBaseUrl() . 'schedule.php');
    exit;
}

$pdo = getDbConnection();
$currentUser = getCurrentUser();

$stmt = $pdo->prepare("SELECT * FROM registrations WHERE id = ? AND user_id = ?");
$stmt->execute([$regId, $currentUser['id']]);
$reg = $stmt->fetch();

if (!$reg) {
    setFlashMessage('danger', 'Registration record not found or permission denied.');
    header('Location: ' . getBaseUrl() . 'schedule.php');
    exit;
}

$updateStmt = $pdo->prepare("UPDATE registrations SET status = 'cancelled' WHERE id = ?");
$updateStmt->execute([$regId]);

setFlashMessage('info', 'Your event registration has been successfully cancelled.');
header('Location: ' . getBaseUrl() . 'schedule.php');
exit;
