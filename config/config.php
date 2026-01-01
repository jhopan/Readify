<?php
// General Configuration
session_start();

// Timezone
date_default_timezone_set('Asia/Jakarta');

// Auto-detect Base URL (works on localhost & hosting)
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$scriptPath = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
$baseUrl = rtrim($protocol . '://' . $host . $scriptPath, '/');

// Remove common paths to get root
$baseUrl = preg_replace('#/(config|pages|includes|assets).*$#', '', $baseUrl);

// Site Configuration
define('SITE_NAME', 'Readify');
define('SITE_URL', $baseUrl);
define('SITE_DESCRIPTION', 'A Smart Digital Library Platform With Personalized Reading Experience');

// Path Configuration
define('BASE_PATH', dirname(__DIR__));
define('UPLOAD_PATH', BASE_PATH . '/assets/uploads/');

// Fine Configuration (per hari dalam Rupiah)
define('FINE_PER_DAY', 1000);

// Loan Configuration (hari)
define('DEFAULT_LOAN_DAYS', 14);

// Pagination
define('ITEMS_PER_PAGE', 10);

// Auto load classes
spl_autoload_register(function ($class) {
    $file = BASE_PATH . '/models/' . $class . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

// Include database
require_once BASE_PATH . '/config/database.php';

// Helper Functions
function redirect($url) {
    header("Location: " . SITE_URL . $url);
    exit();
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        redirect('/pages/auth/login.php');
    }
}

function getCurrentUser() {
    if (isLoggedIn()) {
        $db = new Database();
        $user = $db->fetchOne("SELECT id, name, email, role FROM users WHERE id = ?", [$_SESSION['user_id']]);
        return $user;
    }
    return null;
}

function isAdmin() {
    return isLoggedIn() && isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

function isStaff() {
    return isLoggedIn() && isset($_SESSION['user_role']) && in_array($_SESSION['user_role'], ['admin', 'staff']);
}

function isMember() {
    return isLoggedIn() && isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'member';
}

function requireAdmin() {
    if (!isAdmin()) {
        setFlashMessage('error', 'Akses ditolak! Anda tidak memiliki permission untuk mengakses halaman ini.');
        redirect('/pages/dashboard.php');
    }
}

function requireStaff() {
    if (!isStaff()) {
        setFlashMessage('error', 'Akses ditolak! Halaman ini hanya untuk staff.');
        redirect('/pages/member/dashboard.php');
    }
}

function requireMember() {
    if (!isMember()) {
        setFlashMessage('error', 'Akses ditolak! Halaman ini hanya untuk member.');
        redirect('/pages/dashboard.php');
    }
}

function getUserRole() {
    return isset($_SESSION['user_role']) ? $_SESSION['user_role'] : null;
}

function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

function formatDate($date) {
    return date('d F Y', strtotime($date));
}

function formatRupiah($amount) {
    return 'Rp ' . number_format($amount, 0, ',', '.');
}

function setFlashMessage($type, $message) {
    $_SESSION['flash_type'] = $type;
    $_SESSION['flash_message'] = $message;
}

function getFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $message = [
            'type' => $_SESSION['flash_type'],
            'message' => $_SESSION['flash_message']
        ];
        unset($_SESSION['flash_type']);
        unset($_SESSION['flash_message']);
        return $message;
    }
    return null;
}
