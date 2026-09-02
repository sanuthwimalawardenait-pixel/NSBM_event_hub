<?php
$pageTitle = 'Manage Events';
require_once __DIR__ . '/header.php';
$pdo = getDbConnection();

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$categoryId = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$status = isset($_GET['status']) ? trim($_GET['status']) : '';

$categories = $pdo->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();

$query = "
    SELECT e.*, c.name as category_name, c.color_code as category_color,
           (SELECT COUNT(*) FROM registrations r WHERE r.event_id = e.id AND r.status != 'cancelled') as registered_count
    FROM events e
    JOIN categories c ON e.category_id = c.id
    WHERE 1=1
";
$params = [];

if (!empty($search)) {
    $query .= " AND (e.title LIKE ? OR e.club_name LIKE ? OR e.venue LIKE ?)";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
}

if ($categoryId > 0) {
    $query .= " AND e.category_id = ?";
    $params[] = $categoryId;
}

if (!empty($status)) {
    $query .= " AND e.status = ?";
    $params[] = $status;
}

$query .= " ORDER BY e.event_date DESC, e.start_time ASC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$events = $stmt->fetchAll();
?>

<div class="section-header">
    <div>
        <h1 class="section-title"><i class="bi bi-calendar-event"></i> Manage Campus Events</h1>
        <p class="section-subtitle">Create, update, cancel, and oversee all university club events and conferences</p>
    </div>
    <div style="display: flex; gap: 0.75rem;">
        <a href="<?php echo $baseUrl; ?>admin/event_add.php" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> Create Event
        </a>
    </div>
</div>

<div class="card" style="margin-bottom: 1.5rem;">
    <div class="card-body">
        <form action="<?php echo $baseUrl; ?>admin/events.php" method="GET" style="display: flex; gap: 1rem; flex-wrap: wrap;">
            <div style="flex: 2; min-width: 200px;">
                <input type="text" name="search" class="form-control" placeholder="Search event title, club or venue..." value="<?php echo htmlspecialchars($search); ?>">
            </div>
            <div style="flex: 1; min-width: 160px;">
                <select name="category" class="form-control">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo $cat['id']; ?>" <?php echo $categoryId == $cat['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cat['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div style="flex: 1; min-width: 140px;">
                <select name="status" class="form-control">
                    <option value="">All Statuses</option>
                    <option value="upcoming" <?php echo $status === 'upcoming' ? 'selected' : ''; ?>>Upcoming</option>
                    <option value="ongoing" <?php echo $status === 'ongoing' ? 'selected' : ''; ?>>Ongoing</option>
                    <option value="completed" <?php echo $status === 'completed' ? 'selected' : ''; ?>>Completed</option>
                    <option value="cancelled" <?php echo $status === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-funnel"></i> Filter
            </button>
            <?php if (!empty($search) || $categoryId > 0 || !empty($status)): ?>
                <a href="<?php echo $baseUrl; ?>admin/events.php" class="btn btn-secondary">
                    <i class="bi bi-arrow-counterclockwise"></i> Reset
                </a>
            <?php endif; ?>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body" style="padding: 0;">
        <?php if (empty($events)): ?>
            <div style="text-align: center; padding: 3rem 1.5rem; color: var(--text-muted);">
                <i class="bi bi-calendar-x" style="font-size: 2.5rem; display: block; margin-bottom: 0.75rem;"></i>
                <h4>No Events Found</h4>
                <p>No events match the selected criteria.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive" style="border: none; box-shadow: none;">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Event Details</th>
                            <th>Category</th>
                            <th>Date & Time</th>
                            <th>Venue</th>
                            <th>Registrations</th>
                            <th>Status</th>
                            <th style="text-align: right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($events as $evt): 
                            $fillPct = min(100, round(($evt['registered_count'] / $evt['capacity']) * 100));
                        ?>
                            <tr>
                                <td>
                                    <div style="font-weight: 700; font-size: 1rem; color: var(--secondary);">
                                        <a href="<?php echo $baseUrl; ?>event_details.php?id=<?php echo $evt['id']; ?>" target="_blank">
                                            <?php echo htmlspecialchars($evt['title']); ?>
                                        </a>
                                    </div>
                                    <div style="font-size: 0.8rem; color: var(--text-muted);">
                                        <i class="bi bi-people"></i> <?php echo htmlspecialchars($evt['club_name']); ?>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge" style="background: <?php echo htmlspecialchars($evt['category_color']); ?>; color: #fff;">
                                        <?php echo htmlspecialchars($evt['category_name']); ?>
                                    </span>
                                </td>
                                <td>
                                    <strong><?php echo formatDate($evt['event_date']); ?></strong>
                                    <div style="font-size: 0.775rem; color: var(--text-muted);">
                                        <?php echo formatTime($evt['start_time']); ?> - <?php echo formatTime($evt['end_time']); ?>
                                    </div>
                                </td>
                                <td>
                                    <span style="font-size: 0.9rem;"><?php echo htmlspecialchars($evt['venue']); ?></span>
                                </td>
                                <td style="min-width: 140px;">
                                    <div style="display: flex; justify-content: space-between; font-size: 0.8rem; margin-bottom: 0.25rem;">
                                        <span><?php echo $evt['registered_count']; ?> / <?php echo $evt['capacity']; ?></span>
                                        <strong><?php echo $fillPct; ?>%</strong>
                                    </div>
                                    <div class="progress-bar-bg" style="height: 5px;">
                                        <div class="progress-bar-fill <?php echo $fillPct >= 90 ? 'danger' : ($fillPct >= 70 ? 'warning' : ''); ?>" style="width: <?php echo $fillPct; ?>%;"></div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge <?php echo getStatusBadgeClass($evt['status']); ?>">
                                        <?php echo ucfirst($evt['status']); ?>
                                    </span>
                                </td>
                                <td style="text-align: right; white-space: nowrap;">
                                    <a href="<?php echo $baseUrl; ?>admin/registrations.php?event_id=<?php echo $evt['id']; ?>" class="btn btn-secondary btn-sm" title="View Registrations">
                                        <i class="bi bi-people"></i>
                                    </a>
                                    <a href="<?php echo $baseUrl; ?>admin/export_participants.php?event_id=<?php echo $evt['id']; ?>" class="btn btn-secondary btn-sm" title="Export CSV Participant List">
                                        <i class="bi bi-download"></i>
                                    </a>
                                    <a href="<?php echo $baseUrl; ?>admin/event_edit.php?id=<?php echo $evt['id']; ?>" class="btn btn-secondary btn-sm" title="Edit Event">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <a href="<?php echo $baseUrl; ?>admin/event_delete.php?id=<?php echo $evt['id']; ?>" class="btn btn-outline-danger btn-sm" title="Delete Event" onclick="return confirm('Are you sure you want to delete this event? This will also remove all attendee registrations.');">
                                        <i class="bi bi-trash"></i>
                                    </a>
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
