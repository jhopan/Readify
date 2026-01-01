# Readify - Smart Digital Library Platform

## 📚 Deskripsi Project

Readify adalah platform perpustakaan digital pintar dengan sistem rekomendasi personal. Aplikasi ini menyediakan manajemen perpustakaan lengkap dengan tiga tingkat akses: **Admin**, **Staff**, dan **Member** (customer interface).

## 👥 Tim Pengembang

- **Anggota A (NIM: 22001)**: CRUD Buku + Dashboard Rekap Data
- **Anggota B (NIM: 22002)**: CRUD Anggota Perpustakaan + CRUD Users
- **Anggota C (NIM: 22003)**: CRUD Peminjaman & Pengembalian + Fitur Rekomendasi Personal

## ✨ Fitur Utama

1. **Three-Tier Authentication**:

   - **Admin** - Full system access (CRUD users, books, members, loans)
   - **Staff** - Library operations (approve/reject loans, manage returns, inventory)
   - **Member** - Customer interface (browse books, request loans, view history)

2. **Member Interface**:

   - Auto-registration system untuk member baru
   - Katalog buku dengan gradient cover (8 color schemes)
   - Search & filter by category (10 kategori)
   - Request peminjaman dengan status pending

3. **Loan Approval System**:

   - Status workflow: Pending → Approved/Rejected → Returned
   - Staff approval dashboard dengan 4 tabs
   - Fine calculation otomatis (Rp 1,000/hari)
   - Stock management otomatis

4. **Smart Recommendations**:

   - Algoritma berbasis top 2 kategori yang sering dipinjam
   - Personalisasi per member
   - Badge "⭐ Top Pick" untuk rekomendasi terbaik
   - Exclude buku yang sedang dipinjam

5. **History & Analytics**:

   - Filter by year/month
   - Statistics: Total books, On-time, Late, Total fine
   - Accuracy percentage
   - Visual cards dengan gradient thumbnails

6. **Dashboard Visualization**:
   - Real-time statistics
   - Book availability counter
   - Active loans tracking
   - Member activity monitoring

## 🛠️ Tech Stack

