<?php
require_once '../../config/config.php';
requireLogin();
requireAdmin(); // Only admin can access this page

$db = new Database();

// Get user ID
$userId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Get user data
$user = $db->fetchOne("SELECT * FROM users WHERE id = ?", [$userId]);

if (!$user) {
    setFlashMessage('error', 'User tidak ditemukan!');
    redirect('/pages/users/index.php');
}

// Prevent deleting own account
if ($user['id'] == $_SESSION['user_id']) {
    setFlashMessage('error', 'Anda tidak dapat menghapus akun Anda sendiri!');
    redirect('/pages/users/index.php');
}

// Delete user
try {
    $db->query("DELETE FROM users WHERE id = ?", [$userId]);
    setFlashMessage('success', 'User berhasil dihapus!');
} catch (Exception $e) {
    setFlashMessage('error', 'Gagal menghapus user. User mungkin masih memiliki data terkait.');
}

redirect('/pages/users/index.php');
