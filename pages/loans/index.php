<?php
require_once '../../config/config.php';
requireLogin();
requireStaff();

$user = getCurrentUser();
$db = new Database();

// Handle actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';
    $loanId = $_POST['loan_id'] ?? 0;
    
    if ($action == 'approve' && $loanId) {
        try {
            // Get loan details
            $loan = $db->fetchOne("SELECT * FROM loans WHERE id = ?", [$loanId]);
            
            if ($loan && $loan['status'] == 'pending') {
                // Check book stock
                $book = $db->fetchOne("SELECT * FROM books WHERE id = ?", [$loan['book_id']]);
                
                if ($book && $book['stock'] > 0) {
                    // Approve loan
                    $db->query("UPDATE loans SET status = 'approved', approved_by = ?, approved_date = NOW() WHERE id = ?", 
                        [$user['id'], $loanId]);
                    
                    // Reduce stock
                    $db->query("UPDATE books SET stock = stock - 1 WHERE id = ?", [$loan['book_id']]);
                    
                    setFlashMessage('success', 'Peminjaman berhasil disetujui.');
                } else {
                    setFlashMessage('error', 'Stok buku tidak tersedia.');
                }
            }
        } catch (Exception $e) {
            setFlashMessage('error', 'Gagal menyetujui peminjaman.');
        }
    } elseif ($action == 'reject' && $loanId) {
        try {
            $reason = $_POST['reason'] ?? 'Tidak ada alasan';
            $db->query("UPDATE loans SET status = 'rejected', approved_by = ?, approved_date = NOW(), notes = ? WHERE id = ?", 
                [$user['id'], $reason, $loanId]);
            
            setFlashMessage('success', 'Peminjaman ditolak.');
        } catch (Exception $e) {
            setFlashMessage('error', 'Gagal menolak peminjaman.');
        }
    } elseif ($action == 'return' && $loanId) {
        try {
            $loan = $db->fetchOne("SELECT * FROM loans WHERE id = ?", [$loanId]);
            
            if ($loan && ($loan['status'] == 'approved' || $loan['status'] == 'return_requested')) {
                $returnDate = date('Y-m-d');
                $fine = 0;
                
                // Calculate fine if overdue
                if (strtotime($returnDate) > strtotime($loan['due_date'])) {
                    $daysOverdue = floor((strtotime($returnDate) - strtotime($loan['due_date'])) / 86400);
                    $fine = $daysOverdue * FINE_PER_DAY;
                }
                
                // Update loan
                $db->query("UPDATE loans SET status = 'returned', return_date = ?, fine_amount = ? WHERE id = ?", 
                    [$returnDate, $fine, $loanId]);
                
                // Restore stock
                $db->query("UPDATE books SET stock = stock + 1 WHERE id = ?", [$loan['book_id']]);
                
                setFlashMessage('success', 'Buku berhasil dikembalikan.' . ($fine > 0 ? ' Denda: Rp ' . number_format($fine, 0, ',', '.') : ''));
            }
        } catch (Exception $e) {
            setFlashMessage('error', 'Gagal memproses pengembalian.');
        }
    } elseif ($action == 'confirm_return' && $loanId) {
        try {
            $loan = $db->fetchOne("SELECT * FROM loans WHERE id = ?", [$loanId]);
            
            if ($loan && $loan['status'] == 'return_requested') {
                $returnDate = date('Y-m-d');
                $fine = 0;
                
                // Calculate fine if overdue
                if (strtotime($returnDate) > strtotime($loan['due_date'])) {
                    $daysOverdue = floor((strtotime($returnDate) - strtotime($loan['due_date'])) / 86400);
                    $fine = $daysOverdue * FINE_PER_DAY;
                }
                
                // Update loan
                $db->query("UPDATE loans SET status = 'returned', return_date = ?, fine_amount = ?, approved_by = ?, approved_date = NOW() WHERE id = ?", 
                    [$returnDate, $fine, $user['id'], $loanId]);
                
                // Restore stock
                $db->query("UPDATE books SET stock = stock + 1 WHERE id = ?", [$loan['book_id']]);
                
                setFlashMessage('success', 'Pengembalian berhasil dikonfirmasi.' . ($fine > 0 ? ' Denda: Rp ' . number_format($fine, 0, ',', '.') : ''));
            }
        } catch (Exception $e) {
            setFlashMessage('error', 'Gagal mengkonfirmasi pengembalian.');
        }
    } elseif ($action == 'confirm_payment' && $loanId) {
        // Konfirmasi pembayaran denda untuk pengembalian terlambat
        try {
            $loan = $db->fetchOne("SELECT * FROM loans WHERE id = ?", [$loanId]);
            
            if ($loan && $loan['status'] == 'payment_pending') {
                $returnDate = date('Y-m-d');
                
                // Update loan - fine sudah dihitung sebelumnya, status jadi returned
                $oldNotes = $loan['notes'] ?? '';
                $newNotes = $oldNotes . " | Denda dibayar dan dikonfirmasi oleh " . $user['name'] . " pada " . date('d/m/Y H:i');
                
                $db->query("UPDATE loans SET status = 'returned', return_date = ?, notes = ?, approved_by = ?, approved_date = NOW() WHERE id = ?", 
                    [$returnDate, $newNotes, $user['id'], $loanId]);
                
                // Restore stock
                $db->query("UPDATE books SET stock = stock + 1 WHERE id = ?", [$loan['book_id']]);
                
                setFlashMessage('success', 'Pembayaran denda dikonfirmasi! Buku "' . $loan['loan_id'] . '" berhasil dikembalikan. Denda: Rp ' . number_format($loan['fine_amount'], 0, ',', '.'));
            }
        } catch (Exception $e) {
            setFlashMessage('error', 'Gagal mengkonfirmasi pembayaran.');
        }
    }
    
    redirect('/pages/loans/index.php');
}

