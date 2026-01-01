<?php
require_once '../../config/config.php';
requireLogin();

$pageTitle = 'Rekomendasi Buku';
$db = new Database();
$user = getCurrentUser();

// Get member_id from users table
$member = $db->fetchOne("SELECT * FROM members WHERE user_id = ?", [$user['id']]);

// If member doesn't exist by user_id, check by email
if (!$member) {
    $member = $db->fetchOne("SELECT * FROM members WHERE email = ?", [$user['email']]);
    
    // If found by email, update the user_id
    if ($member) {
        $db->query("UPDATE members SET user_id = ? WHERE id = ?", [$user['id'], $member['id']]);
    } else {
        // Create new member
        $lastMember = $db->fetchOne("SELECT member_id FROM members ORDER BY id DESC LIMIT 1");
        $nextNumber = 1;
        if ($lastMember) {
            $nextNumber = intval(substr($lastMember['member_id'], 1)) + 1;
        }
        $newMemberId = 'M' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
        
        $db->query("INSERT INTO members (member_id, user_id, name, email, phone, address, join_date) VALUES (?, ?, ?, ?, ?, ?, CURDATE())", 
            [$newMemberId, $user['id'], $user['name'], $user['email'], '-', '-']);
        
        $member = $db->fetchOne("SELECT * FROM members WHERE user_id = ?", [$user['id']]);
    }
}

$member_id = $member['id'];

