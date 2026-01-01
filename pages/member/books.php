<?php
require_once '../../config/config.php';
requireLogin();
requireMember();

$pageTitle = 'Katalog Buku';
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

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * ITEMS_PER_PAGE;

// Search & Filter
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$category = isset($_GET['category']) ? (int)$_GET['category'] : 0;

$params = [];
$whereClause = " WHERE 1=1";

if ($search) {
    $whereClause .= " AND (b.title LIKE ? OR b.author LIKE ? OR b.isbn LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}
if ($category) {
    $whereClause .= " AND b.category_id = ?";
    $params[] = $category;
}

// Get categories
$categories = $db->fetchAll("SELECT * FROM categories ORDER BY name");

// Get total books (dengan parameter binding)
$countQuery = "SELECT COUNT(*) as count FROM books b $whereClause";
$totalBooks = $db->fetchOne($countQuery, $params)['count'];
$totalPages = ceil($totalBooks / ITEMS_PER_PAGE);

// Get books (dengan parameter binding)
$booksQuery = "
    SELECT b.*, c.name as category_name 
    FROM books b 
    LEFT JOIN categories c ON b.category_id = c.id 
    $whereClause 
    ORDER BY b.id DESC 
    LIMIT ? OFFSET ?
";
$params[] = ITEMS_PER_PAGE;
$params[] = $offset;
$books = $db->fetchAll($booksQuery, $params);

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

        <a href="<?php echo SITE_URL; ?>/pages/member/books.php" class="nav-item active">
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
        <h1 class="page-title" style="font-size: 32px; font-weight: 700; margin-bottom: 8px;">Katalog Buku</h1>
        <p style="color: var(--gray-600); margin-bottom: 32px;">Jelajahi koleksi buku kami dan pinjam buku favoritmu!</p>

        <?php 
        $flash = getFlashMessage();
        if ($flash): 
        ?>
            <div class="alert alert-<?php echo $flash['type']; ?>">
                <?php echo $flash['message']; ?>
            </div>
        <?php endif; ?>

        <?php if (!$member): ?>
            <div class="alert alert-info">
                <strong>Informasi:</strong> Anda belum terdaftar sebagai anggota perpustakaan. 
                <a href="register-member.php" style="color: var(--primary-700); text-decoration: underline;">Daftar sekarang</a> untuk dapat meminjam buku.
            </div>
        <?php endif; ?>

        <!-- Recommendations Section -->
        <?php if ($member): ?>
            <?php
            // Get user's most borrowed categories
            $topCategories = $db->fetchAll("
                SELECT c.id, c.name, COUNT(*) as borrow_count
                FROM loans l
                INNER JOIN books b ON l.book_id = b.id
                INNER JOIN categories c ON b.category_id = c.id
                WHERE l.member_id = ?
                GROUP BY c.id, c.name
                ORDER BY borrow_count DESC
                LIMIT 2
            ", [$member['id']]);
            
            if (!empty($topCategories)):
                $categoryIds = array_column($topCategories, 'id');
                $placeholders = implode(',', array_fill(0, count($categoryIds), '?'));
                
                // Get borrowed book IDs to exclude
                $borrowedIds = $db->fetchAll("
                    SELECT DISTINCT book_id 
                    FROM loans 
                    WHERE member_id = ?
                ", [$member['id']]);
                
                $excludeIds = array_column($borrowedIds, 'book_id');
                $excludeIds[] = 0; // Prevent empty array
                $excludePlaceholders = implode(',', array_fill(0, count($excludeIds), '?'));
                
                // Get recommended books
                $recommended = $db->fetchAll("
                    SELECT b.*, c.name as category_name
                    FROM books b
                    LEFT JOIN categories c ON b.category_id = c.id
                    WHERE b.category_id IN ($placeholders)
                    AND b.id NOT IN ($excludePlaceholders)
                    AND b.stock > 0
                    ORDER BY RAND()
                    LIMIT 4
                ", array_merge($categoryIds, $excludeIds));
                
                if (!empty($recommended)):
            ?>
                <div style="margin-bottom: 40px;">
                    <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 20px;">
                        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color: var(--primary-600);">
                            <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"></path>
                        </svg>
                        <div>
                            <h2 style="font-size: 24px; font-weight: 700; margin: 0; color: var(--gray-900);">Rekomendasi untuk Anda</h2>
                            <p style="color: var(--gray-600); font-size: 14px; margin: 4px 0 0 0;">Berdasarkan kebiasaan membaca Anda di kategori <?php echo implode(' & ', array_column($topCategories, 'name')); ?></p>
                        </div>
                    </div>
                    
                    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 24px;">
                        <?php foreach ($recommended as $book): ?>
                            <div class="card" style="padding: 0; overflow: hidden; height: 100%; border: 2px solid var(--primary-200); background: linear-gradient(to bottom, var(--primary-50), white);">
                                <!-- Book Cover -->
                                <?php 
                                $colors = [
                                    ['from' => '#667eea', 'to' => '#764ba2'],
                                    ['from' => '#f093fb', 'to' => '#f5576c'],
                                    ['from' => '#4facfe', 'to' => '#00f2fe'],
                                    ['from' => '#43e97b', 'to' => '#38f9d7'],
                                    ['from' => '#fa709a', 'to' => '#fee140'],
                                    ['from' => '#30cfd0', 'to' => '#330867'],
                                ];
                                $colorPair = $colors[$book['id'] % count($colors)];
                                ?>
                                <div style="height: 250px; background: linear-gradient(135deg, <?php echo $colorPair['from']; ?>, <?php echo $colorPair['to']; ?>); position: relative; overflow: hidden; display: flex; align-items: center; justify-content: center;">
                                    <svg width="100" height="100" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="1.5" style="opacity: 0.9;">
                                        <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
                                        <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>
                                        <line x1="10" y1="8" x2="16" y2="8"></line>
                                        <line x1="10" y1="12" x2="16" y2="12"></line>
                                        <line x1="10" y1="16" x2="14" y2="16"></line>
                                    </svg>
                                    <div style="position: absolute; top: 12px; left: 12px; background: var(--warning-500); color: white; padding: 6px 12px; border-radius: 6px; font-size: 12px; font-weight: 700; box-shadow: 0 2px 8px rgba(0,0,0,0.2);">
                                        ⭐ Rekomendasi
                                    </div>
                                    <div style="position: absolute; top: 12px; right: 12px; background: rgba(255,255,255,0.95); padding: 6px 12px; border-radius: 6px; font-size: 13px; font-weight: 600; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                                        <?php echo htmlspecialchars($book['category_name'] ?? 'Umum'); ?>
                                    </div>
                                </div>
                                
                                <!-- Book Info -->
                                <div style="padding: 20px;">
                                    <div style="margin-bottom: 12px;">
                                        <span class="badge <?php echo $book['stock'] == 0 ? 'badge-danger' : ($book['stock'] <= 2 ? 'badge-warning' : 'badge-success'); ?>">
                                            <?php if($book['stock'] == 0): ?>
                                                Habis
                                            <?php else: ?>
                                                Tersedia: <?php echo $book['stock']; ?> buku
                                            <?php endif; ?>
                                        </span>
                                    </div>
                                    
                                    <h3 style="font-size: 18px; font-weight: 600; margin-bottom: 8px; color: var(--gray-900); min-height: 50px;">
                                        <?php echo htmlspecialchars($book['title']); ?>
                                    </h3>
                                    
                                    <p style="color: var(--gray-600); font-size: 14px; margin-bottom: 4px;">
                                        <strong>Pengarang:</strong> <?php echo htmlspecialchars($book['author']); ?>
                                    </p>
                                    
                                    <p style="color: var(--gray-600); font-size: 14px; margin-bottom: 4px;">
                                        <strong>Penerbit:</strong> <?php echo htmlspecialchars($book['publisher']); ?>
                                    </p>
                                    
                                    <p style="color: var(--gray-600); font-size: 14px; margin-bottom: 16px;">
                                        <strong>Tahun:</strong> <?php echo $book['year']; ?> • 
                                        <strong>Halaman:</strong> <?php echo $book['pages']; ?>
                                    </p>
                                    
                                    <a href="borrow.php?book_id=<?php echo $book['id']; ?>" 
                                       class="btn btn-primary" 
                                       style="width: 100%; justify-content: center;">
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"></path>
                                        </svg>
                                        Pinjam Buku
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php 
                endif;
            endif;
            ?>
        <?php endif; ?>

        <div class="card">
            <!-- Search & Filter -->
            <form method="GET" action="" style="display: flex; gap: 12px; margin-bottom: 24px; flex-wrap: wrap;">
                <div style="flex: 1; min-width: 200px; position: relative;">
                    <svg style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--gray-400); pointer-events: none; z-index: 1;" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="8"></circle>
                        <path d="m21 21-4.35-4.35"></path>
                    </svg>
                    <input type="text" name="search" class="form-control" placeholder="Cari buku (judul, pengarang, ISBN)..." value="<?php echo htmlspecialchars($search); ?>" style="padding-left: 40px;">
                </div>
                
                <select name="category" class="form-control" style="max-width: 200px;">
                    <option value="">Semua Kategori</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo $cat['id']; ?>" <?php echo $category == $cat['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cat['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                
                <button type="submit" class="btn btn-primary">Cari</button>
                <?php if ($search || $category): ?>
                    <a href="books.php" class="btn btn-secondary">Reset</a>
                <?php endif; ?>
            </form>

            <!-- Books Grid -->
            <?php if (empty($books)): ?>
                <p style="text-align: center; padding: 60px 20px; color: var(--gray-500);">
                    Tidak ada buku ditemukan
                </p>
            <?php else: ?>
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 24px;">
                    <?php foreach ($books as $book): ?>
                        <div class="card" style="padding: 0; overflow: hidden; height: 100%;">
                            <!-- Book Cover -->
                            <?php 
                            // Generate random gradient for book cover
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
                            $colorPair = $colors[$book['id'] % count($colors)];
                            ?>
                            <div style="height: 250px; background: linear-gradient(135deg, <?php echo $colorPair['from']; ?>, <?php echo $colorPair['to']; ?>); position: relative; overflow: hidden; display: flex; align-items: center; justify-content: center;">
                                <svg width="100" height="100" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="1.5" style="opacity: 0.9;">
                                    <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
                                    <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>
                                    <line x1="10" y1="8" x2="16" y2="8"></line>
                                    <line x1="10" y1="12" x2="16" y2="12"></line>
                                    <line x1="10" y1="16" x2="14" y2="16"></line>
                                </svg>
                                <div style="position: absolute; top: 12px; right: 12px; background: rgba(255,255,255,0.95); padding: 6px 12px; border-radius: 6px; font-size: 13px; font-weight: 600; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                                    <?php echo htmlspecialchars($book['category_name'] ?? 'Umum'); ?>
                                </div>
                            </div>
                            
                            <!-- Book Info -->
                            <div style="padding: 20px;">
                                <div style="margin-bottom: 12px;">
                                    <span class="badge <?php echo $book['stock'] == 0 ? 'badge-danger' : ($book['stock'] <= 2 ? 'badge-warning' : 'badge-success'); ?>">
                                        <?php if($book['stock'] == 0): ?>
                                            Habis
                                        <?php else: ?>
                                            Tersedia: <?php echo $book['stock']; ?> buku
                                        <?php endif; ?>
                                    </span>
                                </div>
                                
                                <h3 style="font-size: 18px; font-weight: 600; margin-bottom: 8px; color: var(--gray-900); min-height: 50px;">
                                    <?php echo htmlspecialchars($book['title']); ?>
                                </h3>
                                
                                <p style="color: var(--gray-600); font-size: 14px; margin-bottom: 4px;">
                                    <strong>Pengarang:</strong> <?php echo htmlspecialchars($book['author']); ?>
                                </p>
                                
                                <p style="color: var(--gray-600); font-size: 14px; margin-bottom: 4px;">
                                    <strong>Penerbit:</strong> <?php echo htmlspecialchars($book['publisher']); ?>
                                </p>
                                
                                <p style="color: var(--gray-600); font-size: 14px; margin-bottom: 16px;">
                                    <strong>Tahun:</strong> <?php echo $book['year']; ?> • 
                                    <strong>Halaman:</strong> <?php echo $book['pages']; ?>
                                </p>
                                
                                <?php if ($member): ?>
                                    <a href="borrow.php?book_id=<?php echo $book['id']; ?>" 
                                       class="btn btn-primary" 
                                       style="width: 100%; justify-content: center;">
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"></path>
                                        </svg>
                                        Pinjam Buku
                                    </a>
                                <?php else: ?>
                                    <button class="btn btn-secondary" style="width: 100%; justify-content: center;" disabled>
                                        Daftar Dulu untuk Pinjam
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Pagination -->
                <?php if ($totalPages > 1): ?>
                    <div class="pagination" style="margin-top: 32px;">
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <a href="?page=<?php echo $i; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?><?php echo $category ? '&category=' . $category : ''; ?>" 
                               class="page-link <?php echo $i == $page ? 'active' : ''; ?>">
                                <?php echo $i; ?>
                            </a>
                        <?php endfor; ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include_once '../../includes/footer.php'; ?>
