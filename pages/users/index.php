<?php
require_once '../../config/config.php';
requireLogin();
requireAdmin(); // Only admin can access this page

$pageTitle = 'Kelola Users';
$db = new Database();

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * ITEMS_PER_PAGE;

// Search
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$searchQuery = $search ? " WHERE name LIKE '%$search%' OR email LIKE '%$search%'" : '';

// Get total users
$totalUsers = $db->fetchOne("SELECT COUNT(*) as count FROM users $searchQuery")['count'];
$totalPages = ceil($totalUsers / ITEMS_PER_PAGE);

// Get users
$users = $db->fetchAll("SELECT * FROM users $searchQuery ORDER BY created_at DESC LIMIT " . ITEMS_PER_PAGE . " OFFSET $offset");

include_once '../../includes/header.php';
include_once '../../includes/sidebar.php';
?>

<div class="container">
    <div class="card-header">
        <h1 class="card-title">Kelola Users</h1>
        <a href="create.php" class="btn btn-primary">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="12" y1="5" x2="12" y2="19"></line>
                <line x1="5" y1="12" x2="19" y2="12"></line>
            </svg>
            Tambah User
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
            <input type="text" name="search" class="form-control" placeholder="Cari user (nama, email)..." value="<?php echo htmlspecialchars($search); ?>">
            <svg class="search-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="11" cy="11" r="8"></circle>
                <path d="m21 21-4.35-4.35"></path>
            </svg>
        </form>

        <!-- Users Table -->
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>Telepon</th>
                        <th>Role</th>
                        <th>Dibuat</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($users)): ?>
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 40px; color: var(--gray-500);">
                                Tidak ada user ditemukan
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?php echo $user['id']; ?></td>
                                <td><strong><?php echo htmlspecialchars($user['name']); ?></strong></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td><?php echo htmlspecialchars($user['phone'] ?? '-'); ?></td>
                                <td>
                                    <?php if ($user['role'] == 'admin'): ?>
                                        <span class="badge badge-danger">Admin</span>
                                    <?php elseif ($user['role'] == 'staff'): ?>
                                        <span class="badge badge-info">Staff</span>
                                    <?php else: ?>
                                        <span class="badge badge-success">Member</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo formatDate($user['created_at']); ?></td>
                                <td>
                                    <div style="display: flex; gap: 8px;">
                                        <a href="edit.php?id=<?php echo $user['id']; ?>" class="btn btn-sm btn-secondary">Edit</a>
                                        <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                            <a href="delete.php?id=<?php echo $user['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirmDelete('Hapus user ini?')">Hapus</a>
                                        <?php endif; ?>
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
