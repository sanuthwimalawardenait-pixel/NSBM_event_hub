<?php
$pageTitle = 'My Event Schedule';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth_check.php';

requireStudent();

$pdo = getDbConnection();
$currentUser = getCurrentUser();
$userId = $currentUser['id'];

$upcomingStmt = $pdo->prepare("
    SELECT r.*, e.title, e.club_name, e.description, e.venue, e.event_date, e.start_time, e.end_time, e.status as event_status,
           c.name as category_name, c.color_code as category_color, c.icon as category_icon
    FROM registrations r
    JOIN events e ON r.event_id = e.id
    JOIN categories c ON e.category_id = c.id
    WHERE r.user_id = ? AND r.status != 'cancelled' AND e.event_date >= CURDATE()
    ORDER BY e.event_date ASC, e.start_time ASC
");
$upcomingStmt->execute([$userId]);
$upcomingSchedules = $upcomingStmt->fetchAll();

$pastStmt = $pdo->prepare("
    SELECT r.*, e.title, e.club_name, e.description, e.venue, e.event_date, e.start_time, e.end_time, e.status as event_status,
           c.name as category_name, c.color_code as category_color
    FROM registrations r
    JOIN events e ON r.event_id = e.id
    JOIN categories c ON e.category_id = c.id
    WHERE r.user_id = ? AND (r.status = 'cancelled' OR e.event_date < CURDATE())
    ORDER BY e.event_date DESC, e.start_time DESC
");
$pastStmt->execute([$userId]);
$pastSchedules = $pastStmt->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>

<div class="container" style="padding-top: 2rem;">
    <div class="section-header">
        <div>
            <h1 class="section-title"><i class="bi bi-calendar-check"></i> My Personal Event Schedule</h1>
            <p class="section-subtitle">Track your upcoming university sessions, workshops, hackathons, and download event passes</p>
        </div>
        <div style="display: flex; gap: 0.75rem;">
            <button type="button" class="btn btn-secondary btn-print">
                <i class="bi bi-printer"></i> Print Schedule
            </button>
            <a href="<?php echo $baseUrl; ?>events.php" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Browse More Events
            </a>
        </div>
    </div>

    <div style="display: flex; gap: 1rem; margin-bottom: 2rem; border-bottom: 2px solid var(--border-color); padding-bottom: 0.5rem;">
        <button type="button" id="tabUpcomingBtn" class="btn btn-primary btn-sm" onclick="switchScheduleTab('upcoming')">
            <i class="bi bi-calendar-event"></i> Upcoming Registrations (<?php echo count($upcomingSchedules); ?>)
        </button>
        <button type="button" id="tabPastBtn" class="btn btn-secondary btn-sm" onclick="switchScheduleTab('past')">
            <i class="bi bi-clock-history"></i> History & Past Events (<?php echo count($pastSchedules); ?>)
        </button>
    </div>

    <div id="upcomingSection">
        <?php if (empty($upcomingSchedules)): ?>
            <div class="card" style="text-align: center; padding: 4rem 1.5rem;">
                <div style="width: 70px; height: 70px; border-radius: 50%; background-color: var(--primary-subtle); color: var(--primary); display: flex; align-items: center; justify-content: center; font-size: 2rem; margin: 0 auto 1.5rem;">
                    <i class="bi bi-calendar-plus"></i>
                </div>
                <h3>Your Schedule is Currently Empty</h3>
                <p style="color: var(--text-muted); max-width: 460px; margin: 0.5rem auto 1.75rem;">
                    You have not registered for any upcoming events. Explore upcoming university clubs, competitions, and guest sessions to start building your personal schedule.
                </p>
                <a href="<?php echo $baseUrl; ?>events.php" class="btn btn-primary btn-lg">
                    <i class="bi bi-search"></i> Explore Upcoming Events
                </a>
            </div>
        <?php else: ?>
            <div class="timeline">
                <?php foreach ($upcomingSchedules as $item): 
                    $evtDateObj = new DateTime($item['event_date']);
                    $daysUntil = (int)(new DateTime())->diff($evtDateObj)->format('%r%a');
                ?>
                    <div class="timeline-item">
                        <div class="timeline-marker"></div>
                        <div class="timeline-card">
                            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1rem; flex-wrap: wrap; gap: 0.75rem;">
                                <div>
                                    <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.35rem;">
                                        <span class="badge" style="background: <?php echo htmlspecialchars($item['category_color']); ?>; color: #fff;">
                                            <?php echo htmlspecialchars($item['category_name']); ?>
                                        </span>
                                        <span class="badge badge-registered">Registered</span>
                                        <?php if ($daysUntil === 0): ?>
                                            <span class="badge badge-urgent">Happening Today</span>
                                        <?php elseif ($daysUntil === 1): ?>
                                            <span class="badge badge-important">Tomorrow</span>
                                        <?php elseif ($daysUntil > 1): ?>
                                            <span class="badge badge-normal">In <?php echo $daysUntil; ?> days</span>
                                        <?php endif; ?>
                                    </div>
                                    <h3 style="font-size: 1.35rem; font-weight: 700; color: var(--secondary);">
                                        <a href="<?php echo $baseUrl; ?>event_details.php?id=<?php echo $item['event_id']; ?>"><?php echo htmlspecialchars($item['title']); ?></a>
                                    </h3>
                                    <div style="font-size: 0.9rem; color: var(--text-muted);">
                                        <i class="bi bi-people"></i> Organized by <?php echo htmlspecialchars($item['club_name']); ?>
                                    </div>
                                </div>

                                <div style="text-align: right;">
                                    <div class="ticket-code" style="font-size: 0.95rem;"><?php echo htmlspecialchars($item['ticket_code']); ?></div>
                                    <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.25rem;">Pass Code</div>
                                </div>
                            </div>

                            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; background-color: #f8fafc; border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 1rem; margin-bottom: 1.25rem; font-size: 0.9rem;">
                                <div>
                                    <span style="color: var(--text-muted); display: block; font-size: 0.8rem;"><i class="bi bi-calendar-event"></i> Date</span>
                                    <strong><?php echo formatDate($item['event_date']); ?></strong>
                                </div>
                                <div>
                                    <span style="color: var(--text-muted); display: block; font-size: 0.8rem;"><i class="bi bi-clock"></i> Time Window</span>
                                    <strong><?php echo formatTime($item['start_time']); ?> - <?php echo formatTime($item['end_time']); ?></strong>
                                </div>
                                <div>
                                    <span style="color: var(--text-muted); display: block; font-size: 0.8rem;"><i class="bi bi-geo-alt"></i> Venue</span>
                                    <strong><?php echo htmlspecialchars($item['venue']); ?></strong>
                                </div>
                            </div>

                            <?php if (!empty($item['special_requirements'])): ?>
                                <div style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 1rem;">
                                    <strong>Your Notes:</strong> <?php echo htmlspecialchars($item['special_requirements']); ?>
                                </div>
                            <?php endif; ?>

                            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.75rem; border-top: 1px solid var(--border-color); padding-top: 1rem;">
                                <div style="font-size: 0.8rem; color: var(--text-light);">
                                    Registered on <?php echo formatDate($item['registration_date']); ?>
                                </div>
                                <div style="display: flex; gap: 0.5rem;">
                                    <a href="<?php echo $baseUrl; ?>event_details.php?id=<?php echo $item['event_id']; ?>" class="btn btn-secondary btn-sm">
                                        <i class="bi bi-box-arrow-up-right"></i> Event Details
                                    </a>
                                    <a href="<?php echo $baseUrl; ?>cancel_registration.php?id=<?php echo $item['id']; ?>" class="btn btn-outline-danger btn-sm" onclick="return confirm('Are you sure you want to cancel your registration for this event?');">
                                        <i class="bi bi-x-circle"></i> Cancel Registration
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <div id="pastSection" style="display: none;">
        <?php if (empty($pastSchedules)): ?>
            <div class="card" style="text-align: center; padding: 3rem 1.5rem;">
                <p style="color: var(--text-muted);">No past event history recorded.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Ticket ID</th>
                            <th>Event Title</th>
                            <th>Category</th>
                            <th>Date & Time</th>
                            <th>Venue</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pastSchedules as $item): ?>
                            <tr>
                                <td><code><?php echo htmlspecialchars($item['ticket_code']); ?></code></td>
                                <td>
                                    <strong><?php echo htmlspecialchars($item['title']); ?></strong>
                                    <div style="font-size: 0.8rem; color: var(--text-muted);"><?php echo htmlspecialchars($item['club_name']); ?></div>
                                </td>
                                <td><?php echo htmlspecialchars($item['category_name']); ?></td>
                                <td><?php echo formatDate($item['event_date']); ?></td>
                                <td><?php echo htmlspecialchars($item['venue']); ?></td>
                                <td>
                                    <span class="badge <?php echo getStatusBadgeClass($item['status']); ?>">
                                        <?php echo ucfirst($item['status']); ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
function switchScheduleTab(tab) {
    var upcomingSec = document.getElementById('upcomingSection');
    var pastSec = document.getElementById('pastSection');
    var upBtn = document.getElementById('tabUpcomingBtn');
    var pastBtn = document.getElementById('tabPastBtn');

    if (tab === 'upcoming') {
        upcomingSec.style.display = 'block';
        pastSec.style.display = 'none';
        upBtn.className = 'btn btn-primary btn-sm';
        pastBtn.className = 'btn btn-secondary btn-sm';
    } else {
        upcomingSec.style.display = 'none';
        pastSec.style.display = 'block';
        upBtn.className = 'btn btn-secondary btn-sm';
        pastBtn.className = 'btn btn-primary btn-sm';
    }
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
