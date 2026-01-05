<?php
require_once '../../config/config.php';
requireLogin();
requireMember();

$user = getCurrentUser();
$db = new Database();

// Get member data
$member = $db->fetchOne("SELECT * FROM members WHERE email = ?", [$user['email']]);

if (!$member) {
    setFlashMessage('error', 'Anda belum terdaftar sebagai anggota perpustakaan.');
    redirect('/pages/member/register-member.php');
}

// Get book ID
$bookId = isset($_GET['book_id']) ? (int)$_GET['book_id'] : 0;

// Get book data
$book = $db->fetchOne("SELECT * FROM books WHERE id = ? AND stock > 0", [$bookId]);

if (!$book) {
    setFlashMessage('error', 'Buku tidak tersedia atau stok habis.');
    redirect('/pages/member/books.php');
}

// Check if member already has active or pending loan for this book
$existingLoan = $db->fetchOne("
    SELECT * FROM loans 
    WHERE member_id = ? AND book_id = ? AND status IN ('pending', 'approved')
", [$member['id'], $bookId]);

if ($existingLoan) {
    $statusText = $existingLoan['status'] == 'pending' ? 'menunggu persetujuan' : 'sedang dipinjam';
    setFlashMessage('error', 'Anda sudah mengajukan peminjaman buku ini (' . $statusText . ').');
    redirect('/pages/member/books.php');
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        // Generate loan ID yang unik
        $lastLoan = $db->fetchOne("SELECT loan_id FROM loans ORDER BY id DESC LIMIT 1");
        if ($lastLoan) {
            $lastNum = (int)substr($lastLoan['loan_id'], 1);
            $loanId = 'L' . str_pad($lastNum + 1, 5, '0', STR_PAD_LEFT);
        } else {
            $loanId = 'L00001';
        }
        
        // Calculate due date (DEFAULT_LOAN_DAYS from config)
        $loanDate = date('Y-m-d');
        $dueDate = date('Y-m-d', strtotime('+' . DEFAULT_LOAN_DAYS . ' days'));
        
        // Insert loan with pending status (waiting for staff approval)
        $db->query("
            INSERT INTO loans (loan_id, member_id, book_id, loan_date, due_date, status)
            VALUES (?, ?, ?, ?, ?, 'pending')
        ", [$loanId, $member['id'], $bookId, $loanDate, $dueDate]);
        
        // Kurangi stok buku saat pengajuan (akan dikembalikan jika ditolak)
        $db->query("UPDATE books SET stock = stock - 1 WHERE id = ?", [$bookId]);
        
        setFlashMessage('success', 'Pengajuan peminjaman berhasil dikirim! Menunggu persetujuan staff.');
        redirect('/pages/member/my-loans.php');
    } catch (Exception $e) {
        $error = 'Terjadi kesalahan saat memproses peminjaman. Silakan coba lagi.';
    }
}

$pageTitle = 'Pinjam Buku';
include_once '../../includes/header.php';
?>

<!-- Member Sidebar (simplified) -->
<div class="sidebar">
    <div class="sidebar-logo">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
            <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>
        </svg>
        <h1>Readify</h1>
    </div>

    <nav class="sidebar-nav">
        <a href="<?php echo SITE_URL; ?>/pages/member/dashboard.php" class="nav-item">Dashboard</a>
        <a href="<?php echo SITE_URL; ?>/pages/member/books.php" class="nav-item active">Katalog Buku</a>
        <a href="<?php echo SITE_URL; ?>/pages/member/my-loans.php" class="nav-item">Peminjaman Saya</a>
        <a href="<?php echo SITE_URL; ?>/pages/member/history.php" class="nav-item">Riwayat</a>

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
        <a href="<?php echo SITE_URL; ?>/pages/auth/logout.php" class="btn btn-danger btn-sm" style="width: 100%;">Logout</a>
    </div>
</div>

<div class="main-content">
    <div class="container">
        <div class="card-header">
            <h1 class="card-title">Konfirmasi Peminjaman Buku</h1>
            <a href="books.php" class="btn btn-secondary">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="19" y1="12" x2="5" y2="12"></line>
                    <polyline points="12 19 5 12 12 5"></polyline>
                </svg>
                Kembali
            </a>
        </div>

        <?php if (isset($error)): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>

        <div class="card">
            <div style="display: grid; grid-template-columns: 200px 1fr; gap: 32px;">
                <!-- Book Cover -->
                <div style="height: 280px; background: linear-gradient(135deg, var(--primary-100), var(--primary-200)); display: flex; align-items: center; justify-content: center; border-radius: 12px;">
                    <svg width="100" height="100" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color: var(--primary-600);">
                        <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
                        <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>
                    </svg>
                </div>

                <!-- Book Details -->
                <div>
                    <h2 style="font-size: 28px; font-weight: 700; margin-bottom: 16px; color: var(--gray-900);">
                        <?php echo htmlspecialchars($book['title']); ?>
                    </h2>
                    
                    <div style="margin-bottom: 24px;">
                        <span class="badge badge-info"><?php echo htmlspecialchars($book['category_name'] ?? 'Umum'); ?></span>
                        <span class="badge badge-success">Stok: <?php echo $book['stock']; ?></span>
                    </div>

                    <table style="width: 100%; margin-bottom: 24px;">
                        <tr>
                            <td style="padding: 8px 0; color: var(--gray-600); width: 150px;"><strong>ISBN:</strong></td>
                            <td style="padding: 8px 0;"><?php echo htmlspecialchars($book['isbn']); ?></td>
                        </tr>
                        <tr>
                            <td style="padding: 8px 0; color: var(--gray-600);"><strong>Pengarang:</strong></td>
                            <td style="padding: 8px 0;"><?php echo htmlspecialchars($book['author']); ?></td>
                        </tr>
                        <tr>
                            <td style="padding: 8px 0; color: var(--gray-600);"><strong>Penerbit:</strong></td>
                            <td style="padding: 8px 0;"><?php echo htmlspecialchars($book['publisher']); ?></td>
                        </tr>
                        <tr>
                            <td style="padding: 8px 0; color: var(--gray-600);"><strong>Tahun:</strong></td>
                            <td style="padding: 8px 0;"><?php echo $book['year']; ?></td>
                        </tr>
                        <tr>
                            <td style="padding: 8px 0; color: var(--gray-600);"><strong>Halaman:</strong></td>
                            <td style="padding: 8px 0;"><?php echo $book['pages']; ?></td>
                        </tr>
                    </table>

                    <?php if ($book['description']): ?>
                        <div style="padding: 16px; background: var(--gray-50); border-radius: 8px; margin-bottom: 24px;">
                            <h3 style="font-size: 14px; font-weight: 600; margin-bottom: 8px;">Deskripsi:</h3>
                            <p style="color: var(--gray-700); line-height: 1.6;"><?php echo nl2br(htmlspecialchars($book['description'])); ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <hr style="margin: 32px 0; border: none; border-top: 1px solid var(--gray-200);">

            <!-- Loan Details -->
            <div style="background: var(--primary-50); padding: 24px; border-radius: 12px; margin-bottom: 24px;">
                <h3 style="font-size: 18px; font-weight: 600; margin-bottom: 16px; color: var(--primary-900);">
                    📋 Detail Peminjaman
                </h3>
                <table style="width: 100%;">
                    <tr>
                        <td style="padding: 8px 0; color: var(--primary-800); width: 200px;"><strong>Peminjam:</strong></td>
                        <td style="padding: 8px 0; color: var(--primary-900);"><?php echo htmlspecialchars($member['name']); ?></td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0; color: var(--primary-800);"><strong>ID Anggota:</strong></td>
                        <td style="padding: 8px 0; color: var(--primary-900);"><?php echo htmlspecialchars($member['member_id']); ?></td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0; color: var(--primary-800);"><strong>Tanggal Pinjam:</strong></td>
                        <td style="padding: 8px 0; color: var(--primary-900);"><?php echo formatDate(date('Y-m-d')); ?></td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0; color: var(--primary-800);"><strong>Jatuh Tempo:</strong></td>
                        <td style="padding: 8px 0; color: var(--primary-900);"><?php echo formatDate(date('Y-m-d', strtotime('+' . DEFAULT_LOAN_DAYS . ' days'))); ?> (<?php echo DEFAULT_LOAN_DAYS; ?> hari)</td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0; color: var(--primary-800);"><strong>Denda Keterlambatan:</strong></td>
                        <td style="padding: 8px 0; color: var(--primary-900);"><?php echo formatRupiah(FINE_PER_DAY); ?>/hari</td>
                    </tr>
                </table>
            </div>

            <!-- Confirmation Form -->
            <form method="POST" action="">
                <div style="padding: 16px; background: var(--gray-50); border-radius: 8px; margin-bottom: 24px;">
                    <h4 style="font-size: 14px; font-weight: 600; margin-bottom: 12px;">⚠️ Ketentuan Peminjaman:</h4>
                    <ul style="color: var(--gray-700); line-height: 1.8; padding-left: 20px; margin: 0;">
                        <li>Buku harus dikembalikan sebelum jatuh tempo</li>
                        <li>Denda keterlambatan: <?php echo formatRupiah(FINE_PER_DAY); ?> per hari</li>
                        <li>Buku harus dikembalikan dalam kondisi baik</li>
                        <li>Jika buku rusak atau hilang, akan dikenakan ganti rugi</li>
                    </ul>
                </div>

                <div style="display: flex; gap: 12px;">
                    <button type="submit" class="btn btn-primary" style="flex: 1;">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="20 6 9 17 4 12"></polyline>
                        </svg>
                        Konfirmasi Peminjaman
                    </button>
                    <a href="books.php" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include_once '../../includes/footer.php'; ?>
