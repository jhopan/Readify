<?php
require_once '../../config/config.php';

// If already logged in, redirect to dashboard
if (isLoggedIn()) {
    redirect('/pages/dashboard.php');
}

// Handle registration form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = sanitize($_POST['name']);
    $email = sanitize($_POST['email']);
    $phone = sanitize($_POST['phone']);
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];

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
    
    if (strlen($password) < 6) {
        $errors[] = 'Password minimal 6 karakter';
    }
    
    if ($password !== $confirmPassword) {
        $errors[] = 'Password tidak cocok';
    }

    // Check if email already exists
    if (empty($errors)) {
        $db = new Database();
        $existingUser = $db->fetchOne("SELECT id FROM users WHERE email = ?", [$email]);
        
        if ($existingUser) {
            $errors[] = 'Email sudah terdaftar';
        }
    }

    // Register user
    if (empty($errors)) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        try {
            // Insert user dengan role member
            $db->query("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'member')", 
                [$name, $email, $hashedPassword]);
            
            $userId = $db->lastInsertId();
            
            // Generate member ID
            $memberCount = $db->fetchOne("SELECT COUNT(*) as count FROM members")['count'];
            $memberId = 'M' . str_pad($memberCount + 1, 3, '0', STR_PAD_LEFT);
            
            // Insert ke tabel members juga
            $db->query("INSERT INTO members (member_id, name, email, phone, join_date, status) VALUES (?, ?, ?, ?, CURDATE(), 'active')", 
                [$memberId, $name, $email, $phone]);
            
            // Set flag untuk tampilkan pesan sukses
            $registrationSuccess = true;
        } catch (Exception $e) {
            $errors[] = 'Registrasi gagal. Silakan coba lagi.';
        }
    }
}

$pageTitle = 'Register';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - Readify</title>
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <?php if (isset($registrationSuccess) && $registrationSuccess): ?>
                <!-- Success Message -->
                <div style="text-align: center; padding: 40px 20px;">
                    <div style="width: 80px; height: 80px; background: var(--success-100); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 24px;">
                        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="var(--success-600)" stroke-width="2">
                            <polyline points="20 6 9 17 4 12"></polyline>
                        </svg>
                    </div>
                    <h2 style="font-size: 24px; font-weight: 700; color: var(--success-600); margin-bottom: 12px;">Registrasi Berhasil!</h2>
                    <p style="color: var(--gray-600); margin-bottom: 24px; line-height: 1.6;">
                        Akun Anda berhasil dibuat. Silakan login untuk mulai menggunakan layanan perpustakaan Readify.
                    </p>
                    <a href="login.php" class="btn btn-primary" style="width: 100%;">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right: 8px;">
                            <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"></path>
                            <polyline points="10 17 15 12 10 7"></polyline>
                            <line x1="15" y1="12" x2="3" y2="12"></line>
                        </svg>
                        Login Sekarang
                    </a>
                </div>
            <?php else: ?>
                <!-- Registration Form -->
                <div class="auth-logo">
                    <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
                        <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>
                    </svg>
                    <h1 style="font-size: 32px; font-weight: 700; margin-top: 16px;">Readify</h1>
                    <p style="color: var(--gray-600); margin-top: 8px;">Buat Akun Baru</p>
                </div>

                <h2 class="auth-title">Register</h2>

                <div style="margin-bottom: 20px; padding: 12px; background-color: var(--primary-50); border-radius: 8px; border-left: 4px solid var(--primary-600);">
                    <p style="font-size: 14px; color: var(--primary-800); margin: 0;">
                        📝 Daftar sebagai <strong>Member</strong> perpustakaan dan nikmati akses ke ribuan buku!
                    </p>
                </div>

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
                        <label class="form-label">Nama Lengkap</label>
                        <input type="text" name="name" class="form-control" placeholder="John Doe" 
                               value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" placeholder="email@example.com" 
                               value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Nomor Telepon</label>
                        <input type="tel" name="phone" class="form-control" placeholder="08123456789" 
                               value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" placeholder="••••••••" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Konfirmasi Password</label>
                        <input type="password" name="confirm_password" class="form-control" placeholder="••••••••" required>
                    </div>

                    <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 24px;">
                        Daftar
                    </button>
                </form>

                <div style="text-align: center; margin-top: 24px; color: var(--gray-600);">
                    Sudah punya akun? 
                    <a href="login.php" style="color: var(--primary-600); text-decoration: none; font-weight: 600;">
                        Login di sini
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
