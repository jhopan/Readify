<?php
require_once '../../config/config.php';
requireLogin();
requireStaff(); // Staff dan Admin bisa tambah anggota

$pageTitle = 'Tambah Anggota';
$db = new Database();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = sanitize($_POST['name']);
    $email = sanitize($_POST['email']);
    $phone = sanitize($_POST['phone']);
    $address = sanitize($_POST['address']);

    // Validation
    $errors = [];
    
    if (empty($name)) {
        $errors[] = 'Nama harus diisi';
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Email tidak valid';
    }

    if (empty($phone)) {
        $errors[] = 'Nomor telepon harus diisi';
    } elseif (!preg_match('/^[0-9]{10,15}$/', $phone)) {
        $errors[] = 'Nomor telepon tidak valid (10-15 digit)';
    }

    // Check if email already exists
    if (empty($errors)) {
        $existingMember = $db->fetchOne("SELECT id FROM members WHERE email = ?", [$email]);
        
        if ($existingMember) {
            $errors[] = 'Email sudah terdaftar sebagai anggota';
        }
    }

    // Create member
    if (empty($errors)) {
        try {
            // Generate member ID
            $lastMember = $db->fetchOne("SELECT member_id FROM members ORDER BY id DESC LIMIT 1");
            if ($lastMember) {
                $lastNum = (int)substr($lastMember['member_id'], 1);
                $memberId = 'M' . str_pad($lastNum + 1, 3, '0', STR_PAD_LEFT);
            } else {
                $memberId = 'M001';
            }
            
            $db->query("INSERT INTO members (member_id, name, email, phone, address, join_date, status) 
                        VALUES (?, ?, ?, ?, ?, CURDATE(), 'active')", 
                [$memberId, $name, $email, $phone, $address]);
            
            setFlashMessage('success', 'Anggota berhasil ditambahkan dengan ID: ' . $memberId);
            redirect('/pages/members/index.php');
        } catch (Exception $e) {
            $errors[] = 'Gagal menambahkan anggota. Silakan coba lagi.';
        }
    }
}

include_once '../../includes/header.php';
include_once '../../includes/sidebar.php';
?>

<div class="container">
    <div class="card-header">
        <h1 class="card-title">Tambah Anggota</h1>
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
                <label class="form-label">Nama Lengkap <span style="color: red;">*</span></label>
                <input type="text" name="name" class="form-control" placeholder="Masukkan nama lengkap" 
                       value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>" required>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Email <span style="color: red;">*</span></label>
                    <input type="email" name="email" class="form-control" placeholder="email@example.com" 
                           value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Nomor Telepon <span style="color: red;">*</span></label>
                    <input type="tel" name="phone" class="form-control" placeholder="08123456789" 
                           value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>" required>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Alamat</label>
                <textarea name="address" class="form-control" rows="3" placeholder="Alamat lengkap (opsional)"><?php echo isset($_POST['address']) ? htmlspecialchars($_POST['address']) : ''; ?></textarea>
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
