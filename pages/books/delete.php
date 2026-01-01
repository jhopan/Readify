<?php
require_once '../../config/config.php';
requireLogin();
requireStaff(); // Staff dan Admin bisa hapus buku

$db = new Database();

// Get book ID
$bookId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Get book data
$book = $db->fetchOne("SELECT * FROM books WHERE id = ?", [$bookId]);

if (!$book) {
    setFlashMessage('error', 'Buku tidak ditemukan!');
    redirect('/pages/books/index.php');
}

// Check if book has active loans
$activeLoans = $db->fetchOne("SELECT COUNT(*) as count FROM loans WHERE book_id = ? AND status IN ('pending', 'approved')", [$bookId]);

if ($activeLoans['count'] > 0) {
    setFlashMessage('error', 'Tidak dapat menghapus buku yang masih dalam peminjaman aktif!');
    redirect('/pages/books/index.php');
}

try {
    // Delete book
    $db->query("DELETE FROM books WHERE id = ?", [$bookId]);
    
    setFlashMessage('success', 'Buku berhasil dihapus!');
} catch (Exception $e) {
    setFlashMessage('error', 'Gagal menghapus buku. Silakan coba lagi.');
}

redirect('/pages/books/index.php');
