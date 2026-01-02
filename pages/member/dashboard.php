<?php
require_once '../../config/config.php';
requireLogin();
requireMember();

$pageTitle = 'Dashboard Member';
$user = getCurrentUser();
$db = new Database();

// Get member data from members table
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
    
    setFlashMessage('success', 'Selamat datang! Anda sekarang sudah terdaftar sebagai anggota perpustakaan dengan ID: ' . $memberId);
}

// Get statistics
$totalBooksAvailable = $db->fetchOne("SELECT COUNT(*) as count FROM books WHERE stock > 0")['count'];
$myActiveLoans = 0;
$myOverdueLoans = 0;

if ($member) {
    $myActiveLoans = $db->fetchOne("SELECT COUNT(*) as count FROM loans WHERE member_id = ? AND status = 'approved' AND return_date IS NULL", [$member['id']])['count'];
    $myOverdueLoans = $db->fetchOne("SELECT COUNT(*) as count FROM loans WHERE member_id = ? AND status = 'approved' AND return_date IS NULL AND due_date < CURDATE()", [$member['id']])['count'];
}

// Get my active loans
$myLoans = [];
if ($member) {
    $myLoans = $db->fetchAll("
        SELECT l.*, b.title as book_title, b.author, b.isbn
        FROM loans l
        JOIN books b ON l.book_id = b.id
        WHERE l.member_id = ? AND l.status = 'approved' AND l.return_date IS NULL
        ORDER BY l.due_date ASC
        LIMIT 5
    ", [$member['id']]);
}

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
        <a href="<?php echo SITE_URL; ?>/pages/member/dashboard.php" class="nav-item active">
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

        <a href="<?php echo SITE_URL; ?>/pages/member/my-loans.php" class="nav-item">
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
        <h1 class="page-title" style="font-size: 32px; font-weight: 700; margin-bottom: 8px;">Dashboard Member</h1>
        <p style="color: var(--gray-600); margin-bottom: 32px;">Selamat datang di perpustakaan digital Readify! 📚</p>

        <?php 
        $flash = getFlashMessage();
        if ($flash): 
        ?>
            <div class="alert alert-<?php echo $flash['type']; ?>">
                <?php echo $flash['message']; ?>
            </div>
        <?php endif; ?>

        <!-- Statistics Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-card-icon" style="background-color: var(--primary-50);">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color: var(--primary-600);">
                        <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
                        <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>
                    </svg>
                </div>
                <div class="stat-card-value"><?php echo number_format($totalBooksAvailable); ?></div>
                <div class="stat-card-label">Buku Tersedia</div>
            </div>

            <div class="stat-card">
                <div class="stat-card-icon" style="background-color: #fef3c7;">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color: #d97706;">
                        <circle cx="12" cy="12" r="10"></circle>
                        <polyline points="12 6 12 12 16 14"></polyline>
                    </svg>
                </div>
                <div class="stat-card-value"><?php echo number_format($myActiveLoans); ?></div>
                <div class="stat-card-label">Sedang Dipinjam</div>
            </div>

            <div class="stat-card">
                <div class="stat-card-icon" style="background-color: #fee2e2;">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color: #dc2626;">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="12" y1="8" x2="12" y2="12"></line>
                        <line x1="12" y1="16" x2="12.01" y2="16"></line>
                    </svg>
                </div>
                <div class="stat-card-value"><?php echo number_format($myOverdueLoans); ?></div>
                <div class="stat-card-label">Terlambat</div>
            </div>
        </div>

        <!-- My Active Loans -->
        <?php if ($member && !empty($myLoans)): ?>
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title" style="font-size: 18px;">Peminjaman Aktif Saya</h2>
                    <a href="my-loans.php" class="btn btn-sm btn-secondary">Lihat Semua</a>
                </div>

                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID Pinjam</th>
                                <th>Buku</th>
                                <th>Tanggal Pinjam</th>
                                <th>Jatuh Tempo</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($myLoans as $loan): 
                                $isOverdue = strtotime($loan['due_date']) < time();
                            ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($loan['loan_id']); ?></strong></td>
                                    <td>
                                        <?php echo htmlspecialchars($loan['book_title']); ?><br>
                                        <small style="color: var(--gray-500);"><?php echo htmlspecialchars($loan['author']); ?></small>
                                    </td>
                                    <td><?php echo formatDate($loan['loan_date']); ?></td>
                                    <td>
                                        <?php echo formatDate($loan['due_date']); ?>
                                        <?php if ($isOverdue): ?>
                                            <br><span class="badge badge-danger">Terlambat!</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge <?php echo $isOverdue ? 'badge-danger' : 'badge-warning'; ?>">
                                            <?php echo $isOverdue ? 'Terlambat' : 'Dipinjam'; ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>

        <!-- Quick Actions -->
        <div class="card">
            <h2 class="card-title" style="font-size: 18px; margin-bottom: 20px;">Mulai Cari Buku</h2>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px;">
                <a href="books.php" class="btn btn-primary" style="justify-content: center;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="8"></circle>
                        <path d="m21 21-4.35-4.35"></path>
                    </svg>
                    Jelajahi Katalog Buku
                </a>
                <?php if ($member): ?>
                    <a href="my-loans.php" class="btn btn-secondary" style="justify-content: center;">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"></circle>
                            <polyline points="12 6 12 12 16 14"></polyline>
                        </svg>
                        Peminjaman Saya
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Info Box -->
        <div style="padding: 20px; background: var(--primary-50); border-radius: 12px; border-left: 4px solid var(--primary-600);">
            <h3 style="font-size: 16px; font-weight: 600; color: var(--primary-900); margin-bottom: 12px;">
                📖 Cara Meminjam Buku
            </h3>
            <ol style="color: var(--primary-800); line-height: 1.8; padding-left: 20px; margin: 0;">
                <li>Pilih buku dari <a href="books.php" style="color: var(--primary-700); text-decoration: underline;">Katalog Buku</a></li>
                <li>Klik tombol "Pinjam Buku" pada buku yang tersedia</li>
                <li>Konfirmasi peminjaman</li>
                <li>Buku akan tercatat di "Peminjaman Saya"</li>
                <li>Kembalikan buku sebelum jatuh tempo untuk menghindari denda</li>
            </ol>
            <p style="color: var(--primary-700); margin-top: 12px; font-size: 14px;">
                <strong>Catatan:</strong> Denda keterlambatan: <?php echo formatRupiah(FINE_PER_DAY); ?>/hari
            </p>
        </div>
    </div>
</div>

<?php include_once '../../includes/footer.php'; ?>
