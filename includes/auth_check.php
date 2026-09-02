<?php
require_once __DIR__ . '/../config/database.php';

function requireLogin($customMessage = null) {
    if (!isLoggedIn()) {
        $msg = $customMessage ?? 'Please sign in to access this page or register for events.';
        setFlashMessage('warning', $msg);
        
        $currentUri = $_SERVER['REQUEST_URI'] ?? '';
        $redirectParam = !empty($currentUri) ? '?redirect=' . urlencode($currentUri) : '';
        header('Location: ' . getBaseUrl() . 'login.php' . $redirectParam);
        exit;
    }
}

function requireAdmin() {
    requireLogin('Administrator authentication is required to access the admin portal.');
    if (!isAdmin()) {
        setFlashMessage('danger', 'Access denied. Administrator privileges are required to access this module.');
        header('Location: ' . getBaseUrl() . 'index.php');
        exit;
    }
}

function requireStudent() {
    requireLogin('Please sign in with your student credentials to register for campus events.');
    if (!isStudent()) {
        setFlashMessage('warning', 'Event pass registration and personal schedules are reserved for registered student accounts.');
        header('Location: ' . getBaseUrl() . 'index.php');
        exit;
    }
}

function hasRole($role) {
    if (!isLoggedIn()) return false;
    $user = getCurrentUser();
    return $user && ($user['role'] === $role);
}
