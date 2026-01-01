<?php
require_once '../../config/config.php';
requireLogin();

$pageTitle = 'Daftar Buku';
$db = new Database();

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * ITEMS_PER_PAGE;

// Search
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$searchQuery = $search ? " WHERE title LIKE '%$search%' OR author LIKE '%$search%' OR isbn LIKE '%$search%'" : '';

// Get total books
$totalBooks = $db->fetchOne("SELECT COUNT(*) as count FROM books $searchQuery")['count'];
$totalPages = ceil($totalBooks / ITEMS_PER_PAGE);

// Get books
$books = $db->fetchAll("SELECT b.*, c.name as category_name FROM books b LEFT JOIN categories c ON b.category_id = c.id $searchQuery ORDER BY b.created_at DESC LIMIT " . ITEMS_PER_PAGE . " OFFSET $offset");

include_once '../../includes/header.php';
include_once '../../includes/sidebar.php';
?>

<div class="container">
    <div class="card-header">
        <h1 class="card-title">Daftar Buku</h1>
        <a href="create.php" class="btn btn-primary">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="12" y1="5" x2="12" y2="19"></line>
                <line x1="5" y1="12" x2="19" y2="12"></line>
            </svg>
            Tambah Buku
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

    <div class="card">
        <!-- Search Bar -->
        <form method="GET" action="" class="search-bar">
            <input type="text" name="search" class="form-control" placeholder="Cari buku (judul, pengarang, ISBN)..." value="<?php echo htmlspecialchars($search); ?>">
            <svg class="search-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="11" cy="11" r="8"></circle>
                <path d="m21 21-4.35-4.35"></path>
            </svg>
        </form>

        <!-- Books Table -->
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>ISBN</th>
                        <th>Judul</th>
                        <th>Pengarang</th>
                        <th>Kategori</th>
                        <th>Tahun</th>
                        <th>Stok</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($books)): ?>
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 40px; color: var(--gray-500);">
                                Tidak ada buku ditemukan
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($books as $book): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($book['isbn']); ?></td>
                                <td><strong><?php echo htmlspecialchars($book['title']); ?></strong></td>
                                <td><?php echo htmlspecialchars($book['author']); ?></td>
                                <td><?php echo htmlspecialchars($book['category_name'] ?? '-'); ?></td>
                                <td><?php echo $book['year']; ?></td>
                                <td>
                                    <?php if ($book['stock'] <= 2): ?>
                                        <span class="badge badge-danger"><?php echo $book['stock']; ?></span>
                                    <?php else: ?>
                                        <span class="badge badge-success"><?php echo $book['stock']; ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div style="display: flex; gap: 8px;">
                                        <a href="edit.php?id=<?php echo $book['id']; ?>" class="btn btn-sm btn-secondary">Edit</a>
                                        <a href="delete.php?id=<?php echo $book['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirmDelete('Hapus buku ini?')">Hapus</a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
            <div class="pagination">
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <a href="?page=<?php echo $i; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>" 
                       class="page-link <?php echo $i == $page ? 'active' : ''; ?>">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include_once '../../includes/footer.php'; ?>
