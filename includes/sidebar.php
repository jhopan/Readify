<?php
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
$user = getCurrentUser();
?>

<div class="sidebar">
    <div class="sidebar-logo">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
            <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>
        </svg>
        <h1>Readify</h1>
    </div>

    <nav class="sidebar-nav">
        <a href="<?php echo SITE_URL; ?>/pages/dashboard.php" class="nav-item <?php echo $currentPage == 'dashboard' ? 'active' : ''; ?>">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="3" y="3" width="7" height="7"></rect>
                <rect x="14" y="3" width="7" height="7"></rect>
                <rect x="14" y="14" width="7" height="7"></rect>
                <rect x="3" y="14" width="7" height="7"></rect>
            </svg>
            Dashboard
        </a>

        <a href="<?php echo SITE_URL; ?>/pages/books/index.php" class="nav-item <?php echo $currentPage == 'index' && strpos($_SERVER['PHP_SELF'], 'books') !== false ? 'active' : ''; ?>">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
                <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>
            </svg>
            Buku
        </a>

        <a href="<?php echo SITE_URL; ?>/pages/members/index.php" class="nav-item <?php echo $currentPage == 'index' && strpos($_SERVER['PHP_SELF'], 'members') !== false ? 'active' : ''; ?>">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
                <circle cx="9" cy="7" r="4"></circle>
                <path d="M22 21v-2a4 4 0 0 0-3-3.87"></path>
                <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
            </svg>
            Anggota
        </a>

        <a href="<?php echo SITE_URL; ?>/pages/loans/index.php" class="nav-item <?php echo $currentPage == 'index' && strpos($_SERVER['PHP_SELF'], 'loans') !== false ? 'active' : ''; ?>">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
                <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>
                <path d="M12 7v10"></path>
                <path d="M9 10l3-3 3 3"></path>
            </svg>
            Peminjaman
        </a>

        <?php if (isAdmin()): ?>
            <hr style="margin: 16px 0; border: none; border-top: 1px solid var(--gray-200);">
            
            <a href="<?php echo SITE_URL; ?>/pages/users/index.php" class="nav-item <?php echo $currentPage == 'index' && strpos($_SERVER['PHP_SELF'], 'users') !== false ? 'active' : ''; ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                    <circle cx="9" cy="7" r="4"></circle>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                </svg>
                Kelola Users
            </a>
        <?php endif; ?>
    </nav>

    <div class="sidebar-user">
        <div class="user-info">
            <div class="user-avatar"><?php echo strtoupper(substr($user['name'], 0, 1)); ?></div>
            <div>
                <div class="user-name"><?php echo htmlspecialchars($user['name']); ?></div>
                <div class="user-role"><?php echo ucfirst($user['role']); ?></div>
            </div>
        </div>
        <a href="<?php echo SITE_URL; ?>/pages/auth/logout.php" class="btn btn-danger btn-sm" style="width: 100%;">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                <polyline points="16 17 21 12 16 7"></polyline>
                <line x1="21" y1="12" x2="9" y2="12"></line>
            </svg>
            Logout
        </a>
    </div>
</div>

<div class="main-content">
