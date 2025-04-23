-- Buat tabel users
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nik VARCHAR(16) UNIQUE NOT NULL,
    nama VARCHAR(100) NOT NULL,
    password VARCHAR(255) NOT NULL,
    no_kk VARCHAR(16),
    alamat TEXT,
    email VARCHAR(100),
    no_hp VARCHAR(15),
    role ENUM('warga', 'admin') DEFAULT 'warga',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Buat tabel surat
CREATE TABLE surat (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    jenis ENUM('domisili', 'sktm', 'usaha', 'nikah', 'ahli_waris'),
    status ENUM('pending', 'diproses', 'selesai', 'ditolak') DEFAULT 'pending',
    dokumen_path VARCHAR(255),
    keterangan TEXT,
    tanggal_diajukan DATETIME DEFAULT CURRENT_TIMESTAMP,
    tanggal_diproses DATETIME,
    tanggal_selesai DATETIME,
    admin_id INT,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (admin_id) REFERENCES users(id)
);

CREATE TABLE `lampiran` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `surat_id` int(11) NOT NULL,
  `nama_file` varchar(255) NOT NULL,
  `jenis` varchar(50) NOT NULL,
  `tanggal_upload` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `surat_id` (`surat_id`),
  CONSTRAINT `lampiran_ibfk_1` FOREIGN KEY (`surat_id`) REFERENCES `surat` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert admin default
INSERT INTO users (nik, nama, password, role, email) 
VALUES ('1234567890123456', 'Admin Desa', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'admin@sukaresik.desa.id');
-- Password: password