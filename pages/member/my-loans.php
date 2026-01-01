<?php
require_once '../../config/config.php';
requireLogin();
requireMember();

$user = getCurrentUser();
$db = new Database();

// Get member data
$member = $db->fetchOne("SELECT * FROM members WHERE email = ?", [$user['email']]);

// Auto-register as member if not exists
if (!$member) {
    // Generate member ID
    $memberCount = $db->fetchOne("SELECT COUNT(*) as count FROM members")['count'];
    $memberId = 'M' . str_pad($memberCount + 1, 3, '0', STR_PAD_LEFT);
    
    // Insert to members table
    $db->query("INSERT INTO members (member_id, name, email, join_date, status) VALUES (?, ?, ?, CURDATE(), 'active')", 
        [$memberId, $user['name'], $user['email']]);
    
    // Reload member data
    $member = $db->fetchOne("SELECT * FROM members WHERE email = ?", [$user['email']]);
}

// Get loans
$status = isset($_GET['status']) ? $_GET['status'] : 'all';

$where = ["l.member_id = ?"];
$params = [$member['id']];

if ($status == 'overdue') {
    // Filter untuk yang terlambat (approved dan lewat due_date)
    $where[] = "l.status = 'approved' AND l.due_date < CURDATE()";
} elseif ($status != 'all') {
    $where[] = "l.status = ?";
    $params[] = $status;
}

$whereClause = implode(' AND ', $where);

