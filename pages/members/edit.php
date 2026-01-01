<?php
require_once '../../config/config.php';
requireLogin();
requireStaff(); // Staff dan Admin bisa edit anggota

$pageTitle = 'Edit Anggota';
$db = new Database();

// Get member ID
$memberId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Get member data
$member = $db->fetchOne("SELECT * FROM members WHERE id = ?", [$memberId]);

if (!$member) {
    setFlashMessage('error', 'Anggota tidak ditemukan!');
    redirect('/pages/members/index.php');
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = sanitize($_POST['name']);
    $email = sanitize($_POST['email']);
    $phone = sanitize($_POST['phone']);
    $address = sanitize($_POST['address']);
    $status = sanitize($_POST['status']);

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

    if (!in_array($status, ['active', 'inactive'])) {
        $errors[] = 'Status tidak valid';
    }

    // Check if email already exists (except current member)
    if (empty($errors)) {
        $existingMember = $db->fetchOne("SELECT id FROM members WHERE email = ? AND id != ?", [$email, $memberId]);
        
        if ($existingMember) {
            $errors[] = 'Email sudah digunakan oleh anggota lain';
        }
    }

    // Update member
    if (empty($errors)) {
        try {
            $db->query("UPDATE members SET name = ?, email = ?, phone = ?, address = ?, status = ?, updated_at = NOW() WHERE id = ?", 
                [$name, $email, $phone, $address, $status, $memberId]);
            
            // Also update linked user if exists
            if ($member['user_id']) {
                $db->query("UPDATE users SET name = ?, email = ?, phone = ?, updated_at = NOW() WHERE id = ?", 
                    [$name, $email, $phone, $member['user_id']]);
            }
            
            setFlashMessage('success', 'Anggota berhasil diupdate!');
            redirect('/pages/members/index.php');
        } catch (Exception $e) {
            $errors[] = 'Gagal mengupdate anggota. Silakan coba lagi.';
        }
    }
}

include_once '../../includes/header.php';
include_once '../../includes/sidebar.php';
?>

<div class="container">
    <div class="card-header">
        <h1 class="card-title">Edit Anggota</h1>
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

        <div style="background: var(--gray-100); padding: 12px 16px; border-radius: 8px; margin-bottom: 20px;">
            <strong>ID Anggota:</strong> <?php echo htmlspecialchars($member['member_id']); ?>
        </div>

        <form method="POST" action="">
            <div class="form-group">
                <label class="form-label">Nama Lengkap <span style="color: red;">*</span></label>
                <input type="text" name="name" class="form-control" placeholder="Masukkan nama lengkap" 
                       value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : htmlspecialchars($member['name']); ?>" required>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Email <span style="color: red;">*</span></label>
                    <input type="email" name="email" class="form-control" placeholder="email@example.com" 
                           value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : htmlspecialchars($member['email']); ?>" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Nomor Telepon <span style="color: red;">*</span></label>
                    <input type="tel" name="phone" class="form-control" placeholder="08123456789" 
                           value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : htmlspecialchars($member['phone'] ?? ''); ?>" required>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Alamat</label>
                <textarea name="address" class="form-control" rows="3" placeholder="Alamat lengkap (opsional)"><?php echo isset($_POST['address']) ? htmlspecialchars($_POST['address']) : htmlspecialchars($member['address'] ?? ''); ?></textarea>
            </div>

            <div class="form-group">
                <label class="form-label">Status <span style="color: red;">*</span></label>
                <select name="status" class="form-control" required>
                    <option value="active" <?php echo (isset($_POST['status']) ? $_POST['status'] : $member['status']) == 'active' ? 'selected' : ''; ?>>Aktif</option>
                    <option value="inactive" <?php echo (isset($_POST['status']) ? $_POST['status'] : $member['status']) == 'inactive' ? 'selected' : ''; ?>>Tidak Aktif</option>
                </select>
            </div>

            <div style="display: flex; gap: 12px; margin-top: 24px;">
                <button type="submit" class="btn btn-primary">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="20 6 9 17 4 12"></polyline>
                    </svg>
                    Update
                </button>
                <a href="index.php" class="btn btn-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>

<?php include_once '../../includes/footer.php'; ?>
