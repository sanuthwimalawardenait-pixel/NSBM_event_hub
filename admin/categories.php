<?php
$pageTitle = 'Manage Event Categories';
require_once __DIR__ . '/header.php';
$pdo = getDbConnection();

$error = '';
$editCategory = null;
$editId = isset($_GET['edit']) ? (int)$_GET['edit'] : 0;
$deleteId = isset($_GET['delete']) ? (int)$_GET['delete'] : 0;

if ($deleteId > 0) {
    $delCheck = $pdo->prepare("SELECT COUNT(*) FROM events WHERE category_id = ?");
    $delCheck->execute([$deleteId]);
    $eventCount = $delCheck->fetchColumn();

    if ($eventCount > 0) {
        setFlashMessage('danger', 'Cannot delete this category because it contains ' . $eventCount . ' event(s). Please reassign or delete the events first.');
    } else {
        $delStmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
        $delStmt->execute([$deleteId]);
        setFlashMessage('success', 'Category deleted successfully.');
    }
    header('Location: ' . getBaseUrl() . 'admin/categories.php');
    exit;
}

if ($editId > 0) {
    $editStmt = $pdo->prepare("SELECT * FROM categories WHERE id = ?");
    $editStmt->execute([$editId]);
    $editCategory = $editStmt->fetch();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $icon = trim($_POST['icon'] ?? 'bi-bookmark');
    $colorCode = trim($_POST['color_code'] ?? '#006838');
    $categoryId = (int)($_POST['category_id'] ?? 0);

    if (empty($name)) {
        $error = 'Category name cannot be empty.';
    } else {
        if ($categoryId > 0) {
            $checkStmt = $pdo->prepare("SELECT id FROM categories WHERE name = ? AND id != ?");
            $checkStmt->execute([$name, $categoryId]);
            if ($checkStmt->fetch()) {
                $error = 'Another category with this name already exists.';
            } else {
                $updateStmt = $pdo->prepare("UPDATE categories SET name = ?, description = ?, icon = ?, color_code = ? WHERE id = ?");
                $updateStmt->execute([$name, $description, $icon, $colorCode, $categoryId]);
                setFlashMessage('success', 'Category updated successfully.');
                header('Location: ' . getBaseUrl() . 'admin/categories.php');
                exit;
            }
        } else {
            $checkStmt = $pdo->prepare("SELECT id FROM categories WHERE name = ?");
            $checkStmt->execute([$name]);
            if ($checkStmt->fetch()) {
                $error = 'A category with this name already exists.';
            } else {
                $insertStmt = $pdo->prepare("INSERT INTO categories (name, description, icon, color_code) VALUES (?, ?, ?, ?)");
                $insertStmt->execute([$name, $description, $icon, $colorCode]);
                setFlashMessage('success', 'New category added successfully.');
                header('Location: ' . getBaseUrl() . 'admin/categories.php');
                exit;
            }
        }
    }
}

