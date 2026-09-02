<?php
$pageTitle = 'Event Registrations';
require_once __DIR__ . '/header.php';
$pdo = getDbConnection();

$selectedEventId = isset($_GET['event_id']) ? (int)$_GET['event_id'] : 0;
$statusFilter = isset($_GET['status']) ? trim($_GET['status']) : '';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

$updateStatusId = isset($_GET['update_id']) ? (int)$_GET['update_id'] : 0;
$newStatus = isset($_GET['new_status']) ? trim($_GET['new_status']) : '';

if ($updateStatusId > 0 && in_array($newStatus, ['registered', 'attended', 'cancelled'])) {
    $updateStmt = $pdo->prepare("UPDATE registrations SET status = ? WHERE id = ?");
    $updateStmt->execute([$newStatus, $updateStatusId]);
    setFlashMessage('success', 'Registration status updated to "' . ucfirst($newStatus) . '".');
    
    $redirectUrl = $baseUrl . 'admin/registrations.php';
    $params = [];
    if ($selectedEventId > 0) $params[] = "event_id={$selectedEventId}";
    if (!empty($statusFilter)) $params[] = "status={$statusFilter}";
    if (!empty($search)) $params[] = "search=" . urlencode($search);
    if (!empty($params)) $redirectUrl .= '?' . implode('&', $params);

    header('Location: ' . $redirectUrl);
    exit;
}

$events = $pdo->query("SELECT id, title, event_date FROM events ORDER BY event_date DESC")->fetchAll();

$query = "
    SELECT r.*, u.full_name, u.student_id, u.email, u.faculty, u.phone,
           e.title as event_title, e.event_date, e.venue, e.club_name
    FROM registrations r
    JOIN users u ON r.user_id = u.id
    JOIN events e ON r.event_id = e.id
    WHERE 1=1
";
$params = [];

if ($selectedEventId > 0) {
    $query .= " AND r.event_id = ?";
    $params[] = $selectedEventId;
}

if (!empty($statusFilter)) {
    $query .= " AND r.status = ?";
    $params[] = $statusFilter;
}

if (!empty($search)) {
    $query .= " AND (u.full_name LIKE ? OR u.student_id LIKE ? OR u.email LIKE ? OR r.ticket_code LIKE ?)";
    $term = "%{$search}%";
    $params[] = $term;
    $params[] = $term;
    $params[] = $term;
    $params[] = $term;
}

$query .= " ORDER BY r.registration_date DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$registrations = $stmt->fetchAll();
?>

<div class="section-header">
    <div>
        <h1 class="section-title"><i class="bi bi-people-fill"></i> Event Attendee Registrations</h1>
        <p class="section-subtitle">Monitor ticket bookings, verify student passes, and track on-site attendance</p>
    </div>
    <div style="display: flex; gap: 0.75rem;">
        <a href="<?php echo $baseUrl; ?>admin/export_participants.php<?php echo $selectedEventId > 0 ? '?event_id=' . $selectedEventId : ''; ?>" class="btn btn-primary">
            <i class="bi bi-file-earmark-spreadsheet"></i> Export Participant List
        </a>
    </div>
</div>

