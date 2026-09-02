<?php
require_once __DIR__ . '/../config/database.php';
$currentUser = getCurrentUser();
$flash = getFlashMessage();
$currentScript = basename($_SERVER['PHP_SELF']);
$baseUrl = getBaseUrl();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) . ' | ' : ''; ?>NSBM EventHub</title>
    <link rel="icon" type="image/jpeg" href="<?php echo $baseUrl; ?>assets/images/logo.jpeg">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?php echo $baseUrl; ?>assets/css/style.css">
</head>
<body>
    <header class="navbar">
        <div class="container navbar-container">
            <a href="<?php echo $baseUrl; ?>index.php" class="navbar-brand">
                <img src="<?php echo $baseUrl; ?>assets/images/logo.jpeg" alt="NSBM EventHub Logo" class="brand-logo-img">
                <span class="brand-badge">Green Campus</span>
            </a>

            <button class="mobile-toggle" id="mobileMenuToggle" aria-label="Toggle navigation">
                <i class="bi bi-list"></i>
            </button>

            <ul class="nav-menu" id="navbarMenu">
                <li>
                    <a href="<?php echo $baseUrl; ?>index.php" class="nav-link <?php echo ($currentScript === 'index.php' && strpos($_SERVER['REQUEST_URI'], '/admin') === false) ? 'active' : ''; ?>">
                        <i class="bi bi-house"></i> Home
                    </a>
                </li>
                <li>
                    <a href="<?php echo $baseUrl; ?>events.php" class="nav-link <?php echo ($currentScript === 'events.php' && strpos($_SERVER['REQUEST_URI'], '/admin') === false) ? 'active' : ''; ?>">
                        <i class="bi bi-grid"></i> Events
                    </a>
                </li>
                <li>
                    <a href="<?php echo $baseUrl; ?>announcements.php" class="nav-link <?php echo ($currentScript === 'announcements.php' && strpos($_SERVER['REQUEST_URI'], '/admin') === false) ? 'active' : ''; ?>">
                        <i class="bi bi-megaphone"></i> Announcements
                    </a>
                </li>

                <?php if (isLoggedIn() && isStudent()): ?>
                <li>
                    <a href="<?php echo $baseUrl; ?>schedule.php" class="nav-link <?php echo $currentScript === 'schedule.php' ? 'active' : ''; ?>">
                        <i class="bi bi-calendar-check"></i> My Schedule
                    </a>
                </li>
                <?php endif; ?>

                <?php if (isAdmin()): ?>
                <li>
                    <a href="<?php echo $baseUrl; ?>admin/dashboard.php" class="nav-link <?php echo strpos($_SERVER['REQUEST_URI'], '/admin') !== false ? 'active' : ''; ?>">
                        <i class="bi bi-speedometer2"></i> Admin Panel
                    </a>
                </li>
                <?php endif; ?>
            </ul>

            <div class="nav-actions">
                <?php if (isLoggedIn()): ?>
                    <div style="display: flex; align-items: center; gap: 0.6rem;">
                        <a href="<?php echo $baseUrl; ?>profile.php" class="btn btn-secondary btn-sm" title="My Profile">
                            <i class="bi bi-person-circle"></i>
                            <span><?php echo htmlspecialchars($currentUser['name']); ?></span>
                            <span class="badge <?php echo isAdmin() ? 'badge-urgent' : 'badge-registered'; ?>"><?php echo ucfirst($currentUser['role']); ?></span>
                        </a>
                        <a href="<?php echo $baseUrl; ?>logout.php" class="btn btn-outline-danger btn-sm" title="Sign Out">
                            <i class="bi bi-box-arrow-right"></i>
                        </a>
                    </div>
                <?php else: ?>
                    <a href="<?php echo $baseUrl; ?>login.php" class="btn btn-secondary btn-sm">
                        <i class="bi bi-box-arrow-in-right"></i> Sign In
                    </a>
                    <a href="<?php echo $baseUrl; ?>register.php" class="btn btn-primary btn-sm">
                        <i class="bi bi-person-plus"></i> Register
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <main class="main-content">
        <?php if ($flash): ?>
            <div class="container" style="margin-top: 1.5rem;">
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
            </div>
        <?php endif; ?>
