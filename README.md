# Readify - Platform Perpustakaan Digital Pintar

## 📚 Deskripsi Proyek

Readify adalah platform perpustakaan digital pintar dengan sistem rekomendasi personal. Aplikasi ini menyediakan manajemen perpustakaan lengkap dengan tiga tingkat akses: **Admin**, **Staff**, dan **Member** (antarmuka pelanggan).

## 👥 Anggota Kelompok dan Tugas

### 1. Jhosua Armando Putra Panjaitan (NIM: 2305541067)

Bertanggung jawab pada pengembangan modul inti sistem yang berkaitan langsung dengan operasional utama perpustakaan, meliputi:

**CRUD Buku**

- Tambah, ubah, hapus, dan lihat data buku
- Pengelolaan stok buku
- Integrasi kategori buku

**CRUD Peminjaman & Pengembalian**

- Pengajuan peminjaman oleh member
- Persetujuan dan penolakan peminjaman oleh staff/admin
- Proses pengembalian buku
- Perhitungan denda keterlambatan

**Kontribusi tambahan:**

- Penyusunan alur proses peminjaman
- Implementasi aturan bisnis (lama peminjaman dan denda)
- Integrasi antar modul

> 👉 **Alasan pembagian**: Modul ini merupakan fitur utama sistem sehingga membutuhkan integrasi antar tabel dan proses bisnis yang kompleks.

### 2. Rafly Kusuma Putra (NIM: 2305541133)

Bertanggung jawab pada pengelolaan data pengguna dan anggota perpustakaan, meliputi:

**CRUD Anggota Perpustakaan**

- Tambah, ubah, hapus, dan lihat data anggota
- Pengelolaan status anggota (aktif / nonaktif)

**CRUD Users**

- Manajemen akun Admin dan Staff
- Pengaturan role pengguna
- Validasi data pengguna

**Kontribusi tambahan:**

- Penyesuaian struktur tabel user
- Integrasi data user dengan data anggota

> 👉 **Alasan pembagian**: Modul ini berfokus pada manajemen data pengguna yang mendukung sistem autentikasi dan kontrol akses.

### 3. Chandra Bintang Lumban Siantar (NIM: 2105541037)

Bertanggung jawab pada penyajian data dan fitur pendukung sistem, meliputi:

**CRUD Dashboard Rekap Data**

- Menampilkan statistik jumlah buku, anggota, dan peminjaman
- Penyajian data dalam bentuk ringkasan sistem

**CRUD Riwayat Peminjaman**

- Menampilkan riwayat peminjaman anggota
- Penyimpanan histori transaksi peminjaman

**Kontribusi tambahan:**

- Implementasi tampilan dashboard
- Pengolahan data laporan peminjaman

> 👉 **Alasan pembagian**: Modul ini berfokus pada visualisasi data dan informasi sebagai bahan monitoring sistem.

## ✨ Fitur Utama

1. **Autentikasi Tiga Tingkat**:

   - **Admin** - Akses penuh sistem (CRUD users, books, members, loans)
   - **Staff** - Operasional perpustakaan (approve/reject loans, manage returns, inventory)
   - **Member** - Antarmuka pelanggan (browse books, request loans, view history)

2. **Antarmuka Member**:

   - Sistem pendaftaran otomatis untuk member baru
   - Katalog buku dengan gradient cover (8 skema warna)
   - Pencarian & filter berdasarkan kategori (10 kategori)
   - Pengajuan peminjaman dengan status pending

3. **Sistem Persetujuan Peminjaman**:

   - Alur status: Pending → Approved/Rejected → Returned
   - Dashboard persetujuan staff dengan 4 tab
   - Perhitungan denda otomatis (Rp 1.000/hari)
   - Manajemen stok otomatis

4. **Rekomendasi Pintar**:

   - Algoritma berbasis 2 kategori teratas yang sering dipinjam
   - Personalisasi per member
   - Badge "⭐ Pilihan Terbaik" untuk rekomendasi terbaik
   - Tidak termasuk buku yang sedang dipinjam

5. **Riwayat & Analitik**:

   - Filter berdasarkan tahun/bulan
   - Statistik: Total buku, Tepat waktu, Terlambat, Total denda
   - Persentase akurasi
   - Kartu visual dengan gradient thumbnails

6. **Visualisasi Dashboard**:
   - Statistik real-time
   - Penghitung ketersediaan buku
   - Pelacakan peminjaman aktif
   - Monitoring aktivitas member

