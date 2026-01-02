<?php
require_once '../config/config.php';
requireLogin();

$pageTitle = 'Dashboard';
$user = getCurrentUser();

// Get database connection
$db = new Database();

// Get statistics
$totalBooks = $db->fetchOne("SELECT COUNT(*) as count FROM books")['count'];
$totalMembers = $db->fetchOne("SELECT COUNT(*) as count FROM members WHERE status = 'active'")['count'];
$activeLoans = $db->fetchOne("SELECT COUNT(*) as count FROM loans WHERE status = 'approved' AND return_date IS NULL")['count'];
$overdueLoans = $db->fetchOne("SELECT COUNT(*) as count FROM loans WHERE status = 'approved' AND return_date IS NULL AND due_date < CURDATE()")['count'];

// Get recent loans
$recentLoans = $db->fetchAll("
    SELECT l.*, m.name as member_name, m.member_id, b.title as book_title
    FROM loans l
    JOIN members m ON l.member_id = m.id
    JOIN books b ON l.book_id = b.id
    ORDER BY l.created_at DESC
    LIMIT 5
");

// Get books with low stock
$lowStockBooks = $db->fetchAll("
    SELECT * FROM books 
    WHERE stock <= 2 AND stock > 0
    ORDER BY stock ASC
    LIMIT 5
");

// Get popular books (most borrowed)
$popularBooks = $db->fetchAll("
    SELECT b.*, COUNT(l.id) as borrow_count
    FROM books b
    LEFT JOIN loans l ON b.id = l.book_id
    GROUP BY b.id
    ORDER BY borrow_count DESC
    LIMIT 5
");

include_once '../includes/header.php';
include_once '../includes/sidebar.php';
?>

<div class="container">
    <h1 class="page-title" style="font-size: 32px; font-weight: 700; margin-bottom: 8px;">Dashboard</h1>
    <p style="color: var(--gray-600); margin-bottom: 32px;">Selamat datang kembali, <?php echo htmlspecialchars($user['name']); ?>! 👋</p>

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
            <div class="stat-card-value"><?php echo number_format($totalBooks); ?></div>
            <div class="stat-card-label">Total Buku</div>
        </div>

        <div class="stat-card">
            <div class="stat-card-icon" style="background-color: #dcfce7;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color: #16a34a;">
                    <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
                    <circle cx="9" cy="7" r="4"></circle>
                    <path d="M22 21v-2a4 4 0 0 0-3-3.87"></path>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                </svg>
            </div>
            <div class="stat-card-value"><?php echo number_format($totalMembers); ?></div>
            <div class="stat-card-label">Anggota Aktif</div>
        </div>

        <div class="stat-card">
            <div class="stat-card-icon" style="background-color: #fef3c7;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color: #d97706;">
                    <circle cx="12" cy="12" r="10"></circle>
                    <polyline points="12 6 12 12 16 14"></polyline>
                </svg>
            </div>
            <div class="stat-card-value"><?php echo number_format($activeLoans); ?></div>
            <div class="stat-card-label">Peminjaman Aktif</div>
        </div>

        <div class="stat-card">
            <div class="stat-card-icon" style="background-color: #fee2e2;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color: #dc2626;">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="12" y1="8" x2="12" y2="12"></line>
                    <line x1="12" y1="16" x2="12.01" y2="16"></line>
                </svg>
            </div>
            <div class="stat-card-value"><?php echo number_format($overdueLoans); ?></div>
            <div class="stat-card-label">Terlambat</div>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 24px;">
        <!-- Recent Loans -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title" style="font-size: 18px;">Peminjaman Terbaru</h2>
                <a href="loans/index.php" class="btn btn-sm btn-secondary">Lihat Semua</a>
            </div>

            <?php if (empty($recentLoans)): ?>
                <p style="color: var(--gray-500); text-align: center; padding: 40px 0;">
                    Belum ada peminjaman
                </p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Anggota</th>
                                <th>Buku</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentLoans as $loan): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($loan['loan_id']); ?></td>
                                    <td><?php echo htmlspecialchars($loan['member_name']); ?></td>
                                    <td><?php echo htmlspecialchars($loan['book_title']); ?></td>
                                    <td>
                                        <?php if ($loan['status'] == 'pending'): ?>
                                            <span class="badge badge-warning">Pending</span>
                                        <?php elseif ($loan['status'] == 'approved'): ?>
                                            <?php if ($loan['return_date']): ?>
                                                <span class="badge badge-success">Dikembalikan</span>
                                            <?php elseif ($loan['due_date'] < date('Y-m-d')): ?>
                                                <span class="badge badge-danger">Terlambat</span>
                                            <?php else: ?>
                                                <span class="badge badge-info">Dipinjam</span>
                                            <?php endif; ?>
                                        <?php elseif ($loan['status'] == 'returned'): ?>
                                            <span class="badge badge-success">Dikembalikan</span>
                                        <?php elseif ($loan['status'] == 'rejected'): ?>
                                            <span class="badge badge-danger">Ditolak</span>
                                        <?php elseif ($loan['status'] == 'return_requested'): ?>
                                            <span class="badge badge-warning">Ajuan Kembali</span>
                                        <?php else: ?>
                                            <span class="badge badge-secondary"><?php echo ucfirst($loan['status']); ?></span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

        <!-- Low Stock Books -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title" style="font-size: 18px;">Stok Buku Menipis</h2>
                <a href="books/index.php" class="btn btn-sm btn-secondary">Lihat Semua</a>
            </div>

            <?php if (empty($lowStockBooks)): ?>
                <p style="color: var(--gray-500); text-align: center; padding: 40px 0;">
                    Semua buku memiliki stok yang cukup
                </p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Judul</th>
                                <th>Pengarang</th>
                                <th>Stok</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($lowStockBooks as $book): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($book['title']); ?></td>
                                    <td><?php echo htmlspecialchars($book['author']); ?></td>
                                    <td>
                                        <span class="badge badge-danger"><?php echo $book['stock']; ?></span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Popular Books -->
    <div class="card">
        <div class="card-header">
            <h2 class="card-title" style="font-size: 18px;">Buku Populer</h2>
        </div>

        <?php if (empty($popularBooks)): ?>
            <p style="color: var(--gray-500); text-align: center; padding: 40px 0;">
                Belum ada data peminjaman
            </p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ISBN</th>
                            <th>Judul</th>
                            <th>Pengarang</th>
                            <th>Penerbit</th>
                            <th>Stok</th>
                            <th>Total Dipinjam</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($popularBooks as $book): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($book['isbn']); ?></td>
                                <td><?php echo htmlspecialchars($book['title']); ?></td>
                                <td><?php echo htmlspecialchars($book['author']); ?></td>
                                <td><?php echo htmlspecialchars($book['publisher']); ?></td>
                                <td>
                                    <?php if ($book['stock'] <= 2): ?>
                                        <span class="badge badge-danger"><?php echo $book['stock']; ?></span>
                                    <?php else: ?>
                                        <span class="badge badge-success"><?php echo $book['stock']; ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge badge-info"><?php echo $book['borrow_count']; ?>x</span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include_once '../includes/footer.php'; ?>
