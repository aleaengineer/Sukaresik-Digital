<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Redirect jika sudah login
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] == 'admin') {
        header('Location: admin/dashboard.php');
    } else {
        header('Location: warga/pengajuan.php');
    }
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nik = sanitize($_POST['nik']);
    $nama = sanitize($_POST['nama']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $no_kk = sanitize($_POST['no_kk']);
    $alamat = sanitize($_POST['alamat']);
    $email = sanitize($_POST['email']);
    $no_hp = sanitize($_POST['no_hp']);
    
    // Validasi input
    if (empty($nik) || empty($nama) || empty($password) || empty($confirm_password)) {
        $error = "Semua field wajib diisi!";
    } elseif (!validateNIK($nik)) {
        $error = "NIK harus 16 digit angka!";
    } elseif ($password !== $confirm_password) {
        $error = "Password dan konfirmasi password tidak cocok!";
    } elseif (strlen($password) < 6) {
        $error = "Password minimal 6 karakter!";
    } else {
        // Cek apakah NIK sudah terdaftar
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE nik = ?");
        $stmt->execute([$nik]);
        $count = $stmt->fetchColumn();
        
        if ($count > 0) {
            $error = "NIK sudah terdaftar!";
        } else {
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert user baru
            $stmt = $pdo->prepare("INSERT INTO users (nik, nama, password, no_kk, alamat, email, no_hp) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $result = $stmt->execute([$nik, $nama, $hashed_password, $no_kk, $alamat, $email, $no_hp]);
            
            if ($result) {
                $success = "Pendaftaran berhasil! Silahkan login.";
            } else {
                $error = "Terjadi kesalahan, silahkan coba lagi!";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrasi - Sukaresik Digital</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="index.php">Sukaresik Digital</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="login.php">Masuk</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="register.php">Daftar</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">Registrasi Akun Warga</h4>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($error)): ?>
                            <div class="alert alert-danger"><?= $error ?></div>
                        <?php endif; ?>
                        
                        <?php if (!empty($success)): ?>
                            <div class="alert alert-success"><?= $success ?></div>
                            <div class="text-center mb-3">
                                <a href="login.php" class="btn btn-primary">Login Sekarang</a>
                            </div>
                        <?php else: ?>
                            <form method="POST" action="">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="nik" class="form-label">NIK</label>
                                        <input type="text" class="form-control" id="nik" name="nik" placeholder="Masukkan NIK 16 digit" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="no_kk" class="form-label">Nomor KK</label>
                                        <input type="text" class="form-control" id="no_kk" name="no_kk" placeholder="Masukkan Nomor KK 16 digit">
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="nama" class="form-label">Nama Lengkap</label>
                                    <input type="text" class="form-control" id="nama" name="nama" placeholder="Masukkan nama lengkap" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="alamat" class="form-label">Alamat</label>
                                    <textarea class="form-control" id="alamat" name="alamat" rows="2" placeholder="Masukkan alamat lengkap"></textarea>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="email" class="form-label">Email</label>
                                        <input type="email" class="form-control" id="email" name="email" placeholder="Masukkan email">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="no_hp" class="form-label">Nomor HP</label>
                                        <input type="text" class="form-control" id="no_hp" name="no_hp" placeholder="Masukkan nomor HP">
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="password" class="form-label">Password</label>
                                        <input type="password" class="form-control" id="password" name="password" placeholder="Minimal 6 karakter" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="confirm_password" class="form-label">Konfirmasi Password</label>
                                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Ulangi password" required>
                                    </div>
                                </div>
                                
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary">Daftar</button>
                                </div>
                            </form>
                        <?php endif; ?>
                        
                        <div class="mt-3 text-center">
                            <p>Sudah punya akun? <a href="login.php">Login</a></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer class="bg-dark text-white mt-5 py-4">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center">
                    <p class="mb-0">© 2023 Sukaresik Digital. All rights reserved.</p>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>