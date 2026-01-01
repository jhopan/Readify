<?php
require_once '../../config/config.php';
requireLogin();
requireStaff(); // Staff dan Admin bisa proses pengembalian

$db = new Database();

// Get loan ID
$loanId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Get loan data
$loan = $db->fetchOne("SELECT * FROM loans WHERE id = ?", [$loanId]);

if (!$loan) {
    setFlashMessage('error', 'Peminjaman tidak ditemukan!');
    redirect('/pages/loans/index.php');
}

// Only allow return for approved loans
if ($loan['status'] !== 'approved') {
    setFlashMessage('error', 'Hanya peminjaman dengan status "Sedang Dipinjam" yang dapat dikembalikan!');
    redirect('/pages/loans/index.php');
}

try {
    $returnDate = date('Y-m-d');
    $fine = 0;
    
    // Calculate fine if overdue
    if (strtotime($returnDate) > strtotime($loan['due_date'])) {
        $daysOverdue = floor((strtotime($returnDate) - strtotime($loan['due_date'])) / 86400);
        $fine = $daysOverdue * FINE_PER_DAY;
    }
    
    // Update loan status to returned
    $db->query("UPDATE loans SET status = 'returned', return_date = ?, fine_amount = ? WHERE id = ?", 
        [$returnDate, $fine, $loanId]);
    
    // Restore book stock
    $db->query("UPDATE books SET stock = stock + 1 WHERE id = ?", [$loan['book_id']]);
    
    $message = 'Buku berhasil dikembalikan!';
    if ($fine > 0) {
        $message .= ' Denda keterlambatan: Rp ' . number_format($fine, 0, ',', '.');
    }
    
    setFlashMessage('success', $message);
} catch (Exception $e) {
    setFlashMessage('error', 'Gagal memproses pengembalian. Silakan coba lagi.');
}

redirect('/pages/loans/index.php?status=returned');