## 🛠️ Teknologi yang Digunakan

- **Frontend**: HTML5, CSS3 (Custom CSS Variables), JavaScript (Vanilla)
- **Backend**: PHP 8.1+ (Native Sessions, PDO)
- **Database**: MySQL 8.0+
- **Autentikasi**: Role-based access control (admin/staff/member)
- **Keamanan Password**: bcrypt (password_hash/password_verify)
- **Keamanan**: Prepared statements, proteksi SQL injection
- **UI**: Gradient SVG book covers, responsive card layouts

## 📁 Struktur Folder

```
Readify/
├── assets/
│   ├── css/
│   │   └── style.css         # Custom CSS with variables
│   └── js/
│       └── main.js           # JavaScript utilities
├── config/
│   ├── config.php            # Site configuration & helpers
│   └── database.php          # PDO Database class
├── database/
│   ├── schema.sql            # Database schema only
│   └── database_full.sql     # Full database with sample data
├── includes/
│   ├── header.php            # HTML head & meta tags
│   ├── sidebar.php           # Admin/Staff sidebar navigation
│   └── footer.php            # Footer template
├── pages/
│   ├── auth/
│   │   ├── login.php         # Login page
│   │   ├── register.php      # Register with auto-member creation
│   │   └── logout.php        # Logout handler
│   ├── books/
│   │   ├── index.php         # Book list (admin/staff)
│   │   ├── create.php        # Add new book
│   │   ├── edit.php          # Edit book
│   │   └── delete.php        # Delete book handler
│   ├── members/
│   │   ├── index.php         # Member list
│   │   ├── create.php        # Add member
│   │   ├── edit.php          # Edit member
│   │   └── delete.php        # Delete member handler
│   ├── loans/
│   │   ├── index.php         # Loan approval dashboard (staff)
│   │   ├── create.php        # Create new loan
│   │   ├── return.php        # Return book handler
│   │   └── delete.php        # Delete loan handler
│   ├── users/
│   │   ├── index.php         # User management (admin only)
│   │   ├── create.php        # Add user
│   │   ├── edit.php          # Edit user
│   │   └── delete.php        # Delete user handler
│   ├── member/               # Customer interface
│   │   ├── dashboard.php     # Member dashboard
│   │   ├── books.php         # Book catalog with search
│   │   ├── borrow.php        # Loan request confirmation
│   │   ├── my-loans.php      # Active loans with status
│   │   ├── history.php       # Borrowing history with filters
│   │   ├── recommendations.php # Personal recommendations
│   │   └── request-return.php  # Request book return
│   ├── dashboard.php         # Admin/Staff dashboard
│   └── recommendations.php   # Admin recommendations view
├── CREDENTIALS.txt           # Login credentials (DO NOT UPLOAD!)
├── landing.php               # Landing page
├── index.php                 # Entry point (redirects to landing)
└── README.md                 # This file
```

## 🚀 Cara Menjalankan

### Prasyarat

- **PHP 8.1** atau lebih baru
- **MySQL 8.0** atau lebih baru
- **Laragon** / XAMPP / WAMP / MAMP
- **HeidiSQL** atau phpMyAdmin
- Web Browser modern (Chrome, Firefox, Edge)

### Instalasi

1. **Clone atau download repository**

```bash
git clone <repository-url>
# atau download ZIP dan extract
```

2. **Pindahkan ke folder server**

```bash
# Laragon
C:/laragon/www/UAS

# XAMPP
C:/xampp/htdocs/UAS

# WAMP
C:/wamp64/www/UAS
```

3. **Buat & Import Database**

**Opsi A - Via HeidiSQL (Recommended):**

```sql
-- 1. Buka HeidiSQL, connect ke MySQL
-- 2. Open SQL file: database/schema.sql
-- 3. Execute (F9)
-- Database 'readify' akan dibuat otomatis dengan:
--    - 7 tables (users, members, books, categories, loans, reading_history, backup_logs)
--    - 10 categories (Fiksi, Non-Fiksi, Sains, Teknologi, Sejarah, Biografi, Pendidikan, Komik, Bisnis, Kesehatan)
--    - 56 books across all categories
--    - 4 test users (admin, jhopan, rafly, staff)
--    - Sample members and loans
```

**Opsi B - Via MySQL CLI:**

```bash
mysql -u root -p
# Paste content dari schema.sql atau:
source C:/laragon/www/Readify/database/schema.sql
```

4. **Konfigurasi Database (Jika berbeda)**

