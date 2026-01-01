<?php
require_once '../../config/config.php';

// If already logged in, redirect to dashboard
if (isLoggedIn()) {
    redirect('/pages/dashboard.php');
}

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];

    $db = new Database();
    $user = $db->fetchOne("SELECT * FROM users WHERE email = ?", [$email]);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_role'] = $user['role'];
        
        setFlashMessage('success', 'Login berhasil! Selamat datang, ' . $user['name']);
        
        // Redirect berdasarkan role
        if ($user['role'] === 'member') {
            redirect('/pages/member/dashboard.php');
        } else {
            redirect('/pages/dashboard.php');
        }
    } else {
        $error = 'Email atau password salah!';
    }
}

$pageTitle = 'Login';
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
            <div class="auth-logo">
                <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
                    <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>
                </svg>
                <h1 style="font-size: 32px; font-weight: 700; margin-top: 16px;">Readify</h1>
                <p style="color: var(--gray-600); margin-top: 8px;">Smart Digital Library Platform</p>
            </div>

            <h2 class="auth-title">Login</h2>

            <?php if (isset($error)): ?>
                <div class="alert alert-error">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" placeholder="email@example.com" required autofocus>
                </div>

                <div class="form-group">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" placeholder="••••••••" required>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 24px;">
                    Login
                </button>
            </form>

            <div style="text-align: center; margin-top: 24px; color: var(--gray-600);">
                Belum punya akun? 
                <a href="register.php" style="color: var(--primary-600); text-decoration: none; font-weight: 600;">
                    Daftar sekarang
                </a>
            </div>
        </div>
    </div>
</body>
</html>
