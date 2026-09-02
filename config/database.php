<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database configuration with environment variable support for cloud/container deployment
$dbHost = getenv('DB_HOST') ?: (getenv('MYSQLHOST') ?: '127.0.0.1');
$dbPort = getenv('DB_PORT') ?: (getenv('MYSQLPORT') ?: '3306');
$dbName = getenv('DB_NAME') ?: (getenv('MYSQLDATABASE') ?: 'nsbm_eventhub');
$dbUser = getenv('DB_USER') ?: (getenv('MYSQLUSER') ?: 'root');
$dbPass = getenv('DB_PASS') ?: (getenv('MYSQLPASSWORD') ?: (getenv('DB_PASSWORD') ?: '12345'));

// Support DATABASE_URL / MYSQL_URL (Railway, Heroku, Render, etc.)
$databaseUrl = getenv('DATABASE_URL') ?: (getenv('MYSQL_URL') ?: (getenv('CLEARDB_DATABASE_URL') ?: getenv('JAWSDB_URL')));
if (!empty($databaseUrl)) {
    $dbParts = parse_url($databaseUrl);
    if ($dbParts && isset($dbParts['host'])) {
        $dbHost = $dbParts['host'];
        $dbPort = $dbParts['port'] ?? 3306;
        $dbUser = $dbParts['user'] ?? 'root';
        $dbPass = $dbParts['pass'] ?? '';
        $dbName = isset($dbParts['path']) ? ltrim($dbParts['path'], '/') : 'nsbm_eventhub';
    }
}

define('DB_HOST', $dbHost);
define('DB_PORT', $dbPort);
define('DB_NAME', $dbName);
define('DB_USER', $dbUser);
define('DB_PASS', $dbPass);

function getDbConnection() {
    static $pdo = null;
    if ($pdo === null) {
        $candidatePasswords = [DB_PASS, '12345', '', 'root', '12345678', 'password', '123456', 'admin'];
        $connected = false;
        $lastError = '';

        foreach (array_unique($candidatePasswords) as $pass) {
            try {
                $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4";
                $options = [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ];
                $pdo = new PDO($dsn, DB_USER, $pass, $options);
                $connected = true;
                break;
            } catch (PDOException $e) {
                $lastError = $e->getMessage();
            }
        }

        if (!$connected) {
            die("Database connection failed: " . $lastError);
        }
    }
    return $pdo;
}

function sanitize($data) {
    if (is_array($data)) {
        return array_map('sanitize', $data);
    }
    return htmlspecialchars(trim((string)$data), ENT_QUOTES, 'UTF-8');
}

function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function isAdmin() {
    return isLoggedIn() && isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

function isStudent() {
    return isLoggedIn() && isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'student';
}

function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    return [
        'id' => $_SESSION['user_id'],
        'name' => $_SESSION['user_name'] ?? '',
        'email' => $_SESSION['user_email'] ?? '',
        'role' => $_SESSION['user_role'] ?? '',
        'student_id' => $_SESSION['student_id'] ?? '',
        'faculty' => $_SESSION['faculty'] ?? ''
    ];
}

function setFlashMessage($type, $message) {
    $_SESSION['flash_message'] = [
        'type' => $type,
        'message' => $message
    ];
}

function getFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $msg = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $msg;
    }
    return null;
}

function formatDate($dateString) {
    if (!$dateString) return '';
    $timestamp = strtotime($dateString);
    return date('M d, Y', $timestamp);
}

function formatTime($timeString) {
    if (!$timeString) return '';
    $timestamp = strtotime($timeString);
    return date('h:i A', $timestamp);
}

function generateTicketCode($eventId, $userId) {
    $prefix = 'NSBM';
    $year = date('y');
    $rand = strtoupper(bin2hex(random_bytes(3)));
    return "{$prefix}-E{$eventId}-U{$userId}-{$rand}";
}

function getBaseUrl() {
    $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
    $dir = str_replace('\\', '/', dirname($scriptName));
    if (strpos($dir, '/admin') !== false) {
        $dir = str_replace('\\', '/', dirname($dir));
    }
    $trimmed = trim($dir, '/');
    return $trimmed === '' ? '/' : '/' . $trimmed . '/';
}

function getStatusBadgeClass($status) {
    switch (strtolower($status)) {
        case 'upcoming':
            return 'badge-upcoming';
        case 'ongoing':
            return 'badge-ongoing';
        case 'completed':
            return 'badge-completed';
        case 'cancelled':
            return 'badge-cancelled';
        case 'registered':
            return 'badge-registered';
        case 'attended':
            return 'badge-attended';
        default:
            return 'badge-default';
    }
}

function getPriorityBadgeClass($priority) {
    switch (strtolower($priority)) {
        case 'urgent':
            return 'badge-urgent';
        case 'important':
            return 'badge-important';
        default:
            return 'badge-normal';
    }
}