```php
// File: config/database.php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');           // Sesuaikan jika ada password
define('DB_NAME', 'readify');
```

5. **Start Services**

```bash
# Laragon: Start All
# XAMPP: Start Apache + MySQL di Control Panel
```

6. **Akses Aplikasi**

```
Landing Page: http://localhost/Readify
Login Page:   http://localhost/Readify/pages/auth/login.php
```

> 💡 **URL Auto-Detect**: Sistem otomatis mendeteksi URL (localhost atau hosting).  
> Saat deploy ke hosting, URL akan otomatis berubah tanpa perlu edit kode!

### Akun Login Default

> 📄 **File `CREDENTIALS.txt` akan diberikan oleh pemilik repository**
>
> File tersebut berisi 7 akun lengkap (2 Admin, 3 Staff, 2 Member) dengan email, password, dan informasi lengkap lainnya untuk keperluan testing.

## 📝 Detail Fitur

### 🔐 Autentikasi & Otorisasi

- **Sistem role tiga tingkat**: admin, staff, member
- **Redirect berbasis role**:
  - Admin/Staff → `/pages/dashboard.php`
  - Member → `/pages/member/dashboard.php`
- **Pendaftaran otomatis**: Record member dibuat otomatis saat login pertama
- **Hashing password**: bcrypt dengan `password_hash()`
- **Manajemen session**: Secure PHP sessions
- **Generasi Member ID**: M001, M002, M003, dll.

### 📚 Manajemen Buku (Admin/Staff)

- **Operasi CRUD**: Create, Read, Update, Delete buku
- **Pencarian**: Judul, Penulis, ISBN dengan prepared statements
- **Filter Kategori**: 10 kategori dengan ID eksplisit
- **Manajemen Stok**: Update otomatis saat pinjam/kembali
- **Detail Buku**: Judul, Penulis, Penerbit, Tahun, Halaman, Deskripsi
- **Sistem ISBN**: Pola unik per kategori
- **Pemetaan Kategori**:
  - 1 = Fiksi (5 books)
  - 2 = Non-Fiksi (3 books)
  - 3 = Sains (5 books)
  - 4 = Teknologi (11 books)
  - 5 = Sejarah (7 books)
  - 6 = Biografi (6 books)
  - 7 = Pendidikan (2 books)
  - 8 = Komik (5 books)
  - 9 = Bisnis (7 books)
  - 10 = Kesehatan (5 books)

### 👥 Manajemen Member (Admin/Staff)

- **CRUD Anggota**: Tambah, Ubah, Lihat member
- **Status Member**: Aktif/Nonaktif
- **Link otomatis ke Users**: Hubungkan member ke akun user via email
- **Statistik Member**: Total buku dipinjam, tingkat ketepatan waktu
- **Pelacakan Tanggal Bergabung**: Diatur otomatis saat dibuat

### 📖 Antarmuka Member (Pelanggan)

#### Dashboard

- **Pendaftaran otomatis**: Member baru dibuat otomatis dengan ID M00X
- **Kartu Statistik**: Buku tersedia, Peminjaman aktif, Buku dipinjam
- **Aksi Cepat**: Jelajahi katalog, Lihat peminjaman, Cek riwayat

#### Katalog Buku

- **Gradient Covers**: 8 skema warna (berbasis SVG, tanpa gambar eksternal)
- **Pencarian & Filter**: Berdasarkan judul, penulis, ISBN, dan kategori
- **Kartu Buku**: Judul, Penulis, Penerbit, Tahun, Stok
- **Tombol Pinjam**: Pengajuan peminjaman langsung
- **Proteksi SQL Injection**: Prepared statements dengan parameter binding

#### Peminjaman Saya