$categoriesStmt = $pdo->query("
    SELECT c.*, COUNT(e.id) as total_events
    FROM categories c
    LEFT JOIN events e ON c.id = e.category_id
    GROUP BY c.id
    ORDER BY c.name ASC
");
$categories = $categoriesStmt->fetchAll();
?>

<div class="section-header">
    <div>
        <h1 class="section-title"><i class="bi bi-tags"></i> Event Categories</h1>
        <p class="section-subtitle">Organize campus activities into thematic departments and faculty clusters</p>
    </div>
</div>

<?php if (!empty($error)): ?>
    <div class="alert alert-danger">
        <i class="bi bi-exclamation-octagon-fill"></i> <?php echo htmlspecialchars($error); ?>
    </div>
<?php endif; ?>

<div style="display: grid; grid-template-columns: 1fr 2fr; gap: 2rem; align-items: start;">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="bi <?php echo $editCategory ? 'bi-pencil' : 'bi-plus-circle'; ?>"></i>
                <?php echo $editCategory ? 'Edit Category' : 'Add New Category'; ?>
            </h3>
        </div>
        <div class="card-body">
            <form action="<?php echo $baseUrl; ?>admin/categories.php" method="POST">
                <input type="hidden" name="category_id" value="<?php echo $editCategory ? $editCategory['id'] : 0; ?>">

                <div class="form-group">
                    <label class="form-label" for="cat_name">Category Name *</label>
                    <input type="text" name="name" id="cat_name" class="form-control" placeholder="e.g. Computing & AI" required value="<?php echo htmlspecialchars($editCategory['name'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label class="form-label" for="cat_icon">Icon Class (Bootstrap Icons)</label>
                    <select name="icon" id="cat_icon" class="form-control">
                        <?php 
                        $icons = [
                            'bi-laptop' => 'Laptop / Computing',
                            'bi-briefcase' => 'Briefcase / Business',
                            'bi-trophy' => 'Trophy / Sports',
                            'bi-music-note-beamed' => 'Music / Arts',
                            'bi-heart' => 'Heart / Volunteering',
                            'bi-cpu' => 'CPU / Engineering',
                            'bi-lightbulb' => 'Lightbulb / Innovation',
                            'bi-book' => 'Book / Academic',
                            'bi-camera' => 'Camera / Media',
                            'bi-globe' => 'Globe / International'
                        ];
                        $selectedIcon = $editCategory['icon'] ?? 'bi-laptop';
                        foreach ($icons as $val => $label):
                        ?>
                            <option value="<?php echo $val; ?>" <?php echo $selectedIcon === $val ? 'selected' : ''; ?>>
                                <?php echo $label; ?> (<?php echo $val; ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label" for="color_code">Category Accent Color</label>
                    <input type="color" name="color_code" id="color_code" class="form-control" style="height: 42px; padding: 4px;" value="<?php echo htmlspecialchars($editCategory['color_code'] ?? '#006838'); ?>">
                </div>

                <div class="form-group">
                    <label class="form-label" for="cat_desc">Description</label>
                    <textarea name="description" id="cat_desc" class="form-control" rows="3" placeholder="Brief explanation of events belonging to this category..."><?php echo htmlspecialchars($editCategory['description'] ?? ''); ?></textarea>
                </div>

                <div style="display: flex; gap: 0.5rem; margin-top: 1.5rem;">
                    <button type="submit" class="btn btn-primary" style="flex: 1;">
                        <i class="bi bi-check-lg"></i> <?php echo $editCategory ? 'Update' : 'Create'; ?>
                    </button>
                    <?php if ($editCategory): ?>
                        <a href="<?php echo $baseUrl; ?>admin/categories.php" class="btn btn-secondary">Cancel</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><i class="bi bi-list-ul"></i> Existing Categories (<?php echo count($categories); ?>)</h3>
        </div>
        <div class="card-body" style="padding: 0;">
            <div class="table-responsive" style="border: none; box-shadow: none;">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Category</th>
                            <th>Description</th>
                            <th>Color</th>
                            <th>Events</th>
                            <th style="text-align: right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($categories as $c): ?>
                            <tr>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                                        <div style="width: 32px; height: 32px; border-radius: var(--radius-sm); background-color: var(--primary-subtle); color: var(--primary); display: flex; align-items: center; justify-content: center;">
                                            <i class="bi <?php echo htmlspecialchars($c['icon']); ?>"></i>
                                        </div>
                                        <strong><?php echo htmlspecialchars($c['name']); ?></strong>
                                    </div>
                                </td>
                                <td style="font-size: 0.85rem; color: var(--text-muted); max-width: 250px;">
                                    <?php echo htmlspecialchars($c['description'] ?: 'No description provided'); ?>
                                </td>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 0.4rem;">
                                        <span style="display: inline-block; width: 14px; height: 14px; border-radius: 50%; background-color: <?php echo htmlspecialchars($c['color_code']); ?>;"></span>
                                        <code style="font-size: 0.75rem;"><?php echo htmlspecialchars($c['color_code']); ?></code>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge badge-normal"><?php echo $c['total_events']; ?> Events</span>
                                </td>
                                <td style="text-align: right; white-space: nowrap;">
                                    <a href="<?php echo $baseUrl; ?>admin/categories.php?edit=<?php echo $c['id']; ?>" class="btn btn-secondary btn-sm" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <a href="<?php echo $baseUrl; ?>admin/categories.php?delete=<?php echo $c['id']; ?>" class="btn btn-outline-danger btn-sm" title="Delete" onclick="return confirm('Are you sure you want to delete category \'<?php echo htmlspecialchars($c['name']); ?>\'?');">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
