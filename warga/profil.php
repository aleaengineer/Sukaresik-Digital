<?php
require_once '../includes/auth.php';
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Pastikan user sudah login dan role-nya warga
requireWarga();

$user = getCurrentUser($pdo);
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama = sanitize($_POST['nama']);
    $no_kk = sanitize($_POST['no_kk']);
    $alamat = sanitize($_POST['alamat']);
    $email = sanitize($_POST['email']);
    $no_hp = sanitize($_POST['no_hp']);
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Update data profil
    if (empty($current_password)) {
        // Update tanpa ganti password
        $stmt = $pdo->prepare("UPDATE users SET nama = ?, no_kk = ?, alamat = ?, email = ?, no_hp = ? WHERE id = ?");
        $result = $stmt->execute([$nama, $no_kk, $alamat, $email, $no_hp, $_SESSION['user_id']]);
        
        if ($result) {
            $success = "Profil berhasil diperbarui!";
            // Refresh data user
            $user = getCurrentUser($pdo);
        } else {
            $error = "Gagal memperbarui profil!";
        }
    } else {
        // Update dengan ganti password
        if (password_verify($current_password, $user['password'])) {
            if (empty($new_password) || empty($confirm_password)) {
                $error = "Password baru dan konfirmasi password harus diisi!";
            } elseif ($new_password !== $confirm_password) {
                $error = "Password baru dan konfirmasi password tidak cocok!";
            } elseif (strlen($new_password) < 6) {
                $error = "Password minimal 6 karakter!";
            } else {
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE users SET nama = ?, no_kk = ?, alamat = ?, email = ?, no_hp = ?, password = ? WHERE id = ?");
                $result = $stmt->execute([$nama, $no_kk, $alamat, $email, $no_hp, $hashed_password, $_SESSION['user_id']]);
                
                if ($result) {
                    $success = "Profil dan password berhasil diperbarui!";
                    // Refresh data user
                    $user = getCurrentUser($pdo);
                } else {
                    $error = "Gagal memperbarui profil!";
                }
            }
        } else {
            $error = "Password saat ini salah!";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil - Sukaresik Digital</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="../index.php">Sukaresik Digital</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="pengajuan.php">Pengajuan Surat</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="status.php">Status Pengajuan</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="profil.php">Profil</a>
                    </li>
                </ul>
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-person-circle"></i> <?= htmlspecialchars($user['nama']) ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                            <li><a class="dropdown-item" href="profil.php">Profil</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="../logout.php">Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Profil Saya</h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($error)): ?>
                            <div class="alert alert-danger"><?= $error ?></div>
                        <?php endif; ?>
                        
                        <?php if (!empty($success)): ?>
                            <div class="alert alert-success"><?= $success ?></div>
                        <?php endif; ?>
                        
                        <form method="POST" action="">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="nik" class="form-label">NIK</label>
                                    <input type="text" class="form-control" id="nik" value="<?= htmlspecialchars($user['nik']) ?>" readonly>
                                    <div class="form-text">NIK tidak dapat diubah.</div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="nama" class="form-label">Nama Lengkap</label>
                                    <input type="text" class="form-control" id="nama" name="nama" value="<?= htmlspecialchars($user['nama']) ?>" required>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="no_kk" class="form-label">Nomor KK</label>
                                    <input type="text" class="form-control" id="no_kk" name="no_kk" value="<?= htmlspecialchars($user['no_kk']) ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="no_hp" class="form-label">Nomor HP</label>
                                    <input type="text" class="form-control" id="no_hp" name="no_hp" value="<?= htmlspecialchars($user['no_hp']) ?>">
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="alamat" class="form-label">Alamat</label>
                                <textarea class="form-control" id="alamat" name="alamat" rows="2"><?= htmlspecialchars($user['alamat']) ?></textarea>
                            </div>
                            
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>">
                            </div>
                            
                            <hr>
                            <h5>Ubah Password</h5>
                            <div class="mb-3">
                                <label for="current_password" class="form-label">Password Saat Ini</label>
                                <input type="password" class="form-control" id="current_password" name="current_password">
                                <div class="form-text">Kosongkan jika tidak ingin mengubah password.</div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="new_password" class="form-label">Password Baru</label>
                                    <input type="password" class="form-control" id="new_password" name="new_password">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="confirm_password" class="form-label">Konfirmasi Password Baru</label>
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password">
                                </div>
                            </div>
                            
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                            </div>
                        </form>
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