// Get filter - default to 'all' (semua data)
$status = isset($_GET['status']) ? $_GET['status'] : 'all';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Build query
$where = [];
$params = [];

// Only filter by status if not 'all'
if ($status !== 'all') {
    if ($status == 'overdue') {
        // Filter pinjaman terlambat (approved tapi sudah melewati due_date)
        $where[] = "l.status = 'approved' AND l.return_date IS NULL AND l.due_date < CURDATE()";
    } else {
        $where[] = "l.status = ?";
        $params[] = $status;
    }
}

if ($search) {
    $where[] = "(m.name LIKE ? OR m.member_id LIKE ? OR b.title LIKE ? OR l.loan_id LIKE ?)";
    $searchParam = "%$search%";
    $params = array_merge($params, [$searchParam, $searchParam, $searchParam, $searchParam]);
}

$whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

// Get loans
$loans = $db->fetchAll("
    SELECT l.*, 
           m.name as member_name, m.member_id, m.phone, m.email,
           b.title as book_title, b.isbn, b.author,
           u.name as approved_by_name,
           DATEDIFF(CURDATE(), l.due_date) as days_overdue
    FROM loans l
    INNER JOIN members m ON l.member_id = m.id
    INNER JOIN books b ON l.book_id = b.id
    LEFT JOIN users u ON l.approved_by = u.id
    $whereClause
    ORDER BY l.created_at DESC
", $params);

// Get statistics
$stats = [
    'all' => $db->fetchOne("SELECT COUNT(*) as count FROM loans")['count'],
    'pending' => $db->fetchOne("SELECT COUNT(*) as count FROM loans WHERE status = 'pending'")['count'],
    'approved' => $db->fetchOne("SELECT COUNT(*) as count FROM loans WHERE status = 'approved' AND return_date IS NULL")['count'],
    'overdue' => $db->fetchOne("SELECT COUNT(*) as count FROM loans WHERE status = 'approved' AND return_date IS NULL AND due_date < CURDATE()")['count'],
    'return_requested' => $db->fetchOne("SELECT COUNT(*) as count FROM loans WHERE status = 'return_requested'")['count'],
    'payment_pending' => $db->fetchOne("SELECT COUNT(*) as count FROM loans WHERE status = 'payment_pending'")['count'],
    'returned' => $db->fetchOne("SELECT COUNT(*) as count FROM loans WHERE status = 'returned'")['count'],
    'rejected' => $db->fetchOne("SELECT COUNT(*) as count FROM loans WHERE status = 'rejected'")['count'],
];

$pageTitle = 'Manajemen Peminjaman';
include_once '../../includes/header.php';
?>

<!-- Sidebar -->
<div class="sidebar">
    <div class="sidebar-logo">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
            <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>
        </svg>
        <h1>Readify</h1>
    </div>

    <nav class="sidebar-nav">
        <a href="<?php echo SITE_URL; ?>/pages/dashboard.php" class="nav-item">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="3" y="3" width="7" height="7"></rect>
                <rect x="14" y="3" width="7" height="7"></rect>
                <rect x="14" y="14" width="7" height="7"></rect>
                <rect x="3" y="14" width="7" height="7"></rect>
            </svg>
            Dashboard
        </a>

        <a href="<?php echo SITE_URL; ?>/pages/books/index.php" class="nav-item">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
                <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>
            </svg>
            Buku
        </a>

        <a href="<?php echo SITE_URL; ?>/pages/members/index.php" class="nav-item">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                <circle cx="9" cy="7" r="4"></circle>
                <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
            </svg>
            Anggota
        </a>

        <a href="<?php echo SITE_URL; ?>/pages/loans/index.php" class="nav-item active">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                <polyline points="14 2 14 8 20 8"></polyline>
                <line x1="16" y1="13" x2="8" y2="13"></line>
                <line x1="16" y1="17" x2="8" y2="17"></line>
                <polyline points="10 9 9 9 8 9"></polyline>
            </svg>
            Peminjaman
        </a>

        <?php if (isAdmin()): ?>
        <a href="<?php echo SITE_URL; ?>/pages/users/index.php" class="nav-item">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                <circle cx="8.5" cy="7" r="4"></circle>
                <polyline points="17 11 19 13 23 9"></polyline>
            </svg>
            Pengguna
        </a>
        <?php endif; ?>
    </nav>

    <div class="sidebar-user">
        <div class="user-info">
            <div class="user-avatar"><?php echo strtoupper(substr($user['name'], 0, 1)); ?></div>
            <div>
                <div class="user-name"><?php echo htmlspecialchars($user['name']); ?></div>
                <div class="user-role"><?php echo ucfirst($user['role']); ?></div>
            </div>
        </div>
        <a href="<?php echo SITE_URL; ?>/pages/auth/logout.php" class="btn btn-danger btn-sm" style="width: 100%;">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                <polyline points="16 17 21 12 16 7"></polyline>
                <line x1="21" y1="12" x2="9" y2="12"></line>
            </svg>
            Logout
        </a>
    </div>
</div>

<div class="main-content">
    <div class="container">
        <div class="card-header" style="margin-bottom: 20px;">
            <h1 class="page-title" style="margin: 0;">Manajemen Peminjaman</h1>
            <a href="create.php" class="btn btn-primary">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="12" y1="5" x2="12" y2="19"></line>
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                </svg>
                Tambah Peminjaman
            </a>
        </div>
        
        <?php 
        $flash = getFlashMessage();
        if ($flash): 
        ?>
            <div class="alert alert-<?php echo $flash['type']; ?>">
                <?php echo $flash['message']; ?>
            </div>
        <?php endif; ?>

        <!-- Statistics Cards -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 20px; margin-bottom: 30px;">
            <a href="?status=all" class="card" style="text-decoration: none; color: inherit; padding: 20px; <?php echo $status == 'all' ? 'border: 2px solid var(--gray-500);' : ''; ?>">
                <div style="display: flex; justify-content: space-between; align-items: start;">
                    <div>
                        <p style="color: var(--gray-600); font-size: 14px; margin-bottom: 8px;">Semua Data</p>
                        <p style="font-size: 32px; font-weight: 700; color: var(--gray-700);"><?php echo $stats['all']; ?></p>
                    </div>
                    <div style="width: 48px; height: 48px; background: var(--gray-100); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color: var(--gray-600);">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                            <polyline points="14 2 14 8 20 8"></polyline>
                            <line x1="16" y1="13" x2="8" y2="13"></line>
                            <line x1="16" y1="17" x2="8" y2="17"></line>
                        </svg>
                    </div>
                </div>
            </a>

            <a href="?status=pending" class="card" style="text-decoration: none; color: inherit; padding: 20px; <?php echo $status == 'pending' ? 'border: 2px solid var(--warning-500);' : ''; ?>">
                <div style="display: flex; justify-content: space-between; align-items: start;">
                    <div>
                        <p style="color: var(--gray-600); font-size: 14px; margin-bottom: 8px;">Menunggu Persetujuan</p>
                        <p style="font-size: 32px; font-weight: 700; color: var(--warning-600);"><?php echo $stats['pending']; ?></p>
                    </div>
                    <div style="width: 48px; height: 48px; background: var(--warning-100); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color: var(--warning-600);">
                            <circle cx="12" cy="12" r="10"></circle>
                            <polyline points="12 6 12 12 16 14"></polyline>
                        </svg>
                    </div>
                </div>
            </a>

            <a href="?status=approved" class="card" style="text-decoration: none; color: inherit; padding: 20px; <?php echo $status == 'approved' ? 'border: 2px solid var(--success-500);' : ''; ?>">
                <div style="display: flex; justify-content: space-between; align-items: start;">
                    <div>
                        <p style="color: var(--gray-600); font-size: 14px; margin-bottom: 8px;">Sedang Dipinjam</p>
                        <p style="font-size: 32px; font-weight: 700; color: var(--success-600);"><?php echo $stats['approved']; ?></p>
                    </div>
                    <div style="width: 48px; height: 48px; background: var(--success-100); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color: var(--success-600);">
                            <polyline points="20 6 9 17 4 12"></polyline>
                        </svg>
                    </div>
                </div>
            </a>

            <a href="?status=overdue" class="card" style="text-decoration: none; color: inherit; padding: 20px; <?php echo $status == 'overdue' ? 'border: 2px solid var(--danger-500);' : ''; ?>">
                <div style="display: flex; justify-content: space-between; align-items: start;">
                    <div>
                        <p style="color: var(--gray-600); font-size: 14px; margin-bottom: 8px;">⚠️ Terlambat</p>
                        <p style="font-size: 32px; font-weight: 700; color: var(--danger-600);"><?php echo $stats['overdue']; ?></p>
                    </div>
                    <div style="width: 48px; height: 48px; background: var(--danger-100); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color: var(--danger-600);">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="12" y1="8" x2="12" y2="12"></line>
                            <line x1="12" y1="16" x2="12.01" y2="16"></line>
                        </svg>
                    </div>
                </div>
            </a>

            <a href="?status=return_requested" class="card" style="text-decoration: none; color: inherit; padding: 20px; <?php echo $status == 'return_requested' ? 'border: 2px solid var(--info-500);' : ''; ?>">
                <div style="display: flex; justify-content: space-between; align-items: start;">
                    <div>
                        <p style="color: var(--gray-600); font-size: 14px; margin-bottom: 8px;">Pengajuan Pengembalian</p>
                        <p style="font-size: 32px; font-weight: 700; color: var(--info-600);"><?php echo $stats['return_requested']; ?></p>
                    </div>
                    <div style="width: 48px; height: 48px; background: var(--info-100); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color: var(--info-600);">
                            <polyline points="9 10 4 15 9 20"></polyline>
                            <path d="M20 4v7a4 4 0 0 1-4 4H4"></path>
                        </svg>
                    </div>
                </div>
            </a>

            <a href="?status=payment_pending" class="card" style="text-decoration: none; color: inherit; padding: 20px; <?php echo $status == 'payment_pending' ? 'border: 2px solid var(--danger-500);' : ''; ?>">
                <div style="display: flex; justify-content: space-between; align-items: start;">
                    <div>
                        <p style="color: var(--gray-600); font-size: 14px; margin-bottom: 8px;">💰 Denda</p>
                        <p style="font-size: 32px; font-weight: 700; color: var(--danger-600);"><?php echo $stats['payment_pending']; ?></p>
                    </div>
                    <div style="width: 48px; height: 48px; background: var(--danger-100); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color: var(--danger-600);">
                            <line x1="12" y1="1" x2="12" y2="23"></line>
                            <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                        </svg>
                    </div>
                </div>
            </a>

            <a href="?status=returned" class="card" style="text-decoration: none; color: inherit; padding: 20px; <?php echo $status == 'returned' ? 'border: 2px solid var(--primary-500);' : ''; ?>">
                <div style="display: flex; justify-content: space-between; align-items: start;">
                    <div>
                        <p style="color: var(--gray-600); font-size: 14px; margin-bottom: 8px;">Dikembalikan</p>
                        <p style="font-size: 32px; font-weight: 700; color: var(--primary-600);"><?php echo $stats['returned']; ?></p>
                    </div>
                    <div style="width: 48px; height: 48px; background: var(--primary-100); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color: var(--primary-600);">
                            <polyline points="23 4 23 10 17 10"></polyline>
                            <path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"></path>
                        </svg>
                    </div>
                </div>
            </a>

            <a href="?status=rejected" class="card" style="text-decoration: none; color: inherit; padding: 20px; <?php echo $status == 'rejected' ? 'border: 2px solid var(--danger-500);' : ''; ?>">
                <div style="display: flex; justify-content: space-between; align-items: start;">
                    <div>
                        <p style="color: var(--gray-600); font-size: 14px; margin-bottom: 8px;">Ditolak</p>
                        <p style="font-size: 32px; font-weight: 700; color: var(--danger-600);"><?php echo $stats['rejected']; ?></p>
                    </div>
                    <div style="width: 48px; height: 48px; background: var(--danger-100); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color: var(--danger-600);">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="15" y1="9" x2="9" y2="15"></line>
                            <line x1="9" y1="9" x2="15" y2="15"></line>
                        </svg>
                    </div>
                </div>
            </a>
        </div>

        <!-- Search & Filter -->
        <div class="card">
            <form method="GET" style="display: flex; gap: 12px; margin-bottom: 24px;">
                <input type="hidden" name="status" value="<?php echo htmlspecialchars($status); ?>">
                <div style="flex: 1; position: relative;">
                    <input type="text" name="search" class="form-control" 
                           placeholder="Cari anggota, buku, ID peminjaman..." 
                           value="<?php echo htmlspecialchars($search); ?>">
                </div>
                <button type="submit" class="btn btn-primary">Cari</button>
                <?php if ($search): ?>
                    <a href="?status=<?php echo $status; ?>" class="btn btn-secondary">Reset</a>
                <?php endif; ?>
            </form>

            <!-- Loans Table -->
            <?php if (empty($loans)): ?>
                <p style="text-align: center; padding: 60px 20px; color: var(--gray-500);">
                    <?php 
                    $statusLabels = [
                        'all' => 'Tidak ada data peminjaman',
                        'pending' => 'Tidak ada data peminjaman dengan status "Menunggu Persetujuan"',
                        'approved' => 'Tidak ada data peminjaman dengan status "Sedang Dipinjam"',
                        'overdue' => 'Tidak ada data peminjaman yang terlambat',
                        'return_requested' => 'Tidak ada data peminjaman dengan status "Pengajuan Pengembalian"',
                        'payment_pending' => 'Tidak ada data peminjaman dengan status "Denda"',
                        'returned' => 'Tidak ada data peminjaman dengan status "Dikembalikan"',
                        'rejected' => 'Tidak ada data peminjaman dengan status "Ditolak"'
                    ];
                    echo $statusLabels[$status] ?? 'Tidak ada data peminjaman';
                    ?>
                </p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Anggota</th>
                                <th>Buku</th>
                                <th>Tgl Pinjam</th>
                                <th>Jatuh Tempo</th>
                                <th>Status</th>
                                <?php if ($status == 'returned' || $status == 'payment_pending' || $status == 'all'): ?>
                                    <th>Tgl Kembali</th>
                                    <th>Denda</th>
                                <?php endif; ?>
                                <?php if ($status == 'pending' || $status == 'approved' || $status == 'return_requested' || $status == 'payment_pending' || $status == 'all'): ?>
                                    <th>Aksi</th>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($loans as $loan): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($loan['loan_id']); ?></strong>
                                    </td>
                                    <td>
                                        <div>
                                            <strong><?php echo htmlspecialchars($loan['member_name']); ?></strong><br>
                                            <small style="color: var(--gray-600);"><?php echo htmlspecialchars($loan['member_id']); ?></small>
                                        </div>
                                    </td>
                                    <td>
                                        <div>
                                            <strong><?php echo htmlspecialchars($loan['book_title']); ?></strong><br>
                                            <small style="color: var(--gray-600);">by <?php echo htmlspecialchars($loan['author']); ?></small>
                                        </div>
                                    </td>
                                    <td><?php echo formatDate($loan['loan_date']); ?></td>
                                    <td><?php echo formatDate($loan['due_date']); ?></td>
                                    <td>
                                        <?php 
                                        // Check if this loan is overdue (approved but past due date)
                                        $isOverdue = ($loan['status'] == 'approved' && $loan['days_overdue'] > 0);
                                        
                                        if ($isOverdue) {
                                            echo '<span class="badge badge-danger">⚠️ Terlambat ' . $loan['days_overdue'] . ' hari</span>';
                                        } else {
                                            $statusBadge = [
                                                'pending' => '<span class="badge badge-warning">Menunggu</span>',
                                                'approved' => '<span class="badge badge-success">Dipinjam</span>',
                                                'return_requested' => '<span class="badge badge-info">Pengajuan Pengembalian</span>',
                                                'payment_pending' => '<span class="badge badge-danger">💰 Denda: Rp ' . number_format($loan['fine_amount'], 0, ',', '.') . '</span>',
                                                'returned' => '<span class="badge badge-primary">Dikembalikan</span>',
                                                'rejected' => '<span class="badge badge-danger">Ditolak</span>'
                                            ];
                                            echo $statusBadge[$loan['status']] ?? '<span class="badge">' . ucfirst($loan['status']) . '</span>';
                                        }
                                        ?>
                                    </td>
                                    
                                    <?php if ($status == 'returned' || $status == 'payment_pending' || $status == 'all'): ?>
                                        <td><?php echo $loan['return_date'] ? formatDate($loan['return_date']) : '-'; ?></td>
                                        <td>
                                            <?php if ($loan['fine_amount'] > 0): ?>
                                                <span class="badge badge-danger">Rp <?php echo number_format($loan['fine_amount'], 0, ',', '.'); ?></span>
                                            <?php else: ?>
                                                <span class="badge badge-success">Tidak ada</span>
                                            <?php endif; ?>
                                        </td>
                                    <?php endif; ?>
                                    
                                    
                                    
                                    <?php if ($status == 'pending' || $status == 'approved' || $status == 'return_requested' || $status == 'payment_pending' || $status == 'all'): ?>
                                        <td>
                                            <?php if ($loan['status'] == 'pending'): ?>
                                                <div style="display: flex; gap: 8px;">
                                                    <form method="POST" style="display: inline;">
                                                        <input type="hidden" name="action" value="approve">
                                                        <input type="hidden" name="loan_id" value="<?php echo $loan['id']; ?>">
                                                        <button type="submit" class="btn btn-success btn-sm" 
                                                                onclick="return confirm('Setujui peminjaman ini?')">
                                                            ✓ Setuju
                                                        </button>
                                                    </form>
                                                    <button class="btn btn-danger btn-sm" 
                                                            onclick="showRejectModal(<?php echo $loan['id']; ?>)">
                                                        ✗ Tolak
                                                    </button>
                                                </div>
                                            <?php elseif ($loan['status'] == 'approved'): ?>
                                                <form method="POST" style="display: inline;">
                                                    <input type="hidden" name="action" value="return">
                                                    <input type="hidden" name="loan_id" value="<?php echo $loan['id']; ?>">
                                                    <button type="submit" class="btn btn-primary btn-sm" 
                                                            onclick="return confirm('Proses pengembalian buku ini?')">
                                                        Kembalikan
                                                    </button>
                                                </form>
                                            <?php elseif ($loan['status'] == 'return_requested'): ?>
                                                <form method="POST" style="display: inline;">
                                                    <input type="hidden" name="action" value="confirm_return">
                                                    <input type="hidden" name="loan_id" value="<?php echo $loan['id']; ?>">
                                                    <button type="submit" class="btn btn-success btn-sm" 
                                                            onclick="return confirm('Konfirmasi pengembalian buku ini?')">
                                                        ✓ Konfirmasi
                                                    </button>
                                                </form>
                                            <?php elseif ($loan['status'] == 'payment_pending'): ?>
                                                <form method="POST" style="display: inline;">
                                                    <input type="hidden" name="action" value="confirm_payment">
                                                    <input type="hidden" name="loan_id" value="<?php echo $loan['id']; ?>">
                                                    <button type="submit" class="btn btn-success btn-sm" 
                                                            onclick="return confirm('Konfirmasi pembayaran denda Rp <?php echo number_format($loan['fine_amount'], 0, ',', '.'); ?>? Buku akan dikembalikan.')">
                                                        💰 Konfirmasi Bayar
                                                    </button>
                                                </form>
                                            <?php else: ?>
                                                <span style="color: var(--gray-400);">-</span>
                                            <?php endif; ?>
                                        </td>
                                    <?php endif; ?>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Reject Modal -->
<div id="rejectModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
    <div class="card" style="width: 90%; max-width: 500px; margin: 20px;">
        <h3 style="margin-bottom: 20px;">Tolak Peminjaman</h3>
        <form method="POST" id="rejectForm">
            <input type="hidden" name="action" value="reject">
            <input type="hidden" name="loan_id" id="rejectLoanId">
            
            <div class="form-group">
                <label>Alasan Penolakan *</label>
                <textarea name="reason" class="form-control" rows="4" required 
                          placeholder="Masukkan alasan penolakan..."></textarea>
            </div>
            
            <div style="display: flex; gap: 12px; justify-content: flex-end; margin-top: 24px;">
                <button type="button" class="btn btn-secondary" onclick="closeRejectModal()">Batal</button>
                <button type="submit" class="btn btn-danger">Tolak Peminjaman</button>
            </div>
        </form>
    </div>
</div>

<script>
function showRejectModal(loanId) {
    document.getElementById('rejectLoanId').value = loanId;
    document.getElementById('rejectModal').style.display = 'flex';
}

function closeRejectModal() {
    document.getElementById('rejectModal').style.display = 'none';
    document.getElementById('rejectForm').reset();
}

// Close modal on outside click
document.getElementById('rejectModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeRejectModal();
    }
});
</script>

<?php include_once '../../includes/footer.php'; ?>
