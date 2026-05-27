-- ======================================================
-- Database: manajemen_rt
-- Versi: Final (dengan penyempurnaan)
-- ======================================================

CREATE DATABASE IF NOT EXISTS manajemen_rt;
USE manajemen_rt;

-- --------------------------------------------------------
-- Tabel roles
-- --------------------------------------------------------
CREATE TABLE roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_role VARCHAR(50) NOT NULL UNIQUE
);

INSERT INTO roles (nama_role) VALUES ('superadmin'), ('admin'), ('bendahara'), ('warga')
ON DUPLICATE KEY UPDATE nama_role = VALUES(nama_role);

-- --------------------------------------------------------
-- Tabel users (dihapus kolom reset_token, tambah indeks)
-- --------------------------------------------------------
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role_id INT NOT NULL,
    fullname VARCHAR(100) NOT NULL,
    no_hp VARCHAR(20),
    alamat TEXT,
    status_aktif ENUM('aktif','nonaktif') DEFAULT 'aktif',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE,
    INDEX idx_role_id (role_id),
    INDEX idx_status (status_aktif)
);

-- --------------------------------------------------------
-- Tabel warga_detail
-- --------------------------------------------------------
CREATE TABLE warga_detail (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL UNIQUE,
    no_kk VARCHAR(20) NOT NULL,
    blok_rumah VARCHAR(20),
    status_warga ENUM('aktif','pindah','menunggak') DEFAULT 'aktif',
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id)
);

-- --------------------------------------------------------
-- Tabel iuran
-- --------------------------------------------------------
CREATE TABLE iuran (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_iuran VARCHAR(100) NOT NULL,
    nominal DECIMAL(15,2) NOT NULL,
    deskripsi TEXT,
    is_default BOOLEAN DEFAULT TRUE
);

INSERT INTO iuran (nama_iuran, nominal, deskripsi) VALUES
('Kas Bulanan', 50000, 'Iuran rutin setiap bulan'),
('Dana Sosial', 25000, 'Untuk kegiatan sosial'),
('Kebersihan', 20000, 'Iuran kebersihan lingkungan'),
('Keamanan', 30000, 'Iuran keamanan lingkungan'),
('Iuran Khusus', 0, 'Iuran insidental (nominal diisi manual)')
ON DUPLICATE KEY UPDATE nominal = VALUES(nominal);

-- --------------------------------------------------------
-- Tabel periode pembayaran
-- --------------------------------------------------------
CREATE TABLE periode (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tahun INT NOT NULL,
    bulan INT NOT NULL,
    status ENUM('aktif','tutup') DEFAULT 'aktif',
    UNIQUE KEY(tahun, bulan),
    INDEX idx_tahun_bulan (tahun, bulan)
);

-- --------------------------------------------------------
-- Tabel pembayaran (perbaikan: default tanggal, foreign key cascade)
-- --------------------------------------------------------
CREATE TABLE pembayaran (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    iuran_id INT NOT NULL,
    periode_id INT NOT NULL,
    jumlah DECIMAL(15,2) NOT NULL,
    bukti_file VARCHAR(255),
    status ENUM('pending','disetujui','ditolak') DEFAULT 'pending',
    tanggal_pembayaran DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    approved_by INT,
    catatan TEXT,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (iuran_id) REFERENCES iuran(id) ON DELETE RESTRICT,
    FOREIGN KEY (periode_id) REFERENCES periode(id) ON DELETE RESTRICT,
    FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_user_id (user_id),
    INDEX idx_status (status)
);

-- --------------------------------------------------------
-- Tabel pengumuman
-- --------------------------------------------------------
CREATE TABLE pengumuman (
    id INT AUTO_INCREMENT PRIMARY KEY,
    judul VARCHAR(200) NOT NULL,
    isi TEXT NOT NULL,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    target_role_id INT,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (target_role_id) REFERENCES roles(id) ON DELETE SET NULL,
    INDEX idx_created_at (created_at)
);

-- --------------------------------------------------------
-- Tabel agenda
-- --------------------------------------------------------
CREATE TABLE agenda (
    id INT AUTO_INCREMENT PRIMARY KEY,
    judul VARCHAR(200) NOT NULL,
    tanggal DATE NOT NULL,
    lokasi VARCHAR(255),
    deskripsi TEXT,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_tanggal (tanggal)
);

-- --------------------------------------------------------
-- Tabel pengeluaran
-- --------------------------------------------------------
CREATE TABLE pengeluaran (
    id INT AUTO_INCREMENT PRIMARY KEY,
    deskripsi TEXT NOT NULL,
    jumlah DECIMAL(15,2) NOT NULL,
    tanggal DATE NOT NULL,
    bukti_file VARCHAR(255),
    input_by INT NOT NULL,
    FOREIGN KEY (input_by) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_tanggal (tanggal)
);

-- --------------------------------------------------------
-- Tabel log_aktivitas
-- --------------------------------------------------------
CREATE TABLE log_aktivitas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    aksi VARCHAR(255) NOT NULL,
    waktu TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ip VARCHAR(45),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_waktu (waktu)
);

-- --------------------------------------------------------
-- Tabel pengaturan
-- --------------------------------------------------------
CREATE TABLE pengaturan (
    setting_key VARCHAR(100) PRIMARY KEY,
    setting_value TEXT
);

INSERT INTO pengaturan (setting_key, setting_value) VALUES
('nama_rt', 'RT 01 / RW 05'),
('ketua_rt', 'Bapak Slamet Riyadi'),
('alamat_rt', 'Jl. Mawar No.10, Kelurahan Melati'),
('logo_rt', 'logo_default.png')
ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value);

-- --------------------------------------------------------
-- Tabel surat_pengajuan
-- --------------------------------------------------------
CREATE TABLE surat_pengajuan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    jenis_surat ENUM('domisili','pengantar') NOT NULL,
    status ENUM('pending','diproses','selesai','ditolak') DEFAULT 'pending',
    file_hasil VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_status (status)
);

-- --------------------------------------------------------
-- Data awal: Superadmin (password: 12345678)
-- --------------------------------------------------------
INSERT INTO users (username, email, password, role_id, fullname, no_hp, alamat) VALUES
('superadmin', 'superadmin@rt.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1, 'Super Admin', '08123456789', 'Kantor RT')
ON DUPLICATE KEY UPDATE username = username;