<div class="card" style="margin-bottom: 1.5rem;">
    <div class="card-body">
        <form action="<?php echo $baseUrl; ?>admin/registrations.php" method="GET" style="display: flex; gap: 1rem; flex-wrap: wrap;">
            <div style="flex: 2; min-width: 200px;">
                <input type="text" name="search" class="form-control" placeholder="Search student name, ID, email or ticket code..." value="<?php echo htmlspecialchars($search); ?>">
            </div>
            <div style="flex: 2; min-width: 220px;">
                <select name="event_id" class="form-control">
                    <option value="">All Events</option>
                    <?php foreach ($events as $ev): ?>
                        <option value="<?php echo $ev['id']; ?>" <?php echo $selectedEventId == $ev['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($ev['title']); ?> (<?php echo formatDate($ev['event_date']); ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div style="flex: 1; min-width: 140px;">
                <select name="status" class="form-control">
                    <option value="">All Statuses</option>
                    <option value="registered" <?php echo $statusFilter === 'registered' ? 'selected' : ''; ?>>Registered</option>
                    <option value="attended" <?php echo $statusFilter === 'attended' ? 'selected' : ''; ?>>Attended</option>
                    <option value="cancelled" <?php echo $statusFilter === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-funnel"></i> Filter
            </button>
            <?php if ($selectedEventId > 0 || !empty($statusFilter) || !empty($search)): ?>
                <a href="<?php echo $baseUrl; ?>admin/registrations.php" class="btn btn-secondary">
                    <i class="bi bi-arrow-counterclockwise"></i> Reset
                </a>
            <?php endif; ?>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">
            <i class="bi bi-ticket-detailed"></i> Registration Records (<?php echo count($registrations); ?>)
        </h3>
    </div>
    <div class="card-body" style="padding: 0;">
        <?php if (empty($registrations)): ?>
            <div style="text-align: center; padding: 3rem 1.5rem; color: var(--text-muted);">
                <i class="bi bi-person-x" style="font-size: 2.5rem; display: block; margin-bottom: 0.75rem;"></i>
                <h4>No Registrations Found</h4>
                <p>No attendee records matched your search parameters.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive" style="border: none; box-shadow: none;">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Ticket Pass</th>
                            <th>Student Information</th>
                            <th>Faculty</th>
                            <th>Event Details</th>
                            <th>Booked On</th>
                            <th>Attendance Status</th>
                            <th style="text-align: right;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($registrations as $r): ?>
                            <tr>
                                <td>
                                    <div class="ticket-code" style="font-size: 0.8rem; padding: 0.25rem 0.5rem;">
                                        <?php echo htmlspecialchars($r['ticket_code']); ?>
                                    </div>
                                    <?php if (!empty($r['special_requirements'])): ?>
                                        <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.3rem; max-width: 160px;" title="<?php echo htmlspecialchars($r['special_requirements']); ?>">
                                            <i class="bi bi-chat-left-text"></i> <?php echo htmlspecialchars($r['special_requirements']); ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <strong><?php echo htmlspecialchars($r['full_name']); ?></strong>
                                    <div style="font-size: 0.775rem; color: var(--text-muted);">
                                        <code><?php echo htmlspecialchars($r['student_id'] ?: 'N/A'); ?></code> &bull; <?php echo htmlspecialchars($r['email']); ?>
                                    </div>
                                    <?php if (!empty($r['phone'])): ?>
                                        <div style="font-size: 0.75rem; color: var(--text-light);"><i class="bi bi-telephone"></i> <?php echo htmlspecialchars($r['phone']); ?></div>
                                    <?php endif; ?>
                                </td>
                                <td style="font-size: 0.85rem;">
                                    <?php echo htmlspecialchars($r['faculty'] ?: 'General'); ?>
                                </td>
                                <td>
                                    <div style="font-weight: 600; font-size: 0.9rem; color: var(--secondary);">
                                        <?php echo htmlspecialchars($r['event_title']); ?>
                                    </div>
                                    <div style="font-size: 0.775rem; color: var(--text-muted);">
                                        <i class="bi bi-calendar-event"></i> <?php echo formatDate($r['event_date']); ?> &bull; <?php echo htmlspecialchars($r['venue']); ?>
                                    </div>
                                </td>
                                <td style="font-size: 0.8rem; color: var(--text-muted); white-space: nowrap;">
                                    <?php echo formatDate($r['registration_date']); ?>
                                </td>
                                <td>
                                    <span class="badge <?php echo getStatusBadgeClass($r['status']); ?>">
                                        <?php echo ucfirst($r['status']); ?>
                                    </span>
                                </td>
                                <td style="text-align: right; white-space: nowrap;">
                                    <?php if ($r['status'] !== 'attended'): ?>
                                        <a href="<?php echo $baseUrl; ?>admin/registrations.php?update_id=<?php echo $r['id']; ?>&new_status=attended<?php echo $selectedEventId > 0 ? '&event_id=' . $selectedEventId : ''; ?>" class="btn btn-secondary btn-sm" title="Mark Attended">
                                            <i class="bi bi-check-circle" style="color: var(--success);"></i> Attended
                                        </a>
                                    <?php else: ?>
                                        <a href="<?php echo $baseUrl; ?>admin/registrations.php?update_id=<?php echo $r['id']; ?>&new_status=registered<?php echo $selectedEventId > 0 ? '&event_id=' . $selectedEventId : ''; ?>" class="btn btn-secondary btn-sm" title="Mark Registered">
                                            <i class="bi bi-arrow-counterclockwise"></i> Reset
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