$loans = $db->fetchAll("
    SELECT l.*, 
           b.title as book_title, b.author, b.isbn, b.publisher,
           DATEDIFF(CURDATE(), l.due_date) as days_overdue,
           CASE 
               WHEN l.status = 'approved' AND CURDATE() > l.due_date THEN DATEDIFF(CURDATE(), l.due_date) * " . FINE_PER_DAY . "
               ELSE COALESCE(l.fine_amount, 0)
           END as calculated_fine
    FROM loans l
    INNER JOIN books b ON l.book_id = b.id
    WHERE $whereClause
    ORDER BY 
        CASE l.status
            WHEN 'pending' THEN 1
            WHEN 'approved' THEN 2
            WHEN 'rejected' THEN 3
            WHEN 'returned' THEN 4
        END,
        l.created_at DESC
", $params);

// Get statistics
$stats = [
    'pending' => $db->fetchOne("SELECT COUNT(*) as count FROM loans WHERE member_id = ? AND status = 'pending'", [$member['id']])['count'],
    'approved' => $db->fetchOne("SELECT COUNT(*) as count FROM loans WHERE member_id = ? AND status = 'approved'", [$member['id']])['count'],
    'overdue' => $db->fetchOne("SELECT COUNT(*) as count FROM loans WHERE member_id = ? AND status = 'approved' AND due_date < CURDATE()", [$member['id']])['count'],
    'return_requested' => $db->fetchOne("SELECT COUNT(*) as count FROM loans WHERE member_id = ? AND status = 'return_requested'", [$member['id']])['count'],
    'payment_pending' => $db->fetchOne("SELECT COUNT(*) as count FROM loans WHERE member_id = ? AND status = 'payment_pending'", [$member['id']])['count'],
    'returned' => $db->fetchOne("SELECT COUNT(*) as count FROM loans WHERE member_id = ? AND status = 'returned'", [$member['id']])['count'],
    'rejected' => $db->fetchOne("SELECT COUNT(*) as count FROM loans WHERE member_id = ? AND status = 'rejected'", [$member['id']])['count'],
];

$pageTitle = 'Peminjaman Saya';
include_once '../../includes/header.php';
?>

<!-- Member Sidebar -->
<div class="sidebar">
    <div class="sidebar-logo">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
            <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>
        </svg>
        <h1>Readify</h1>
    </div>

    <nav class="sidebar-nav">
        <a href="<?php echo SITE_URL; ?>/pages/member/dashboard.php" class="nav-item">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="3" y="3" width="7" height="7"></rect>
                <rect x="14" y="3" width="7" height="7"></rect>
                <rect x="14" y="14" width="7" height="7"></rect>
                <rect x="3" y="14" width="7" height="7"></rect>
            </svg>
            Dashboard
        </a>

        <a href="<?php echo SITE_URL; ?>/pages/member/books.php" class="nav-item">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
                <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>
            </svg>
            Katalog Buku
        </a>

        <a href="<?php echo SITE_URL; ?>/pages/member/my-loans.php" class="nav-item active">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"></circle>
                <polyline points="12 6 12 12 16 14"></polyline>
            </svg>
            Peminjaman Saya
        </a>

        <a href="<?php echo SITE_URL; ?>/pages/member/history.php" class="nav-item">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline>
            </svg>
            Riwayat
        </a>

        <a href="<?php echo SITE_URL; ?>/pages/member/recommendations.php" class="nav-item">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="3"></circle>
                <path d="M12 2v4m0 12v4M4.93 4.93l2.83 2.83m8.48 8.48l2.83 2.83M2 12h4m12 0h4M4.93 19.07l2.83-2.83m8.48-8.48l2.83-2.83"></path>
            </svg>
            Rekomendasi
        </a>
    </nav>

    <div class="sidebar-user">
        <div class="user-info">
            <div class="user-avatar"><?php echo strtoupper(substr($user['name'], 0, 1)); ?></div>
            <div>
                <div class="user-name"><?php echo htmlspecialchars($user['name']); ?></div>
                <div class="user-role">Member</div>
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
        <h1 class="page-title" style="font-size: 32px; font-weight: 700; margin-bottom: 8px;">Peminjaman Saya</h1>
        <p style="color: var(--gray-600); margin-bottom: 32px;">Kelola dan pantau status peminjaman buku Anda</p>

        <?php 
        $flash = getFlashMessage();
        if ($flash): 
        ?>
            <div class="alert alert-<?php echo $flash['type']; ?>">
                <?php echo $flash['message']; ?>
            </div>
        <?php endif; ?>

        <!-- Status Cards -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(130px, 1fr)); gap: 16px; margin-bottom: 30px;">
            <a href="?status=pending" class="card" style="text-decoration: none; color: inherit; padding: 16px; <?php echo $status == 'pending' ? 'border: 2px solid var(--warning-500);' : ''; ?>">
                <p style="color: var(--gray-600); font-size: 13px; margin-bottom: 4px;">Menunggu</p>
                <p style="font-size: 28px; font-weight: 700; color: var(--warning-600);"><?php echo $stats['pending']; ?></p>
            </a>

            <a href="?status=approved" class="card" style="text-decoration: none; color: inherit; padding: 16px; <?php echo $status == 'approved' ? 'border: 2px solid var(--success-500);' : ''; ?>">
                <p style="color: var(--gray-600); font-size: 13px; margin-bottom: 4px;">Dipinjam</p>
                <p style="font-size: 28px; font-weight: 700; color: var(--success-600);"><?php echo $stats['approved']; ?></p>
            </a>

            <a href="?status=overdue" class="card" style="text-decoration: none; color: inherit; padding: 16px; <?php echo $status == 'overdue' ? 'border: 2px solid var(--danger-500);' : ''; ?>">
                <p style="color: var(--gray-600); font-size: 13px; margin-bottom: 4px;">⚠️ Terlambat</p>
                <p style="font-size: 28px; font-weight: 700; color: var(--danger-600);"><?php echo $stats['overdue']; ?></p>
            </a>

            <a href="?status=return_requested" class="card" style="text-decoration: none; color: inherit; padding: 16px; <?php echo $status == 'return_requested' ? 'border: 2px solid var(--info-500);' : ''; ?>">
                <p style="color: var(--gray-600); font-size: 13px; margin-bottom: 4px;">Pengajuan Pengembalian</p>
                <p style="font-size: 28px; font-weight: 700; color: var(--info-600);"><?php echo $stats['return_requested']; ?></p>
            </a>

            <a href="?status=payment_pending" class="card" style="text-decoration: none; color: inherit; padding: 16px; <?php echo $status == 'payment_pending' ? 'border: 2px solid var(--danger-500);' : ''; ?>">
                <p style="color: var(--gray-600); font-size: 13px; margin-bottom: 4px;">💰 Denda</p>
                <p style="font-size: 28px; font-weight: 700; color: var(--danger-600);"><?php echo $stats['payment_pending']; ?></p>
            </a>

            <a href="?status=returned" class="card" style="text-decoration: none; color: inherit; padding: 16px; <?php echo $status == 'returned' ? 'border: 2px solid var(--primary-500);' : ''; ?>">
                <p style="color: var(--gray-600); font-size: 13px; margin-bottom: 4px;">Dikembalikan</p>
                <p style="font-size: 28px; font-weight: 700; color: var(--primary-600);"><?php echo $stats['returned']; ?></p>
            </a>

            <a href="?status=rejected" class="card" style="text-decoration: none; color: inherit; padding: 16px; <?php echo $status == 'rejected' ? 'border: 2px solid var(--danger-500);' : ''; ?>">
                <p style="color: var(--gray-600); font-size: 13px; margin-bottom: 4px;">Ditolak</p>
                <p style="font-size: 28px; font-weight: 700; color: var(--danger-600);"><?php echo $stats['rejected']; ?></p>
            </a>
        </div>

        <!-- Loans List -->
        <div class="card">
            <div style="margin-bottom: 20px; display: flex; gap: 8px; flex-wrap: wrap;">
                <a href="?" class="btn btn-sm <?php echo $status == 'all' ? 'btn-primary' : 'btn-secondary'; ?>">Semua</a>
                <a href="?status=pending" class="btn btn-sm <?php echo $status == 'pending' ? 'btn-primary' : 'btn-secondary'; ?>">Menunggu</a>
                <a href="?status=approved" class="btn btn-sm <?php echo $status == 'approved' ? 'btn-primary' : 'btn-secondary'; ?>">Dipinjam</a>
                <a href="?status=overdue" class="btn btn-sm <?php echo $status == 'overdue' ? 'btn-danger' : 'btn-secondary'; ?>">⚠️ Terlambat</a>
                <a href="?status=return_requested" class="btn btn-sm <?php echo $status == 'return_requested' ? 'btn-primary' : 'btn-secondary'; ?>">Pengajuan Pengembalian</a>
                <a href="?status=payment_pending" class="btn btn-sm <?php echo $status == 'payment_pending' ? 'btn-primary' : 'btn-secondary'; ?>">💰 Denda</a>
                <a href="?status=returned" class="btn btn-sm <?php echo $status == 'returned' ? 'btn-primary' : 'btn-secondary'; ?>">Dikembalikan</a>
                <a href="?status=rejected" class="btn btn-sm <?php echo $status == 'rejected' ? 'btn-primary' : 'btn-secondary'; ?>">Ditolak</a>
            </div>

            <?php if (empty($loans)): ?>
                <p style="text-align: center; padding: 60px 20px; color: var(--gray-500);">
                    <?php if ($status == 'all'): ?>
                        Anda belum pernah meminjam buku.<br>
                        <a href="books.php" class="btn btn-primary" style="margin-top: 16px;">Jelajahi Katalog</a>
                    <?php else: ?>
                        Tidak ada peminjaman dengan status "<?php echo $status; ?>"
                    <?php endif; ?>
                </p>
            <?php else: ?>
                <div style="display: flex; flex-direction: column; gap: 16px;">
                    <?php foreach ($loans as $loan): ?>
                        <div class="card" style="padding: 20px; border: 1px solid var(--gray-200);">
                            <div style="display: flex; gap: 20px; flex-wrap: wrap;">
                                <!-- Book Info -->
                                <div style="flex: 1; min-width: 250px;">
                                    <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 12px;">
                                        <div>
                                            <h3 style="font-size: 18px; font-weight: 600; margin-bottom: 4px;">
                                                <?php echo htmlspecialchars($loan['book_title']); ?>
                                            </h3>
                                            <p style="color: var(--gray-600); font-size: 14px;">
                                                by <?php echo htmlspecialchars($loan['author']); ?>
                                            </p>
                                        </div>
                                        
                                        <?php
                                        $statusClass = '';
                                        $statusText = '';
                                        $statusIcon = '';
                                        
                                        switch($loan['status']) {
                                            case 'pending':
                                                $statusClass = 'badge-warning';
                                                $statusText = 'Menunggu Persetujuan';
                                                $statusIcon = '⏳';
                                                break;
                                            case 'approved':
                                                if ($loan['days_overdue'] > 0) {
                                                    $statusClass = 'badge-danger';
                                                    $statusText = 'Terlambat ' . $loan['days_overdue'] . ' hari';
                                                    $statusIcon = '⚠️';
                                                } else {
                                                    $statusClass = 'badge-success';
                                                    $statusText = 'Sedang Dipinjam';
                                                    $statusIcon = '✓';
                                                }
                                                break;
                                            case 'return_requested':
                                                $statusClass = 'badge-info';
                                                $statusText = 'Pengajuan Pengembalian';
                                                $statusIcon = '📤';
                                                break;
                                            case 'payment_pending':
                                                $statusClass = 'badge-danger';
                                                $statusText = 'Bayar Denda: Rp ' . number_format($loan['fine_amount'], 0, ',', '.');
                                                $statusIcon = '💰';
                                                break;
                                            case 'returned':
                                                $statusClass = 'badge-info';
                                                $statusText = 'Dikembalikan';
                                                $statusIcon = '✓';
                                                break;
                                            case 'rejected':
                                                $statusClass = 'badge-danger';
                                                $statusText = 'Ditolak';
                                                $statusIcon = '✗';
                                                break;
                                        }
                                        ?>
                                        
                                        <span class="badge <?php echo $statusClass; ?>" style="font-size: 13px;">
                                            <?php echo $statusIcon; ?> <?php echo $statusText; ?>
                                        </span>
                                    </div>

                                    <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px; font-size: 14px;">
                                        <div>
                                            <p style="color: var(--gray-500); margin-bottom: 4px;">ID Peminjaman</p>
                                            <p style="font-weight: 600;"><?php echo htmlspecialchars($loan['loan_id']); ?></p>
                                        </div>
                                        <div>
                                            <p style="color: var(--gray-500); margin-bottom: 4px;">ISBN</p>
                                            <p style="font-weight: 600;"><?php echo htmlspecialchars($loan['isbn']); ?></p>
                                        </div>
                                        <div>
                                            <p style="color: var(--gray-500); margin-bottom: 4px;">Tanggal Pinjam</p>
                                            <p style="font-weight: 600;"><?php echo formatDate($loan['loan_date']); ?></p>
                                        </div>
                                        <div>
                                            <p style="color: var(--gray-500); margin-bottom: 4px;">Jatuh Tempo</p>
                                            <p style="font-weight: 600; <?php echo ($loan['status'] == 'approved' && $loan['days_overdue'] > 0) ? 'color: var(--danger-600);' : ''; ?>">
                                                <?php echo formatDate($loan['due_date']); ?>
                                            </p>
                                        </div>
                                        
                                        <?php if ($loan['status'] == 'returned'): ?>
                                            <div>
                                                <p style="color: var(--gray-500); margin-bottom: 4px;">Tanggal Kembali</p>
                                                <p style="font-weight: 600;"><?php echo formatDate($loan['return_date']); ?></p>
                                            </div>
                                            <div>
                                                <p style="color: var(--gray-500); margin-bottom: 4px;">Denda</p>
                                                <p style="font-weight: 600; <?php echo $loan['calculated_fine'] > 0 ? 'color: var(--danger-600);' : 'color: var(--success-600);'; ?>">
                                                    <?php echo $loan['calculated_fine'] > 0 ? 'Rp ' . number_format($loan['calculated_fine'], 0, ',', '.') : 'Tidak ada'; ?>
                                                </p>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <?php if ($loan['status'] == 'approved' && $loan['calculated_fine'] > 0): ?>
                                            <div style="grid-column: 1 / -1;">
                                                <div style="padding: 12px; background: var(--danger-50); border-left: 4px solid var(--danger-600); border-radius: 4px;">
                                                    <p style="color: var(--danger-800); font-size: 14px; margin: 0;">
                                                        ⚠️ <strong>Denda Keterlambatan:</strong> Rp <?php echo number_format($loan['calculated_fine'], 0, ',', '.'); ?>
                                                        (Rp <?php echo number_format(FINE_PER_DAY, 0, ',', '.'); ?>/hari × <?php echo $loan['days_overdue']; ?> hari)
                                                    </p>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <?php if ($loan['status'] == 'rejected' && $loan['notes']): ?>
                                            <div style="grid-column: 1 / -1;">
                                                <p style="color: var(--gray-500); margin-bottom: 4px;">Alasan Penolakan</p>
                                                <p style="color: var(--danger-600); font-style: italic;"><?php echo htmlspecialchars($loan['notes']); ?></p>
                                            </div>
                                        <?php endif; ?>

                                        <?php if ($loan['status'] == 'return_requested' && $loan['notes']): ?>
                                            <div style="grid-column: 1 / -1;">
                                                <div style="padding: 12px; background: var(--info-50); border-left: 4px solid var(--info-600); border-radius: 4px;">
                                                    <p style="color: var(--info-800); font-size: 14px; margin: 0;">
                                                        📤 <strong>Catatan Pengajuan:</strong> <?php echo htmlspecialchars($loan['notes']); ?>
                                                    </p>
                                                </div>
                                            </div>
                                        <?php endif; ?>

                                        <?php if ($loan['status'] == 'payment_pending'): ?>
                                            <div style="grid-column: 1 / -1;">
                                                <div style="padding: 16px; background: var(--danger-50); border-left: 4px solid var(--danger-600); border-radius: 4px;">
                                                    <p style="color: var(--danger-800); font-size: 14px; margin: 0 0 8px 0;">
                                                        💰 <strong>DENDA KETERLAMBATAN</strong>
                                                    </p>
                                                    <p style="font-size: 24px; font-weight: 700; color: var(--danger-700); margin: 0 0 8px 0;">
                                                        Rp <?php echo number_format($loan['fine_amount'], 0, ',', '.'); ?>
                                                    </p>
                                                    <p style="color: var(--danger-700); font-size: 13px; margin: 0;">
                                                        Silakan bayar denda ke petugas perpustakaan untuk menyelesaikan pengembalian buku.
                                                    </p>
                                                    <?php if ($loan['notes']): ?>
                                                        <p style="color: var(--gray-600); font-size: 12px; margin: 8px 0 0 0; font-style: italic;">
                                                            <?php echo htmlspecialchars($loan['notes']); ?>
                                                        </p>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        <?php endif; ?>

                                        <?php if ($loan['status'] == 'approved'): ?>
                                            <div style="grid-column: 1 / -1; margin-top: 12px; padding-top: 16px; border-top: 1px solid var(--gray-200);">
                                                <button type="button" onclick="openReturnModal(<?php echo $loan['id']; ?>, '<?php echo htmlspecialchars($loan['book_title'], ENT_QUOTES); ?>')" class="btn btn-primary btn-sm">
                                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right: 6px;">
                                                        <polyline points="9 10 4 15 9 20"></polyline>
                                                        <path d="M20 4v7a4 4 0 0 1-4 4H4"></path>
                                                    </svg>
                                                    Ajukan Pengembalian
                                                </button>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Info Box -->
        <?php if ($stats['approved'] > 0): ?>
            <div style="margin-top: 30px; padding: 20px; background: var(--primary-50); border-left: 4px solid var(--primary-600); border-radius: 8px;">
                <h3 style="font-size: 16px; font-weight: 600; margin-bottom: 12px; color: var(--primary-900);">
                    📚 Informasi Peminjaman
                </h3>
                <ul style="margin: 0; padding-left: 20px; color: var(--primary-800);">
                    <li>Batas waktu peminjaman: <strong><?php echo DEFAULT_LOAN_DAYS; ?> hari</strong></li>
                    <li>Denda keterlambatan: <strong>Rp <?php echo number_format(FINE_PER_DAY, 0, ',', '.'); ?> per hari</strong></li>
                    <li>Klik "Ajukan Pengembalian" untuk mengajukan pengembalian buku</li>
                    <li>Kembalikan buku tepat waktu untuk menghindari denda</li>
                </ul>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal Ajukan Pengembalian -->
