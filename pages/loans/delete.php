<?php
require_once '../../config/config.php';
requireLogin();
requireStaff(); // Staff dan Admin bisa hapus peminjaman

$db = new Database();

// Get loan ID
$loanId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Get loan data
$loan = $db->fetchOne("SELECT * FROM loans WHERE id = ?", [$loanId]);

if (!$loan) {
    setFlashMessage('error', 'Peminjaman tidak ditemukan!');
    redirect('/pages/loans/index.php');
}

// Only allow delete for pending or rejected loans
if (!in_array($loan['status'], ['pending', 'rejected'])) {
    setFlashMessage('error', 'Hanya peminjaman dengan status pending atau ditolak yang dapat dihapus!');
    redirect('/pages/loans/index.php');
}

try {
    // Delete loan
    $db->query("DELETE FROM loans WHERE id = ?", [$loanId]);
    
    setFlashMessage('success', 'Data peminjaman berhasil dihapus!');
} catch (Exception $e) {
    setFlashMessage('error', 'Gagal menghapus peminjaman. Silakan coba lagi.');
}

redirect('/pages/loans/index.php');
