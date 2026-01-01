-- Readify Database Schema
-- Database untuk Smart Digital Library Platform
-- COMPLETE DATABASE SETUP - Jalankan file ini untuk setup lengkap

DROP DATABASE IF EXISTS readify;
CREATE DATABASE readify CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE readify;

-- Tabel Users (Admin/Staff/Member)
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    phone VARCHAR(20),
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'staff', 'member') DEFAULT 'member',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabel Kategori Buku
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabel Buku
CREATE TABLE books (
    id INT AUTO_INCREMENT PRIMARY KEY,
    isbn VARCHAR(20) UNIQUE NOT NULL,
    title VARCHAR(255) NOT NULL,
    author VARCHAR(100) NOT NULL,
    publisher VARCHAR(100),
    year YEAR,
    category_id INT,
    stock INT DEFAULT 0,
    pages INT,
    description TEXT,
    cover_image VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
    INDEX idx_title (title),
    INDEX idx_author (author),
    INDEX idx_isbn (isbn)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabel Anggota Perpustakaan
CREATE TABLE members (
    id INT AUTO_INCREMENT PRIMARY KEY,
    member_id VARCHAR(20) UNIQUE NOT NULL,
    user_id INT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    phone VARCHAR(20),
    address TEXT,
    join_date DATE NOT NULL,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_member_id (member_id),
    INDEX idx_name (name),
    INDEX idx_user_id (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabel Peminjaman
CREATE TABLE loans (
    id INT AUTO_INCREMENT PRIMARY KEY,
    loan_id VARCHAR(20) UNIQUE NOT NULL,
    member_id INT NOT NULL,
    book_id INT NOT NULL,
    loan_date DATE NOT NULL,
    due_date DATE NOT NULL,
    return_date DATE,
    status VARCHAR(50) DEFAULT 'pending',
    fine_amount DECIMAL(10,2) DEFAULT 0,
    approved_by INT,
    approved_date DATETIME,
    book_condition ENUM('good', 'fair', 'damaged') DEFAULT 'good',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (member_id) REFERENCES members(id) ON DELETE CASCADE,
    FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE CASCADE,
    FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_loan_id (loan_id),
    INDEX idx_status (status),
    INDEX idx_member (member_id),
    INDEX idx_book (book_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabel Riwayat Baca (untuk rekomendasi)
CREATE TABLE reading_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    member_id INT NOT NULL,
    book_id INT NOT NULL,
    category_id INT,
    read_date DATE NOT NULL,
    rating INT CHECK (rating BETWEEN 1 AND 5),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (member_id) REFERENCES members(id) ON DELETE CASCADE,
    FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
    INDEX idx_member (member_id),
    INDEX idx_category (category_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabel Backup Log (untuk fitur backup otomatis)
CREATE TABLE backup_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    backup_file VARCHAR(255) NOT NULL,
    backup_size INT,
    status ENUM('success', 'failed') DEFAULT 'success',
    message TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert default users (password: admin untuk admin, staff untuk staff, rafly untuk rafly, jhopan untuk jhopan)
INSERT INTO users (name, email, password, role) VALUES 
('Administrator', 'admin@readify.com', '$2y$10$JsqZXVI40HH6Y/uvZv4hZ.aA0hzBjyhAzL7aEysAXwakZW.Va4a9.', 'admin'),
('Jhopan User', 'jhopan@readify.com', '$2y$10$6a4VeVxDh3F0K8wh1GE8juKqQxWZCt7kKWfVXGfMK8Y5kJZYWJN4S', 'admin'),
('Rafly User', 'rafly@readify.com', '$2y$10$9y9K3bKZ7vLGQV0H5E8mWetN5XKfVXL8F9Kw8YJwGMwZ8X9YwZ8mW', 'member'),
('Staff User', 'staff@readify.com', '$2y$10$ILs2LRjlRcKXSa6VIlvq8ueLlX7TtO4TLzifxhnx2k.XmNql7D78u', 'staff');

-- Insert default categories
INSERT INTO categories (id, name, description) VALUES
(1, 'Fiksi', 'Novel dan cerita fiksi'),
(2, 'Non-Fiksi', 'Buku fakta dan non-fiksi'),
(3, 'Sains', 'Buku sains dan ilmu pengetahuan'),
(4, 'Teknologi', 'Buku pemrograman dan teknologi'),
(5, 'Sejarah', 'Buku tentang sejarah dunia dan peradaban'),
(6, 'Biografi', 'Biografi dan memoar tokoh terkenal'),
(7, 'Pendidikan', 'Buku pendidikan dan pembelajaran'),
(8, 'Komik', 'Komik dan manga'),
(9, 'Bisnis', 'Buku tentang bisnis, manajemen, dan kewirausahaan'),
(10, 'Kesehatan', 'Buku tentang kesehatan, medis, dan gaya hidup sehat');

-- Insert sample books
INSERT INTO books (isbn, title, author, publisher, year, category_id, stock, pages, description) VALUES
-- Teknologi (5 buku original)
('978-0-00-000001-1', 'Pemrograman Web', 'John Doe', 'TechPress', 2020, 4, 10, 350, 'Panduan lengkap pemrograman web modern.'),
('978-0-00-000002-8', 'Database MySQL', 'Jane Smith', 'DataBooks', 2019, 4, 8, 280, 'Belajar database MySQL dari dasar hingga mahir.'),
('978-0-00-000003-5', 'Belajar PHP', 'Bob Johnson', 'CodePublish', 2021, 4, 12, 420, 'Tutorial PHP untuk pemula dan menengah.'),
('978-0-00-000004-2', 'Manajemen Proyek IT', 'Alice Brown', 'PMBooks', 2018, 9, 5, 310, 'Strategi manajemen proyek IT yang efektif.'),
('978-0-00-000005-9', 'Design Thinking', 'Charlie Green', 'CreativePress', 2022, 2, 7, 265, 'Metodologi design thinking untuk inovasi.'),

-- Fiksi (5 buku)
('978-1-11-111111-1', 'To Kill a Mockingbird', 'Harper Lee', 'J.B. Lippincott & Co.', 1960, 1, 4, 324, 'Novel klasik tentang rasisme dan ketidakadilan di Amerika Selatan tahun 1930-an.'),
('978-1-11-111112-8', '1984', 'George Orwell', 'Secker & Warburg', 1949, 1, 5, 328, 'Dystopia tentang totalitarianisme dan pengawasan massa di masa depan.'),
('978-1-11-111113-5', 'Harry Potter and the Prisoner of Azkaban', 'J.K. Rowling', 'Bloomsbury', 1999, 1, 6, 435, 'Petualangan Harry Potter tahun ketiga di Hogwarts menghadapi pelarian Azkaban.'),
('978-1-11-111114-2', 'The Kite Runner', 'Khaled Hosseini', 'Riverhead Books', 2003, 1, 3, 371, 'Kisah persahabatan dan pengkhianatan di Afghanistan yang penuh emosi.'),
('978-1-11-111115-9', 'The Catcher in the Rye', 'J.D. Salinger', 'Little, Brown', 1951, 1, 4, 277, 'Novel tentang remaja Holden Caulfield yang memberontak terhadap kemunafikan.'),

-- Non-Fiksi (3 buku)
('978-2-22-222221-6', 'Into the Wild', 'Jon Krakauer', 'Villard', 1996, 2, 3, 207, 'Kisah nyata tentang Christopher McCandless yang meninggalkan kehidupan modern.'),
('978-2-22-222222-3', 'The Tipping Point', 'Malcolm Gladwell', 'Little, Brown', 2000, 2, 5, 301, 'Bagaimana ide dan produk kecil bisa menjadi fenomena massal.'),
('978-2-22-222223-0', 'Educated', 'Tara Westover', 'Random House', 2018, 2, 4, 334, 'Memoir tentang seorang wanita yang tumbuh di keluarga survivalis dan mengejar pendidikan.'),

-- Sains (5 buku)
('978-3-33-333301-5', 'A Brief History of Time', 'Stephen Hawking', 'Bantam Books', 1988, 3, 6, 256, 'Penjelasan tentang asal usul alam semesta dari fisikawan terkenal.'),
('978-3-33-333302-2', 'Cosmos', 'Carl Sagan', 'Random House', 1980, 3, 5, 365, 'Perjalanan epik melalui ruang dan waktu tentang evolusi alam semesta.'),
('978-3-33-333303-9', 'The Selfish Gene', 'Richard Dawkins', 'Oxford University Press', 1976, 3, 4, 360, 'Pandangan revolusioner tentang evolusi dari perspektif gen.'),
('978-3-33-333304-6', 'Astrophysics for People in a Hurry', 'Neil deGrasse Tyson', 'W.W. Norton', 2017, 3, 7, 222, 'Pengantar singkat dan mudah dipahami tentang astrofisika.'),
('978-3-33-333305-3', 'The Origin of Species', 'Charles Darwin', 'John Murray', 2009, 3, 3, 502, 'Karya fundamental tentang teori evolusi melalui seleksi alam - Edisi Modern.'),

-- Teknologi tambahan (6 buku)
('978-4-44-444440-1', 'Introduction to Algorithms', 'Thomas H. Cormen', 'MIT Press', 2009, 4, 3, 1312, 'Buku teks komprehensif tentang algoritma dan struktur data.'),
('978-4-44-444441-8', 'JavaScript: The Good Parts', 'Douglas Crockford', 'O Reilly Media', 2008, 4, 6, 176, 'Panduan untuk menulis kode JavaScript yang baik dan efisien.'),
('978-4-44-444442-5', 'Design Patterns', 'Erich Gamma', 'Addison-Wesley', 1994, 4, 4, 395, 'Pola desain software yang dapat digunakan kembali dalam pemrograman berorientasi objek.'),
('978-4-44-444443-2', 'Python Crash Course', 'Eric Matthes', 'No Starch Press', 2019, 4, 7, 544, 'Pengantar praktis pemrograman Python untuk pemula.'),
('978-4-44-444444-9', 'Introduction to Machine Learning with Python', 'Andreas C. Müller', 'O Reilly Media', 2016, 4, 5, 400, 'Panduan praktis untuk machine learning menggunakan Python dan scikit-learn.'),
('978-4-44-444445-6', 'Eloquent JavaScript', 'Marijn Haverbeke', 'No Starch Press', 2018, 4, 6, 472, 'Pengantar modern pemrograman JavaScript dengan contoh interaktif.'),

-- Sejarah (7 buku)
('978-5-55-555551-7', 'Guns, Germs, and Steel', 'Jared Diamond', 'W.W. Norton', 1997, 5, 3, 528, 'Mengapa peradaban Eropa dan Asia mendominasi dunia modern.'),
('978-5-55-555552-4', 'Sapiens', 'Yuval Noah Harari', 'Harper', 2014, 5, 8, 443, 'Sejarah singkat manusia dari zaman batu hingga era modern.'),
('978-5-55-555553-1', 'The Silk Roads', 'Peter Frankopan', 'Bloomsbury', 2015, 5, 4, 636, 'Sejarah baru dunia yang berpusat pada Jalur Sutra.'),
('978-5-55-555554-8', 'A Short History of Nearly Everything', 'Bill Bryson', 'Broadway Books', 2003, 5, 5, 544, 'Perjalanan memukau melalui sejarah sains dan penemuan manusia.'),
('978-5-55-555555-5', 'The Diary of a Young Girl', 'Anne Frank', 'Bantam Books', 1947, 5, 6, 283, 'Diary Anne Frank yang menyentuh selama persembunyiannya dari Nazi.'),
('978-5-55-555556-2', '1491: New Revelations of the Americas Before Columbus', 'Charles C. Mann', 'Knopf', 2005, 5, 3, 541, 'Sejarah Amerika sebelum kedatangan Columbus dengan perspektif baru.'),
('978-5-55-555557-9', 'Team of Rivals', 'Doris Kearns Goodwin', 'Simon & Schuster', 2005, 5, 4, 944, 'Kepemimpinan politik Abraham Lincoln selama Perang Saudara Amerika.'),

-- Biografi (6 buku)
('978-6-66-666601-6', 'Steve Jobs', 'Walter Isaacson', 'Simon & Schuster', 2011, 6, 5, 656, 'Biografi resmi pendiri Apple yang penuh dengan inovasi dan kontroversi.'),
('978-6-66-666602-3', 'Einstein: His Life and Universe', 'Walter Isaacson', 'Simon & Schuster', 2007, 6, 4, 675, 'Biografi komprehensif tentang Albert Einstein dan teorinya yang mengubah dunia.'),
('978-6-66-666603-0', 'Long Walk to Freedom', 'Nelson Mandela', 'Little, Brown', 1994, 6, 6, 656, 'Autobiografi Nelson Mandela dari masa kecil hingga menjadi presiden Afrika Selatan.'),
('978-6-66-666604-7', 'The Diary of a Young Girl', 'Anne Frank', 'Contact Publishing', 1947, 6, 7, 283, 'Catatan harian Anne Frank selama bersembunyi dari Nazi di Amsterdam.'),
('978-6-66-666605-4', 'Becoming', 'Michelle Obama', 'Crown Publishing', 2018, 6, 8, 448, 'Memoar mantan Ibu Negara Amerika Serikat tentang perjalanan hidupnya.'),
('978-6-66-666606-1', 'Elon Musk', 'Ashlee Vance', 'Ecco', 2015, 6, 5, 400, 'Biografi entrepreneur visioner di balik Tesla, SpaceX, dan perusahaan inovatif lainnya.'),

-- Pendidikan (2 buku)
('978-7-77-777771-5', 'Mindset: The New Psychology of Success', 'Carol S. Dweck', 'Random House', 2006, 7, 5, 320, 'Tentang pola pikir tetap vs pola pikir berkembang dalam belajar.'),
('978-7-77-777772-2', 'Drive: The Surprising Truth About What Motivates Us', 'Daniel H. Pink', 'Riverhead Books', 2009, 7, 4, 272, 'Sains baru tentang motivasi manusia dalam belajar dan bekerja.'),

-- Komik (5 buku)
('978-8-88-888801-9', 'One Piece Vol. 1', 'Eiichiro Oda', 'Shueisha', 1997, 8, 10, 216, 'Petualangan Monkey D. Luffy mencari harta karun legendaris One Piece.'),
('978-8-88-888802-6', 'Naruto Vol. 1', 'Masashi Kishimoto', 'Shueisha', 1999, 8, 9, 192, 'Kisah ninja muda Naruto Uzumaki yang bercita-cita menjadi Hokage.'),
('978-8-88-888803-3', 'Attack on Titan Vol. 1', 'Hajime Isayama', 'Kodansha', 2009, 8, 8, 194, 'Manusia melawan titan raksasa yang mengancam kelangsungan hidup mereka.'),
('978-8-88-888804-0', 'Death Note Vol. 1', 'Tsugumi Ohba', 'Shueisha', 2003, 8, 7, 200, 'Seorang siswa menemukan buku misterius yang bisa membunuh siapa saja.'),
('978-8-88-888805-7', 'My Hero Academia Vol. 1', 'Kohei Horikoshi', 'Shueisha', 2014, 8, 10, 192, 'Di dunia di mana hampir semua orang punya kekuatan super, seorang anak tanpa kekuatan bermimpi jadi pahlawan.'),

-- Bisnis (7 buku)
('978-9-99-999991-5', 'Atomic Habits', 'James Clear', 'Avery', 2018, 9, 8, 320, 'Cara membangun kebiasaan baik dan menghilangkan kebiasaan buruk untuk meningkatkan produktivitas.'),
('978-9-99-999992-2', 'Start With Why', 'Simon Sinek', 'Portfolio', 2009, 9, 6, 256, 'Bagaimana pemimpin besar menginspirasi orang untuk bertindak dengan menemukan tujuan mereka.'),
('978-9-99-999993-9', 'The 7 Habits of Highly Effective People', 'Stephen R. Covey', 'Free Press', 1989, 9, 7, 381, 'Prinsip-prinsip karakter untuk efektivitas pribadi dan profesional.'),
('978-9-99-999994-6', 'Thinking, Fast and Slow', 'Daniel Kahneman', 'Farrar, Straus and Giroux', 2011, 9, 5, 499, 'Psikolog pemenang Nobel mengungkap dua sistem berpikir manusia.'),
('978-9-99-999995-3', 'Good to Great', 'Jim Collins', 'HarperBusiness', 2001, 9, 5, 300, 'Mengapa beberapa perusahaan membuat lompatan dari baik menjadi hebat.'),
('978-9-99-999996-0', 'Principles', 'Ray Dalio', 'Simon & Schuster', 2017, 9, 4, 592, 'Prinsip hidup dan kerja dari pendiri Bridgewater Associates.'),
('978-9-99-999997-7', 'The Lean Startup', 'Eric Ries', 'Crown Business', 2011, 9, 6, 336, 'Bagaimana membangun startup yang sukses dengan metode lean.'),

-- Kesehatan (5 buku)
('978-1-01-010101-4', 'Why We Sleep', 'Matthew Walker', 'Scribner', 2017, 10, 6, 368, 'Ilmu tidur dan mimpi yang mengubah cara kita memahami pentingnya istirahat.'),
('978-1-01-010102-1', 'The Body: A Guide for Occupants', 'Bill Bryson', 'Doubleday', 2019, 10, 5, 450, 'Panduan lengkap tentang tubuh manusia yang mengagumkan dan kompleks.'),
('978-1-01-010103-8', 'How Not to Die', 'Michael Greger', 'Flatiron Books', 2015, 10, 4, 562, 'Penemuan ilmiah tentang makanan yang dapat mencegah dan membalikkan penyakit.'),
('978-1-01-010104-5', 'Brain Rules', 'John Medina', 'Pear Press', 2008, 10, 5, 304, '12 prinsip untuk bertahan dan berkembang di tempat kerja, rumah, dan sekolah.'),
('978-1-01-010105-2', 'Breath: The New Science of a Lost Art', 'James Nestor', 'Riverhead Books', 2020, 10, 4, 304, 'Seni pernapasan yang benar dan pengaruhnya terhadap kesehatan.');

-- Insert sample members (linked to users rafly and staff)
INSERT INTO members (member_id, user_id, name, email, phone, address, join_date, status) VALUES
('M001', 3, 'Rafly User', 'rafly@readify.com', '081234567890', 'Jl. Merdeka No. 10, Jakarta', '2024-01-15', 'active'),
('M002', NULL, 'Siti Nurhaliza', 'siti@example.com', '081234567891', 'Jl. Sudirman No. 25, Bandung', '2024-02-20', 'active'),
('M003', NULL, 'Ahmad Fauzi', 'ahmad@example.com', '081234567892', 'Jl. Diponegoro No. 5, Surabaya', '2024-03-10', 'active');

-- Insert sample loans
INSERT INTO loans (loan_id, member_id, book_id, loan_date, due_date, status) VALUES
('L001', 1, 1, '2024-12-15', '2024-12-29', 'active'),
('L002', 2, 3, '2024-12-20', '2025-01-03', 'active');
