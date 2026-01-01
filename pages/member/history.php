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

// Get search and filter
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$year = isset($_GET['year']) ? (int)$_GET['year'] : 0;
$month = isset($_GET['month']) ? (int)$_GET['month'] : 0;

// Build query for returned books only
$where = ["l.member_id = ?", "l.status = 'returned'"];
$params = [$member['id']];

if ($search) {
    $where[] = "(b.title LIKE ? OR b.author LIKE ? OR b.isbn LIKE ?)";
    $searchParam = "%$search%";
    $params = array_merge($params, [$searchParam, $searchParam, $searchParam]);
}

if ($year > 0) {
    $where[] = "YEAR(l.return_date) = ?";
    $params[] = $year;
}

if ($month > 0) {
    $where[] = "MONTH(l.return_date) = ?";
    $params[] = $month;
}

$whereClause = implode(' AND ', $where);

// Get history
$history = $db->fetchAll("
    SELECT l.*, 
           b.title as book_title, b.author, b.isbn, b.publisher, b.year as book_year,
           c.name as category_name,
           DATEDIFF(l.return_date, l.due_date) as days_late,
           COALESCE(l.fine_amount, 0) as fine_amount
    FROM loans l
    INNER JOIN books b ON l.book_id = b.id
    LEFT JOIN categories c ON b.category_id = c.id
    WHERE $whereClause
    ORDER BY l.return_date DESC, l.created_at DESC
", $params);

// Get statistics
$stats = [
    'total_books' => count($history),
    'total_fine' => $db->fetchOne("SELECT SUM(COALESCE(fine_amount, 0)) as total FROM loans WHERE member_id = ? AND status = 'returned'", [$member['id']])['total'] ?? 0,
    'on_time' => $db->fetchOne("SELECT COUNT(*) as count FROM loans WHERE member_id = ? AND status = 'returned' AND return_date <= due_date", [$member['id']])['count'],
    'late' => $db->fetchOne("SELECT COUNT(*) as count FROM loans WHERE member_id = ? AND status = 'returned' AND return_date > due_date", [$member['id']])['count'],
];

// Get available years for filter
$years = $db->fetchAll("
    SELECT DISTINCT YEAR(return_date) as year 
    FROM loans 
    WHERE member_id = ? AND status = 'returned' AND return_date IS NOT NULL
    ORDER BY year DESC
", [$member['id']]);

// Months array
$months = [
    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
    5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
    9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
];

$pageTitle = 'Riwayat Peminjaman';
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

        <a href="<?php echo SITE_URL; ?>/pages/member/my-loans.php" class="nav-item">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"></circle>
                <polyline points="12 6 12 12 16 14"></polyline>
            </svg>
            Peminjaman Saya
        </a>

        <a href="<?php echo SITE_URL; ?>/pages/member/history.php" class="nav-item active">
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
        <h1 class="page-title" style="font-size: 32px; font-weight: 700; margin-bottom: 8px;">Riwayat Peminjaman</h1>
        <p style="color: var(--gray-600); margin-bottom: 32px;">Lihat semua buku yang pernah Anda pinjam</p>

        <?php 
        $flash = getFlashMessage();
        if ($flash): 
        ?>
            <div class="alert alert-<?php echo $flash['type']; ?>">
                <?php echo $flash['message']; ?>
            </div>
        <?php endif; ?>

        <!-- Statistics Cards -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 30px;">
            <div class="card" style="padding: 20px;">
                <div style="display: flex; align-items: center; gap: 16px;">
                    <div style="width: 48px; height: 48px; background: var(--primary-100); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color: var(--primary-600);">
                            <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
                            <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>
                        </svg>
                    </div>
                    <div>
                        <p style="color: var(--gray-600); font-size: 14px; margin-bottom: 4px;">Total Buku</p>
                        <p style="font-size: 28px; font-weight: 700; color: var(--primary-600);"><?php echo $stats['total_books']; ?></p>
                    </div>
                </div>
            </div>

            <div class="card" style="padding: 20px;">
                <div style="display: flex; align-items: center; gap: 16px;">
                    <div style="width: 48px; height: 48px; background: var(--success-100); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color: var(--success-600);">
                            <polyline points="20 6 9 17 4 12"></polyline>
                        </svg>
                    </div>
                    <div>
                        <p style="color: var(--gray-600); font-size: 14px; margin-bottom: 4px;">Tepat Waktu</p>
                        <p style="font-size: 28px; font-weight: 700; color: var(--success-600);"><?php echo $stats['on_time']; ?></p>
                    </div>
                </div>
            </div>

            <div class="card" style="padding: 20px;">
                <div style="display: flex; align-items: center; gap: 16px;">
                    <div style="width: 48px; height: 48px; background: var(--danger-100); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color: var(--danger-600);">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="12" y1="8" x2="12" y2="12"></line>
                            <line x1="12" y1="16" x2="12.01" y2="16"></line>
                        </svg>
                    </div>
                    <div>
                        <p style="color: var(--gray-600); font-size: 14px; margin-bottom: 4px;">Terlambat</p>
                        <p style="font-size: 28px; font-weight: 700; color: var(--danger-600);"><?php echo $stats['late']; ?></p>
                    </div>
                </div>
            </div>

            <div class="card" style="padding: 20px;">
                <div style="display: flex; align-items: center; gap: 16px;">
                    <div style="width: 48px; height: 48px; background: var(--warning-100); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color: var(--warning-600);">
                            <line x1="12" y1="1" x2="12" y2="23"></line>
                            <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                        </svg>
                    </div>
                    <div>
                        <p style="color: var(--gray-600); font-size: 14px; margin-bottom: 4px;">Total Denda</p>
                        <p style="font-size: 20px; font-weight: 700; color: var(--warning-600);">RP <?php echo number_format($stats['total_fine'], 0, ',', '.'); ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Search & Filter -->
        <div class="card" style="margin-bottom: 24px;">
            <form method="GET" style="display: flex; gap: 12px; flex-wrap: wrap;">
                <div style="flex: 1; min-width: 200px;">
                    <input type="text" name="search" class="form-control" 
                           placeholder="Cari judul, pengarang, ISBN..." 
                           value="<?php echo htmlspecialchars($search); ?>">
                </div>
                
                <select name="year" class="form-control" style="max-width: 150px;">
                    <option value="">Semua Tahun</option>
                    <?php foreach ($years as $y): ?>
                        <option value="<?php echo $y['year']; ?>" <?php echo $year == $y['year'] ? 'selected' : ''; ?>>
                            <?php echo $y['year']; ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <select name="month" class="form-control" style="max-width: 150px;">
                    <option value="">Semua Bulan</option>
                    <?php foreach ($months as $m => $monthName): ?>
                        <option value="<?php echo $m; ?>" <?php echo $month == $m ? 'selected' : ''; ?>>
                            <?php echo $monthName; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                
                <button type="submit" class="btn btn-primary">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="8"></circle>
                        <path d="m21 21-4.35-4.35"></path>
                    </svg>
                    Cari
                </button>
                
                <?php if ($search || $year || $month): ?>
                    <a href="?" class="btn btn-secondary">Reset</a>
                <?php endif; ?>
            </form>
        </div>

        <!-- History List -->
        <div class="card">
            <?php if (empty($history)): ?>
                <div style="text-align: center; padding: 60px 20px; color: var(--gray-500);">
                    <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-bottom: 16px; opacity: 0.5;">
                        <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline>
                    </svg>
                    <p style="font-size: 18px; margin-bottom: 8px;">Belum ada riwayat peminjaman</p>
                    <p>Buku yang sudah Anda kembalikan akan muncul di sini</p>
                    <a href="books.php" class="btn btn-primary" style="margin-top: 20px;">
                        Jelajahi Katalog Buku
                    </a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th style="width: 40%;">Buku</th>
                                <th>Tanggal Pinjam</th>
                                <th>Tanggal Kembali</th>
                                <th>Status</th>
                                <th>Denda</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($history as $item): ?>
                                <tr>
                                    <td>
                                        <div style="display: flex; gap: 12px; align-items: start;">
                                            <?php 
                                            // Generate random gradient for book thumbnail
                                            $colors = [
                                                ['from' => '#667eea', 'to' => '#764ba2'],
                                                ['from' => '#f093fb', 'to' => '#f5576c'],
                                                ['from' => '#4facfe', 'to' => '#00f2fe'],
                                                ['from' => '#43e97b', 'to' => '#38f9d7'],
                                                ['from' => '#fa709a', 'to' => '#fee140'],
                                                ['from' => '#30cfd0', 'to' => '#330867'],
                                            ];
                                            $colorPair = $colors[$item['book_id'] % count($colors)];
                                            ?>
                                            <div style="width: 60px; height: 80px; border-radius: 6px; background: linear-gradient(135deg, <?php echo $colorPair['from']; ?>, <?php echo $colorPair['to']; ?>); flex-shrink: 0; display: flex; align-items: center; justify-content: center;">
                                                <svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" style="opacity: 0.8;">
                                                    <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
                                                    <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>
                                                </svg>
                                            </div>
                                            <div>
                                                <h4 style="font-size: 15px; font-weight: 600; margin-bottom: 4px; color: var(--gray-900);">
                                                    <?php echo htmlspecialchars($item['book_title']); ?>
                                                </h4>
                                                <p style="color: var(--gray-600); font-size: 13px; margin-bottom: 4px;">
                                                    <?php echo htmlspecialchars($item['author']); ?>
                                                </p>
                                                <div style="display: flex; gap: 6px; flex-wrap: wrap;">
                                                    <?php if ($item['category_name']): ?>
                                                        <span class="badge badge-info" style="font-size: 11px;"><?php echo htmlspecialchars($item['category_name']); ?></span>
                                                    <?php endif; ?>
                                                    <span class="badge badge-secondary" style="font-size: 11px;"><?php echo htmlspecialchars($item['isbn']); ?></span>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div style="font-size: 14px;">
                                            <?php echo formatDate($item['loan_date']); ?>
                                        </div>
                                        <small style="color: var(--gray-500);">
                                            Jatuh tempo: <?php echo formatDate($item['due_date']); ?>
                                        </small>
                                    </td>
                                    <td>
                                        <div style="font-size: 14px; font-weight: 600;">
                                            <?php echo formatDate($item['return_date']); ?>
                                        </div>
                                        <small style="color: var(--gray-500);">
                                            <?php 
                                            $loanDays = (strtotime($item['return_date']) - strtotime($item['loan_date'])) / 86400;
                                            echo round($loanDays) . ' hari';
                                            ?>
                                        </small>
                                    </td>
                                    <td>
                                        <?php if ($item['days_late'] > 0): ?>
                                            <span class="badge badge-danger">
                                                Terlambat <?php echo $item['days_late']; ?> hari
                                            </span>
                                        <?php else: ?>
                                            <span class="badge badge-success">
                                                Tepat Waktu
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($item['fine_amount'] > 0): ?>
                                            <span style="color: var(--danger-600); font-weight: 600;">
                                                Rp <?php echo number_format($item['fine_amount'], 0, ',', '.'); ?>
                                            </span>
                                        <?php else: ?>
                                            <span style="color: var(--success-600); font-weight: 600;">
                                                -
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

        <!-- Summary Box -->
        <?php if (!empty($history)): ?>
            <div style="margin-top: 30px; padding: 20px; background: var(--gray-50); border-radius: 12px; border: 1px solid var(--gray-200);">
                <h3 style="font-size: 16px; font-weight: 600; margin-bottom: 16px; color: var(--gray-900);">
                    📊 Ringkasan
                </h3>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px;">
                    <div>
                        <p style="color: var(--gray-600); font-size: 13px; margin-bottom: 4px;">Total Buku Dipinjam</p>
                        <p style="font-size: 20px; font-weight: 700; color: var(--primary-600);"><?php echo $stats['total_books']; ?> buku</p>
                    </div>
                    <div>
                        <p style="color: var(--gray-600); font-size: 13px; margin-bottom: 4px;">Tingkat Ketepatan</p>
                        <p style="font-size: 20px; font-weight: 700; color: var(--success-600);">
                            <?php 
                            $accuracy = $stats['total_books'] > 0 ? round(($stats['on_time'] / $stats['total_books']) * 100) : 0;
                            echo $accuracy; 
                            ?>%
                        </p>
                    </div>
                    <div>
                        <p style="color: var(--gray-600); font-size: 13px; margin-bottom: 4px;">Total Denda</p>
                        <p style="font-size: 20px; font-weight: 700; color: var(--warning-600);">
                            Rp <?php echo number_format($stats['total_fine'], 0, ',', '.'); ?>
                        </p>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include_once '../../includes/footer.php'; ?>
