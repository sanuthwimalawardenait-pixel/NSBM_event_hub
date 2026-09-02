<?php
require_once __DIR__ . '/config/database.php';
$pdo = getDbConnection();

$eventId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($eventId <= 0) {
    setFlashMessage('danger', 'Invalid event selected.');
    header('Location: ' . getBaseUrl() . 'events.php');
    exit;
}

$stmt = $pdo->prepare("
    SELECT e.*, c.name as category_name, c.color_code as category_color, c.icon as category_icon,
           u.full_name as creator_name,
           (SELECT COUNT(*) FROM registrations r WHERE r.event_id = e.id AND r.status != 'cancelled') as registered_count
    FROM events e
    JOIN categories c ON e.category_id = c.id
    JOIN users u ON e.created_by = u.id
    WHERE e.id = ?
");
$stmt->execute([$eventId]);
$event = $stmt->fetch();

if (!$event) {
    setFlashMessage('danger', 'Event not found.');
    header('Location: ' . getBaseUrl() . 'events.php');
    exit;
}

$pageTitle = $event['title'];
$currentUser = getCurrentUser();
$isRegistered = false;
$userRegistration = null;

if (isLoggedIn() && isStudent()) {
    $regCheck = $pdo->prepare("SELECT * FROM registrations WHERE event_id = ? AND user_id = ? AND status != 'cancelled'");
    $regCheck->execute([$eventId, $currentUser['id']]);
    $userRegistration = $regCheck->fetch();
    $isRegistered = $userRegistration ? true : false;
}

$seatsLeft = max(0, $event['capacity'] - $event['registered_count']);
$isFull = $seatsLeft <= 0;
$isPast = strtotime($event['event_date']) < strtotime(date('Y-m-d'));

$relatedStmt = $pdo->prepare("
    SELECT e.*, c.name as category_name, c.color_code as category_color,
           (SELECT COUNT(*) FROM registrations r WHERE r.event_id = e.id AND r.status != 'cancelled') as registered_count
    FROM events e
    JOIN categories c ON e.category_id = c.id
    WHERE e.category_id = ? AND e.id != ? AND e.status = 'upcoming' AND e.event_date >= CURDATE()
    ORDER BY e.event_date ASC
    LIMIT 3
");
$relatedStmt->execute([$event['category_id'], $eventId]);
$relatedEvents = $relatedStmt->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>

<div class="container" style="padding-top: 2rem;">
    <div style="margin-bottom: 1.5rem;">
        <a href="<?php echo $baseUrl; ?>events.php" class="btn btn-secondary btn-sm">
            <i class="bi bi-arrow-left"></i> Back to All Events
        </a>
    </div>

    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 2rem; align-items: start;">
        <div>
            <div class="card">
                <div style="background: linear-gradient(135deg, #004d2a 0%, #006838 50%, #0f172a 100%); color: #fff; padding: 2.5rem 2rem; border-radius: var(--radius-lg) var(--radius-lg) 0 0;">
                    <div style="display: flex; gap: 0.5rem; align-items: center; margin-bottom: 1rem; flex-wrap: wrap;">
                        <span class="badge" style="background: <?php echo htmlspecialchars($event['category_color']); ?>; color: #fff;">
                            <i class="bi <?php echo htmlspecialchars($event['category_icon']); ?>"></i> <?php echo htmlspecialchars($event['category_name']); ?>
                        </span>
                        <span class="badge <?php echo getStatusBadgeClass($event['status']); ?>">
                            <?php echo ucfirst($event['status']); ?>
                        </span>
                    </div>
                    <h1 style="font-size: 2rem; font-weight: 800; line-height: 1.25; margin-bottom: 0.75rem;">
                        <?php echo htmlspecialchars($event['title']); ?>
                    </h1>
                    <div style="display: flex; align-items: center; gap: 0.5rem; font-size: 1rem; color: #e2e8f0;">
                        <i class="bi bi-people-fill" style="color: #fde68a;"></i> Organized by <strong><?php echo htmlspecialchars($event['club_name']); ?></strong>
                    </div>
                </div>

                <div class="card-body">
                    <h3 style="font-size: 1.25rem; font-weight: 700; margin-bottom: 1rem; color: var(--secondary);">
                        About this Event
                    </h3>
                    <div style="font-size: 1rem; color: var(--text-main); line-height: 1.8; margin-bottom: 2rem; white-space: pre-line;">
                        <?php echo htmlspecialchars($event['description']); ?>
                    </div>

                    <div style="background-color: #f8fafc; border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 1.5rem; margin-bottom: 2rem;">
                        <h4 style="font-size: 1.1rem; font-weight: 700; margin-bottom: 1rem; color: var(--secondary);">
                            <i class="bi bi-info-square-fill" style="color: var(--primary);"></i> Important Event Guidelines
                        </h4>
                        <ul style="padding-left: 1.25rem; color: var(--text-muted); display: flex; flex-direction: column; gap: 0.5rem; font-size: 0.925rem;">
                            <li>Please present your digital or printed ticket code at the entrance checkpoint.</li>
                            <li>University student ID card is mandatory for gate clearance.</li>
                            <li>Participants are requested to arrive at least 15 minutes prior to the scheduled start time.</li>
                            <li>Certificates of participation will be issued based on the verified attendance list.</li>
                        </ul>
                    </div>

                    <?php if ($isRegistered && $userRegistration): ?>
                        <div class="ticket-pass">
                            <div class="ticket-header">
                                <div>
                                    <span style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px; color: var(--text-muted); font-weight: 700;">Official Registration Pass</span>
                                    <h4 style="color: var(--secondary); margin-top: 0.2rem;"><?php echo htmlspecialchars($event['title']); ?></h4>
                                </div>
                                <span class="badge badge-registered">Confirmed</span>
                            </div>
                            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 1rem; margin-bottom: 1.25rem; font-size: 0.9rem;">
                                <div>
                                    <span style="color: var(--text-muted); display: block; font-size: 0.8rem;">Attendee</span>
                                    <strong><?php echo htmlspecialchars($currentUser['name']); ?></strong>
                                </div>
                                <div>
                                    <span style="color: var(--text-muted); display: block; font-size: 0.8rem;">Student ID</span>
                                    <strong><?php echo htmlspecialchars($currentUser['student_id'] ?: 'N/A'); ?></strong>
                                </div>
                                <div>
                                    <span style="color: var(--text-muted); display: block; font-size: 0.8rem;">Date & Time</span>
                                    <strong><?php echo formatDate($event['event_date']); ?> | <?php echo formatTime($event['start_time']); ?></strong>
                                </div>
                                <div>
                                    <span style="color: var(--text-muted); display: block; font-size: 0.8rem;">Venue</span>
                                    <strong><?php echo htmlspecialchars($event['venue']); ?></strong>
                                </div>
                            </div>
                            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: gap; gap: 1rem;">
                                <div>
                                    <span style="font-size: 0.75rem; color: var(--text-muted); display: block; margin-bottom: 0.2rem;">Ticket ID</span>
                                    <div class="ticket-code"><?php echo htmlspecialchars($userRegistration['ticket_code']); ?></div>
                                </div>
                                <div style="display: flex; gap: 0.5rem;">
                                    <button type="button" class="btn btn-secondary btn-sm btn-print">
                                        <i class="bi bi-printer"></i> Print Pass
                                    </button>
                                    <a href="<?php echo $baseUrl; ?>cancel_registration.php?id=<?php echo $userRegistration['id']; ?>" class="btn btn-outline-danger btn-sm" onclick="return confirm('Are you sure you want to cancel your registration?');">
                                        <i class="bi bi-x-circle"></i> Cancel Pass
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div>
            <div class="card" style="position: sticky; top: 90px;">
                <div class="card-header">
                    <h3 class="card-title"><i class="bi bi-calendar-check"></i> Event Overview</h3>
                </div>
                <div class="card-body">
                    <div style="display: flex; flex-direction: column; gap: 1.25rem; margin-bottom: 1.5rem;">
                        <div style="display: flex; gap: 1rem; align-items: flex-start;">
                            <div style="width: 40px; height: 40px; border-radius: var(--radius-md); background: var(--primary-subtle); color: var(--primary); display: flex; align-items: center; justify-content: center; font-size: 1.25rem; flex-shrink: 0;">
                                <i class="bi bi-calendar4-week"></i>
                            </div>
                            <div>
                                <span style="font-size: 0.8rem; color: var(--text-muted); display: block;">Event Date</span>
                                <strong style="font-size: 0.95rem; color: var(--secondary);"><?php echo formatDate($event['event_date']); ?></strong>
                            </div>
                        </div>

                        <div style="display: flex; gap: 1rem; align-items: flex-start;">
                            <div style="width: 40px; height: 40px; border-radius: var(--radius-md); background: var(--primary-subtle); color: var(--primary); display: flex; align-items: center; justify-content: center; font-size: 1.25rem; flex-shrink: 0;">
                                <i class="bi bi-clock"></i>
                            </div>
                            <div>
                                <span style="font-size: 0.8rem; color: var(--text-muted); display: block;">Session Time</span>
                                <strong style="font-size: 0.95rem; color: var(--secondary);">
                                    <?php echo formatTime($event['start_time']); ?> - <?php echo formatTime($event['end_time']); ?>
                                </strong>
                            </div>
                        </div>

                        <div style="display: flex; gap: 1rem; align-items: flex-start;">
                            <div style="width: 40px; height: 40px; border-radius: var(--radius-md); background: var(--primary-subtle); color: var(--primary); display: flex; align-items: center; justify-content: center; font-size: 1.25rem; flex-shrink: 0;">
                                <i class="bi bi-geo-alt"></i>
                            </div>
                            <div>
                                <span style="font-size: 0.8rem; color: var(--text-muted); display: block;">Location / Venue</span>
                                <strong style="font-size: 0.95rem; color: var(--secondary);"><?php echo htmlspecialchars($event['venue']); ?></strong>
                            </div>
                        </div>

                        <div style="display: flex; gap: 1rem; align-items: flex-start;">
                            <div style="width: 40px; height: 40px; border-radius: var(--radius-md); background: var(--primary-subtle); color: var(--primary); display: flex; align-items: center; justify-content: center; font-size: 1.25rem; flex-shrink: 0;">
                                <i class="bi bi-person-badge"></i>
                            </div>
                            <div>
                                <span style="font-size: 0.8rem; color: var(--text-muted); display: block;">Organizing Club</span>
                                <strong style="font-size: 0.95rem; color: var(--secondary);"><?php echo htmlspecialchars($event['club_name']); ?></strong>
                            </div>
                        </div>
                    </div>

                    <div style="border-top: 1px solid var(--border-color); padding-top: 1.25rem; margin-bottom: 1.5rem;">
                        <div style="display: flex; justify-content: space-between; font-size: 0.85rem; margin-bottom: 0.5rem;">
                            <span>Capacity Status</span>
                            <strong><?php echo $event['registered_count']; ?> / <?php echo $event['capacity']; ?></strong>
                        </div>
                        <div class="progress-bar-bg">
                            <?php $fillPct = min(100, round(($event['registered_count'] / $event['capacity']) * 100)); ?>
                            <div class="progress-bar-fill <?php echo $fillPct >= 90 ? 'danger' : ($fillPct >= 70 ? 'warning' : ''); ?>" style="width: <?php echo $fillPct; ?>%;"></div>
                        </div>
                        <div style="text-align: right; margin-top: 0.4rem; font-size: 0.8rem; color: <?php echo $seatsLeft <= 5 ? 'var(--danger)' : 'var(--text-muted)'; ?>;">
                            <?php echo $seatsLeft; ?> seats remaining
                        </div>
                    </div>

                    <?php if ($isPast): ?>
                        <div class="alert alert-warning" style="margin-bottom: 0;">
                            <i class="bi bi-hourglass-bottom"></i> This event has concluded.
                        </div>
                    <?php elseif ($isRegistered): ?>
                        <div class="alert alert-success" style="margin-bottom: 0;">
                            <i class="bi bi-check-circle-fill"></i> You are registered for this event.
                        </div>
                    <?php elseif ($isFull): ?>
                        <div class="alert alert-danger" style="margin-bottom: 0;">
                            <i class="bi bi-x-circle-fill"></i> Registrations are closed (Max Capacity Reached).
                        </div>
                    <?php elseif (!isLoggedIn()): ?>
                        <div style="text-align: center; padding: 0.5rem 0;">
                            <div style="margin-bottom: 1rem; color: var(--text-muted); font-size: 0.9rem;">
                                <i class="bi bi-shield-lock" style="font-size: 1.75rem; color: var(--primary); display: block; margin-bottom: 0.35rem;"></i>
                                <strong style="color: var(--secondary);">Login Required to Register</strong>
                                <p style="margin-top: 0.25rem;">You must be signed in with a student account to book your event pass.</p>
                            </div>
                            <a href="<?php echo $baseUrl; ?>login.php?redirect=<?php echo urlencode('event_details.php?id=' . $event['id']); ?>" class="btn btn-primary btn-lg" style="width: 100%; margin-bottom: 0.75rem;">
                                <i class="bi bi-box-arrow-in-right"></i> Sign In to Register
                            </a>
                            <div style="font-size: 0.85rem; color: var(--text-muted);">
                                New student? <a href="<?php echo $baseUrl; ?>register.php?redirect=<?php echo urlencode('event_details.php?id=' . $event['id']); ?>" style="font-weight: 600;">Create Account</a>
                            </div>
                        </div>
                    <?php elseif (isStudent()): ?>
                        <form action="<?php echo $baseUrl; ?>register_event.php" method="POST">
                            <input type="hidden" name="event_id" value="<?php echo $event['id']; ?>">
                            <div class="form-group">
                                <label class="form-label" for="special_requirements">Special Requirements (Optional)</label>
                                <textarea name="special_requirements" id="special_requirements" class="form-control" rows="2" placeholder="e.g. Dietary preferences, team name, accessibility..."></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary btn-lg" style="width: 100%;">
                                <i class="bi bi-ticket-perforated-fill"></i> Confirm Registration
                            </button>
                        </form>
                    <?php elseif (isAdmin()): ?>
                        <div style="text-align: center; padding: 0.5rem 0;">
                            <div style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 1rem;">
                                <span class="badge badge-urgent" style="margin-bottom: 0.5rem; display: inline-block;">Administrator Access</span>
                                <p>You have full managerial privileges over this campus event.</p>
                            </div>
                            <a href="<?php echo $baseUrl; ?>admin/registrations.php?event_id=<?php echo $event['id']; ?>" class="btn btn-secondary" style="width: 100%; margin-bottom: 0.5rem;">
                                <i class="bi bi-people-fill"></i> Manage Registrations
                            </a>
                            <a href="<?php echo $baseUrl; ?>admin/event_edit.php?id=<?php echo $event['id']; ?>" class="btn btn-outline-secondary" style="width: 100%;">
                                <i class="bi bi-pencil-square"></i> Edit Event Details
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <?php if (!empty($relatedEvents)): ?>
        <div style="margin-top: 3.5rem;">
            <h3 class="section-title" style="font-size: 1.35rem; margin-bottom: 1.25rem;">
                <i class="bi bi-stars"></i> More <?php echo htmlspecialchars($event['category_name']); ?> Events
            </h3>
            <div class="events-grid">
                <?php foreach ($relatedEvents as $rel): 
                    $relSeats = max(0, $rel['capacity'] - $rel['registered_count']);
                    $relFill = min(100, round(($rel['registered_count'] / $rel['capacity']) * 100));
                ?>
                    <div class="event-card">
                        <div class="event-card-header" style="background-image: linear-gradient(135deg, rgba(0, 104, 56, 0.85) 0%, rgba(15, 23, 42, 0.9) 100%);">
                            <div class="event-date-badge">
                                <div class="event-date-month"><?php echo date('M', strtotime($rel['event_date'])); ?></div>
                                <div class="event-date-day"><?php echo date('d', strtotime($rel['event_date'])); ?></div>
                            </div>
                            <span class="event-category-tag"><?php echo htmlspecialchars($rel['category_name']); ?></span>
                            <div class="event-club-meta"><?php echo htmlspecialchars($rel['club_name']); ?></div>
                        </div>
                        <div class="event-card-body">
                            <h4 class="event-title"><a href="<?php echo $baseUrl; ?>event_details.php?id=<?php echo $rel['id']; ?>"><?php echo htmlspecialchars($rel['title']); ?></a></h4>
                            <p class="event-description"><?php echo htmlspecialchars($rel['description']); ?></p>
                            <div class="event-card-footer">
                                <a href="<?php echo $baseUrl; ?>event_details.php?id=<?php echo $rel['id']; ?>" class="btn btn-secondary btn-sm" style="width: 100%;">
                                    View Event
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
