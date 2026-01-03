<?php
require_once '../../config/config.php';
requireLogin();
requireAdmin(); // Only admin can access this page

$pageTitle = 'Edit User';
$db = new Database();

// Get user ID from GET or POST
$userId = isset($_GET['id']) ? (int)$_GET['id'] : (isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0);

// Get user data
$user = $db->fetchOne("SELECT * FROM users WHERE id = ?", [$userId]);

if (!$user) {
    setFlashMessage('error', 'User tidak ditemukan!');
    redirect('/pages/users/index.php');
}

// Prevent editing own account role
if ($user['id'] == $_SESSION['user_id']) {
    setFlashMessage('error', 'Anda tidak dapat mengubah role akun Anda sendiri!');
    redirect('/pages/users/index.php');
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = sanitize($_POST['name']);
    $email = sanitize($_POST['email']);
    $phone = sanitize($_POST['phone']);
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];
    $role = sanitize($_POST['role']);

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
    
    if (!empty($password)) {
        if (strlen($password) < 6) {
            $errors[] = 'Password minimal 6 karakter';
        }
        
        if ($password !== $confirmPassword) {
            $errors[] = 'Password tidak cocok';
        }
    }

    if (!in_array($role, ['admin', 'staff', 'member'])) {
        $errors[] = 'Role tidak valid';
    }

    // Check if email already exists (except current user)
    if (empty($errors)) {
        $existingUser = $db->fetchOne("SELECT id FROM users WHERE email = ? AND id != ?", [$email, $userId]);
        
        if ($existingUser) {
            $errors[] = 'Email sudah digunakan oleh user lain';
        }
    }

    // Update user
    if (empty($errors)) {
        try {
            if (!empty($password)) {
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $db->query("UPDATE users SET name = ?, email = ?, phone = ?, password = ?, role = ?, updated_at = NOW() WHERE id = ?", 
                    [$name, $email, $phone, $hashedPassword, $role, $userId]);
            } else {
                $db->query("UPDATE users SET name = ?, email = ?, phone = ?, role = ?, updated_at = NOW() WHERE id = ?", 
                    [$name, $email, $phone, $role, $userId]);
            }
            
            setFlashMessage('success', 'User berhasil diupdate!');
            redirect('/pages/users/index.php');
        } catch (Exception $e) {
            $errors[] = 'Gagal mengupdate user. Silakan coba lagi.';
        }
    }
}

include_once '../../includes/header.php';
include_once '../../includes/sidebar.php';
?>

<div class="container">
    <div class="card-header">
        <h1 class="card-title">Edit User</h1>
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

        <form method="POST" action="edit.php?id=<?php echo $userId; ?>">
            <input type="hidden" name="user_id" value="<?php echo $userId; ?>">
            <div class="form-group">
                <label class="form-label">Nama Lengkap <span style="color: red;">*</span></label>
                <input type="text" name="name" class="form-control" placeholder="John Doe" 
                       value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : htmlspecialchars($user['name']); ?>" required>
            </div>

            <div class="form-group">
                <label class="form-label">Email <span style="color: red;">*</span></label>
                <input type="email" name="email" class="form-control" placeholder="email@example.com" 
                       value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : htmlspecialchars($user['email']); ?>" required>
            </div>

            <div class="form-group">
                <label class="form-label">Nomor Telepon <span style="color: red;">*</span></label>
                <input type="tel" name="phone" class="form-control" placeholder="08123456789" 
                       value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : htmlspecialchars($user['phone'] ?? ''); ?>" required>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Password Baru</label>
                    <input type="password" name="password" class="form-control" placeholder="Kosongkan jika tidak ingin mengubah">
                    <small style="color: var(--gray-600); display: block; margin-top: 4px;">
                        Minimal 6 karakter. Kosongkan jika tidak ingin mengubah password
                    </small>
                </div>

                <div class="form-group">
                    <label class="form-label">Konfirmasi Password Baru</label>
                    <input type="password" name="confirm_password" class="form-control" placeholder="Ulangi password baru">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Role <span style="color: red;">*</span></label>
                <select name="role" class="form-control" required>
                    <option value="">-- Pilih Role --</option>
                    <option value="member" <?php echo (isset($_POST['role']) ? $_POST['role'] : $user['role']) == 'member' ? 'selected' : ''; ?>>Member (Anggota Perpustakaan)</option>
                    <option value="staff" <?php echo (isset($_POST['role']) ? $_POST['role'] : $user['role']) == 'staff' ? 'selected' : ''; ?>>Staff (Kelola Perpustakaan)</option>
                    <option value="admin" <?php echo (isset($_POST['role']) ? $_POST['role'] : $user['role']) == 'admin' ? 'selected' : ''; ?>>Admin (Full Access)</option>
                </select>
                <small style="color: var(--gray-600); display: block; margin-top: 4px;">
                    Member: akses dashboard member | Staff: kelola buku, anggota, peminjaman | Admin: akses penuh termasuk kelola user
                </small>
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
