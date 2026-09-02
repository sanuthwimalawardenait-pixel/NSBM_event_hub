<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth_check.php';

requireAdmin();

$currentUser = getCurrentUser();
$flash = getFlashMessage();
$currentAdminScript = basename($_SERVER['PHP_SELF']);
$baseUrl = getBaseUrl();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) . ' | ' : ''; ?>Admin Control Panel - NSBM EventHub</title>
    <link rel="icon" type="image/jpeg" href="<?php echo $baseUrl; ?>assets/images/logo.jpeg">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?php echo $baseUrl; ?>assets/css/style.css">
</head>
<body>
    <header class="navbar">
        <div class="container-fluid navbar-container">
            <div style="display: flex; align-items: center; gap: 1rem;">
                <a href="<?php echo $baseUrl; ?>admin/dashboard.php" class="navbar-brand">
                    <img src="<?php echo $baseUrl; ?>assets/images/logo.jpeg" alt="NSBM EventHub Logo" class="brand-logo-img">
                    <span class="brand-badge" style="background: var(--danger-subtle); color: var(--danger);">Admin</span>
                </a>
            </div>

            <div class="nav-actions">
                <a href="<?php echo $baseUrl; ?>index.php" class="btn btn-secondary btn-sm" target="_blank">
                    <i class="bi bi-box-arrow-up-right"></i> View Public Site
                </a>
                <a href="<?php echo $baseUrl; ?>profile.php" class="btn btn-secondary btn-sm">
                    <i class="bi bi-person-circle"></i> <?php echo htmlspecialchars($currentUser['name']); ?>
                </a>
                <a href="<?php echo $baseUrl; ?>logout.php" class="btn btn-outline-danger btn-sm" title="Sign Out">
                    <i class="bi bi-box-arrow-right"></i> Sign Out
                </a>
            </div>
        </div>
    </header>

    <div class="admin-layout">
        <aside class="admin-sidebar">
            <div class="sidebar-heading">Core Modules</div>
            <a href="<?php echo $baseUrl; ?>admin/dashboard.php" class="sidebar-link <?php echo ($currentAdminScript === 'dashboard.php' || $currentAdminScript === 'index.php') ? 'active' : ''; ?>">
                <i class="bi bi-speedometer2"></i> Dashboard
            </a>
            <a href="<?php echo $baseUrl; ?>admin/events.php" class="sidebar-link <?php echo in_array($currentAdminScript, ['events.php', 'event_add.php', 'event_edit.php']) ? 'active' : ''; ?>">
                <i class="bi bi-calendar-event"></i> Manage Events
            </a>
            <a href="<?php echo $baseUrl; ?>admin/categories.php" class="sidebar-link <?php echo $currentAdminScript === 'categories.php' ? 'active' : ''; ?>">
                <i class="bi bi-tags"></i> Event Categories
            </a>

            <div class="sidebar-heading">Registrations & Reports</div>
            <a href="<?php echo $baseUrl; ?>admin/registrations.php" class="sidebar-link <?php echo $currentAdminScript === 'registrations.php' ? 'active' : ''; ?>">
                <i class="bi bi-people"></i> Event Registrations
            </a>
            <a href="<?php echo $baseUrl; ?>admin/export_participants.php" class="sidebar-link <?php echo $currentAdminScript === 'export_participants.php' ? 'active' : ''; ?>">
                <i class="bi bi-file-earmark-spreadsheet"></i> Export Participant Lists
            </a>

            <div class="sidebar-heading">Communications</div>
            <a href="<?php echo $baseUrl; ?>admin/announcements.php" class="sidebar-link <?php echo $currentAdminScript === 'announcements.php' ? 'active' : ''; ?>">
                <i class="bi bi-megaphone"></i> Manage Announcements
            </a>

            <div style="margin-top: auto; padding-top: 1rem; border-top: 1px solid var(--border-color); font-size: 0.8rem; color: var(--text-light); text-align: center;">
                NSBM EventHub v2.0
            </div>
        </aside>

        <main class="admin-main">
            <?php if ($flash): ?>
                <div class="alert alert-<?php echo htmlspecialchars($flash['type']); ?>">
                    <div style="display: flex; align-items: center; gap: 0.6rem;">
                        <i class="bi <?php 
                            echo $flash['type'] === 'success' ? 'bi-check-circle-fill' : 
                                ($flash['type'] === 'danger' ? 'bi-exclamation-octagon-fill' : 
                                ($flash['type'] === 'warning' ? 'bi-exclamation-triangle-fill' : 'bi-info-circle-fill')); 
                        ?>"></i>
                        <span><?php echo htmlspecialchars($flash['message']); ?></span>
                    </div>
                    <button type="button" class="alert-close" aria-label="Close">&times;</button>
                </div>
            <?php endif; ?>
