<?php
require_once __DIR__ . '/../config/database.php';

function requireLogin() {
    if (!isLoggedIn()) {
        setFlashMessage('warning', 'Please sign in to access this page.');
        header('Location: ' . getBaseUrl() . 'login.php');
        exit;
    }
}

function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        setFlashMessage('danger', 'Access denied. Administrator privileges are required.');
        header('Location: ' . getBaseUrl() . 'index.php');
        exit;
    }
}

function requireStudent() {
    requireLogin();
    if (!isStudent()) {
        setFlashMessage('warning', 'This feature is only available for registered students.');
        header('Location: ' . getBaseUrl() . 'index.php');
        exit;
    }
}
