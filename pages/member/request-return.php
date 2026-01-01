<?php
require_once '../../config/config.php';
requireLogin();
requireMember();

$user = getCurrentUser();
$db = new Database();

// Get member data
$member = $db->fetchOne("SELECT * FROM members WHERE email = ?", [$user['email']]);

if (!$member) {
    setFlashMessage('error', 'Data anggota tidak ditemukan.');
    redirect('/pages/member/my-loans.php');
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $loanId = $_POST['loan_id'] ?? 0;
    $notes = trim($_POST['notes'] ?? '');
    
    if (!$loanId) {
        setFlashMessage('error', 'ID Peminjaman tidak valid.');
        redirect('/pages/member/my-loans.php');
    }
    
    try {
        // Verify loan belongs to this member and is approved
        $loan = $db->fetchOne("SELECT * FROM loans WHERE id = ? AND member_id = ?", [$loanId, $member['id']]);
        
        if (!$loan) {
            setFlashMessage('error', 'Data peminjaman tidak ditemukan.');
            redirect('/pages/member/my-loans.php');
        }
        
        if ($loan['status'] !== 'approved') {
            setFlashMessage('error', 'Peminjaman ini tidak dalam status dipinjam.');
            redirect('/pages/member/my-loans.php');
        }
        
        // Check if overdue - calculate fine
        $today = date('Y-m-d');
        $dueDate = $loan['due_date'];
        $fine = 0;
        $newStatus = 'return_requested';
        
        if (strtotime($today) > strtotime($dueDate)) {
            // Overdue - need to pay fine first
            $daysOverdue = floor((strtotime($today) - strtotime($dueDate)) / 86400);
            $fine = $daysOverdue * FINE_PER_DAY;
            $newStatus = 'payment_pending'; // Status khusus untuk menunggu pembayaran
            $returnNote = "Pengajuan pengembalian (TERLAMBAT $daysOverdue hari - Denda: Rp " . number_format($fine, 0, ',', '.') . "): " . ($notes ? $notes : "Tidak ada catatan");
            
            $db->query("UPDATE loans SET status = ?, notes = ?, fine_amount = ? WHERE id = ?", 
                [$newStatus, $returnNote, $fine, $loanId]);
            
            setFlashMessage('warning', 'Peminjaman Anda terlambat ' . $daysOverdue . ' hari. Silakan bayar denda Rp ' . number_format($fine, 0, ',', '.') . ' ke petugas untuk menyelesaikan pengembalian.');
        } else {
            // Not overdue - normal return request
            $returnNote = "Pengajuan pengembalian: " . ($notes ? $notes : "Tidak ada catatan");
            
            $db->query("UPDATE loans SET status = ?, notes = ? WHERE id = ?", 
                [$newStatus, $returnNote, $loanId]);
            
            setFlashMessage('success', 'Pengajuan pengembalian berhasil dikirim. Silakan tunggu konfirmasi dari petugas.');
        }
        
        redirect('/pages/member/my-loans.php');
        
    } catch (Exception $e) {
        setFlashMessage('error', 'Gagal mengajukan pengembalian: ' . $e->getMessage());
        redirect('/pages/member/my-loans.php');
    }
} else {
    setFlashMessage('error', 'Method tidak diizinkan.');
    redirect('/pages/member/my-loans.php');
}
?>