<div id="returnModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; justify-content: center; align-items: center;">
    <div style="background: white; border-radius: 12px; padding: 24px; max-width: 500px; width: 90%; max-height: 90vh; overflow-y: auto;">
        <h3 style="font-size: 20px; font-weight: 600; margin-bottom: 8px;">Ajukan Pengembalian Buku</h3>
        <p style="color: var(--gray-600); margin-bottom: 20px;">Buku: <strong id="modalBookTitle"></strong></p>
        
        <form action="request-return.php" method="POST">
            <input type="hidden" name="loan_id" id="modalLoanId">
            
            <div class="form-group">
                <label class="form-label">Catatan (Opsional)</label>
                <textarea name="notes" class="form-control" rows="3" placeholder="Tambahkan catatan jika ada..."></textarea>
            </div>
            
            <div style="display: flex; gap: 12px; justify-content: flex-end; margin-top: 20px;">
                <button type="button" onclick="closeReturnModal()" class="btn btn-secondary">Batal</button>
                <button type="submit" class="btn btn-primary">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right: 6px;">
                        <polyline points="9 10 4 15 9 20"></polyline>
                        <path d="M20 4v7a4 4 0 0 1-4 4H4"></path>
                    </svg>
                    Ajukan Pengembalian
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openReturnModal(loanId, bookTitle) {
    document.getElementById('modalLoanId').value = loanId;
    document.getElementById('modalBookTitle').textContent = bookTitle;
    document.getElementById('returnModal').style.display = 'flex';
}

function closeReturnModal() {
    document.getElementById('returnModal').style.display = 'none';
}

// Close modal on outside click
document.getElementById('returnModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeReturnModal();
    }
});
</script>

<?php include_once '../../includes/footer.php'; ?>
