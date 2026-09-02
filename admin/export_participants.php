<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth_check.php';

requireAdmin();

$pdo = getDbConnection();
$selectedEventId = isset($_GET['event_id']) ? (int)$_GET['event_id'] : 0;
$format = isset($_GET['format']) ? trim($_GET['format']) : '';

$events = $pdo->query("SELECT id, title, event_date, venue, club_name, capacity FROM events ORDER BY event_date DESC")->fetchAll();

$query = "
    SELECT r.*, u.full_name, u.student_id, u.email, u.faculty, u.phone,
           e.title as event_title, e.club_name, e.venue, e.event_date, e.start_time, e.end_time
    FROM registrations r
    JOIN users u ON r.user_id = u.id
    JOIN events e ON r.event_id = e.id
    WHERE r.status != 'cancelled'
";
$params = [];

if ($selectedEventId > 0) {
    $query .= " AND r.event_id = ?";
    $params[] = $selectedEventId;
}

$query .= " ORDER BY e.event_date ASC, r.registration_date ASC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$participants = $stmt->fetchAll();

if ($format === 'csv') {
    $filename = "NSBM_Event_Participants_" . ($selectedEventId > 0 ? "Event_{$selectedEventId}_" : "All_") . date('Ymd_His') . ".csv";
    
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    $output = fopen('php://output', 'w');
    fputcsv($output, [
        'Registration ID',
        'Ticket Code',
        'Student Name',
        'Student ID',
        'Email Address',
        'Faculty',
        'Phone Number',
        'Event Title',
        'Organizing Club',
        'Event Venue',
        'Event Date',
        'Start Time',
        'Attendance Status',
        'Registration Date',
        'Special Requirements'
    ]);

    foreach ($participants as $p) {
        fputcsv($output, [
            $p['id'],
            $p['ticket_code'],
            $p['full_name'],
            $p['student_id'] ?: 'N/A',
            $p['email'],
            $p['faculty'] ?: 'General',
            $p['phone'] ?: 'N/A',
            $p['event_title'],
            $p['club_name'],
            $p['venue'],
            $p['event_date'],
            $p['start_time'],
            ucfirst($p['status']),
            $p['registration_date'],
            $p['special_requirements'] ?: 'None'
        ]);
    }
    fclose($output);
    exit;
}

$pageTitle = 'Generate Participant Lists';
require_once __DIR__ . '/header.php';

$activeEvent = null;
if ($selectedEventId > 0) {
    foreach ($events as $ev) {
        if ($ev['id'] == $selectedEventId) {
            $activeEvent = $ev;
            break;
        }
    }
}
?>

<div class="section-header no-print">
    <div>
        <h1 class="section-title"><i class="bi bi-file-earmark-spreadsheet"></i> Participant Attendance Lists</h1>
        <p class="section-subtitle">Generate official participant rosters, download CSV spreadsheets, or print check-in roll sheets</p>
    </div>
    <div style="display: flex; gap: 0.75rem;">
        <button type="button" class="btn btn-secondary btn-print">
            <i class="bi bi-printer"></i> Print Roster Sheet
        </button>
        <a href="<?php echo $baseUrl; ?>admin/export_participants.php?format=csv<?php echo $selectedEventId > 0 ? '&event_id=' . $selectedEventId : ''; ?>" class="btn btn-primary">
            <i class="bi bi-file-earmark-arrow-down-fill"></i> Download CSV File
        </a>
    </div>
</div>

<div class="card no-print" style="margin-bottom: 1.5rem;">
    <div class="card-body">
        <form action="<?php echo $baseUrl; ?>admin/export_participants.php" method="GET" style="display: flex; gap: 1rem; align-items: flex-end; flex-wrap: wrap;">
            <div style="flex: 2; min-width: 250px;">
                <label class="form-label" for="export_event_select"><i class="bi bi-calendar-event"></i> Select Target Event for Participant Roster</label>
                <select name="event_id" id="export_event_select" class="form-control">
                    <option value="">-- All University Events (Consolidated) --</option>
                    <?php foreach ($events as $ev): ?>
                        <option value="<?php echo $ev['id']; ?>" <?php echo $selectedEventId == $ev['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($ev['title']); ?> (<?php echo formatDate($ev['event_date']); ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div style="display: flex; gap: 0.5rem;">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-funnel"></i> View Roster
                </button>
                <?php if ($selectedEventId > 0): ?>
                    <a href="<?php echo $baseUrl; ?>admin/export_participants.php" class="btn btn-secondary">
                        <i class="bi bi-arrow-counterclockwise"></i> Show All
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header" style="background-color: #f8fafc;">
        <div>
            <h3 class="card-title" style="margin-bottom: 0.2rem;">
                <?php echo $activeEvent ? htmlspecialchars($activeEvent['title']) : 'Consolidated Campus Participant Roster'; ?>
            </h3>
            <div style="font-size: 0.85rem; color: var(--text-muted);">
                <?php if ($activeEvent): ?>
                    <span><strong>Club:</strong> <?php echo htmlspecialchars($activeEvent['club_name']); ?></span> &bull; 
                    <span><strong>Venue:</strong> <?php echo htmlspecialchars($activeEvent['venue']); ?></span> &bull; 
                    <span><strong>Date:</strong> <?php echo formatDate($activeEvent['event_date']); ?></span>
                <?php else: ?>
                    <span>All active registered participants across scheduled events</span>
                <?php endif; ?>
            </div>
        </div>
        <div>
            <span class="badge badge-registered" style="font-size: 0.85rem; padding: 0.4rem 0.8rem;">
                Total: <?php echo count($participants); ?> Attendees
            </span>
        </div>
    </div>
    <div class="card-body" style="padding: 0;">
        <?php if (empty($participants)): ?>
            <div style="text-align: center; padding: 3rem 1.5rem; color: var(--text-muted);">
                <i class="bi bi-people" style="font-size: 2.5rem; display: block; margin-bottom: 0.75rem;"></i>
                <h4>No Registered Participants</h4>
                <p>No students have registered for this selection yet.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive" style="border: none; box-shadow: none;">
                <table class="table">
                    <thead>
                        <tr>
                            <th style="width: 50px;">#</th>
                            <th>Ticket Pass</th>
                            <th>Student Name</th>
                            <th>Student ID</th>
                            <th>Email</th>
                            <th>Faculty</th>
                            <th>Event</th>
                            <th>Status</th>
                            <th class="print-only">Signature</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $idx = 1; foreach ($participants as $p): ?>
                            <tr>
                                <td><?php echo $idx++; ?></td>
                                <td><code><?php echo htmlspecialchars($p['ticket_code']); ?></code></td>
                                <td><strong><?php echo htmlspecialchars($p['full_name']); ?></strong></td>
                                <td><code><?php echo htmlspecialchars($p['student_id'] ?: 'N/A'); ?></code></td>
                                <td style="font-size: 0.85rem;"><?php echo htmlspecialchars($p['email']); ?></td>
                                <td style="font-size: 0.85rem;"><?php echo htmlspecialchars($p['faculty'] ?: 'General'); ?></td>
                                <td style="font-size: 0.85rem;">
                                    <?php echo htmlspecialchars($p['event_title']); ?>
                                </td>
                                <td>
                                    <span class="badge <?php echo getStatusBadgeClass($p['status']); ?>">
                                        <?php echo ucfirst($p['status']); ?>
                                    </span>
                                </td>
                                <td class="print-only" style="border-bottom: 1px solid #999; width: 120px;"></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
