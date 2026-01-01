<?php
require_once '../config/config.php';
requireLogin();

$pageTitle = 'Rekomendasi Buku';
$db = new Database();

// Get all books with categories
$books = $db->fetchAll("
    SELECT b.*, c.name as category_name
    FROM books b
    LEFT JOIN categories c ON b.category_id = c.id
    WHERE b.stock > 0
    ORDER BY RAND()
    LIMIT 12
");

include_once '../includes/header.php';
include_once '../includes/sidebar.php';
?>

<div class="container">
    <h1 class="page-title" style="font-size: 32px; font-weight: 700; margin-bottom: 8px;">Rekomendasi Buku</h1>
    <p style="color: var(--gray-600); margin-bottom: 32px;">Temukan buku-buku menarik yang mungkin Anda sukai</p>

    <?php 
    $flash = getFlashMessage();
    if ($flash): 
    ?>
        <div class="alert alert-<?php echo $flash['type']; ?>">
            <?php echo $flash['message']; ?>
        </div>
    <?php endif; ?>

    <?php if (empty($books)): ?>
        <div class="card">
            <p style="text-align: center; padding: 60px 20px; color: var(--gray-500);">
                Tidak ada buku tersedia saat ini
            </p>
        </div>
    <?php else: ?>
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 24px;">
            <?php foreach ($books as $book): ?>
                <div class="card" style="padding: 0; overflow: hidden;">
                    <div style="height: 200px; background: linear-gradient(135deg, var(--primary-100), var(--primary-200)); display: flex; align-items: center; justify-content: center;">
                        <svg width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color: var(--primary-600);">
                            <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
                            <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>
                        </svg>
                    </div>
                    <div style="padding: 20px;">
                        <div style="margin-bottom: 8px;">
                            <span class="badge badge-info"><?php echo htmlspecialchars($book['category_name'] ?? 'Umum'); ?></span>
                        </div>
                        <h3 style="font-size: 18px; font-weight: 600; margin-bottom: 8px; color: var(--gray-900);">
                            <?php echo htmlspecialchars($book['title']); ?>
                        </h3>
                        <p style="color: var(--gray-600); font-size: 14px; margin-bottom: 4px;">
                            <?php echo htmlspecialchars($book['author']); ?>
                        </p>
                        <p style="color: var(--gray-500); font-size: 12px; margin-bottom: 16px;">
                            <?php echo $book['publisher']; ?> • <?php echo $book['year']; ?>
                        </p>
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <div>
                                <span style="font-size: 12px; color: var(--gray-600);">Tersedia:</span>
                                <span class="badge <?php echo $book['stock'] <= 2 ? 'badge-warning' : 'badge-success'; ?>">
                                    <?php echo $book['stock']; ?> buku
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php include_once '../includes/footer.php'; ?>
