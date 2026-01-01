<?php
// Load configuration untuk dynamic URL
require_once 'config/config.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Readify - Smart Digital Library Platform</title>
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        /* Landing Page Specific Styles */
        .landing-navbar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            z-index: 1000;
            padding: 16px 0;
        }

        .navbar-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .navbar-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
        }

        .navbar-brand svg {
            width: 36px;
            height: 36px;
            color: var(--primary-600);
        }

        .navbar-brand h1 {
            font-size: 24px;
            font-weight: 700;
            color: var(--gray-900);
        }

        .navbar-menu {
            display: flex;
            align-items: center;
            gap: 32px;
        }

        .navbar-links {
            display: flex;
            gap: 32px;
            align-items: center;
        }

        .nav-link {
            color: var(--gray-700);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.2s;
        }

        .nav-link:hover {
            color: var(--primary-600);
        }

        .navbar-actions {
            display: flex;
            gap: 12px;
        }

        .hero-section {
            margin-top: 80px;
            padding: 80px 24px;
            background: linear-gradient(135deg, var(--primary-50) 0%, #bae6fd 50%, #e0f2fe 100%);
            text-align: center;
        }

        .hero-content {
            max-width: 800px;
            margin: 0 auto;
        }

        .hero-title {
            font-size: 56px;
            font-weight: 800;
            color: var(--gray-900);
            margin-bottom: 24px;
            line-height: 1.2;
        }

        .hero-subtitle {
            font-size: 20px;
            color: var(--gray-600);
            margin-bottom: 40px;
            line-height: 1.6;
        }

        .hero-buttons {
            display: flex;
            gap: 16px;
            justify-content: center;
        }

        .btn-hero {
            padding: 16px 32px;
            font-size: 16px;
            font-weight: 600;
        }

        .features-section {
            padding: 80px 24px;
            background: white;
        }

        .section-header {
            text-align: center;
            margin-bottom: 60px;
        }

        .section-title {
            font-size: 40px;
            font-weight: 700;
            color: var(--gray-900);
            margin-bottom: 16px;
        }

        .section-subtitle {
            font-size: 18px;
            color: var(--gray-600);
        }

        .features-grid {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 32px;
        }

        .feature-card {
            background: white;
            border: 2px solid var(--gray-100);
            border-radius: 16px;
            padding: 32px;
            text-align: center;
            transition: all 0.3s;
        }

        .feature-card:hover {
            border-color: var(--primary-600);
            transform: translateY(-4px);
            box-shadow: 0 10px 30px rgba(14, 165, 233, 0.1);
        }

        .feature-icon {
            width: 64px;
            height: 64px;
            background: var(--primary-50);
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 24px;
        }

        .feature-icon svg {
            width: 32px;
            height: 32px;
            color: var(--primary-600);
        }

        .feature-title {
            font-size: 20px;
            font-weight: 600;
            color: var(--gray-900);
            margin-bottom: 12px;
        }

        .feature-description {
            color: var(--gray-600);
            line-height: 1.6;
        }

        .testimonials-section {
            padding: 80px 24px;
            background: var(--gray-50);
        }

        .testimonials-grid {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 32px;
        }

        .testimonial-card {
            background: white;
            border-radius: 16px;
            padding: 32px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
        }

        .testimonial-rating {
            color: #fbbf24;
            margin-bottom: 16px;
            font-size: 20px;
        }

        .testimonial-text {
            color: var(--gray-700);
            line-height: 1.6;
            margin-bottom: 24px;
            font-style: italic;
        }

        .testimonial-author {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .author-avatar {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            background: var(--primary-100);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            color: var(--primary-700);
        }

        .author-name {
            font-weight: 600;
            color: var(--gray-900);
        }

        .author-role {
            font-size: 14px;
            color: var(--gray-500);
        }

        .footer {
            background: var(--gray-900);
            color: white;
            padding: 40px 24px;
            text-align: center;
        }

        .footer-content {
            max-width: 1200px;
            margin: 0 auto;
        }

        @media (max-width: 768px) {
            .navbar-links {
                display: none;
            }

            .hero-title {
                font-size: 36px;
            }

            .hero-subtitle {
                font-size: 16px;
            }

            .hero-buttons {
                flex-direction: column;
            }

            .section-title {
                font-size: 32px;
            }
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="landing-navbar">
        <div class="navbar-container">
            <a href="#" class="navbar-brand">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
                    <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>
                </svg>
                <h1>Readify</h1>
            </a>

            <div class="navbar-menu">
                <div class="navbar-links">
                    <a href="#features" class="nav-link">Fitur</a>
                    <a href="#testimonials" class="nav-link">Testimoni</a>
                    <a href="#about" class="nav-link">Tentang</a>
                </div>

                <div class="navbar-actions">
                    <a href="pages/auth/login.php" class="btn btn-secondary btn-sm">Login</a>
                    <a href="pages/auth/register.php" class="btn btn-primary btn-sm">Daftar Gratis</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="hero-content">
            <h1 class="hero-title">Perpustakaan Digital Cerdas untuk Masa Depan</h1>
            <p class="hero-subtitle">
                Readify adalah platform perpustakaan digital modern yang memudahkan pengelolaan buku, anggota, dan peminjaman dengan sistem rekomendasi berbasis AI untuk pengalaman membaca yang personal.
            </p>
            <div class="hero-buttons">
                <a href="pages/auth/register.php" class="btn btn-primary btn-hero">Mulai Gratis</a>
                <a href="#features" class="btn btn-secondary btn-hero">Pelajari Lebih Lanjut</a>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="features-section">
        <div class="section-header">
            <h2 class="section-title">Fitur Unggulan</h2>
            <p class="section-subtitle">Solusi lengkap untuk manajemen perpustakaan modern</p>
        </div>

        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
                        <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>
                    </svg>
                </div>
                <h3 class="feature-title">Manajemen Buku Digital</h3>
                <p class="feature-description">
                    Kelola koleksi buku dengan mudah, lengkap dengan ISBN, kategori, dan informasi detail lainnya.
                </p>
            </div>

            <div class="feature-card">
                <div class="feature-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
                        <circle cx="9" cy="7" r="4"></circle>
                        <path d="M22 21v-2a4 4 0 0 0-3-3.87"></path>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                    </svg>
                </div>
                <h3 class="feature-title">Keanggotaan Fleksibel</h3>
                <p class="feature-description">
                    Sistem keanggotaan yang mudah dengan tracking aktivitas dan riwayat peminjaman lengkap.
                </p>
            </div>

            <div class="feature-card">
                <div class="feature-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"></circle>
                        <polyline points="12 6 12 12 16 14"></polyline>
                    </svg>
                </div>
                <h3 class="feature-title">Tracking Peminjaman Real-time</h3>
                <p class="feature-description">
                    Monitor status peminjaman, jatuh tempo, dan pengembalian dengan notifikasi otomatis.
                </p>
            </div>

            <div class="feature-card">
                <div class="feature-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="3"></circle>
                        <path d="M12 2v4m0 12v4M4.93 4.93l2.83 2.83m8.48 8.48l2.83 2.83M2 12h4m12 0h4M4.93 19.07l2.83-2.83m8.48-8.48l2.83-2.83"></path>
                    </svg>
                </div>
                <h3 class="feature-title">Rekomendasi Cerdas</h3>
                <p class="feature-description">
                    Sistem rekomendasi berbasis AI yang mempelajari preferensi pembaca untuk saran buku yang personal.
                </p>
            </div>

            <div class="feature-card">
                <div class="feature-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="12" y1="1" x2="12" y2="23"></line>
                        <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                    </svg>
                </div>
                <h3 class="feature-title">Manajemen Denda Otomatis</h3>
                <p class="feature-description">
                    Perhitungan denda otomatis untuk keterlambatan pengembalian dengan laporan yang detail.
                </p>
            </div>

            <div class="feature-card">
                <div class="feature-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                        <line x1="3" y1="9" x2="21" y2="9"></line>
                        <line x1="9" y1="21" x2="9" y2="9"></line>
                    </svg>
                </div>
                <h3 class="feature-title">Dashboard Komprehensif</h3>
                <p class="feature-description">
                    Visualisasi data lengkap dengan statistik, grafik, dan laporan untuk analisis mendalam.
                </p>
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section id="testimonials" class="testimonials-section">
        <div class="section-header">
            <h2 class="section-title">Apa Kata Mereka?</h2>
            <p class="section-subtitle">Dipercaya oleh berbagai perpustakaan di Indonesia</p>
        </div>

        <div class="testimonials-grid">
            <div class="testimonial-card">
                <div class="testimonial-rating">★★★★★</div>
                <p class="testimonial-text">
                    "Readify sangat membantu kami dalam mengelola perpustakaan sekolah. Interface-nya intuitif dan fitur rekomendasi bukunya luar biasa!"
                </p>
                <div class="testimonial-author">
                    <div class="author-avatar">SN</div>
                    <div>
                        <div class="author-name">Siti Nurhaliza</div>
                        <div class="author-role">Pustakawan, SMA Negeri 1</div>
                    </div>
                </div>
            </div>

            <div class="testimonial-card">
                <div class="testimonial-rating">★★★★★</div>
                <p class="testimonial-text">
                    "Sistem peminjaman otomatis dan tracking yang akurat membuat pekerjaan kami jauh lebih efisien. Highly recommended!"
                </p>
                <div class="testimonial-author">
                    <div class="author-avatar">AF</div>
                    <div>
                        <div class="author-name">Ahmad Fauzi</div>
                        <div class="author-role">Kepala Perpustakaan, Universitas Indonesia</div>
                    </div>
                </div>
            </div>

            <div class="testimonial-card">
                <div class="testimonial-rating">★★★★★</div>
                <p class="testimonial-text">
                    "Dashboard analytics-nya sangat membantu kami membuat keputusan pengadaan buku yang tepat berdasarkan data pembaca."
                </p>
                <div class="testimonial-author">
                    <div class="author-avatar">BS</div>
                    <div>
                        <div class="author-name">Budi Santoso</div>
                        <div class="author-role">Manager, Perpustakaan Kota</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer id="about" class="footer">
        <div class="footer-content">
            <h3 style="margin-bottom: 16px;">Readify</h3>
            <p style="color: var(--gray-400); margin-bottom: 24px;">
                Smart Digital Library Platform<br>
                Solusi modern untuk perpustakaan digital masa depan
            </p>
            <p style="color: var(--gray-500); font-size: 14px;">
                © 2025 Readify. All rights reserved.
            </p>
        </div>
    </footer>

    <script src="assets/js/main.js"></script>
</body>
</html>
