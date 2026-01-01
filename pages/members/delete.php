<?php
require_once '../../config/config.php';
requireLogin();
requireStaff(); // Staff dan Admin bisa hapus anggota

$db = new Database();

// Get member ID
$memberId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Get member data
$member = $db->fetchOne("SELECT * FROM members WHERE id = ?", [$memberId]);

if (!$member) {
    setFlashMessage('error', 'Anggota tidak ditemukan!');
    redirect('/pages/members/index.php');
}

// Check if member has active loans
$activeLoans = $db->fetchOne("SELECT COUNT(*) as count FROM loans WHERE member_id = ? AND status IN ('pending', 'approved')", [$memberId]);

if ($activeLoans['count'] > 0) {
    setFlashMessage('error', 'Tidak dapat menghapus anggota yang masih memiliki peminjaman aktif!');
    redirect('/pages/members/index.php');
}

try {
    // Delete member
    $db->query("DELETE FROM members WHERE id = ?", [$memberId]);
    
    // Also delete linked user if exists
    if ($member['user_id']) {
        $db->query("DELETE FROM users WHERE id = ?", [$member['user_id']]);
    }
    
    setFlashMessage('success', 'Anggota berhasil dihapus!');
} catch (Exception $e) {
    setFlashMessage('error', 'Gagal menghapus anggota. Silakan coba lagi.');
}

redirect('/pages/members/index.php');
