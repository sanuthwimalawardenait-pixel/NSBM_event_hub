<?php
$pageTitle = 'Manage Announcements';
require_once __DIR__ . '/header.php';
$pdo = getDbConnection();
$currentUser = getCurrentUser();

$error = '';
$editAnnouncement = null;
$editId = isset($_GET['edit']) ? (int)$_GET['edit'] : 0;
$deleteId = isset($_GET['delete']) ? (int)$_GET['delete'] : 0;

if ($deleteId > 0) {
    $delStmt = $pdo->prepare("DELETE FROM announcements WHERE id = ?");
    $delStmt->execute([$deleteId]);
    setFlashMessage('info', 'Announcement deleted successfully.');
    header('Location: ' . getBaseUrl() . 'admin/announcements.php');
    exit;
}

if ($editId > 0) {
    $stmt = $pdo->prepare("SELECT * FROM announcements WHERE id = ?");
    $stmt->execute([$editId]);
    $editAnnouncement = $stmt->fetch();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $priority = trim($_POST['priority'] ?? 'normal');
    $targetAudience = trim($_POST['target_audience'] ?? 'all');
    $annId = (int)($_POST['announcement_id'] ?? 0);

    if (empty($title) || empty($content)) {
        $error = 'Title and content are required.';
    } else {
        if ($annId > 0) {
            $updateStmt = $pdo->prepare("
                UPDATE announcements
                SET title = ?, content = ?, priority = ?, target_audience = ?
                WHERE id = ?
            ");
            $updateStmt->execute([$title, $content, $priority, $targetAudience, $annId]);
            setFlashMessage('success', 'Announcement updated successfully.');
        } else {
            $insertStmt = $pdo->prepare("
                INSERT INTO announcements (title, content, priority, target_audience, created_by)
                VALUES (?, ?, ?, ?, ?)
            ");
            $insertStmt->execute([$title, $content, $priority, $targetAudience, $currentUser['id']]);
            setFlashMessage('success', 'New announcement broadcasted successfully.');
        }
        header('Location: ' . getBaseUrl() . 'admin/announcements.php');
        exit;
    }
}

$announcementsStmt = $pdo->query("
    SELECT a.*, u.full_name as author_name
    FROM announcements a
    JOIN users u ON a.created_by = u.id
    ORDER BY a.created_at DESC
");
$announcements = $announcementsStmt->fetchAll();
?>

<div class="section-header">
    <div>
        <h1 class="section-title"><i class="bi bi-megaphone-fill"></i> Campus Announcements</h1>
        <p class="section-subtitle">Publish critical alerts, venue reallocations, and club notices to students and clubs</p>
    </div>
</div>

<?php if (!empty($error)): ?>
    <div class="alert alert-danger">
        <i class="bi bi-exclamation-octagon-fill"></i> <?php echo htmlspecialchars($error); ?>
    </div>
<?php endif; ?>

<div style="display: grid; grid-template-columns: 1fr 1.6fr; gap: 2rem; align-items: start;">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="bi <?php echo $editAnnouncement ? 'bi-pencil-square' : 'bi-plus-circle'; ?>"></i>
                <?php echo $editAnnouncement ? 'Edit Announcement' : 'Create New Announcement'; ?>
            </h3>
        </div>
        <div class="card-body">
            <form action="<?php echo $baseUrl; ?>admin/announcements.php" method="POST">
                <input type="hidden" name="announcement_id" value="<?php echo $editAnnouncement ? $editAnnouncement['id'] : 0; ?>">

                <div class="form-group">
                    <label class="form-label" for="ann_title">Notice Title *</label>
                    <input type="text" name="title" id="ann_title" class="form-control" placeholder="e.g. Schedule Update for Welcome Week" required value="<?php echo htmlspecialchars($editAnnouncement['title'] ?? ''); ?>">
                </div>

                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label class="form-label" for="ann_priority">Priority Level *</label>
                            <select name="priority" id="ann_priority" class="form-control">
                                <option value="normal" <?php echo ($editAnnouncement['priority'] ?? '') === 'normal' ? 'selected' : ''; ?>>Normal</option>
                                <option value="important" <?php echo ($editAnnouncement['priority'] ?? '') === 'important' ? 'selected' : ''; ?>>Important</option>
                                <option value="urgent" <?php echo ($editAnnouncement['priority'] ?? '') === 'urgent' ? 'selected' : ''; ?>>Urgent</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label class="form-label" for="ann_target">Target Audience *</label>
                            <select name="target_audience" id="ann_target" class="form-control">
                                <option value="all" <?php echo ($editAnnouncement['target_audience'] ?? '') === 'all' ? 'selected' : ''; ?>>All Students & Clubs</option>
                                <option value="students" <?php echo ($editAnnouncement['target_audience'] ?? '') === 'students' ? 'selected' : ''; ?>>Undergraduate Students Only</option>
                                <option value="club_members" <?php echo ($editAnnouncement['target_audience'] ?? '') === 'club_members' ? 'selected' : ''; ?>>Club Executives Only</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="ann_content">Notice Message Body *</label>
                    <textarea name="content" id="ann_content" class="form-control" rows="5" placeholder="Write full details of the notice..." required><?php echo htmlspecialchars($editAnnouncement['content'] ?? ''); ?></textarea>
                </div>

                <div style="display: flex; gap: 0.5rem; margin-top: 1.5rem;">
                    <button type="submit" class="btn btn-primary" style="flex: 1;">
                        <i class="bi bi-broadcast"></i> <?php echo $editAnnouncement ? 'Update Notice' : 'Publish Notice'; ?>
                    </button>
                    <?php if ($editAnnouncement): ?>
                        <a href="<?php echo $baseUrl; ?>admin/announcements.php" class="btn btn-secondary">Cancel</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><i class="bi bi-card-checklist"></i> Published Notices (<?php echo count($announcements); ?>)</h3>
        </div>
        <div class="card-body" style="padding: 0;">
            <?php if (empty($announcements)): ?>
                <div style="text-align: center; padding: 3rem 1.5rem; color: var(--text-muted);">
                    <i class="bi bi-bell-slash" style="font-size: 2.5rem; display: block; margin-bottom: 0.75rem;"></i>
                    <h4>No Announcements Published</h4>
                    <p>Use the form on the left to post your first campus notice.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive" style="border: none; box-shadow: none;">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Notice Details</th>
                                <th>Priority</th>
                                <th>Audience</th>
                                <th>Date</th>
                                <th style="text-align: right;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($announcements as $a): ?>
                                <tr>
                                    <td>
                                        <strong style="font-size: 0.95rem; color: var(--secondary);"><?php echo htmlspecialchars($a['title']); ?></strong>
                                        <div style="font-size: 0.8rem; color: var(--text-muted); margin-top: 0.2rem; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                                            <?php echo htmlspecialchars($a['content']); ?>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge <?php echo getPriorityBadgeClass($a['priority']); ?>">
                                            <?php echo ucfirst($a['priority']); ?>
                                        </span>
                                    </td>
                                    <td style="font-size: 0.8rem; text-transform: capitalize;">
                                        <?php echo str_replace('_', ' ', htmlspecialchars($a['target_audience'])); ?>
                                    </td>
                                    <td style="font-size: 0.8rem; color: var(--text-muted); white-space: nowrap;">
                                        <?php echo formatDate($a['created_at']); ?>
                                    </td>
                                    <td style="text-align: right; white-space: nowrap;">
                                        <a href="<?php echo $baseUrl; ?>admin/announcements.php?edit=<?php echo $a['id']; ?>" class="btn btn-secondary btn-sm" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <a href="<?php echo $baseUrl; ?>admin/announcements.php?delete=<?php echo $a['id']; ?>" class="btn btn-outline-danger btn-sm" title="Delete" onclick="return confirm('Are you sure you want to delete this notice?');">
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
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
