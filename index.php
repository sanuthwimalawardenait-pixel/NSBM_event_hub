<?php
$pageTitle = 'Home - University Event Planning & Scheduling';
require_once __DIR__ . '/config/database.php';
$pdo = getDbConnection();

$totalEventsStmt = $pdo->query("SELECT COUNT(*) FROM events WHERE status != 'cancelled'");
$totalEvents = $totalEventsStmt->fetchColumn();

$totalCategoriesStmt = $pdo->query("SELECT COUNT(*) FROM categories");
$totalCategories = $totalCategoriesStmt->fetchColumn();

$totalRegsStmt = $pdo->query("SELECT COUNT(*) FROM registrations WHERE status != 'cancelled'");
$totalRegs = $totalRegsStmt->fetchColumn();

$totalStudentsStmt = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'student'");
$totalStudents = $totalStudentsStmt->fetchColumn();

$categoriesStmt = $pdo->query("SELECT c.*, COUNT(e.id) as event_count FROM categories c LEFT JOIN events e ON c.id = e.category_id AND e.status = 'upcoming' GROUP BY c.id ORDER BY c.name ASC");
$categories = $categoriesStmt->fetchAll();

$eventsStmt = $pdo->query("
    SELECT e.*, c.name as category_name, c.color_code as category_color,
           (SELECT COUNT(*) FROM registrations r WHERE r.event_id = e.id AND r.status != 'cancelled') as registered_count
    FROM events e
    JOIN categories c ON e.category_id = c.id
    WHERE e.status = 'upcoming' AND e.event_date >= CURDATE()
    ORDER BY e.event_date ASC, e.start_time ASC
    LIMIT 6
");
$upcomingEvents = $eventsStmt->fetchAll();

$announcementsStmt = $pdo->query("
    SELECT a.*, u.full_name as author_name
    FROM announcements a
    JOIN users u ON a.created_by = u.id
    ORDER BY FIELD(a.priority, 'urgent', 'important', 'normal'), a.created_at DESC
    LIMIT 6
");
$announcements = $announcementsStmt->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>

<section class="hero-section">
    <div class="hero-video-wrapper">
        <video class="hero-video-bg" id="heroDroneVideo" autoplay muted loop playsinline poster="https://upload.wikimedia.org/wikipedia/commons/8/83/NSBM_Green_University_aerial_view.jpg">
            <source src="https://assets.mixkit.co/videos/preview/mixkit-drone-view-of-a-modern-university-campus-41527-large.mp4" type="video/mp4">
            <source src="https://assets.mixkit.co/videos/preview/mixkit-aerial-view-of-a-green-campus-and-buildings-41529-large.mp4" type="video/mp4">
        </video>
        <div class="hero-overlay"></div>
    </div>

    <button type="button" class="hero-video-toggle" id="heroVideoToggle" title="Toggle Drone Camera">
        <i class="bi bi-camera-video-fill"></i> <span>Live Drone View</span>
    </button>

    <div class="container hero-content">
        <h1 class="hero-title">Discover, Plan & Participate in <span>NSBM University Events</span></h1>
        <p class="hero-subtitle">The official event planning hub for student clubs, hackathons, academic summits, sports championships, and cultural festivals across NSBM Green University.</p>
        
        <form action="<?php echo $baseUrl; ?>events.php" method="GET" class="hero-search-box">
            <input type="text" name="search" class="hero-search-input" placeholder="Search events by title, club or venue...">
            <select name="category" class="hero-search-select">
                <option value="">All Categories</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="btn btn-primary btn-lg">
                <i class="bi bi-search"></i> Explore Events
            </button>
        </form>
    </div>
</section>

<div class="container">
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon green"><i class="bi bi-calendar-event"></i></div>
            <div>
                <div class="stat-number"><?php echo $totalEvents; ?></div>
                <div class="stat-label">Published Events</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon gold"><i class="bi bi-ticket-perforated"></i></div>
            <div>
                <div class="stat-number"><?php echo $totalRegs; ?></div>
                <div class="stat-label">Event Registrations</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon blue"><i class="bi bi-mortarboard"></i></div>
            <div>
                <div class="stat-number"><?php echo $totalStudents; ?></div>
                <div class="stat-label">Active Students</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon purple"><i class="bi bi-tags"></i></div>
            <div>
                <div class="stat-number"><?php echo $totalCategories; ?></div>
                <div class="stat-label">Event Categories</div>
            </div>
        </div>
    </div>

    <div class="section-header">
        <div>
            <h2 class="section-title"><i class="bi bi-fire"></i> Upcoming Featured Events</h2>
            <p class="section-subtitle">Reserve your pass and get involved in university life</p>
        </div>
        <a href="<?php echo $baseUrl; ?>events.php" class="btn btn-secondary">
            View All Events <i class="bi bi-arrow-right"></i>
        </a>
    </div>

    <div class="category-filter-bar">
        <a href="<?php echo $baseUrl; ?>events.php" class="category-pill active">
            <i class="bi bi-grid-fill"></i> All Events
        </a>
        <?php foreach ($categories as $cat): ?>
            <a href="<?php echo $baseUrl; ?>events.php?category=<?php echo $cat['id']; ?>" class="category-pill">
                <i class="bi <?php echo htmlspecialchars($cat['icon']); ?>"></i>
                <?php echo htmlspecialchars($cat['name']); ?>
                <span style="font-size: 0.75rem; opacity: 0.8;">(<?php echo $cat['event_count']; ?>)</span>
            </a>
        <?php endforeach; ?>
    </div>

    <?php if (empty($upcomingEvents)): ?>
        <div class="card" style="text-align: center; padding: 3rem 1rem;">
            <i class="bi bi-calendar-x" style="font-size: 3rem; color: var(--text-light); margin-bottom: 1rem; display: block;"></i>
            <h3>No Upcoming Events at the Moment</h3>
            <p style="color: var(--text-muted); margin-bottom: 1.5rem;">Check back soon for new club activities and campus schedules.</p>
        </div>
    <?php else: ?>
        <div class="events-grid">
            <?php foreach ($upcomingEvents as $evt): 
                $seatsLeft = max(0, $evt['capacity'] - $evt['registered_count']);
                $pctFilled = min(100, round(($evt['registered_count'] / $evt['capacity']) * 100));
                $barClass = $pctFilled >= 90 ? 'danger' : ($pctFilled >= 70 ? 'warning' : '');
                $evtMonth = date('M', strtotime($evt['event_date']));
                $evtDay = date('d', strtotime($evt['event_date']));
            ?>
                <div class="event-card">
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
    <?php endif; ?>

    <div style="margin-top: 4rem;">
        <div class="section-header">
            <div>
                <h2 class="section-title"><i class="bi bi-megaphone"></i> Campus Notices & Announcements</h2>
                <p class="section-subtitle">Official communications from Student Affairs and Club Executives</p>
            </div>
            <a href="<?php echo $baseUrl; ?>announcements.php" class="btn btn-secondary">
                All Announcements (<?php echo count($announcements); ?>+) <i class="bi bi-arrow-right"></i>
            </a>
        </div>

        <div>
            <?php if (empty($announcements)): ?>
                <div class="card" style="padding: 2rem; text-align: center;">
                    <p style="color: var(--text-muted);">No announcements at this time.</p>
                </div>
            <?php else: ?>
                <?php foreach ($announcements as $ann): ?>
                    <div class="announcement-card <?php echo htmlspecialchars($ann['priority']); ?>">
                        <div class="announcement-icon <?php echo htmlspecialchars($ann['priority']); ?>">
                            <i class="bi <?php echo $ann['priority'] === 'urgent' ? 'bi-exclamation-octagon' : ($ann['priority'] === 'important' ? 'bi-exclamation-circle' : 'bi-bell'); ?>"></i>
                        </div>
                        <div class="announcement-content">
                            <div class="announcement-header">
                                <div style="display: flex; align-items: center; gap: 0.6rem; flex-wrap: wrap;">
                                    <h4 class="announcement-title"><?php echo htmlspecialchars($ann['title']); ?></h4>
                                    <span class="badge <?php echo getPriorityBadgeClass($ann['priority']); ?>">
                                        <?php echo ucfirst($ann['priority']); ?>
                                    </span>
                                    <span class="badge badge-normal" style="text-transform: capitalize;">
                                        For: <?php echo str_replace('_', ' ', htmlspecialchars($ann['target_audience'])); ?>
                                    </span>
                                </div>
                                <span class="announcement-time">
                                    <i class="bi bi-clock"></i> <?php echo formatDate($ann['created_at']); ?>
                                </span>
                            </div>
                            <p class="announcement-text"><?php echo nl2br(htmlspecialchars($ann['content'])); ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
