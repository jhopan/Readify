<?php
require_once '../../config/config.php';
requireLogin();
requireStaff(); // Staff dan Admin bisa tambah buku

$pageTitle = 'Tambah Buku';
$db = new Database();

// Get categories for dropdown
$categories = $db->fetchAll("SELECT * FROM categories ORDER BY name");

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $isbn = sanitize($_POST['isbn']);
    $title = sanitize($_POST['title']);
    $author = sanitize($_POST['author']);
    $publisher = sanitize($_POST['publisher']);
    $year = sanitize($_POST['year']);
    $category_id = $_POST['category_id'] ? (int)$_POST['category_id'] : null;
    $stock = (int)$_POST['stock'];
    $pages = $_POST['pages'] ? (int)$_POST['pages'] : null;
    $description = sanitize($_POST['description']);

    // Validation
    $errors = [];
    
    if (empty($isbn)) {
        $errors[] = 'ISBN harus diisi';
    }
    
    if (empty($title)) {
        $errors[] = 'Judul buku harus diisi';
    }
    
    if (empty($author)) {
        $errors[] = 'Pengarang harus diisi';
    }

    if ($stock < 0) {
        $errors[] = 'Stok tidak boleh negatif';
    }

    // Check if ISBN already exists
    if (empty($errors)) {
        $existingBook = $db->fetchOne("SELECT id FROM books WHERE isbn = ?", [$isbn]);
        
        if ($existingBook) {
            $errors[] = 'ISBN sudah terdaftar';
        }
    }

    // Create book
    if (empty($errors)) {
        try {
            $db->query("INSERT INTO books (isbn, title, author, publisher, year, category_id, stock, pages, description) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)", 
                [$isbn, $title, $author, $publisher, $year, $category_id, $stock, $pages, $description]);
            
            setFlashMessage('success', 'Buku berhasil ditambahkan!');
            redirect('/pages/books/index.php');
        } catch (Exception $e) {
            $errors[] = 'Gagal menambahkan buku. Silakan coba lagi.';
        }
    }
}

include_once '../../includes/header.php';
include_once '../../includes/sidebar.php';
?>

<div class="container">
    <div class="card-header">
        <h1 class="card-title">Tambah Buku</h1>
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
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">ISBN <span style="color: red;">*</span></label>
                    <input type="text" name="isbn" class="form-control" placeholder="978-0-00-000000-0" 
                           value="<?php echo isset($_POST['isbn']) ? htmlspecialchars($_POST['isbn']) : ''; ?>" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Tahun Terbit</label>
                    <input type="number" name="year" class="form-control" placeholder="2024" min="1900" max="<?php echo date('Y'); ?>"
                           value="<?php echo isset($_POST['year']) ? htmlspecialchars($_POST['year']) : ''; ?>">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Judul Buku <span style="color: red;">*</span></label>
                <input type="text" name="title" class="form-control" placeholder="Masukkan judul buku" 
                       value="<?php echo isset($_POST['title']) ? htmlspecialchars($_POST['title']) : ''; ?>" required>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Pengarang <span style="color: red;">*</span></label>
                    <input type="text" name="author" class="form-control" placeholder="Nama pengarang" 
                           value="<?php echo isset($_POST['author']) ? htmlspecialchars($_POST['author']) : ''; ?>" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Penerbit</label>
                    <input type="text" name="publisher" class="form-control" placeholder="Nama penerbit" 
                           value="<?php echo isset($_POST['publisher']) ? htmlspecialchars($_POST['publisher']) : ''; ?>">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Kategori</label>
                    <select name="category_id" class="form-control">
                        <option value="">-- Pilih Kategori --</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo $category['id']; ?>" 
                                <?php echo (isset($_POST['category_id']) && $_POST['category_id'] == $category['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($category['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Jumlah Halaman</label>
                    <input type="number" name="pages" class="form-control" placeholder="0" min="0"
                           value="<?php echo isset($_POST['pages']) ? htmlspecialchars($_POST['pages']) : ''; ?>">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Stok <span style="color: red;">*</span></label>
                <input type="number" name="stock" class="form-control" placeholder="0" min="0"
                       value="<?php echo isset($_POST['stock']) ? htmlspecialchars($_POST['stock']) : '0'; ?>" required>
            </div>

            <div class="form-group">
                <label class="form-label">Deskripsi</label>
                <textarea name="description" class="form-control" rows="4" placeholder="Deskripsi buku (opsional)"><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
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
