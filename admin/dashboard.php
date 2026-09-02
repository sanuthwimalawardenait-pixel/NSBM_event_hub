<?php
$pageTitle = 'Dashboard';
require_once __DIR__ . '/header.php';
$pdo = getDbConnection();

$totalEvents = $pdo->query("SELECT COUNT(*) FROM events")->fetchColumn();
$totalRegs = $pdo->query("SELECT COUNT(*) FROM registrations WHERE status != 'cancelled'")->fetchColumn();
$totalStudents = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'student'")->fetchColumn();
$totalCategories = $pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn();
$totalAnnouncements = $pdo->query("SELECT COUNT(*) FROM announcements")->fetchColumn();

$recentRegsStmt = $pdo->query("
    SELECT r.*, u.full_name, u.student_id, u.email, e.title as event_title, e.event_date
    FROM registrations r
    JOIN users u ON r.user_id = u.id
    JOIN events e ON r.event_id = e.id
    ORDER BY r.registration_date DESC
    LIMIT 6
");
$recentRegs = $recentRegsStmt->fetchAll();

$upcomingEventsStmt = $pdo->query("
    SELECT e.*, c.name as category_name,
           (SELECT COUNT(*) FROM registrations r WHERE r.event_id = e.id AND r.status != 'cancelled') as registered_count
    FROM events e
    JOIN categories c ON e.category_id = c.id
    WHERE e.status = 'upcoming' AND e.event_date >= CURDATE()
    ORDER BY e.event_date ASC
    LIMIT 5
");
$upcomingEvents = $upcomingEventsStmt->fetchAll();
?>

<div class="section-header">
    <div>
        <h1 class="section-title"><i class="bi bi-speedometer2"></i> Administrative Overview</h1>
        <p class="section-subtitle">Manage campus events, approve club activities, monitor attendee registrations and post announcements</p>
    </div>
    <div style="display: flex; gap: 0.75rem;">
        <a href="<?php echo $baseUrl; ?>admin/event_add.php" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> Create New Event
        </a>
        <a href="<?php echo $baseUrl; ?>admin/export_participants.php" class="btn btn-secondary">
            <i class="bi bi-file-earmark-spreadsheet"></i> Export Roster
        </a>
    </div>
</div>

<div class="stats-grid" style="margin-top: 0; margin-bottom: 2rem;">
    <div class="stat-card">
        <div class="stat-icon green"><i class="bi bi-calendar-event"></i></div>
        <div>
            <div class="stat-number"><?php echo $totalEvents; ?></div>
            <div class="stat-label">Total Events</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon gold"><i class="bi bi-ticket-perforated"></i></div>
        <div>
            <div class="stat-number"><?php echo $totalRegs; ?></div>
            <div class="stat-label">Active Registrations</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon blue"><i class="bi bi-people"></i></div>
        <div>
            <div class="stat-number"><?php echo $totalStudents; ?></div>
            <div class="stat-label">Registered Students</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon purple"><i class="bi bi-megaphone"></i></div>
        <div>
            <div class="stat-number"><?php echo $totalAnnouncements; ?></div>
            <div class="stat-label">Notices Published</div>
        </div>
    </div>
</div>

<div style="display: grid; grid-template-columns: 2fr 1.2fr; gap: 1.75rem; align-items: start;">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><i class="bi bi-clock-history"></i> Recent Registrations</h3>
            <a href="<?php echo $baseUrl; ?>admin/registrations.php" class="btn btn-secondary btn-sm">View All</a>
        </div>
        <div class="card-body" style="padding: 0;">
            <?php if (empty($recentRegs)): ?>
                <div style="padding: 2rem; text-align: center; color: var(--text-muted);">
                    No registrations recorded yet.
                </div>
            <?php else: ?>
                <div class="table-responsive" style="border: none; box-shadow: none;">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Student</th>
                                <th>Event</th>
                                <th>Pass Code</th>
                                <th>Status</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentRegs as $reg): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($reg['full_name']); ?></strong>
                                        <div style="font-size: 0.775rem; color: var(--text-muted);"><?php echo htmlspecialchars($reg['student_id'] ?: $reg['email']); ?></div>
                                    </td>
                                    <td>
                                        <span style="font-weight: 600;"><?php echo htmlspecialchars($reg['event_title']); ?></span>
                                    </td>
                                    <td><code><?php echo htmlspecialchars($reg['ticket_code']); ?></code></td>
                                    <td>
                                        <span class="badge <?php echo getStatusBadgeClass($reg['status']); ?>">
                                            <?php echo ucfirst($reg['status']); ?>
                                        </span>
                                    </td>
                                    <td style="font-size: 0.8rem; color: var(--text-muted);">
                                        <?php echo formatDate($reg['registration_date']); ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><i class="bi bi-calendar-check"></i> Upcoming Campus Events</h3>
            <a href="<?php echo $baseUrl; ?>admin/events.php" class="btn btn-secondary btn-sm">Manage</a>
        </div>
        <div class="card-body">
            <?php if (empty($upcomingEvents)): ?>
                <p style="color: var(--text-muted); text-align: center;">No upcoming events scheduled.</p>
            <?php else: ?>
                <div style="display: flex; flex-direction: column; gap: 1.25rem;">
                    <?php foreach ($upcomingEvents as $ue): 
                        $fillPct = min(100, round(($ue['registered_count'] / $ue['capacity']) * 100));
                    ?>
                        <div style="border-bottom: 1px solid var(--border-color); padding-bottom: 1rem;">
                            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.25rem;">
                                <h4 style="font-size: 0.95rem; font-weight: 700; color: var(--secondary);">
                                    <a href="<?php echo $baseUrl; ?>admin/event_edit.php?id=<?php echo $ue['id']; ?>"><?php echo htmlspecialchars($ue['title']); ?></a>
                                </h4>
                                <span class="badge badge-normal" style="font-size: 0.7rem;"><?php echo formatDate($ue['event_date']); ?></span>
                            </div>
                            <div style="font-size: 0.8rem; color: var(--text-muted); margin-bottom: 0.4rem;">
                                <?php echo htmlspecialchars($ue['club_name']); ?> &bull; <?php echo htmlspecialchars($ue['venue']); ?>
                            </div>
                            <div style="display: flex; justify-content: space-between; font-size: 0.75rem; color: var(--text-muted); margin-bottom: 0.25rem;">
                                <span>Attendance: <?php echo $ue['registered_count']; ?> / <?php echo $ue['capacity']; ?></span>
                                <span><?php echo $fillPct; ?>%</span>
                            </div>
                            <div class="progress-bar-bg" style="height: 4px;">
                                <div class="progress-bar-fill <?php echo $fillPct >= 90 ? 'danger' : ($fillPct >= 70 ? 'warning' : ''); ?>" style="width: <?php echo $fillPct; ?>%;"></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="card" style="margin-top: 1.75rem;">
    <div class="card-header">
        <h3 class="card-title"><i class="bi bi-lightning-charge-fill" style="color: var(--accent);"></i> Quick Administrative Shortcuts</h3>
    </div>
    <div class="card-body">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
            <a href="<?php echo $baseUrl; ?>admin/event_add.php" class="btn btn-secondary" style="padding: 1rem; text-align: left; justify-content: flex-start;">
                <i class="bi bi-calendar-plus-fill" style="color: var(--primary); font-size: 1.25rem;"></i>
                <div>
                    <strong>Publish Event</strong>
                    <div style="font-size: 0.75rem; color: var(--text-muted); font-weight: normal;">Create club workshop or summit</div>
                </div>
            </a>
            <a href="<?php echo $baseUrl; ?>admin/categories.php" class="btn btn-secondary" style="padding: 1rem; text-align: left; justify-content: flex-start;">
                <i class="bi bi-tags-fill" style="color: var(--info); font-size: 1.25rem;"></i>
                <div>
                    <strong>Event Categories</strong>
                    <div style="font-size: 0.75rem; color: var(--text-muted); font-weight: normal;">Manage faculty tags & icons</div>
                </div>
            </a>
            <a href="<?php echo $baseUrl; ?>admin/announcements.php" class="btn btn-secondary" style="padding: 1rem; text-align: left; justify-content: flex-start;">
                <i class="bi bi-megaphone-fill" style="color: var(--warning); font-size: 1.25rem;"></i>
                <div>
                    <strong>Post Announcement</strong>
                    <div style="font-size: 0.75rem; color: var(--text-muted); font-weight: normal;">Send campus-wide notice</div>
                </div>
            </a>
            <a href="<?php echo $baseUrl; ?>admin/export_participants.php" class="btn btn-secondary" style="padding: 1rem; text-align: left; justify-content: flex-start;">
                <i class="bi bi-file-earmark-arrow-down-fill" style="color: var(--success); font-size: 1.25rem;"></i>
                <div>
                    <strong>Export Participants</strong>
                    <div style="font-size: 0.75rem; color: var(--text-muted); font-weight: normal;">Generate CSV & print rosters</div>
                </div>
            </a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