// Get user's top 2 borrowed categories
$topCategories = $db->fetchAll("
    SELECT c.id, c.name, COUNT(*) as borrow_count
    FROM loans l
    JOIN books b ON l.book_id = b.id
    JOIN categories c ON b.category_id = c.id
    WHERE l.member_id = ?
    GROUP BY c.id, c.name
    ORDER BY borrow_count DESC
    LIMIT 2
", [$member_id]);

// Get recommended books based on top categories
$recommendedBooks = [];
if (!empty($topCategories)) {
    $categoryIds = array_column($topCategories, 'id');
    $placeholders = implode(',', array_fill(0, count($categoryIds), '?'));
    
    // Get books from favorite categories, excluding already borrowed books
    $params = array_merge($categoryIds, [$member_id]);
    $recommendedBooks = $db->fetchAll("
        SELECT b.*, c.name as category_name
        FROM books b
        JOIN categories c ON b.category_id = c.id
        WHERE b.category_id IN ($placeholders)
        AND b.id NOT IN (
            SELECT book_id FROM loans 
            WHERE member_id = ? AND status IN ('pending', 'approved')
        )
        ORDER BY RAND()
        LIMIT 12
    ", $params);
}

// If no recommendations or less than 12, add random books
if (count($recommendedBooks) < 12) {
    $limit = 12 - count($recommendedBooks);
    $excludeIds = array_column($recommendedBooks, 'id');
    
    $excludePlaceholders = '';
    $params = [$member_id];
    
    if (!empty($excludeIds)) {
        $excludePlaceholders = " AND b.id NOT IN (" . implode(',', array_fill(0, count($excludeIds), '?')) . ")";
        $params = array_merge($params, $excludeIds);
    }
    
    $moreBooks = $db->fetchAll("
        SELECT b.*, c.name as category_name
        FROM books b
        JOIN categories c ON b.category_id = c.id
        WHERE b.id NOT IN (
            SELECT book_id FROM loans 
            WHERE member_id = ? AND status IN ('pending', 'approved')
        )
        $excludePlaceholders
        ORDER BY RAND()
        LIMIT $limit
    ", $params);
    
    $recommendedBooks = array_merge($recommendedBooks, $moreBooks);
}

// Get statistics
$totalBooksAvailable = $db->fetchOne("SELECT COUNT(*) as count FROM books")['count'];
$activeLoans = $db->fetchOne("SELECT COUNT(*) as count FROM loans WHERE member_id = ? AND status IN ('pending', 'approved')", [$member_id])['count'];

// Book colors for gradients
$colors = [
    ['from' => '#667eea', 'to' => '#764ba2'],
    ['from' => '#f093fb', 'to' => '#f5576c'],
    ['from' => '#4facfe', 'to' => '#00f2fe'],
    ['from' => '#43e97b', 'to' => '#38f9d7'],
    ['from' => '#fa709a', 'to' => '#fee140'],
    ['from' => '#30cfd0', 'to' => '#330867'],
    ['from' => '#a8edea', 'to' => '#fed6e3'],
    ['from' => '#ff9a9e', 'to' => '#fecfef']
];

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

        <a href="<?php echo SITE_URL; ?>/pages/member/history.php" class="nav-item">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline>
            </svg>
            Riwayat
        </a>

        <a href="<?php echo SITE_URL; ?>/pages/member/recommendations.php" class="nav-item active">
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
        <h1 class="page-title" style="font-size: 32px; font-weight: 700; margin-bottom: 8px;">Rekomendasi untuk Anda ⭐</h1>
        <p style="color: var(--gray-600); margin-bottom: 32px;">
            <?php if (!empty($topCategories)): ?>
                Berdasarkan kebiasaan membaca Anda di kategori 
                <strong><?php echo htmlspecialchars($topCategories[0]['name']); ?></strong>
                <?php if (isset($topCategories[1])): ?>
                    dan <strong><?php echo htmlspecialchars($topCategories[1]['name']); ?></strong>
                <?php endif; ?>
            <?php else: ?>
                Jelajahi koleksi buku pilihan kami
            <?php endif; ?>
        </p>

        <?php 
        $flash = getFlashMessage();
        if ($flash): 
        ?>
            <div class="alert alert-<?php echo $flash['type']; ?>">
                <?php echo $flash['message']; ?>
            </div>
        <?php endif; ?>

        <!-- Statistics Cards -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 32px;">
            <div class="card" style="padding: 20px; text-align: center;">
                <div style="font-size: 36px; font-weight: 700; color: var(--primary-600); margin-bottom: 8px;">
                    <?php echo number_format(count($recommendedBooks)); ?>
                </div>
                <div style="color: var(--gray-600); font-size: 14px;">Buku Direkomendasikan</div>
            </div>
            
            <div class="card" style="padding: 20px; text-align: center;">
                <div style="font-size: 36px; font-weight: 700; color: var(--success-600); margin-bottom: 8px;">
                    <?php echo number_format($totalBooksAvailable); ?>
                </div>
                <div style="color: var(--gray-600); font-size: 14px;">Total Buku Tersedia</div>
            </div>
            
            <div class="card" style="padding: 20px; text-align: center;">
                <div style="font-size: 36px; font-weight: 700; color: var(--warning-600); margin-bottom: 8px;">
                    <?php echo number_format($activeLoans); ?>
                </div>
                <div style="color: var(--gray-600); font-size: 14px;">Peminjaman Aktif</div>
            </div>
        </div>

        <?php if (empty($recommendedBooks)): ?>
            <div class="card" style="padding: 60px 20px; text-align: center;">
                <svg width="120" height="120" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="color: var(--gray-400); margin: 0 auto 24px;">
                    <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
                    <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>
                </svg>
                <h3 style="color: var(--gray-700); margin-bottom: 12px;">Tidak Ada Rekomendasi</h3>
                <p style="color: var(--gray-500); margin-bottom: 24px;">
                    Mulai pinjam buku untuk mendapatkan rekomendasi yang dipersonalisasi
                </p>
                <a href="<?php echo SITE_URL; ?>/pages/member/books.php" class="btn btn-primary">
                    Jelajahi Katalog Buku
                </a>
            </div>
        <?php else: ?>
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 24px;">
                <?php foreach ($recommendedBooks as $index => $book): 
                    $color = $colors[$book['id'] % count($colors)];
                ?>
                    <div class="card" style="padding: 0; overflow: hidden; transition: transform 0.2s, box-shadow 0.2s;" onmouseover="this.style.transform='translateY(-4px)'; this.style.boxShadow='0 8px 16px rgba(0,0,0,0.1)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 1px 3px rgba(0,0,0,0.1)'">
                        <div style="height: 200px; background: linear-gradient(135deg, <?php echo $color['from']; ?>, <?php echo $color['to']; ?>); display: flex; align-items: center; justify-content: center; position: relative;">
                            <svg width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                                <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
                                <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>
                            </svg>
                            <?php if ($index < 4): ?>
                                <div style="position: absolute; top: 12px; right: 12px; background: rgba(255,255,255,0.95); color: #f59e0b; padding: 6px 12px; border-radius: 20px; font-size: 13px; font-weight: 600; display: flex; align-items: center; gap: 4px;">
                                    ⭐ Top Pick
                                </div>
                            <?php endif; ?>
                        </div>
                        <div style="padding: 20px;">
                            <div style="margin-bottom: 12px;">
                                <span class="badge badge-info"><?php echo htmlspecialchars($book['category_name'] ?? 'Umum'); ?></span>
                                <span class="badge <?php echo $book['stock'] <= 2 ? 'badge-warning' : 'badge-success'; ?>" style="margin-left: 8px;">
                                    <?php echo $book['stock']; ?> tersedia
                                </span>
                            </div>
                            <h3 style="font-size: 18px; font-weight: 600; margin-bottom: 8px; color: var(--gray-900); line-height: 1.4;">
                                <?php echo htmlspecialchars($book['title']); ?>
                            </h3>
                            <p style="color: var(--gray-600); font-size: 14px; margin-bottom: 4px; display: flex; align-items: center; gap: 6px;">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                    <circle cx="12" cy="7" r="4"></circle>
                                </svg>
                                <?php echo htmlspecialchars($book['author']); ?>
                            </p>
                            <p style="color: var(--gray-500); font-size: 13px; margin-bottom: 4px; display: flex; align-items: center; gap: 6px;">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                                    <line x1="16" y1="2" x2="16" y2="6"></line>
                                    <line x1="8" y1="2" x2="8" y2="6"></line>
                                    <line x1="3" y1="10" x2="21" y2="10"></line>
                                </svg>
                                <?php echo $book['publisher']; ?> • <?php echo $book['year']; ?>
                            </p>
                            <p style="color: var(--gray-500); font-size: 13px; margin-bottom: 16px; display: flex; align-items: center; gap: 6px;">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
                                    <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>
                                </svg>
                                <?php echo $book['pages']; ?> halaman
                            </p>
                            <?php if (!empty($book['description'])): ?>
                                <p style="color: var(--gray-600); font-size: 13px; line-height: 1.6; margin-bottom: 16px; max-height: 4.8em; overflow: hidden; text-overflow: ellipsis; display: -webkit-box; -webkit-line-clamp: 3; line-clamp: 3; -webkit-box-orient: vertical;">
                                    <?php echo htmlspecialchars($book['description']); ?>
                                </p>
                            <?php endif; ?>
                            <a href="<?php echo SITE_URL; ?>/pages/member/borrow.php?book_id=<?php echo $book['id']; ?>" 
                               class="btn btn-primary btn-sm" 
                               style="width: 100%; display: flex; align-items: center; justify-content: center; gap: 8px;">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
                                    <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>
                                    <path d="M12 7v10"></path>
                                    <path d="M9 10l3-3 3 3"></path>
                                </svg>
                                Pinjam Buku
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <?php include_once '../../includes/footer.php'; ?>
</div>
