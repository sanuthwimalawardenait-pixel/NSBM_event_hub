<?php
$pageTitle = 'Browse Events';
require_once __DIR__ . '/config/database.php';
$pdo = getDbConnection();

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$selectedCategory = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$timeFilter = isset($_GET['time']) ? trim($_GET['time']) : 'upcoming';

$categoriesStmt = $pdo->query("SELECT * FROM categories ORDER BY name ASC");
$categories = $categoriesStmt->fetchAll();

$query = "
    SELECT e.*, c.name as category_name, c.color_code as category_color, c.icon as category_icon,
           (SELECT COUNT(*) FROM registrations r WHERE r.event_id = e.id AND r.status != 'cancelled') as registered_count
    FROM events e
    JOIN categories c ON e.category_id = c.id
    WHERE 1=1
";
$params = [];

if (!empty($search)) {
    $query .= " AND (e.title LIKE ? OR e.club_name LIKE ? OR e.venue LIKE ? OR e.description LIKE ?)";
    $term = "%{$search}%";
    $params[] = $term;
    $params[] = $term;
    $params[] = $term;
    $params[] = $term;
}

if ($selectedCategory > 0) {
    $query .= " AND e.category_id = ?";
    $params[] = $selectedCategory;
}

if ($timeFilter === 'upcoming') {
    $query .= " AND e.event_date >= CURDATE() AND e.status != 'cancelled'";
} elseif ($timeFilter === 'past') {
    $query .= " AND e.event_date < CURDATE()";
} elseif ($timeFilter === 'today') {
    $query .= " AND e.event_date = CURDATE()";
}

$query .= " ORDER BY e.event_date ASC, e.start_time ASC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$events = $stmt->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>

