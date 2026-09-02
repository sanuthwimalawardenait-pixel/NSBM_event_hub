<?php
$pageTitle = 'Create New Event';
require_once __DIR__ . '/header.php';
$pdo = getDbConnection();
$currentUser = getCurrentUser();

$categories = $pdo->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();

$error = '';
$title = '';
$categoryId = '';
$clubName = '';
$venue = '';
$eventDate = '';
$startTime = '';
$endTime = '';
$capacity = 100;
$status = 'upcoming';
$description = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $categoryId = (int)($_POST['category_id'] ?? 0);
    $clubName = trim($_POST['club_name'] ?? '');
    $venue = trim($_POST['venue'] ?? '');
    $eventDate = trim($_POST['event_date'] ?? '');
    $startTime = trim($_POST['start_time'] ?? '');
    $endTime = trim($_POST['end_time'] ?? '');
    $capacity = (int)($_POST['capacity'] ?? 100);
    $status = trim($_POST['status'] ?? 'upcoming');
    $description = trim($_POST['description'] ?? '');

    if (empty($title) || empty($categoryId) || empty($clubName) || empty($venue) || empty($eventDate) || empty($startTime) || empty($endTime) || empty($description)) {
        $error = 'Please fill in all mandatory fields.';
    } elseif ($capacity <= 0) {
        $error = 'Capacity must be at least 1 seat.';
    } else {
        $insertStmt = $pdo->prepare("
            INSERT INTO events (category_id, title, club_name, description, venue, event_date, start_time, end_time, capacity, status, created_by)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $insertStmt->execute([
            $categoryId,
            $title,
            $clubName,
            $description,
            $venue,
            $eventDate,
            $startTime,
            $endTime,
            $capacity,
            $status,
            $currentUser['id']
        ]);

        setFlashMessage('success', 'Event "' . htmlspecialchars($title) . '" has been published successfully.');
        header('Location: ' . getBaseUrl() . 'admin/events.php');
        exit;
    }
}
?>

<div class="section-header">
    <div>
        <h1 class="section-title"><i class="bi bi-calendar-plus"></i> Create New Event</h1>
        <p class="section-subtitle">Add a new campus event, workshop, or inter-faculty championship</p>
    </div>
    <a href="<?php echo $baseUrl; ?>admin/events.php" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Back to Events List
    </a>
</div>

<?php if (!empty($error)): ?>
    <div class="alert alert-danger">
        <i class="bi bi-exclamation-octagon-fill"></i> <?php echo htmlspecialchars($error); ?>
    </div>
<?php endif; ?>

<div class="card">
    <div class="card-body">
        <form action="<?php echo $baseUrl; ?>admin/event_add.php" method="POST">
            <div class="form-row">
                <div class="form-col" style="flex: 2;">
                    <div class="form-group">
                        <label class="form-label" for="title">Event Title *</label>
                        <input type="text" name="title" id="title" class="form-control" placeholder="e.g. NSBM CodeBlast Hackathon 2026" required value="<?php echo htmlspecialchars($title); ?>">
                    </div>
                </div>
                <div class="form-col">
                    <div class="form-group">
                        <label class="form-label" for="category_id">Event Category *</label>
                        <select name="category_id" id="category_id" class="form-control" required>
                            <option value="">Select Category</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo $cat['id']; ?>" <?php echo $categoryId == $cat['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cat['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>

            <div class="form-row">
                <div class="form-col">
                    <div class="form-group">
                        <label class="form-label" for="club_name">Organizing Club / Society *</label>
                        <input type="text" name="club_name" id="club_name" class="form-control" placeholder="e.g. FOSS Community NSBM / Rotaract Club" required value="<?php echo htmlspecialchars($clubName); ?>">
                    </div>
                </div>
                <div class="form-col">
                    <div class="form-group">
                        <label class="form-label" for="venue">Event Venue / Location *</label>
                        <input type="text" name="venue" id="venue" class="form-control" placeholder="e.g. Main Auditorium / Lab Complex 04" required value="<?php echo htmlspecialchars($venue); ?>">
                    </div>
                </div>
            </div>

            <div class="form-row">
                <div class="form-col">
                    <div class="form-group">
                        <label class="form-label" for="event_date">Event Date *</label>
                        <input type="date" name="event_date" id="event_date" class="form-control" required value="<?php echo htmlspecialchars($eventDate); ?>">
                    </div>
                </div>
                <div class="form-col">
                    <div class="form-group">
                        <label class="form-label" for="start_time">Start Time *</label>
                        <input type="time" name="start_time" id="start_time" class="form-control" required value="<?php echo htmlspecialchars($startTime); ?>">
                    </div>
                </div>
                <div class="form-col">
                    <div class="form-group">
                        <label class="form-label" for="end_time">End Time *</label>
                        <input type="time" name="end_time" id="end_time" class="form-control" required value="<?php echo htmlspecialchars($endTime); ?>">
                    </div>
                </div>
            </div>

            <div class="form-row">
                <div class="form-col">
                    <div class="form-group">
                        <label class="form-label" for="capacity">Maximum Attendee Capacity *</label>
                        <input type="number" name="capacity" id="capacity" class="form-control" min="1" max="5000" required value="<?php echo htmlspecialchars($capacity); ?>">
                    </div>
                </div>
                <div class="form-col">
                    <div class="form-group">
                        <label class="form-label" for="status">Publication Status *</label>
                        <select name="status" id="status" class="form-control" required>
                            <option value="upcoming" <?php echo $status === 'upcoming' ? 'selected' : ''; ?>>Upcoming</option>
                            <option value="ongoing" <?php echo $status === 'ongoing' ? 'selected' : ''; ?>>Ongoing</option>
                            <option value="completed" <?php echo $status === 'completed' ? 'selected' : ''; ?>>Completed</option>
                            <option value="cancelled" <?php echo $status === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="description">Detailed Description & Agenda *</label>
                <textarea name="description" id="description" class="form-control" rows="5" placeholder="Provide full details regarding event format, rules, registration requirements, guest speakers, and instructions..." required><?php echo htmlspecialchars($description); ?></textarea>
            </div>

            <div style="display: flex; gap: 1rem; margin-top: 1.5rem;">
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="bi bi-check-circle-fill"></i> Save & Publish Event
                </button>
                <a href="<?php echo $baseUrl; ?>admin/events.php" class="btn btn-secondary btn-lg">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
