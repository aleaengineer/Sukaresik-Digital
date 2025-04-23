<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';

// Redirect jika sudah login
if (isLoggedIn()) {
    if (isAdmin()) {
        header('Location: admin/dashboard.php');
    } else {
        header('Location: warga/pengajuan.php');
    }
    exit;
}

// Ambil data statistik dari database
try {
    // Jumlah pengajuan surat
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM surat");
    $totalSurat = $stmt->fetch()['total'];
    
    // Jumlah pengajuan selesai
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM surat WHERE status = 'selesai'");
    $totalSelesai = $stmt->fetch()['total'];
    
    // Jumlah warga terdaftar
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM users WHERE role = 'warga'");
    $totalWarga = $stmt->fetch()['total'];
    
    // Jenis surat terpopuler
    $stmt = $pdo->query("SELECT jenis, COUNT(*) as jumlah FROM surat GROUP BY jenis ORDER BY jumlah DESC LIMIT 1");
    $suratPopuler = $stmt->fetch();
} catch (PDOException $e) {
    // Jika tabel belum ada, set nilai default
    $totalSurat = 0;
    $totalSelesai = 0;
    $totalWarga = 0;
    $suratPopuler = ['jenis' => 'domisili', 'jumlah' => 0];
}

// Ambil berita/pengumuman terbaru
try {
    $stmt = $pdo->query("SELECT * FROM pengumuman ORDER BY tanggal DESC LIMIT 3");
    $pengumuman = $stmt->fetchAll();
} catch (PDOException $e) {
    $pengumuman = [];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sukaresik Digital - Layanan Administrasi Desa Online</title>
    
    <!-- Favicon -->
    <link rel="icon" href="assets/favicon/favicon.png" type="image/png">
    <link rel="shortcut icon" href="assets/favicon/favicon.ico" type="image/x-icon">
    <link rel="apple-touch-icon" sizes="180x180" href="assets/favicon/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="assets/favicon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="assets/favicon/favicon-16x16.png">
    <link rel="manifest" href="assets/favicon/site.webmanifest">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    
    <!-- AOS Animation Library -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    
    <!-- Swiper Slider CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@8/swiper-bundle.min.css" />
    
    <!-- Custom CSS -->
     <link rel="stylesheet" href="assets/css/custom.css">
</head>
<body>
    <!-- Preloader -->
    <div class="preloader">
        <div class="preloader-spinner"></div>
    </div>
    
    <!-- Back to Top Button -->
    <a href="#" class="back-to-top" id="backToTop">
        <i class="bi bi-arrow-up"></i>
    </a>
    
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-transparent fixed-top">
        <div class="container">
            <a class="navbar-brand fw-bold text-white" href="/">
                <img src="#" alt="" height="80" class="me-2">
                Sukaresik Digital
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="#home">Beranda</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#features">Fitur</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#services">Layanan</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#news">Berita</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#contact">Kontak</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link btn btn-primary ms-3 px-4" href="register.php">Daftar</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link btn btn-primary ms-3 px-4" href="login.php">Masuk</a>
                    </li>                
                </ul>
            </div>
        </div>
    </nav>
    
    <!-- Hero Section -->
    <section class="hero" id="home">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6" data-aos="fade-right" data-aos-duration="1000">
                    <h1 class="hero-title">Layanan Administrasi Desa Digital</h1>
                    <p class="hero-subtitle">Permudah pengurusan surat dan layanan administrasi desa Anda secara online, cepat, dan efisien.</p>
                    <div class="d-flex flex-wrap" data-aos="fade-left">
                    <?php if (isLoggedIn()): ?>
                        <?php if (isAdmin()): ?>
                            <a href="dashboard.php" class="btn btn-light cta-btn">
                                <i class="bi bi-speedometer2 me-2"></i>Dashboard
                            </a>
                        <?php else: ?>
                            <a href="dashboard.php" class="btn btn-light cta-btn">
                                <i class="bi bi-person me-2"></i>Profil Saya
                            </a>
                        <?php endif; ?>
                    <?php else: ?>
                        <a href="register.php" class="btn btn-light cta-btn me-2">
                            <i class="bi bi-person-plus me-2"></i>Daftar Sekarang
                        </a>
                        <a href="login.php" class="btn btn-light cta-btn">
                            <i class="bi bi-box-arrow-in-right me-2"></i>Masuk
                        </a>
                    <?php endif; ?>
                    </div>
                </div>
                <div class="col-lg-6" data-aos="fade-left" data-aos-duration="1000" data-aos-delay="200">
                    <img src="assets/images/logo.png" alt="Sukaresik Digital" class="img-fluid hero-image">
                </div>
            </div>
        </div>
        <svg class="hero-waves" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320">
            <path fill="#ffffff" fill-opacity="1" d="M0,96L48,112C96,128,192,160,288,160C384,160,480,128,576,122.7C672,117,768,139,864,149.3C960,160,1056,160,1152,138.7C1248,117,1344,75,1392,53.3L1440,32L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"></path>
        </svg>
    </section>

    <!-- Features Section -->
    <section class="features" id="features">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="section-title text-center" data-aos="fade-up">Keunggulan Kami</h2>
                <p class="section-subtitle text-center" data-aos="fade-up" data-aos-delay="100">Nikmati berbagai kemudahan dalam pengurusan administrasi desa</p>
            </div>
            
            <div class="row">
            <div class="col-md-4 mb-4" data-aos="fade-up" data-aos-delay="200">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="bi bi-clock"></i>
                        </div>
                        <h3 class="feature-title">Proses Cepat</h3>
                        <p>Pengajuan surat dapat diproses dengan cepat tanpa perlu antri dan menunggu lama di kantor desa.</p>
                    </div>
                </div>
                
                <div class="col-md-4 mb-4" data-aos="fade-up" data-aos-delay="300">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="bi bi-laptop"></i>
                        </div>
                        <h3 class="feature-title">Akses Online</h3>
                        <p>Akses layanan administrasi desa kapan saja dan di mana saja melalui perangkat yang terhubung internet.</p>
                    </div>
                </div>
                
                <div class="col-md-4 mb-4" data-aos="fade-up" data-aos-delay="400">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="bi bi-shield-check"></i>
                        </div>
                        <h3 class="feature-title">Aman & Terpercaya</h3>
                        <p>Data pribadi Anda terlindungi dengan sistem keamanan yang terjamin dan terenkripsi.</p>
                    </div>
                </div>
                
                <div class="col-md-4 mb-4" data-aos="fade-up" data-aos-delay="500">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="bi bi-bell"></i>
                        </div>
                        <h3 class="feature-title">Notifikasi Realtime</h3>
                        <p>Dapatkan pemberitahuan secara langsung mengenai status pengajuan surat Anda.</p>
                    </div>
                </div>
                
                <div class="col-md-4 mb-4" data-aos="fade-up" data-aos-delay="600">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="bi bi-file-earmark-text"></i>
                        </div>
                        <h3 class="feature-title">Beragam Layanan</h3>
                        <p>Tersedia berbagai jenis layanan administrasi desa yang dapat diakses dalam satu platform.</p>
                    </div>
                </div>
                
                <div class="col-md-4 mb-4" data-aos="fade-up" data-aos-delay="700">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="bi bi-chat-dots"></i>
                        </div>
                        <h3 class="feature-title">Dukungan Responsif</h3>
                        <p>Tim dukungan kami siap membantu Anda dengan cepat dan ramah untuk setiap pertanyaan.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Services Section -->
    <section class="services" id="services">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="section-title text-center" data-aos="fade-up">Layanan Kami</h2>
                <p class="section-subtitle text-center" data-aos="fade-up" data-aos-delay="100">Berbagai jenis surat dan layanan administrasi yang dapat diajukan secara online</p>
            </div>
            
            <div class="row">
                <div class="col-md-4 mb-4" data-aos="fade-up" data-aos-delay="200">
                    <div class="service-card">
                        <div class="service-image" style="background-image: url('assets/images/services/domisili.webp')"></div>
                        <div class="service-content">
                            <h3 class="service-title">Surat Keterangan Domisili</h3>
                            <p>Surat yang menerangkan tempat tinggal atau domisili seseorang di wilayah desa.</p>
                            <a href="<?= $isLoggedIn ? 'pengajuan.php?jenis=domisili' : 'login.php' ?>" class="btn btn-primary service-btn">
                                <i class="bi bi-arrow-right me-2"></i>Ajukan Sekarang
                            </a>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4 mb-4" data-aos="fade-up" data-aos-delay="300">
                    <div class="service-card">
                        <div class="service-image" style="background-image: url('assets/images/services/ktp.png')"></div>
                        <div class="service-content">
                            <h3 class="service-title">Surat Pengantar KTP</h3>
                            <p>Surat pengantar yang digunakan untuk keperluan pembuatan KTP baru atau perpanjangan.</p>
                            <a href="<?= $isLoggedIn ? 'pengajuan.php?jenis=pengantar_ktp' : 'login.php' ?>" class="btn btn-primary service-btn">
                                <i class="bi bi-arrow-right me-2"></i>Ajukan Sekarang
                            </a>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4 mb-4" data-aos="fade-up" data-aos-delay="400">
                    <div class="service-card">
                        <div class="service-image" style="background-image: url('assets/images/services/sktm.jpg')"></div>
                        <div class="service-content">
                            <h3 class="service-title">Surat Keterangan Tidak Mampu</h3>
                            <p>Surat yang menerangkan bahwa seseorang tergolong keluarga tidak mampu secara ekonomi.</p>
                            <a href="<?= $isLoggedIn ? 'pengajuan.php?jenis=sktm' : 'login.php' ?>" class="btn btn-primary service-btn">
                                <i class="bi bi-arrow-right me-2"></i>Ajukan Sekarang
                            </a>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4 mb-4" data-aos="fade-up" data-aos-delay="500">
                    <div class="service-card">
                        <div class="service-image" style="background-image: url('assets/images/services/usaha.jpeg')"></div>
                        <div class="service-content">
                            <h3 class="service-title">Surat Keterangan Usaha</h3>
                            <p>Surat yang menerangkan bahwa seseorang memiliki usaha tertentu di wilayah desa.</p>
                            <a href="<?= $isLoggedIn ? 'pengajuan.php?jenis=usaha' : 'login.php' ?>" class="btn btn-primary service-btn">
                                <i class="bi bi-arrow-right me-2"></i>Ajukan Sekarang
                            </a>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4 mb-4" data-aos="fade-up" data-aos-delay="600">
                    <div class="service-card">
                        <div class="service-image" style="background-image: url('assets/images/services/kelahiran.png')"></div>
                        <div class="service-content">
                            <h3 class="service-title">Surat Keterangan Kelahiran</h3>
                            <p>Surat yang menerangkan peristiwa kelahiran yang terjadi di wilayah desa.</p>
                            <a href="<?= $isLoggedIn ? 'pengajuan.php?jenis=kelahiran' : 'login.php' ?>" class="btn btn-primary service-btn">
                                <i class="bi bi-arrow-right me-2"></i>Ajukan Sekarang
                            </a>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4 mb-4" data-aos="fade-up" data-aos-delay="700">
                    <div class="service-card">
                        <div class="service-image" style="background-image: url('assets/images/services/kematian.webp')"></div>
                        <div class="service-content">
                            <h3 class="service-title">Surat Keterangan Kematian</h3>
                            <p>Surat yang menerangkan peristiwa kematian yang terjadi di wilayah desa.</p>
                            <a href="<?= $isLoggedIn ? 'pengajuan.php?jenis=kematian' : 'login.php' ?>" class="btn btn-primary service-btn">
                                <i class="bi bi-arrow-right me-2"></i>Ajukan Sekarang
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Stats Section -->
    <section class="stats">
        <div class="container">
            <div class="row">
                <div class="col-md-3 mb-4" data-aos="fade-up" data-aos-delay="100">
                    <div class="stat-card">
                        <div class="stat-number" data-count="<?= $totalSurat ?>">0</div>
                        <div class="stat-title">Total Pengajuan</div>
                    </div>
                </div>
                
                <div class="col-md-3 mb-4" data-aos="fade-up" data-aos-delay="200">
                    <div class="stat-card">
                        <div class="stat-number" data-count="<?= $totalSelesai ?>">0</div>
                        <div class="stat-title">Pengajuan Selesai</div>
                    </div>
                </div>
                
                <div class="col-md-3 mb-4" data-aos="fade-up" data-aos-delay="300">
                    <div class="stat-card">
                        <div class="stat-number" data-count="<?= $totalWarga ?>">0</div>
                        <div class="stat-title">Warga Terdaftar</div>
                    </div>
                </div>
                
                <div class="col-md-3 mb-4" data-aos="fade-up" data-aos-delay="400">
                    <div class="stat-card">
                        <div class="stat-number" data-count="6">0</div>
                        <div class="stat-title">Jenis Layanan</div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- News Section -->
    <section class="news" id="news">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="section-title text-center" data-aos="fade-up">Berita & Pengumuman</h2>
                <p class="section-subtitle text-center" data-aos="fade-up" data-aos-delay="100">Informasi terbaru dari Desa Sukaresik</p>
            </div>
            
            <div class="row">
                <?php if (count($pengumuman) > 0): ?>
                    <?php foreach ($pengumuman as $index => $item): ?>
                        <div class="col-md-4 mb-4" data-aos="fade-up" data-aos-delay="<?= 200 + ($index * 100) ?>">
                            <div class="news-card">
                                <div class="news-image" style="background-image: url('<?= !empty($item['gambar']) ? 'uploads/pengumuman/' . $item['gambar'] : 'assets/images/news-default.jpg' ?>')"></div>
                                <div class="news-content">
                                    <span class="news-date"><i class="bi bi-calendar me-2"></i><?= formatTanggal($item['tanggal']) ?></span>
                                    <h3 class="news-title"><?= htmlspecialchars($item['judul']) ?></h3>
                                    <p class="news-excerpt"><?= substr(strip_tags($item['isi']), 0, 120) ?>...</p>
                                    <a href="berita.php?id=<?= $item['id'] ?>" class="btn btn-outline-primary">Baca Selengkapnya</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <!-- Default news items if no data from database -->
                    <div class="col-md-4 mb-4" data-aos="fade-up" data-aos-delay="200">
                        <div class="news-card">
                            <div class="news-image" style="background-image: url('assets/images/news1.jpeg')"></div>
                            <div class="news-content">
                                <span class="news-date"><i class="bi bi-calendar me-2"></i><?= date('d M Y') ?></span>
                                <h3 class="news-title">Peluncuran Aplikasi Sukaresik Digital</h3>
                                <p class="news-excerpt">Desa Sukaresik meluncurkan aplikasi layanan administrasi desa online untuk memudahkan warga dalam mengurus berbagai surat...</p>
                                <a href="#" class="btn btn-outline-primary">Baca Selengkapnya</a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4 mb-4" data-aos="fade-up" data-aos-delay="300">
                        <div class="news-card">
                            <div class="news-image" style="background-image: url('assets/images/news2.jpeg')"></div>
                            <div class="news-content">
                                <span class="news-date"><i class="bi bi-calendar me-2"></i><?= date('d M Y', strtotime('-1 day')) ?></span>
                                <h3 class="news-title">Pelatihan Digital untuk Warga Desa</h3>
                                <p class="news-excerpt">Desa Sukaresik mengadakan pelatihan digital untuk warga desa agar dapat memanfaatkan teknologi dalam kehidupan sehari-hari...</p>
                                <a href="#" class="btn btn-outline-primary">Baca Selengkapnya</a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4 mb-4" data-aos="fade-up" data-aos-delay="400">
                        <div class="news-card">
                            <div class="news-image" style="background-image: url('assets/images/news3.jpg')"></div>
                            <div class="news-content">
                                <span class="news-date"><i class="bi bi-calendar me-2"></i><?= date('d M Y', strtotime('-2 days')) ?></span>
                                <h3 class="news-title">Program Bantuan untuk Warga Terdampak</h3>
                                <p class="news-excerpt">Pemerintah Desa Sukaresik menyalurkan bantuan untuk warga yang terdampak pandemi melalui program bantuan sosial...</p>
                                <a href="#" class="btn btn-outline-primary">Baca Selengkapnya</a>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="text-center mt-4" data-aos="fade-up" data-aos-delay="500">
                <a href="berita.php" class="btn btn-primary px-4 py-2">Lihat Semua Berita</a>
            </div>
        </div>
    </section>
    
    <!-- Testimonials Section -->
    <section class="testimonials">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="section-title text-center" data-aos="fade-up">Testimoni Warga</h2>
                <p class="section-subtitle text-center" data-aos="fade-up" data-aos-delay="100">Apa kata warga tentang layanan Sukaresik Digital</p>
            </div>
            
            <div class="swiper testimonialSwiper" data-aos="fade-up" data-aos-delay="200">
                <div class="swiper-wrapper">
                    <div class="swiper-slide">
                        <div class="testimonial-card">
                            <div class="testimonial-content">
                                Sangat membantu sekali! Saya tidak perlu lagi antri berjam-jam di kantor desa untuk mengurus surat. Sekarang cukup dari rumah saja, surat sudah bisa diproses.
                            </div>
                            <div class="testimonial-author">
                                <img src="assets/images/testimonials/personal.jpg" alt="Budi Santoso" class="testimonial-avatar">
                                <div>
                                    <div class="testimonial-name">Budi Santoso</div>
                                    <div class="testimonial-role">Warga Desa Sukaresik</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="swiper-slide">
                        <div class="testimonial-card">
                            <div class="testimonial-content">
                                Aplikasi yang sangat berguna! Prosesnya cepat dan transparan. Saya bisa melihat status pengajuan surat saya secara real-time. Terima kasih Sukaresik Digital!
                            </div>
                            <div class="testimonial-author">
                                <img src="assets/images/testimonials/personal.jpg" alt="Siti Aminah" class="testimonial-avatar">
                                <div>
                                    <div class="testimonial-name">Siti Aminah</div>
                                    <div class="testimonial-role">Guru SD</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="swiper-slide">
                        <div class="testimonial-card">
                            <div class="testimonial-content">
                                Sebagai orang yang sibuk bekerja, aplikasi ini sangat membantu saya. Tidak perlu izin kerja hanya untuk mengurus surat di desa. Semua bisa dilakukan online!
                            </div>
                            <div class="testimonial-author">
                                <img src="assets/images/testimonials/personal.jpg" alt="Hendra Wijaya" class="testimonial-avatar">
                                <div>
                                    <div class="testimonial-name">Hendra Wijaya</div>
                                    <div class="testimonial-role">Karyawan Swasta</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="swiper-slide">
                        <div class="testimonial-card">
                            <div class="testimonial-content">
                                Pelayanan yang cepat dan responsif. Admin selalu membantu ketika saya mengalami kesulitan. Semoga terus berkembang dan menambah layanan lainnya.
                            </div>
                            <div class="testimonial-author">
                                <img src="assets/images/testimonials/personal.jpg" alt="Dewi Lestari" class="testimonial-avatar">
                                <div>
                                    <div class="testimonial-name">Dewi Lestari</div>
                                    <div class="testimonial-role">Ibu Rumah Tangga</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="swiper-slide mb-5">
                        <div class="testimonial-card">
                            <div class="testimonial-content">
                                Dulu saya harus bolak-balik ke kantor desa untuk mengurus surat. Sekarang dengan Sukaresik Digital, semua jadi lebih mudah dan efisien. Terima kasih!
                            </div>
                            <div class="testimonial-author">
                                <img src="assets/images/testimonials/personal.jpg" alt="Ahmad Fauzi" class="testimonial-avatar">
                                <div>
                                    <div class="testimonial-name">Ahmad Fauzi</div>
                                    <div class="testimonial-role">Petani</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="swiper-pagination"></div>
                <div class="swiper-button-next"></div>
                <div class="swiper-button-prev"></div>
            </div>
        </div>
    </section>
    
    <!-- CTA Section -->
    <section class="cta">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-8 mb-4 mb-lg-0" data-aos="fade-right">
                    <h2 class="cta-title">Siap Untuk Memulai?</h2>
                    <p class="cta-subtitle">Daftar sekarang dan nikmati kemudahan layanan administrasi desa digital!</p>
                </div>
                <div class="col-lg-4 text-lg-end" data-aos="fade-left">
                    <?php if (isLoggedIn()): ?>
                        <?php if (isAdmin()): ?>
                            <a href="dashboard.php" class="btn btn-light cta-btn">
                                <i class="bi bi-speedometer2 me-2"></i>Dashboard
                            </a>
                        <?php else: ?>
                            <a href="dashboard.php" class="btn btn-light cta-btn">
                                <i class="bi bi-person me-2"></i>Profil Saya
                            </a>
                        <?php endif; ?>
                    <?php else: ?>
                        <a href="register.php" class="btn btn-light cta-btn">
                            <i class="bi bi-person-plus me-2"></i>Daftar Sekarang
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Footer -->
    <footer class="footer" id="contact">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 mb-5 mb-lg-0">
                    <div class="footer-logo">
                        <img src="assets/images/logo.png" alt="Sukaresik Digital" height="80" class="me-2">
                        Sukaresik Digital
                    </div>
                    <p class="footer-desc">Platform layanan administrasi desa online yang memudahkan warga dalam mengurus berbagai keperluan administrasi secara efisien dan transparan.</p>
                    <div class="footer-social">
                        <a href="#" class="footer-social-link"><i class="bi bi-facebook"></i></a>
                        <a href="#" class="footer-social-link"><i class="bi bi-twitter"></i></a>
                        <a href="#" class="footer-social-link"><i class="bi bi-instagram"></i></a>
                        <a href="#" class="footer-social-link"><i class="bi bi-youtube"></i></a>
                    </div>
                </div>
                
                <div class="col-lg-2 col-md-6 mb-5 mb-md-0">
                    <h4 class="footer-title">Tautan</h4>
                    <ul class="footer-links">
                        <li><a href="#home"><i class="bi bi-chevron-right"></i> Beranda</a></li>
                        <li><a href="#features"><i class="bi bi-chevron-right"></i> Fitur</a></li>
                        <li><a href="#services"><i class="bi bi-chevron-right"></i> Layanan</a></li>
                        <li><a href="#news"><i class="bi bi-chevron-right"></i> Berita</a></li>
                        <li><a href="login.php"><i class="bi bi-chevron-right"></i> Masuk</a></li>
                        <li><a href="register.php"><i class="bi bi-chevron-right"></i> Daftar</a></li>
                    </ul>
                </div>
                
                <div class="col-lg-3 col-md-6 mb-5 mb-md-0">
                    <h4 class="footer-title">Layanan</h4>
                    <ul class="footer-links">
                        <li><a href="<?= $isLoggedIn ? 'pengajuan.php?jenis=domisili' : 'login.php' ?>"><i class="bi bi-chevron-right"></i> Surat Keterangan Domisili</a></li>
                        <li><a href="<?= $isLoggedIn ? 'pengajuan.php?jenis=pengantar_ktp' : 'login.php' ?>"><i class="bi bi-chevron-right"></i> Surat Pengantar KTP</a></li>
                        <li><a href="<?= $isLoggedIn ? 'pengajuan.php?jenis=sktm' : 'login.php' ?>"><i class="bi bi-chevron-right"></i> Surat Keterangan Tidak Mampu</a></li>
                        <li><a href="<?= $isLoggedIn ? 'pengajuan.php?jenis=usaha' : 'login.php' ?>"><i class="bi bi-chevron-right"></i> Surat Keterangan Usaha</a></li>
                        <li><a href="<?= $isLoggedIn ? 'pengajuan.php?jenis=kelahiran' : 'login.php' ?>"><i class="bi bi-chevron-right"></i> Surat Keterangan Kelahiran</a></li>
                        <li><a href="<?= $isLoggedIn ? 'pengajuan.php?jenis=kematian' : 'login.php' ?>"><i class="bi bi-chevron-right"></i> Surat Keterangan Kematian</a></li>
                    </ul>
                </div>
                
                <div class="col-lg-3 col-md-6">
                    <h4 class="footer-title">Kontak Kami</h4>
                    <div class="footer-contact">
                        <i class="bi bi-geo-alt"></i>
                        <div>
                            Jl.Raya Sukaresik<br>
                            Kecamatan Sidamulih<br>
                            Kabupaten Pangandaran, 46365
                        </div>
                    </div>
                    <div class="footer-contact">
                        <i class="bi bi-telephone"></i>
                        <div>
                            (0265) 7500255
                        </div>
                    </div>
                    <div class="footer-contact">
                        <i class="bi bi-envelope"></i>
                        <div>
                            info@sukaresik.desa.id<br>
                            admin@sukaresik.desa.id
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="footer-bottom">
            <div class="container">
                <div class="row">
                    <div class="col-md-6 text-center text-md-start">
                        &copy; <?= date('Y') ?> Sukaresik Digital. Hak Cipta Dilindungi.
                    </div>
                    <div class="col-md-6 text-center text-md-end">
                        Dikembangkan oleh <a href="#" class="text-white">Farhan Maulana Syidiq</a>
                    </div>
                </div>
            </div>
        </div>
    </footer>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- AOS Animation Library -->
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    
    <!-- Swiper Slider JS -->
    <script src="https://cdn.jsdelivr.net/npm/swiper@8/swiper-bundle.min.js"></script>
    
    <!-- Custom JS -->
    <script src="assets/js/script.js"></script>
</body>
</html>
