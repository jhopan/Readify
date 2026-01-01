<?php
require_once '../../config/config.php';
requireLogin();
requireStaff(); // Staff dan Admin bisa tambah peminjaman

$pageTitle = 'Tambah Peminjaman';
$db = new Database();
$user = getCurrentUser();

// Get members for dropdown
$members = $db->fetchAll("SELECT * FROM members WHERE status = 'active' ORDER BY name");

// Get available books for dropdown
$books = $db->fetchAll("SELECT * FROM books WHERE stock > 0 ORDER BY title");

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $memberId = (int)$_POST['member_id'];
    $bookId = (int)$_POST['book_id'];
    $loanDays = isset($_POST['loan_days']) ? (int)$_POST['loan_days'] : DEFAULT_LOAN_DAYS;

    // Validation
    $errors = [];
    
    if (!$memberId) {
        $errors[] = 'Pilih anggota';
    }
    
    if (!$bookId) {
        $errors[] = 'Pilih buku';
    }

    // Check member exists
    $member = $db->fetchOne("SELECT * FROM members WHERE id = ? AND status = 'active'", [$memberId]);
    if (!$member) {
        $errors[] = 'Anggota tidak valid atau tidak aktif';
    }

    // Check book exists and has stock
    $book = $db->fetchOne("SELECT * FROM books WHERE id = ? AND stock > 0", [$bookId]);
    if (!$book) {
        $errors[] = 'Buku tidak tersedia atau stok habis';
    }

    // Check if member already borrowing this book
    if (empty($errors)) {
        $existingLoan = $db->fetchOne(
            "SELECT id FROM loans WHERE member_id = ? AND book_id = ? AND status IN ('pending', 'approved')", 
            [$memberId, $bookId]
        );
        if ($existingLoan) {
            $errors[] = 'Anggota sudah meminjam buku ini';
        }
    }

    // Create loan
    if (empty($errors)) {
        try {
            // Generate loan ID
            $lastLoan = $db->fetchOne("SELECT loan_id FROM loans ORDER BY id DESC LIMIT 1");
            if ($lastLoan) {
                $lastNum = (int)substr($lastLoan['loan_id'], 1);
                $loanId = 'L' . str_pad($lastNum + 1, 5, '0', STR_PAD_LEFT);
            } else {
                $loanId = 'L00001';
            }
            
            $loanDate = date('Y-m-d');
            $dueDate = date('Y-m-d', strtotime("+$loanDays days"));
            
            // Insert loan as approved (karena dibuat langsung oleh staff)
            $db->query("INSERT INTO loans (loan_id, member_id, book_id, loan_date, due_date, status, approved_by, approved_date) 
                        VALUES (?, ?, ?, ?, ?, 'approved', ?, NOW())", 
                [$loanId, $memberId, $bookId, $loanDate, $dueDate, $user['id']]);
            
            // Reduce book stock
            $db->query("UPDATE books SET stock = stock - 1 WHERE id = ?", [$bookId]);
            
            setFlashMessage('success', 'Peminjaman berhasil ditambahkan! ID: ' . $loanId);
            redirect('/pages/loans/index.php?status=approved');
        } catch (Exception $e) {
            $errors[] = 'Gagal menambahkan peminjaman. Silakan coba lagi.';
        }
    }
}

include_once '../../includes/header.php';
include_once '../../includes/sidebar.php';
?>

<div class="container">
    <div class="card-header">
        <h1 class="card-title">Tambah Peminjaman</h1>
        <a href="index.php" class="btn btn-secondary">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="19" y1="12" x2="5" y2="12"></line>
                <polyline points="12 19 5 12 12 5"></polyline>
            </svg>
            Kembali
        </a>
    </div>

    <div class="card">
        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <ul style="margin: 0; padding-left: 20px;">
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo $error; ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label class="form-label">Anggota <span style="color: red;">*</span></label>
                <select name="member_id" class="form-control" required>
                    <option value="">-- Pilih Anggota --</option>
                    <?php foreach ($members as $member): ?>
                        <option value="<?php echo $member['id']; ?>" 
                            <?php echo (isset($_POST['member_id']) && $_POST['member_id'] == $member['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($member['member_id'] . ' - ' . $member['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Buku <span style="color: red;">*</span></label>
                <select name="book_id" class="form-control" required>
                    <option value="">-- Pilih Buku --</option>
                    <?php foreach ($books as $book): ?>
                        <option value="<?php echo $book['id']; ?>" 
                            <?php echo (isset($_POST['book_id']) && $_POST['book_id'] == $book['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($book['title'] . ' (Stok: ' . $book['stock'] . ')'); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Durasi Peminjaman (hari)</label>
                <input type="number" name="loan_days" class="form-control" value="<?php echo DEFAULT_LOAN_DAYS; ?>" min="1" max="30">
                <small style="color: var(--gray-600); display: block; margin-top: 4px;">
                    Default: <?php echo DEFAULT_LOAN_DAYS; ?> hari
                </small>
            </div>

            <div style="display: flex; gap: 12px; margin-top: 24px;">
                <button type="submit" class="btn btn-primary">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="20 6 9 17 4 12"></polyline>
                    </svg>
                    Simpan
                </button>
                <a href="index.php" class="btn btn-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>

<?php include_once '../../includes/footer.php'; ?>
