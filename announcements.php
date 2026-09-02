<?php
$pageTitle = 'Campus Announcements & Notices';
require_once __DIR__ . '/config/database.php';
$pdo = getDbConnection();

$priorityFilter = isset($_GET['priority']) ? trim($_GET['priority']) : '';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

$query = "
    SELECT a.*, u.full_name as author_name
    FROM announcements a
    JOIN users u ON a.created_by = u.id
    WHERE 1=1
";
$params = [];

if (!empty($priorityFilter)) {
    $query .= " AND a.priority = ?";
    $params[] = $priorityFilter;
}

if (!empty($search)) {
    $query .= " AND (a.title LIKE ? OR a.content LIKE ?)";
    $term = "%{$search}%";
    $params[] = $term;
    $params[] = $term;
}

$query .= " ORDER BY FIELD(a.priority, 'urgent', 'important', 'normal'), a.created_at DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$announcements = $stmt->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>

<div class="container" style="padding-top: 2rem;">
    <div class="section-header">
        <div>
            <h1 class="section-title"><i class="bi bi-megaphone"></i> Official Campus Noticeboard</h1>
            <p class="section-subtitle">Real-time alerts, club schedules, shuttle updates, and university event circulars</p>
        </div>
    </div>

    <div class="card" style="margin-bottom: 2rem;">
        <div class="card-body">
            <form action="<?php echo $baseUrl; ?>announcements.php" method="GET" style="display: flex; gap: 1rem; flex-wrap: wrap;">
                <div style="flex: 2; min-width: 240px;">
                    <input type="text" name="search" class="form-control" placeholder="Search notices by keyword..." value="<?php echo htmlspecialchars($search); ?>">
                </div>
                <div style="flex: 1; min-width: 180px;">
                    <select name="priority" class="form-control">
                        <option value="">All Priorities</option>
                        <option value="urgent" <?php echo $priorityFilter === 'urgent' ? 'selected' : ''; ?>>Urgent</option>
                        <option value="important" <?php echo $priorityFilter === 'important' ? 'selected' : ''; ?>>Important</option>
                        <option value="normal" <?php echo $priorityFilter === 'normal' ? 'selected' : ''; ?>>Normal</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-funnel"></i> Filter Notices
                </button>
                <?php if (!empty($priorityFilter) || !empty($search)): ?>
                    <a href="<?php echo $baseUrl; ?>announcements.php" class="btn btn-secondary">
                        <i class="bi bi-arrow-counterclockwise"></i> Reset
                    </a>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <div>
        <?php if (empty($announcements)): ?>
            <div class="card" style="text-align: center; padding: 3rem 1.5rem;">
                <i class="bi bi-bell-slash" style="font-size: 3rem; color: var(--text-light); margin-bottom: 1rem; display: block;"></i>
                <h3>No Announcements Found</h3>
                <p style="color: var(--text-muted);">There are no notices matching your current filter criteria.</p>
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
                                <h3 class="announcement-title"><?php echo htmlspecialchars($ann['title']); ?></h3>
                                <span class="badge <?php echo getPriorityBadgeClass($ann['priority']); ?>">
                                    <?php echo ucfirst($ann['priority']); ?>
                                </span>
                                <span class="badge badge-normal" style="text-transform: capitalize;">
                                    Target: <?php echo str_replace('_', ' ', htmlspecialchars($ann['target_audience'])); ?>
                                </span>
                            </div>
                            <span class="announcement-time">
                                <i class="bi bi-clock"></i> Posted on <?php echo formatDate($ann['created_at']); ?>
                            </span>
                        </div>
                        <div class="announcement-text" style="font-size: 0.95rem; line-height: 1.7; white-space: pre-line; margin-top: 0.5rem;">
                            <?php echo htmlspecialchars($ann['content']); ?>
                        </div>
                        <div style="margin-top: 0.75rem; font-size: 0.8rem; color: var(--text-light);">
                            <i class="bi bi-person-check"></i> Published by <?php echo htmlspecialchars($ann['author_name']); ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