- **Frontend**: HTML5, CSS3 (Custom CSS Variables), JavaScript (Vanilla)
- **Backend**: PHP 8.1+ (Native Sessions, PDO)
- **Database**: MySQL 8.0+
- **Authentication**: Role-based access control (admin/staff/member)
- **Password Security**: bcrypt (password_hash/password_verify)
- **Security**: Prepared statements, SQL injection protection
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
│   └── schema.sql            # Complete database schema (56 books)
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
│   │   └── action.php        # Approve/Reject/Return handler
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
│   │   └── recommendations.php # Personal recommendations
│   ├── dashboard.php         # Admin/Staff dashboard
│   └── recommendations.php   # (Moved to member interface)
├── landing.php               # Landing page
├── index.php                 # Entry point (redirects to landing)
└── README.md                 # This file
```

## 🚀 Cara Menjalankan

### Prerequisites

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
> Lihat [DEPLOYMENT.md](DEPLOYMENT.md) untuk panduan upload ke hosting.

### Default Login Accounts

**📄 Login credentials tersedia di file `CREDENTIALS.txt`**

File ini berisi **7 akun lengkap** dari database:
- **2 Admin accounts** (full system access)
- **3 Staff accounts** (library operations)
- **2 Member accounts** (customer interface)

Setiap akun dilengkapi dengan:
- Email & Password
- Nama lengkap & Phone number
- Role & Status

> ⚠️ **SECURITY WARNING**:  
> - File `CREDENTIALS.txt` berisi informasi sensitif untuk testing
> - **JANGAN upload** ke GitHub atau repository public
> - **WAJIB tambahkan** `CREDENTIALS.txt` ke `.gitignore`
> - Ganti password setelah deployment ke production

## 📝 Fitur Detail

### 🔐 Authentication & Authorization

- **Three-tier role system**: admin, staff, member
- **Role-based redirects**:
  - Admin/Staff → `/pages/dashboard.php`
  - Member → `/pages/member/dashboard.php`
- **Auto-registration**: Member record auto-created on first login
- **Password hashing**: bcrypt with `password_hash()`
- **Session management**: Secure PHP sessions
- **Member ID generation**: M001, M002, M003, etc.

### 📚 Book Management (Admin/Staff)

- **CRUD Operations**: Create, Read, Update, Delete books
- **Search**: Title, Author, ISBN with prepared statements
- **Category Filter**: 10 categories with explicit IDs
- **Stock Management**: Auto-update on loan/return
- **Book Details**: Title, Author, Publisher, Year, Pages, Description
- **ISBN System**: Unique patterns per category
- **Category Mapping**:
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

### 👥 Member Management (Admin/Staff)

- **CRUD Anggota**: Add, Edit, View members
- **Member Status**: Active/Inactive
- **Auto-link to Users**: Connect member to user account via email
- **Member Statistics**: Total books borrowed, on-time rate
- **Join Date Tracking**: Auto-set on creation

### 📖 Member Interface (Customer)

#### Dashboard

- **Auto-registration**: New members auto-created with M00X ID
- **Statistics Cards**: Available books, Active loans, Books borrowed
- **Quick Actions**: Browse catalog, View loans, Check history

#### Book Catalog

- **Gradient Covers**: 8 color schemes (SVG-based, no external images)
- **Search & Filter**: By title, author, ISBN, and category
- **Book Cards**: Title, Author, Publisher, Year, Stock
- **Borrow Button**: Direct loan request
- **SQL Injection Protection**: Prepared statements with parameter binding

#### My Loans

- **Status Filtering**: All, Pending, Approved, Returned
- **Status Cards**: Visual indicators with colors
  - Pending: Yellow (#f59e0b)
  - Approved: Blue (#3b82f6)
  - Returned: Green (#10b981)
  - Rejected: Red (#ef4444)
- **Overdue Warnings**: Red alert for late returns
- **Fine Display**: COALESCE for NULL-safe calculations
- **Due Date Tracking**: Days remaining/overdue

#### History & Analytics

- **Filters**: Year and Month dropdowns from actual data
- **Statistics Cards**:
  - Total Books: COUNT(\*)
  - On-time: DATEDIFF(return_date, due_date) <= 0
  - Late: DATEDIFF(return_date, due_date) > 0
  - Total Fine: SUM(COALESCE(fine_amount, 0))
- **Accuracy Percentage**: (on_time / total_books) \* 100
- **Gradient Thumbnails**: Color-coded book covers
- **Only Returned Status**: Shows completed loans

#### Smart Recommendations

- **Algorithm**: Analyzes top 2 most borrowed categories
- **Personalization**: Based on individual user's reading history
- **Exclusion Logic**: Removes currently borrowed books
- **Top Pick Badge**: ⭐ for first 4 recommendations
- **Fallback**: Random books if no history exists
- **Statistics**: Shows total recommendations, available books, active loans

### 📋 Loan Management (Staff)

#### Approval Dashboard

- **4 Status Tabs**: Pending, Approved, Returned, Rejected
- **Tab Counters**: Real-time count per status
- **Action Buttons**:
  - **Approve**: Sets status='approved', stock-1, approved_by, approved_date
  - **Reject**: Sets status='rejected', requires notes (modal)
  - **Return**: Sets status='returned', stock+1, calculates fine

#### Fine Calculation

- **Formula**: DATEDIFF(CURDATE(), due_date) \* FINE_PER_DAY
- **Constants**:
  - `FINE_PER_DAY = 1000` (Rp 1,000)
  - `DEFAULT_LOAN_DAYS = 14` (2 weeks)
- **Auto-calculation**: On return if overdue
- **NULL-safe**: Uses COALESCE(fine_amount, 0)

#### Loan Workflow

```
1. Member requests → status='pending'
2. Staff reviews in loans/index.php
3. Approve → status='approved', stock-1, log approved_by
4. Reject → status='rejected', save notes
5. Return → status='returned', stock+1, calculate fine if late
```

### 👤 User Management (Admin Only)

- **CRUD Users**: Create, Edit, Delete system users
- **Role Assignment**: admin, staff, member
- **Password Management**: Hash passwords on create/update
- **Access Control**: Only admin can access /pages/users/

### 📊 Dashboard (Admin/Staff)

- **Book Statistics**: Total books, available, borrowed
- **Member Statistics**: Total members, active members
- **Loan Statistics**: Active loans, pending approvals, overdue loans
- **Quick Access**: Direct links to management pages

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

## 📈 Data Statistics

- **Total Books**: 56 books across 10 categories
- **Total Categories**: 10 with explicit IDs
- **Test Users**: 4 (1 admin, 1 jhopan admin, 1 member, 1 staff)
- **Sample Members**: 3 pre-populated
- **Sample Loans**: 2 for testing
- **Book Distribution**:
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

## 🔒 Security Features

- **Password Hashing**: bcrypt via `password_hash()` and `password_verify()`
- **Prepared Statements**: All queries use PDO prepared statements with parameter binding
- **SQL Injection Protection**: No string concatenation in queries
- **Role-Based Access Control**: Three-tier authorization (admin/staff/member)
- **Session Security**: PHP native sessions with secure configuration
- **Input Sanitization**: `htmlspecialchars()` for output, prepared statements for input
- **Foreign Key Constraints**: Database-level referential integrity
- **CSRF Protection**: Session-based validation
- **NULL-Safe Operations**: COALESCE() for database queries
- **Password Validation**: Minimum requirements enforced

## 🎨 UI/UX Features

### Design System

- **CSS Variables**: Centralized color palette and spacing
- **Responsive Layout**: Mobile-friendly card grids
- **Gradient Covers**: 8 color pairs for book thumbnails
- **Status Colors**:
  - Primary: #3b82f6 (Blue)
  - Success: #10b981 (Green)
  - Warning: #f59e0b (Yellow/Orange)
  - Danger: #ef4444 (Red)
  - Info: #06b6d4 (Cyan)

### Components

- **Status Badges**: Color-coded (success/warning/danger/info)
- **Cards**: Hover effects with shadow transitions
- **Modal Dialogs**: For confirmations and forms
- **Statistics Cards**: Grid layout with icons
- **Navigation**: Sidebar with active state indicators
- **Alerts**: Flash messages with auto-dismiss
- **Empty States**: Friendly messages with illustrations

### Accessibility

- **Semantic HTML**: Proper heading hierarchy
- **SVG Icons**: Scalable and crisp on any display
- **Readable Fonts**: System font stack
- **Color Contrast**: WCAG compliant
- **Focus States**: Keyboard navigation support

## 🚨 Error Handling

### Common Issues & Solutions

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

## 📝 Best Practices Implemented

### Code Quality

- ✅ **Separation of Concerns**: Config, Includes, Pages separated
- ✅ **DRY Principle**: Reusable helper functions in config.php
- ✅ **Consistent Naming**: snake_case for DB, camelCase for PHP
- ✅ **Error Handling**: Try-catch blocks for database operations
- ✅ **Code Comments**: Clear explanations for complex logic

### Database Design

- ✅ **Normalization**: Proper 3NF structure
- ✅ **Indexes**: On frequently queried columns
- ✅ **Foreign Keys**: Enforce referential integrity
- ✅ **Timestamps**: created_at, updated_at tracking
- ✅ **Explicit IDs**: Category IDs 1-10 for consistency

### Security

- ✅ **No Plain Passwords**: All bcrypt hashed
- ✅ **Prepared Statements**: 100% of queries
- ✅ **Session Management**: Secure PHP sessions
- ✅ **XSS Prevention**: htmlspecialchars() on output
- ✅ **Access Control**: requireLogin(), requireAdmin(), requireStaff()

## 🔄 Workflow Examples

### Member Borrowing Flow

```
1. Member logs in → Auto-creates member record if needed
2. Browse books.php → Search/filter by category
3. Click "Pinjam Buku" → Confirmation page (borrow.php)
4. Confirm → INSERT into loans with status='pending'
5. Staff views loans/index.php → Pending tab
6. Staff clicks "Approve" → status='approved', stock-1, log approver
7. Member views my-loans.php → See approved status
8. Staff processes return → status='returned', stock+1, calculate fine
9. Member views history.php → See completed loan with fine (if any)
```

### Recommendation Algorithm Flow

```
1. User visits recommendations.php
2. System queries: SELECT top 2 categories by borrow_count
3. If found: Get 12 random books from those categories
4. Exclude: Books currently borrowed by user
5. If < 12 books: Add random books from other categories
6. Display with "⭐ Top Pick" badge for first 4
7. Show statistics: Total recommended, Available, Active loans
```

## 📄 License

MIT License - Free to use for educational purposes

## 🙏 Acknowledgments

- **Icons**: SVG icons (no external dependencies)
- **Colors**: Tailwind-inspired color palette
- **Typography**: System font stack for best performance
- **PHP**: Native features (no frameworks required)
- **Database**: MySQL 8.0+ with InnoDB engine

## 📞 Support

Untuk pertanyaan atau issues, silakan hubungi tim pengembang atau buat issue di repository.

---

**Built with ❤️ for UAS Pronet Ganjil 2025/2026**