- **Filter Status**: Semua, Pending, Approved, Returned
- **Kartu Status**: Indikator visual dengan warna
  - Pending: Kuning (#f59e0b)
  - Approved: Biru (#3b82f6)
  - Returned: Hijau (#10b981)
  - Rejected: Merah (#ef4444)
- **Peringatan Terlambat**: Alert merah untuk pengembalian terlambat
- **Tampilan Denda**: COALESCE untuk kalkulasi NULL-safe
- **Pelacakan Tanggal Jatuh Tempo**: Hari tersisa/terlambat

#### Riwayat & Analitik

- **Filter**: Dropdown tahun dan bulan dari data aktual
- **Kartu Statistik**:
  - Total Buku: COUNT(\*)
  - Tepat waktu: DATEDIFF(return_date, due_date) <= 0
  - Terlambat: DATEDIFF(return_date, due_date) > 0
  - Total Denda: SUM(COALESCE(fine_amount, 0))
- **Persentase Akurasi**: (tepat_waktu / total_buku) \* 100
- **Gradient Thumbnails**: Cover buku berkode warna
- **Hanya Status Returned**: Menampilkan peminjaman selesai

#### Rekomendasi Pintar

- **Algoritma**: Menganalisis 2 kategori teratas yang paling sering dipinjam
- **Personalisasi**: Berdasarkan riwayat baca individual user
- **Logika Pengecualian**: Menghapus buku yang sedang dipinjam
- **Badge Pilihan Terbaik**: ⭐ untuk 4 rekomendasi pertama
- **Fallback**: Buku acak jika tidak ada riwayat
- **Statistik**: Menampilkan total rekomendasi, buku tersedia, peminjaman aktif

### 📋 Manajemen Peminjaman (Staff)

#### Dashboard Persetujuan

- **4 Tab Status**: Pending, Approved, Returned, Rejected
- **Penghitung Tab**: Jumlah real-time per status
- **Tombol Aksi**:
  - **Approve**: Set status='approved', stok-1, approved_by, approved_date
  - **Reject**: Set status='rejected', memerlukan catatan (modal)
  - **Return**: Set status='returned', stok+1, hitung denda

#### Perhitungan Denda

- **Rumus**: DATEDIFF(CURDATE(), due_date) \* FINE_PER_DAY
- **Konstanta**:
  - `FINE_PER_DAY = 1000` (Rp 1.000)
  - `DEFAULT_LOAN_DAYS = 14` (2 minggu)
- **Kalkulasi otomatis**: Saat pengembalian jika terlambat
- **NULL-safe**: Menggunakan COALESCE(fine_amount, 0)

#### Alur Peminjaman

```
1. Member mengajukan → status='pending'
2. Staff meninjau di loans/index.php
3. Approve → status='approved', stok-1, log approved_by
4. Reject → status='rejected', simpan catatan
5. Return → status='returned', stok+1, hitung denda jika terlambat
```

### 👤 Manajemen User (Khusus Admin)

- **CRUD Users**: Create, Edit, Delete user sistem
- **Penugasan Role**: admin, staff, member
- **Manajemen Password**: Hash password saat create/update
- **Kontrol Akses**: Hanya admin yang dapat akses /pages/users/

### 📊 Dashboard (Admin/Staff)

- **Statistik Buku**: Total buku, tersedia, dipinjam
- **Statistik Member**: Total member, member aktif
- **Statistik Peminjaman**: Peminjaman aktif, persetujuan pending, pinjaman terlambat
- **Akses Cepat**: Link langsung ke halaman manajemen

## 📊 Database Schema

Database `readify` dengan 7 tables:

### 1. users

```sql
- id (PK, AUTO_INCREMENT)
- name VARCHAR(100)
- email VARCHAR(100) UNIQUE
- password VARCHAR(255) - bcrypt hash
- role ENUM('admin', 'staff', 'member') DEFAULT 'member'
- created_at, updated_at TIMESTAMP
```

### 2. categories

```sql
- id (PK, AUTO_INCREMENT)
- name VARCHAR(50) UNIQUE
- description TEXT
- Explicit IDs: 1-10 (Fiksi to Kesehatan)
```

### 3. books

```sql
- id (PK, AUTO_INCREMENT)
- isbn VARCHAR(20) UNIQUE
- title VARCHAR(255)
- author VARCHAR(100)
- publisher VARCHAR(100)
- year YEAR (1901-2155 range)
- category_id INT (FK to categories)
- stock INT DEFAULT 0
- pages INT
- description TEXT
- created_at, updated_at TIMESTAMP
```

### 4. members

```sql
- id (PK, AUTO_INCREMENT)
- member_id VARCHAR(20) UNIQUE (M001, M002, etc.)
- user_id INT NULLABLE (FK to users) - Link to login account
- name VARCHAR(100)
- email VARCHAR(100) UNIQUE
- phone VARCHAR(20)
- address TEXT
- join_date DATE
- status ENUM('active', 'inactive') DEFAULT 'active'
- created_at, updated_at TIMESTAMP
```

### 5. loans

```sql
- id (PK, AUTO_INCREMENT)
- loan_id VARCHAR(20) UNIQUE
- member_id INT (FK to members)
- book_id INT (FK to books)
- loan_date DATE
- due_date DATE
- return_date DATE NULLABLE
- status VARCHAR(50) DEFAULT 'pending'
  * Possible values: pending, approved, rejected, returned
- fine_amount DECIMAL(10,2) DEFAULT 0
- approved_by INT NULLABLE (FK to users)
- approved_date DATETIME NULLABLE
- book_condition ENUM('good', 'fair', 'damaged') DEFAULT 'good'
- notes TEXT
- created_at, updated_at TIMESTAMP
```

### 6. reading_history

```sql
- id (PK, AUTO_INCREMENT)
- member_id INT (FK to members)
- book_id INT (FK to books)
- read_date DATE
- rating INT (1-5)
- created_at TIMESTAMP
```

### 7. backup_logs

```sql
- id (PK, AUTO_INCREMENT)
- backup_file VARCHAR(255)
- backup_date DATETIME
- file_size INT
- status ENUM('success', 'failed')
- created_at TIMESTAMP
```

## 📈 Statistik Data

- **Total Buku**: 56 buku di 10 kategori
- **Total Kategori**: 10 dengan ID eksplisit
- **User Testing**: 4 (1 admin, 1 jhopan admin, 1 member, 1 staff)
- **Sample Member**: 3 data awal
- **Sample Peminjaman**: 2 untuk testing
- **Distribusi Buku**:
  - Fiksi: 5 books (To Kill a Mockingbird, 1984, Harry Potter, etc.)
  - Non-Fiksi: 3 books (Into the Wild, The Tipping Point, Educated)
  - Sains: 5 books (Hawking, Sagan, Dawkins, Tyson, Darwin)
  - Teknologi: 11 books (Web Programming, MySQL, PHP, Algorithms, JavaScript, Python, ML, etc.)
  - Sejarah: 7 books (Guns Germs Steel, Sapiens, Silk Roads, etc.)
  - Biografi: 6 books (Steve Jobs, Einstein, Mandela, Anne Frank, Michelle Obama, Elon Musk)
  - Pendidikan: 2 books (Mindset, Drive)
  - Komik: 5 books (One Piece, Naruto, Attack on Titan, Death Note, My Hero Academia)
  - Bisnis: 7 books (Atomic Habits, Start With Why, 7 Habits, etc.)
  - Kesehatan: 5 books (Why We Sleep, The Body, How Not to Die, Brain Rules, Breath)

## 🔒 Fitur Keamanan

- **Hashing Password**: bcrypt via `password_hash()` dan `password_verify()`
- **Prepared Statements**: Semua query menggunakan PDO prepared statements dengan parameter binding
- **Proteksi SQL Injection**: Tidak ada konkatenasi string dalam query
- **Role-Based Access Control**: Otorisasi tiga tingkat (admin/staff/member)
- **Keamanan Session**: PHP native sessions dengan konfigurasi aman
- **Sanitasi Input**: `htmlspecialchars()` untuk output, prepared statements untuk input
- **Foreign Key Constraints**: Integritas referensial di level database
- **Proteksi CSRF**: Validasi berbasis session
- **Operasi NULL-Safe**: COALESCE() untuk query database
- **Validasi Password**: Persyaratan minimum diterapkan

## 🎨 Fitur UI/UX

### Sistem Desain

- **CSS Variables**: Palet warna dan spacing terpusat
- **Layout Responsif**: Grid kartu ramah mobile
- **Gradient Covers**: 8 pasang warna untuk thumbnail buku
- **Warna Status**:
  - Primary: #3b82f6 (Blue)
  - Success: #10b981 (Green)
  - Warning: #f59e0b (Yellow/Orange)
  - Danger: #ef4444 (Red)
  - Info: #06b6d4 (Cyan)

### Komponen

- **Status Badges**: Berkode warna (success/warning/danger/info)
- **Cards**: Efek hover dengan transisi shadow
- **Modal Dialogs**: Untuk konfirmasi dan form
- **Statistics Cards**: Layout grid dengan icon
- **Navigation**: Sidebar dengan indikator status aktif
- **Alerts**: Pesan flash dengan auto-dismiss
- **Empty States**: Pesan ramah dengan ilustrasi

### Aksesibilitas

- **Semantic HTML**: Hierarki heading yang proper
- **SVG Icons**: Dapat diperbesar dan tajam di layar apapun
- **Font yang Mudah Dibaca**: System font stack
- **Kontras Warna**: Sesuai WCAG
- **Focus States**: Dukungan navigasi keyboard

## 🚨 Penanganan Error

### Masalah Umum & Solusi

**1. "Column 'user_id' not found"**

```sql
-- Solution: Run schema.sql fresh install
DROP DATABASE IF EXISTS readify;
-- Then execute full schema.sql
```

**2. "Duplicate entry for key 'members.email'"**

```sql
-- Solution: Update existing member with user_id
UPDATE members SET user_id = (SELECT id FROM users WHERE email = 'user@example.com')
WHERE email = 'user@example.com';
```

**3. "Foreign key constraint fails"**

```sql
-- Solution: Check if referenced user/member/book exists
SELECT * FROM users WHERE id = X;
-- If not exists, use NULL or create the record first
```

**4. "Out of range value for column 'year'"**

```sql
-- Solution: YEAR column accepts 1901-2155 only
-- Update old years to modern editions (e.g., 1859 → 2009)
```

**5. "Undefined method 'execute'"**

```php
// Solution: Use query() instead of execute() in Database class
$db->query("INSERT INTO ...", $params);
// NOT: $db->execute("INSERT INTO ...", $params);
```

## 📝 Best Practices yang Diterapkan

### Kualitas Kode

- ✅ **Separation of Concerns**: Config, Includes, Pages terpisah
- ✅ **DRY Principle**: Fungsi helper yang dapat digunakan kembali di config.php
- ✅ **Consistent Naming**: snake_case untuk DB, camelCase untuk PHP
- ✅ **Error Handling**: Blok try-catch untuk operasi database
- ✅ **Code Comments**: Penjelasan jelas untuk logika kompleks

### Desain Database

- ✅ **Normalisasi**: Struktur 3NF yang proper
- ✅ **Indexes**: Pada kolom yang sering di-query
- ✅ **Foreign Keys**: Menerapkan integritas referensial
- ✅ **Timestamps**: Tracking created_at, updated_at
- ✅ **ID Eksplisit**: ID Kategori 1-10 untuk konsistensi

### Keamanan

- ✅ **Tidak Ada Plain Password**: Semua di-hash bcrypt
- ✅ **Prepared Statements**: 100% dari query
- ✅ **Session Management**: Secure PHP sessions
- ✅ **Pencegahan XSS**: htmlspecialchars() pada output
- ✅ **Access Control**: requireLogin(), requireAdmin(), requireStaff()

## 🔄 Contoh Alur Kerja

### Alur Peminjaman Member

```
1. Member login → Membuat record member otomatis jika diperlukan
2. Browse books.php → Cari/filter berdasarkan kategori
3. Klik "Pinjam Buku" → Halaman konfirmasi (borrow.php)
4. Konfirmasi → INSERT ke loans dengan status='pending'
5. Staff melihat loans/index.php → Tab Pending
6. Staff klik "Approve" → status='approved', stok-1, log approver
7. Member melihat my-loans.php → Lihat status approved
8. Staff proses pengembalian → status='returned', stok+1, hitung denda
9. Member melihat history.php → Lihat peminjaman selesai dengan denda (jika ada)
```

### Alur Algoritma Rekomendasi

```
1. User mengunjungi recommendations.php
2. Sistem query: SELECT 2 kategori teratas berdasarkan borrow_count
3. Jika ditemukan: Ambil 12 buku acak dari kategori tersebut
4. Tidak termasuk: Buku yang sedang dipinjam user
5. Jika < 12 buku: Tambahkan buku acak dari kategori lain
6. Tampilkan dengan badge "⭐ Pilihan Terbaik" untuk 4 pertama
7. Tampilkan statistik: Total rekomendasi, Tersedia, Peminjaman aktif
```

---

## 📄 Copyright & Lisensi

© 2026 **Readify** - Platform Perpustakaan Digital Pintar. All rights reserved.

Dilisensikan di bawah [MIT License](https://opensource.org/licenses/MIT) - Gratis untuk digunakan untuk tujuan edukasi.

---

## 🙏 Acknowledgments

- **Icons**: SVG icons (tanpa dependensi eksternal)
- **Colors**: Palet warna terinspirasi Tailwind
- **Typography**: System font stack untuk performa terbaik
- **PHP**: Fitur native (tanpa framework)
- **Database**: MySQL 8.0+ dengan InnoDB engine

## 📞 Dukungan

Untuk pertanyaan atau issues, silakan hubungi tim pengembang atau buat issue di repository.

---

**Dibuat dengan ❤️ untuk UAS Pronet Ganjil 2025/2026**
