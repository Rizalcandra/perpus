-- ============================================================
-- DATABASE: perpustakaan_desa
-- Project : Perpustakaan Desa v2 - PRA UKK 2026
-- Engine  : MySQL 5.7+ / MariaDB 10.3+ / PHP 8+
-- UKK P4  : RPL - Pengembangan Aplikasi Peminjaman Buku
-- ============================================================

DROP DATABASE IF EXISTS perpustakaan_desa;
CREATE DATABASE perpustakaan_desa CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE perpustakaan_desa;

-- ---------------------------------------------------------------------
-- TABEL: pengaturan (key-value site settings)
-- ---------------------------------------------------------------------
CREATE TABLE pengaturan (
    kunci VARCHAR(100) PRIMARY KEY,
    nilai TEXT NOT NULL,
    keterangan VARCHAR(255) DEFAULT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- TABEL: kategori_buku
-- ---------------------------------------------------------------------
CREATE TABLE kategori_buku (
    id_kategori INT AUTO_INCREMENT PRIMARY KEY,
    nama_kategori VARCHAR(100) NOT NULL,
    slug VARCHAR(120) NOT NULL UNIQUE,
    icon VARCHAR(80) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- TABEL: penerbit
-- ---------------------------------------------------------------------
CREATE TABLE penerbit (
    id_penerbit INT AUTO_INCREMENT PRIMARY KEY,
    nama_penerbit VARCHAR(100) NOT NULL,
    slug VARCHAR(120) NOT NULL UNIQUE,
    logo VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- TABEL: buku
-- ---------------------------------------------------------------------
CREATE TABLE buku (
    id_buku INT AUTO_INCREMENT PRIMARY KEY,
    isbn VARCHAR(50) NOT NULL UNIQUE,
    judul_buku VARCHAR(150) NOT NULL,
    bahasa VARCHAR(80) DEFAULT 'Indonesia',
    id_kategori INT DEFAULT NULL,
    id_penerbit INT DEFAULT NULL,
    tahun_terbit INT DEFAULT NULL,
    jumlah_halaman INT DEFAULT NULL,
    kondisi ENUM('Baru','Baik','Cukup','Rusak') NOT NULL DEFAULT 'Baik',
    stok INT NOT NULL DEFAULT 0,
    gambar VARCHAR(255) DEFAULT 'gambar/default.jpg',
    sinopsis TEXT,
    populer TINYINT(1) NOT NULL DEFAULT 0,
    baru TINYINT(1) NOT NULL DEFAULT 0,
    total_dipinjam INT NOT NULL DEFAULT 0 COMMENT 'Counter: berapa kali dipinjam',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_buku_kategori FOREIGN KEY (id_kategori) REFERENCES kategori_buku(id_kategori) ON DELETE SET NULL,
    CONSTRAINT fk_buku_penerbit FOREIGN KEY (id_penerbit) REFERENCES penerbit(id_penerbit) ON DELETE SET NULL,
    INDEX idx_judul (judul_buku),
    INDEX idx_stok (stok)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- TABEL: anggota (role: admin, petugas, anggota)
-- ---------------------------------------------------------------------
CREATE TABLE anggota (
    id_anggota INT AUTO_INCREMENT PRIMARY KEY,
    nik VARCHAR(20) DEFAULT NULL COMMENT 'Nomor Induk KTP/NIK',
    nama_lengkap VARCHAR(120) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    no_hp VARCHAR(25) DEFAULT NULL,
    alamat TEXT,
    role ENUM('admin','petugas','anggota') NOT NULL DEFAULT 'anggota',
    aktif TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_role (role)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- TABEL: peminjaman
-- status: dipinjam, menunggu_konfirmasi, dikembalikan, terlambat, ditolak
-- ---------------------------------------------------------------------
CREATE TABLE peminjaman (
    id_peminjaman INT AUTO_INCREMENT PRIMARY KEY,
    kode_peminjaman VARCHAR(40) NOT NULL UNIQUE,
    tanggal_pinjam DATE NOT NULL,
    tanggal_kembali DATE NOT NULL COMMENT 'Tenggat pengembalian',
    tanggal_pengembalian DATE DEFAULT NULL COMMENT 'Tanggal aktual pengembalian',
    tanggal_request_kembali DATE DEFAULT NULL COMMENT 'Tanggal user minta kembalikan',
    id_anggota INT DEFAULT NULL,
    nama_peminjam VARCHAR(120) NOT NULL,
    id_buku INT NOT NULL,
    denda INT NOT NULL DEFAULT 0 COMMENT 'Denda dalam Rupiah',
    status VARCHAR(30) NOT NULL DEFAULT 'dipinjam',
    catatan TEXT,
    approved_by INT DEFAULT NULL COMMENT 'ID admin/petugas yang approve',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_pm_anggota FOREIGN KEY (id_anggota) REFERENCES anggota(id_anggota) ON DELETE SET NULL,
    CONSTRAINT fk_pm_buku FOREIGN KEY (id_buku) REFERENCES buku(id_buku) ON DELETE RESTRICT,
    INDEX idx_kode (kode_peminjaman),
    INDEX idx_status (status),
    INDEX idx_tgl_pinjam (tanggal_pinjam)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- TABEL: pengumuman
-- ---------------------------------------------------------------------
CREATE TABLE pengumuman (
    id_pengumuman INT AUTO_INCREMENT PRIMARY KEY,
    judul VARCHAR(200) NOT NULL,
    isi TEXT NOT NULL,
    tipe ENUM('info','penting','darurat') NOT NULL DEFAULT 'info',
    tampil ENUM('ya','tidak') NOT NULL DEFAULT 'ya',
    created_by INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_tampil (tampil)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- TABEL: berita
-- ---------------------------------------------------------------------
CREATE TABLE berita (
    id_berita INT AUTO_INCREMENT PRIMARY KEY,
    judul VARCHAR(200) NOT NULL,
    slug VARCHAR(220) NOT NULL UNIQUE,
    isi TEXT NOT NULL,
    gambar VARCHAR(255) DEFAULT NULL,
    tampil ENUM('ya','tidak') NOT NULL DEFAULT 'ya',
    created_by INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_tampil (tampil)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- TABEL: komentar_buku
-- ---------------------------------------------------------------------
CREATE TABLE komentar_buku (
    id_komentar INT AUTO_INCREMENT PRIMARY KEY,
    id_buku INT NOT NULL,
    id_anggota INT NOT NULL,
    nama_komentar VARCHAR(120) NOT NULL,
    isi_komentar TEXT NOT NULL,
    rating TINYINT DEFAULT NULL COMMENT '1-5 bintang',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_komen_buku FOREIGN KEY (id_buku) REFERENCES buku(id_buku) ON DELETE CASCADE,
    CONSTRAINT fk_komen_anggota FOREIGN KEY (id_anggota) REFERENCES anggota(id_anggota) ON DELETE CASCADE,
    INDEX idx_buku (id_buku)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- TABEL: user_sessions (track logged-in users)
-- ---------------------------------------------------------------------
CREATE TABLE user_sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    session_id VARCHAR(128) NOT NULL UNIQUE,
    id_anggota INT NOT NULL,
    nama_lengkap VARCHAR(120) NOT NULL,
    role VARCHAR(20) NOT NULL,
    ip_address VARCHAR(45) DEFAULT NULL,
    user_agent VARCHAR(255) DEFAULT NULL,
    last_activity TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_session (session_id),
    INDEX idx_anggota (id_anggota)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- TABEL: aktivitas_log (audit trail)
-- ---------------------------------------------------------------------
CREATE TABLE aktivitas_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_anggota INT DEFAULT NULL,
    nama_user VARCHAR(120) NOT NULL,
    aksi VARCHAR(100) NOT NULL,
    detail TEXT,
    ip_address VARCHAR(45) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_anggota (id_anggota),
    INDEX idx_tgl (created_at)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- TABEL: error_log
-- ---------------------------------------------------------------------
CREATE TABLE error_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    level VARCHAR(20) NOT NULL DEFAULT 'ERROR',
    file_source VARCHAR(255) DEFAULT NULL,
    line_number INT DEFAULT NULL,
    message TEXT,
    stack_trace TEXT,
    ip_address VARCHAR(45) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_level (level),
    INDEX idx_tgl (created_at)
) ENGINE=InnoDB;

-- ============================================================
-- STORED PROCEDURES
-- ============================================================

DELIMITER //

-- ---------------------------------------------------------------------
-- SP: Hitung total buku yang sedang dipinjam oleh anggota tertentu
-- ---------------------------------------------------------------------
CREATE PROCEDURE sp_hitung_dipinjam(IN p_id_anggota INT, OUT p_total INT)
BEGIN
    SELECT COUNT(*) INTO p_total
    FROM peminjaman
    WHERE id_anggota = p_id_anggota
      AND status IN ('dipinjam', 'menunggu_konfirmasi');
END //

-- ---------------------------------------------------------------------
-- SP: Proses peminjaman buku (transaksional)
-- ---------------------------------------------------------------------
CREATE PROCEDURE sp_pinjam_buku(
    IN p_kode VARCHAR(40),
    IN p_tgl_pinjam DATE,
    IN p_tgl_kembali DATE,
    IN p_id_anggota INT,
    IN p_nama VARCHAR(120),
    IN p_id_buku INT,
    IN p_catatan TEXT,
    OUT p_sukses BOOLEAN,
    OUT p_pesan VARCHAR(255)
)
BEGIN
    DECLARE v_stok INT;
    DECLARE v_jumlah_dipinjam INT;
    DECLARE v_max_pinjam INT;
    DECLARE v_sudah_pinjam INT;

    -- Ambil pengaturan
    SELECT CAST(nilai AS UNSIGNED) INTO v_max_pinjam FROM pengaturan WHERE kunci = 'max_pinjam';

    -- Cek stok
    SELECT stok INTO v_stok FROM buku WHERE id_buku = p_id_buku FOR UPDATE;

    IF v_stok <= 0 THEN
        SET p_sukses = FALSE;
        SET p_pesan = 'Stok buku habis.';
    ELSE
        -- Cek jumlah dipinjam
        SELECT COUNT(*) INTO v_jumlah_dipinjam
        FROM peminjaman
        WHERE id_anggota = p_id_anggota AND status IN ('dipinjam','menunggu_konfirmasi');

        IF v_jumlah_dipinjam >= v_max_pinjam THEN
            SET p_sukses = FALSE;
            SET p_pesan = CONCAT('Maksimal peminjaman ', v_max_pinjam, ' buku.');
        ELSE
            -- Cek apakah buku ini sudah dipinjam user yang sama
            SELECT COUNT(*) INTO v_sudah_pinjam
            FROM peminjaman
            WHERE id_anggota = p_id_anggota AND id_buku = p_id_buku AND status IN ('dipinjam','menunggu_konfirmasi');

            IF v_sudah_pinjam > 0 THEN
                SET p_sukses = FALSE;
                SET p_pesan = 'Buku ini sudah Anda pinjam.';
            ELSE
                INSERT INTO peminjaman (kode_peminjaman, tanggal_pinjam, tanggal_kembali, id_anggota, nama_peminjam, id_buku, catatan, status)
                VALUES (p_kode, p_tgl_pinjam, p_tgl_kembali, p_id_anggota, p_nama, p_id_buku, p_catatan, 'dipinjam');

                UPDATE buku SET stok = stok - 1, total_dipinjam = total_dipinjam + 1 WHERE id_buku = p_id_buku;

                SET p_sukses = TRUE;
                SET p_pesan = 'Peminjaman berhasil.';
            END IF;
        END IF;
    END IF;
END //

-- ---------------------------------------------------------------------
-- SP: Hitung denda peminjaman
-- ---------------------------------------------------------------------
CREATE PROCEDURE sp_hitung_denda(
    IN p_id_peminjaman INT,
    OUT p_denda INT
)
BEGIN
    DECLARE v_tgl_kembali DATE;
    DECLARE v_denda_per_hari INT;
    DECLARE v_selisih INT;

    SELECT tanggal_kembali INTO v_tgl_kembali FROM peminjaman WHERE id_peminjaman = p_id_peminjaman;
    SELECT CAST(nilai AS UNSIGNED) INTO v_denda_per_hari FROM pengaturan WHERE kunci = 'denda_per_hari';

    SET v_selisih = DATEDIFF(CURDATE(), v_tgl_kembali);
    IF v_selisih > 0 THEN
        SET p_denda = v_selisih * v_denda_per_hari;
    ELSE
        SET p_denda = 0;
    END IF;
END //

-- ---------------------------------------------------------------------
-- SP: Proses pengembalian buku
-- ---------------------------------------------------------------------
CREATE PROCEDURE sp_kembalikan_buku(
    IN p_id_peminjaman INT,
    IN p_approved_by INT,
    OUT p_sukses BOOLEAN,
    OUT p_pesan VARCHAR(255)
)
BEGIN
    DECLARE v_id_buku INT;
    DECLARE v_denda INT;
    DECLARE v_status VARCHAR(30);

    SELECT id_buku, status INTO v_id_buku, v_status
    FROM peminjaman WHERE id_peminjaman = p_id_peminjaman FOR UPDATE;

    IF v_status = 'dikembalikan' THEN
        SET p_sukses = FALSE;
        SET p_pesan = 'Buku sudah dikembalikan.';
    ELSE
        -- Hitung denda
        CALL sp_hitung_denda(p_id_peminjaman, v_denda);

        -- Update peminjaman
        UPDATE peminjaman SET
            status = 'dikembalikan',
            tanggal_pengembalian = CURDATE(),
            tanggal_request_kembali = COALESCE(tanggal_request_kembali, CURDATE()),
            denda = v_denda,
            approved_by = p_approved_by
        WHERE id_peminjaman = p_id_peminjaman;

        -- Kembalikan stok
        UPDATE buku SET stok = stok + 1 WHERE id_buku = v_id_buku;

        SET p_sukses = TRUE;
        SET p_pesan = CONCAT('Buku berhasil dikembalikan. Denda: ', v_denda);
    END IF;
END //

-- ---------------------------------------------------------------------
-- SP: Statistik dashboard
-- ---------------------------------------------------------------------
CREATE PROCEDURE sp_dashboard_statistik(OUT p_total_buku INT, OUT p_total_anggota INT, OUT p_total_dipinjam INT, OUT p_total_terlambat INT, OUT p_total_kembali INT)
BEGIN
    SELECT COUNT(*) INTO p_total_buku FROM buku;
    SELECT COUNT(*) INTO p_total_anggota FROM anggota WHERE role = 'anggota';
    SELECT COUNT(*) INTO p_total_dipinjam FROM peminjaman WHERE status IN ('dipinjam','menunggu_konfirmasi');
    SELECT COUNT(*) INTO p_total_terlambat FROM peminjaman WHERE status IN ('dipinjam') AND tanggal_kembali < CURDATE();
    SELECT COUNT(*) INTO p_total_kembali FROM peminjaman WHERE status = 'dikembalikan';
END //

-- --------------
-- SP: Buku terpopuler
-- ----------------
CREATE PROCEDURE sp_buku_populer(IN p_limit INT)
BEGIN
    SELECT b.id_buku, b.judul_buku, k.nama_kategori, b.total_dipinjam, b.stok
    FROM buku b
    LEFT JOIN kategori_buku k ON b.id_kategori = k.id_kategori
    ORDER BY b.total_dipinjam DESC
    LIMIT p_limit;
END //

DELIMITER ;

-- FUNCTIONS

DELIMITER //

-- -----------------
-- hitung selisih hari
-- ------------------
CREATE FUNCTION fn_selisih_hari(p_tanggal DATE, p_banding DATE) RETURNS INT DETERMINISTIC
BEGIN
    RETURN DATEDIFF(p_banding, p_tanggal);
END //

-- -----------------------------
-- format angka ke rupiah
-- ----------------------------
CREATE FUNCTION fn_format_rupiah(p_nilai INT) RETURNS VARCHAR(50) DETERMINISTIC
BEGIN
    RETURN CONCAT('Rp ', FORMAT(p_nilai, 0, 'id_ID'));
END //

-- -------------------------------
-- Cek ketersediaan buku
-- --------------------------------
CREATE FUNCTION fn_buku_tersedia(p_id_buku INT) RETURNS BOOLEAN DETERMINISTIC
BEGIN
    DECLARE v_stok INT;
    SELECT stok INTO v_stok FROM buku WHERE id_buku = p_id_buku;
    RETURN v_stok > 0;
END //

-- -------------------------
-- Hitung denda per peminjaman
-- ---------------------------
CREATE FUNCTION fn_hitung_denda(p_tgl_kembali DATE) RETURNS INT DETERMINISTIC
BEGIN
    DECLARE v_selisih INT;
    DECLARE v_denda_per_hari INT;
    SET v_selisih = DATEDIFF(CURDATE(), p_tgl_kembali);
    SELECT CAST(nilai AS UNSIGNED) INTO v_denda_per_hari FROM pengaturan WHERE kunci = 'denda_per_hari';
    IF v_selisih > 0 THEN
        RETURN v_selisih * v_denda_per_hari;
    END IF;
    RETURN 0;
END //

DELIMITER ;

-- =====
-- TRIGGERS
-- ========

DELIMITER //

-- TRIGGER: Log aktivitas setelah insert peminjaman

CREATE TRIGGER trg_peminjaman_after_insert
AFTER INSERT ON peminjaman
FOR EACH ROW
BEGIN
    INSERT INTO aktivitas_log (id_anggota, nama_user, aksi, detail)
    VALUES (NEW.id_anggota, NEW.nama_peminjam, 'PINJAM_BUKU',
            CONCAT('Meminjam buku ID=', NEW.id_buku, ' kode=', NEW.kode_peminjaman));
END //

-- ----------------------------------------------------------
-- TRIGGER: Log aktivitas setelah update peminjaman (pengembalian)
-- ------------------------------------------------------
CREATE TRIGGER trg_peminjaman_after_update
AFTER UPDATE ON peminjaman
FOR EACH ROW
BEGIN
    IF NEW.status = 'dikembalikan' AND OLD.status != 'dikembalikan' THEN
        INSERT INTO aktivitas_log (id_anggota, nama_user, aksi, detail)
        VALUES (NEW.id_anggota, NEW.nama_peminjam, 'KEMBALIKAN_BUKU',
                CONCAT('Mengembalikan buku ID=', NEW.id_buku, ' denda=', NEW.denda));
    END IF;
    IF NEW.status = 'menunggu_konfirmasi' AND OLD.status = 'dipinjam' THEN
        INSERT INTO aktivitas_log (id_anggota, nama_user, aksi, detail)
        VALUES (NEW.id_anggota, NEW.nama_peminjam, 'REQUEST_KEMBALI',
                CONCAT('Request pengembalian buku ID=', NEW.id_buku));
    END IF;
END //

-- --------------------------
-- TRIGGER: Log error ke tabel error_log
-- ------------------------------------
CREATE TRIGGER trg_error_log_after_insert
AFTER INSERT ON error_log
FOR EACH ROW
BEGIN
    -- untuk log error
END //

DELIMITER ;

-- VIEW

CREATE OR REPLACE VIEW v_peminjaman_detail AS
SELECT
    p.id_peminjaman,
    p.kode_peminjaman,
    p.tanggal_pinjam,
    p.tanggal_kembali,
    p.tanggal_pengembalian,
    p.tanggal_request_kembali,
    p.id_anggota,
    p.nama_peminjam,
    p.id_buku,
    b.judul_buku,
    b.isbn,
    k.nama_kategori,
    pn.nama_penerbit,
    b.gambar,
    p.denda,
    p.status,
    p.catatan,
    p.approved_by,
    a.email AS email_peminjam,
    fn_hitung_denda(p.tanggal_kembali) AS denda_terhitung
FROM peminjaman p
LEFT JOIN buku b ON p.id_buku = b.id_buku
LEFT JOIN kategori_buku k ON b.id_kategori = k.id_kategori
LEFT JOIN penerbit pn ON b.id_penerbit = pn.id_penerbit
LEFT JOIN anggota a ON p.id_anggota = a.id_anggota;

-- =========== SEED DATA ==========

INSERT INTO pengaturan (kunci, nilai, keterangan) VALUES
('max_pinjam', '2', 'Maksimal buku yang dipinjam per anggota'),
('denda_per_hari', '5000', 'Denda keterlambatan per hari (Rupiah)'),
('lama_pinjam', '14', 'Lama peminjaman dalam hari'),
('jam_buka', '08:00', 'Jam buka perpustakaan'),
('jam_tutup', '16:00', 'Jam tutup perpustakaan'),
('hari_buka', 'Senin-Jumat', 'Hari operasional'),
('hari_tutup', 'Sabtu-Minggu', 'Hari libur'),
('info_layanan', 'Layanan peminjaman buku gratis untuk warga desa. Syarat: KTP dan foto 3x4.', 'Informasi layanan perpustakaan'),
('nama_desa', 'Desa Contoh', 'Nama desa'),
('telepon', '(021) 1234-5678', 'Nomor telepon perpustakaan'),
('email_desa', 'info@perpustakaan.desa.id', 'Email perpustakaan');

INSERT INTO kategori_buku (nama_kategori, slug, icon) VALUES
('Fiksi',           'fiksi',           'bi-book'),
('Non-Fiksi',       'non-fiksi',       'bi-journal-text'),
('Sains',           'sains',           'bi-lightbulb'),
('Biografi',        'biografi',        'bi-person-badge'),
('Pelajaran',       'pelajaran',       'bi-mortarboard'),
('Ensiklopedia',    'ensiklopedia',    'bi-collection');

INSERT INTO penerbit (nama_penerbit, slug) VALUES
('Gramedia',         'gramedia'),
('Erlangga',         'erlangga'),
('Balai Pustaka',    'balai-pustaka'),
('Republika',        'republika'),
('Andi Offset',      'andi-offset'),
('Penerbit ITB',     'penerbit-itb'),
('Bentang Pustaka',  'bentang-pustaka'),
('Kompas Gramedia',  'kompas-gramedia');

INSERT INTO anggota (nik, nama_lengkap, email, password, no_hp, alamat, role) VALUES
('3201010101010001', 'Administrator', 'admin@perpustakaan.id', '$2y$10$IMmCm97FSAbsPXsikzG.H.M.lPAHdhPjP3qsbm.vTKoj8vuh2ZdK.', '081234567890', 'Perpustakaan Desa', 'admin'),
('3201010101010002', 'Petugas Perpustakaan', 'petugas@perpustakaan.id', '$2y$10$IMmCm97FSAbsPXsikzG.H.M.lPAHdhPjP3qsbm.vTKoj8vuh2ZdK.', '081234567891', 'Desa Contoh', 'petugas'),
('3201010101010003', 'Budi Santoso', 'budi@email.com', '$2y$10$IMmCm97FSAbsPXsikzG.H.M.lPAHdhPjP3qsbm.vTKoj8vuh2ZdK.', '081298765432', 'Jl. Merdeka No.10', 'anggota'),
('3201010101010004', 'Siti Rahayu', 'siti@email.com', '$2y$10$IMmCm97FSAbsPXsikzG.H.M.lPAHdhPjP3qsbm.vTKoj8vuh2ZdK.', '081200000001', 'Jl. Pendidikan No.5', 'anggota'),
('3201010101010005', 'Ahmad Fauzi', 'ahmad@email.com', '$2y$10$IMmCm97FSAbsPXsikzG.H.M.lPAHdhPjP3qsbm.vTKoj8vuh2ZdK.', '081300000002', 'Jl. Ilmu No.20', 'anggota');

INSERT INTO buku (isbn, judul_buku, bahasa, id_kategori, id_penerbit, tahun_terbit, jumlah_halaman, kondisi, stok, gambar, sinopsis, populer, baru, total_dipinjam) VALUES
('LP-001-001', 'Laskar Pelangi',             'Indonesia', 1, 7, 2005, 529, 'Baik', 5,  'gambar/buku1.jpg', 'Kisah inspiratif tentang 10 anak dari Belitung yang berjuang untuk mendapatkan pendidikan yang layak.', 1, 0, 12),
('BM-002-001', 'Bumi Manusia',               'Indonesia', 1, 1, 1980, 535, 'Baik', 3,  'gambar/buku2.jpg', 'Novel perjuangan Minke di era kolonial Belanda melawan ketidakadilan.', 1, 0, 8),
('FD-003-001', 'Fisika Dasar Universitas',    'Inggris',   3, 6, 2018, 800, 'Baru', 4,  'gambar/buku3.jpg', 'Buku referensi fisika dasar komprehensif untuk mahasiswa.', 0, 1, 5),
('BH-004-001', 'Biografi BJ Habibie',        'Indonesia', 4, 5, 2015, 350, 'Baik', 3,  'gambar/buku4.jpg', 'Kisah perjalanan hidup BJ Habibie dari Parepare hingga Presiden RI.', 0, 0, 3),
('MT-005-001', 'Matematika Kelas XII',        'Indonesia', 5, 2, 2022, 280, 'Baru', 10, 'gambar/buku5.jpg', 'Buku teks matematika kelas XII SMA sesuai kurikulum terbaru.', 0, 1, 15),
('ES-006-001', 'Ensiklopedia Sains Anak',     'Indonesia', 6, 1, 2020, 300, 'Baik', 2,  'gambar/buku6.jpg', 'Ensiklopedia sains untuk anak dengan ilustrasi menarik.', 0, 0, 2),
('SI-007-001', 'Sejarah Indonesia Modern',    'Indonesia', 2, 4, 2019, 420, 'Baik', 3,  'gambar/buku7.jpg', 'Sejarah Indonesia dari kemerdekaan hingga reformasi.', 0, 0, 4),
('CL-008-001', 'Cantik Itu Luka',            'Indonesia', 1, 7, 2002, 480, 'Cukup', 2,  'gambar/buku1.jpg', 'Novel kehidupan Dewi Ayu yang bangkit dari kuburnya.', 1, 0, 7),
('KO-009-001', 'Kimia Organik Dasar',         'Indonesia', 3, 2, 2021, 550, 'Baru', 4,  'gambar/buku2.jpg', 'Panduan kimia organik dengan penjelasan reaksi dan mekanisme.', 0, 1, 3),
('SJ-010-001', 'Steve Jobs',                  'Inggris',   4, 1, 2011, 656, 'Baik', 2,  'gambar/buku4.jpg', 'Biografi resmi Steve Jobs oleh Walter Isaacson.', 0, 0, 6),
('BI-011-001', 'Bahasa Indonesia SMA',        'Indonesia', 5, 2, 2023, 250, 'Baru', 8,  'gambar/buku5.jpg', 'Buku teks Bahasa Indonesia SMA kurikulum merdeka.', 0, 1, 10),
('AD-012-001', 'Atlas Dunia Lengkap',         'Indonesia', 6, 3, 2017, 400, 'Baik', 1,  'gambar/buku6.jpg', 'Atlas dunia komprehensif dengan peta detail setiap negara.', 0, 0, 1);

INSERT INTO peminjaman (kode_peminjaman, tanggal_pinjam, tanggal_kembali, tanggal_pengembalian, tanggal_request_kembali, id_anggota, nama_peminjam, id_buku, status, denda, catatan, approved_by) VALUES
('PMJ-20250101-001', '2025-01-15', '2025-01-29', '2025-01-28', '2025-01-27', 3, 'Budi Santoso', 1, 'dikembalikan', 0, 'Dikembalikan tepat waktu', 1),
('PMJ-20250201-001', '2025-02-01', '2025-02-15', NULL, NULL, 4, 'Siti Rahayu', 2, 'dipinjam', 0, NULL, NULL),
('PMJ-20250215-001', '2025-02-15', '2025-03-01', NULL, NULL, 5, 'Ahmad Fauzi', 5, 'dipinjam', 0, 'Buku pelajaran untuk ujian', NULL),
('PMJ-20250220-001', '2025-02-20', '2025-03-06', NULL, '2025-03-05', 3, 'Budi Santoso', 2, 'menunggu_konfirmasi', 0, 'Minta kembalikan', NULL),
('PMJ-20250301-001', '2025-01-10', '2025-01-24', '2025-02-05', '2025-02-04', 4, 'Siti Rahayu', 5, 'dikembalikan', 55000, 'Terlambat 11 hari', 1);

INSERT INTO komentar_buku (id_buku, id_anggota, nama_komentar, isi_komentar, rating) VALUES
(1, 3, 'Budi Santoso', 'Buku yang sangat menginspirasi! Cocok untuk semua umur.', 5),
(1, 4, 'Siti Rahayu', 'Ceritanya menyentuh hati, recommended banget.', 4),
(2, 5, 'Ahmad Fauzi', 'Novel klasik yang wajib dibaca.', 5),
(5, 3, 'Budi Santoso', 'Buku pelajaran yang lengkap dan mudah dipahami.', 4),
(2, 3, 'Budi Santoso', 'Salah satu karya terbaik Pramoedya Ananta Toer.', 5);

INSERT INTO pengumuman (judul, isi, tipe, tampil, created_by) VALUES
('Selamat Datang di Perpustakaan Desa', 'Perpustakaan Desa telah dibuka untuk masyarakat. Silakan mendaftar sebagai anggota untuk dapat meminjam buku.', 'info', 'ya', 1),
('Perpanjangan Jam Buka Saat Ujian', 'Mulai tanggal 1 Maret, jam buka perpustakaan diperpanjang hingga pukul 20:00 WIB untuk mendukung persiapan ujian.', 'penting', 'ya', 1),
('Libur Nasional', 'Perpustakaan tutup pada tanggal 17 Agustus dalam rangka memperingati HUT Kemerdekaan RI.', 'darurat', 'ya', 1);

INSERT INTO berita (judul, slug, isi, tampil, created_by) VALUES
('Donasi 500 Buku dari Dinas Pendidikan', 'donasi-500-buku-dari-dinas-pendidikan', 'Perpustakaan Desa menerima donasi 500 eksemplar buku dari Dinas Pendidikan Kabupaten. Buku-buku tersebut mencakup berbagai kategori mulai dari fiksi, sains, hingga ensiklopedia. Koleksi ini diharapkan dapat memperkaya referensi bagi warga desa.', 'ya', 1),
('Kegiatan Bedah Buku Bulanan', 'kegiatan-bedah-buku-bulanan', 'Perpustakaan Desa mengadakan kegiatan bedah buku setiap bulan pada hari Sabtu minggu kedua. Kegiatan ini terbuka untuk umum dan gratis. Datang dan temukan wawasan baru bersama!', 'ya', 1);
