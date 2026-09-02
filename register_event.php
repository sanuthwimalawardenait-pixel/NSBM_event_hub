<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth_check.php';

requireStudent();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . getBaseUrl() . 'events.php');
    exit;
}

$pdo = getDbConnection();
$currentUser = getCurrentUser();
$userId = $currentUser['id'];
$eventId = isset($_POST['event_id']) ? (int)$_POST['event_id'] : 0;
$specialReqs = isset($_POST['special_requirements']) ? trim($_POST['special_requirements']) : '';

if ($eventId <= 0) {
    setFlashMessage('danger', 'Invalid event selected.');
    header('Location: ' . getBaseUrl() . 'events.php');
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM events WHERE id = ?");
$stmt->execute([$eventId]);
$event = $stmt->fetch();

if (!$event) {
    setFlashMessage('danger', 'Event not found.');
    header('Location: ' . getBaseUrl() . 'events.php');
    exit;
}

if ($event['status'] === 'cancelled') {
    setFlashMessage('danger', 'Registration failed. This event has been cancelled.');
    header('Location: ' . getBaseUrl() . 'event_details.php?id=' . $eventId);
    exit;
}

if (strtotime($event['event_date']) < strtotime(date('Y-m-d'))) {
    setFlashMessage('warning', 'Registration is closed as this event date has passed.');
    header('Location: ' . getBaseUrl() . 'event_details.php?id=' . $eventId);
    exit;
}

$regCountStmt = $pdo->prepare("SELECT COUNT(*) FROM registrations WHERE event_id = ? AND status != 'cancelled'");
$regCountStmt->execute([$eventId]);
$currentCount = $regCountStmt->fetchColumn();

if ($currentCount >= $event['capacity']) {
    setFlashMessage('danger', 'Registration failed. The event has reached full capacity.');
    header('Location: ' . getBaseUrl() . 'event_details.php?id=' . $eventId);
    exit;
}

$existingCheck = $pdo->prepare("SELECT * FROM registrations WHERE event_id = ? AND user_id = ?");
$existingCheck->execute([$eventId, $userId]);
$existingReg = $existingCheck->fetch();

if ($existingReg) {
    if ($existingReg['status'] === 'cancelled') {
        $updateStmt = $pdo->prepare("UPDATE registrations SET status = 'registered', special_requirements = ?, registration_date = NOW() WHERE id = ?");
        $updateStmt->execute([$specialReqs, $existingReg['id']]);
        setFlashMessage('success', 'Your registration has been reactivated successfully!');
        header('Location: ' . getBaseUrl() . 'schedule.php');
        exit;
    } else {
        setFlashMessage('info', 'You are already registered for this event.');
        header('Location: ' . getBaseUrl() . 'event_details.php?id=' . $eventId);
        exit;
    }
}

$ticketCode = generateTicketCode($eventId, $userId);

$insertStmt = $pdo->prepare("
    INSERT INTO registrations (event_id, user_id, ticket_code, status, special_requirements)
    VALUES (?, ?, ?, 'registered', ?)
");
$insertStmt->execute([$eventId, $userId, $ticketCode, $specialReqs]);

setFlashMessage('success', 'Congratulations! You have successfully registered for ' . htmlspecialchars($event['title']) . '. Your pass is ready.');
header('Location: ' . getBaseUrl() . 'schedule.php');
exit;