<div class="container" style="padding-top: 2rem;">
    <div class="section-header">
        <div>
            <h1 class="section-title"><i class="bi bi-calendar3"></i> University Events Directory</h1>
            <p class="section-subtitle">Discover and register for campus activities, club workshops, competitions, and conferences</p>
        </div>
    </div>

    <div class="card" style="margin-bottom: 2rem;">
        <div class="card-body">
            <form action="<?php echo $baseUrl; ?>events.php" method="GET" style="display: flex; gap: 1rem; flex-wrap: wrap; align-items: flex-end;">
                <div style="flex: 2; min-width: 220px;">
                    <label class="form-label"><i class="bi bi-search"></i> Keyword Search</label>
                    <input type="text" name="search" id="liveEventSearch" class="form-control" placeholder="Search event title, club name, or venue..." value="<?php echo htmlspecialchars($search); ?>">
                </div>

                <div style="flex: 1; min-width: 180px;">
                    <label class="form-label"><i class="bi bi-tags"></i> Category</label>
                    <select name="category" id="categoryFilterSelect" class="form-control">
                        <option value="">All Categories</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>" <?php echo $selectedCategory == $cat['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div style="flex: 1; min-width: 160px;">
                    <label class="form-label"><i class="bi bi-funnel"></i> Timeline</label>
                    <select name="time" class="form-control">
                        <option value="upcoming" <?php echo $timeFilter === 'upcoming' ? 'selected' : ''; ?>>Upcoming Events</option>
                        <option value="today" <?php echo $timeFilter === 'today' ? 'selected' : ''; ?>>Today's Events</option>
                        <option value="past" <?php echo $timeFilter === 'past' ? 'selected' : ''; ?>>Past Events</option>
                        <option value="all" <?php echo $timeFilter === 'all' ? 'selected' : ''; ?>>All Dates</option>
                    </select>
                </div>

                <div style="display: flex; gap: 0.5rem;">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-filter"></i> Apply Filter
                    </button>
                    <?php if (!empty($search) || $selectedCategory > 0 || $timeFilter !== 'upcoming'): ?>
                        <a href="<?php echo $baseUrl; ?>events.php" class="btn btn-secondary" title="Reset Filters">
                            <i class="bi bi-arrow-counterclockwise"></i> Reset
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <div class="category-filter-bar">
        <button type="button" class="category-pill category-pill-btn <?php echo empty($selectedCategory) ? 'active' : ''; ?>" data-category-id="">
            <i class="bi bi-grid-fill"></i> All
        </button>
        <?php foreach ($categories as $cat): ?>
            <button type="button" class="category-pill category-pill-btn <?php echo $selectedCategory == $cat['id'] ? 'active' : ''; ?>" data-category-id="<?php echo $cat['id']; ?>">
                <i class="bi <?php echo htmlspecialchars($cat['icon']); ?>"></i>
                <?php echo htmlspecialchars($cat['name']); ?>
            </button>
        <?php endforeach; ?>
    </div>

    <div id="noEventsAlert" class="card" style="display: <?php echo empty($events) ? 'block' : 'none'; ?>; text-align: center; padding: 3rem 1.5rem;">
        <i class="bi bi-search" style="font-size: 3rem; color: var(--text-light); margin-bottom: 1rem; display: block;"></i>
        <h3>No Events Found</h3>
        <p style="color: var(--text-muted); margin-bottom: 1.5rem;">Try adjusting your search criteria or selecting a different category.</p>
        <a href="<?php echo $baseUrl; ?>events.php" class="btn btn-primary">
            <i class="bi bi-arrow-clockwise"></i> View All Upcoming Events
        </a>
    </div>

    <div class="events-grid">
        <?php foreach ($events as $evt): 
            $seatsLeft = max(0, $evt['capacity'] - $evt['registered_count']);
            $pctFilled = min(100, round(($evt['registered_count'] / $evt['capacity']) * 100));
            $barClass = $pctFilled >= 90 ? 'danger' : ($pctFilled >= 70 ? 'warning' : '');
            $evtMonth = date('M', strtotime($evt['event_date']));
            $evtDay = date('d', strtotime($evt['event_date']));
        ?>
            <div class="event-card event-card-item" 
                 data-title="<?php echo htmlspecialchars($evt['title']); ?>" 
                 data-club="<?php echo htmlspecialchars($evt['club_name']); ?>" 
                 data-venue="<?php echo htmlspecialchars($evt['venue']); ?>" 
                 data-category-id="<?php echo $evt['category_id']; ?>">
                <div class="event-card-header" style="background-image: linear-gradient(135deg, rgba(0, 104, 56, 0.85) 0%, rgba(15, 23, 42, 0.9) 100%);">
                    <div class="event-date-badge">
                        <div class="event-date-month"><?php echo $evtMonth; ?></div>
                        <div class="event-date-day"><?php echo $evtDay; ?></div>
                    </div>
                    <span class="event-category-tag" style="border-left: 3px solid <?php echo htmlspecialchars($evt['category_color']); ?>;">
                        <?php echo htmlspecialchars($evt['category_name']); ?>
                    </span>
                    <div class="event-club-meta">
                        <i class="bi bi-people-fill"></i> <?php echo htmlspecialchars($evt['club_name']); ?>
                    </div>
                </div>
                <div class="event-card-body">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                        <span class="badge <?php echo getStatusBadgeClass($evt['status']); ?>">
                            <?php echo ucfirst($evt['status']); ?>
                        </span>
                        <span style="font-size: 0.8rem; color: var(--text-muted);">
                            <i class="bi bi-calendar-event"></i> <?php echo formatDate($evt['event_date']); ?>
                        </span>
                    </div>

                    <h3 class="event-title">
                        <a href="<?php echo $baseUrl; ?>event_details.php?id=<?php echo $evt['id']; ?>"><?php echo htmlspecialchars($evt['title']); ?></a>
                    </h3>
                    <p class="event-description"><?php echo htmlspecialchars($evt['description']); ?></p>
                    
                    <ul class="event-meta-list">
                        <li class="event-meta-item">
                            <i class="bi bi-clock"></i>
                            <span><?php echo formatTime($evt['start_time']); ?> - <?php echo formatTime($evt['end_time']); ?></span>
                        </li>
                        <li class="event-meta-item">
                            <i class="bi bi-geo-alt"></i>
                            <span><?php echo htmlspecialchars($evt['venue']); ?></span>
                        </li>
                    </ul>

                    <div class="event-capacity-bar-wrapper">
                        <div class="event-capacity-label">
                            <span><i class="bi bi-people"></i> <?php echo $evt['registered_count']; ?> / <?php echo $evt['capacity']; ?> Registered</span>
                            <span style="font-weight: 700; color: <?php echo $seatsLeft <= 10 ? 'var(--danger)' : 'var(--primary)'; ?>;">
                                <?php echo $seatsLeft; ?> seats left
                            </span>
                        </div>
                        <div class="progress-bar-bg">
                            <div class="progress-bar-fill <?php echo $barClass; ?>" style="width: <?php echo $pctFilled; ?>%;"></div>
                        </div>
                    </div>

                    <div class="event-card-footer">
                        <a href="<?php echo $baseUrl; ?>event_details.php?id=<?php echo $evt['id']; ?>" class="btn btn-secondary btn-sm">
                            <i class="bi bi-info-circle"></i> Details
                        </a>
                        <a href="<?php echo $baseUrl; ?>event_details.php?id=<?php echo $evt['id']; ?>" class="btn btn-primary btn-sm">
                            <i class="bi bi-ticket-perforated"></i> Register
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
