<?php
require_once '../includes/auth.php';
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Pastikan user sudah login dan role-nya admin
requireAdmin();

$user = getCurrentUser($pdo);

// Ambil ID pengajuan dari parameter URL
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    // Redirect jika ID tidak valid
    header('Location: pengajuan.php');
    exit;
}

// Ambil data pengajuan
$stmt = $pdo->prepare("SELECT s.*, u.nama, u.nik, u.no_kk, u.alamat, u.email, u.no_hp 
                      FROM surat s 
                      JOIN users u ON s.user_id = u.id 
                      WHERE s.id = ?");
$stmt->execute([$id]);
$pengajuan = $stmt->fetch();

if (!$pengajuan) {
    // Redirect jika pengajuan tidak ditemukan
    header('Location: pengajuan.php');
    exit;
}

// Ambil data lampiran
$stmt = $pdo->prepare("SELECT * FROM lampiran WHERE surat_id = ?");
$stmt->execute([$id]);
$lampiran = $stmt->fetchAll();

// Proses update status
$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['update_status'])) {
        $new_status = $_POST['new_status'];
        $keterangan = sanitize($_POST['keterangan']);
        
        // Update status pengajuan
        $stmt = $pdo->prepare("UPDATE surat SET status = ?, keterangan = ?, tanggal_update = NOW() WHERE id = ?");
        $result = $stmt->execute([$new_status, $keterangan, $id]);
        
        if ($result) {
            // Kirim notifikasi ke warga (bisa diimplementasikan dengan email atau notifikasi sistem)
            
            // Refresh data
            $stmt = $pdo->prepare("SELECT s.*, u.nama, u.nik, u.no_kk, u.alamat, u.email, u.no_hp 
                                  FROM surat s 
                                  JOIN users u ON s.user_id = u.id 
                                  WHERE s.id = ?");
            $stmt->execute([$id]);
            $pengajuan = $stmt->fetch();
            
            $success_message = "Status pengajuan berhasil diperbarui!";
        } else {
            $error_message = "Gagal memperbarui status pengajuan!";
        }
    } elseif (isset($_POST['upload_surat'])) {
        // Proses upload surat yang sudah jadi
        if (isset($_FILES['file_surat']) && $_FILES['file_surat']['error'] == 0) {
            $allowed_types = ['application/pdf'];
            $max_size = 5 * 1024 * 1024; // 5MB
            
            $file_type = $_FILES['file_surat']['type'];
            $file_size = $_FILES['file_surat']['size'];
            
            if (!in_array($file_type, $allowed_types)) {
                $error_message = "Hanya file PDF yang diperbolehkan!";
            } elseif ($file_size > $max_size) {
                $error_message = "Ukuran file terlalu besar! Maksimal 5MB.";
            } else {
                $upload_dir = '../uploads/surat_jadi/';
                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }
                
                $file_name = 'surat_' . $id . '_' . time() . '.pdf';
                $file_path = $upload_dir . $file_name;
                
                if (move_uploaded_file($_FILES['file_surat']['tmp_name'], $file_path)) {
                    // Update database dengan path file surat
                    $stmt = $pdo->prepare("UPDATE surat SET file_surat = ?, status = 'selesai', tanggal_update = NOW() WHERE id = ?");
                    $result = $stmt->execute([$file_name, $id]);
                    
                    if ($result) {
                        // Refresh data
                        $stmt = $pdo->prepare("SELECT s.*, u.nama, u.nik, u.no_kk, u.alamat, u.email, u.no_hp 
                                              FROM surat s 
                                              JOIN users u ON s.user_id = u.id 
                                              WHERE s.id = ?");
                        $stmt->execute([$id]);
                        $pengajuan = $stmt->fetch();
                        
                        $success_message = "Surat berhasil diunggah dan status diperbarui menjadi Selesai!";
                    } else {
                        $error_message = "Gagal memperbarui database!";
                    }
                } else {
                    $error_message = "Gagal mengunggah file!";
                }
            }
        } else {
            $error_message = "Silakan pilih file surat yang akan diunggah!";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Pengajuan - Sukaresik Digital</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">Sukaresik Digital - Admin</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
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

    <div class="container-fluid">
        <div class="row">
            <nav id="sidebar" class="col-md-3 col-lg-2 d-md-block bg-dark sidebar collapse">
                <div class="position-sticky pt-3">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link" href="dashboard.php">
                                <i class="bi bi-speedometer2 me-2"></i>
                                Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="pengajuan.php">
                                <i class="bi bi-file-earmark-text me-2"></i>
                                Pengajuan Surat
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="warga.php">
                                <i class="bi bi-people me-2"></i>
                                Data Warga
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="laporan.php">
                                <i class="bi bi-bar-chart me-2"></i>
                                Laporan
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="profil.php">
                                <i class="bi bi-person me-2"></i>
                                Profil
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Detail Pengajuan Surat</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <a href="pengajuan.php" class="btn btn-sm btn-secondary">
                            <i class="bi bi-arrow-left"></i> Kembali
                        </a>
                    </div>
                </div>

                <?php if (!empty($success_message)): ?>
                    <div class="alert alert-success"><?= $success_message ?></div>
                <?php endif; ?>
                
                <?php if (!empty($error_message)): ?>
                    <div class="alert alert-danger"><?= $error_message ?></div>
                <?php endif; ?>

                <div class="row">
                    <div class="col-md-6 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Informasi Pengajuan</h5>
                            </div>
                            <div class="card-body">
                                <table class="table table-bordered">
                                    <tr>
                                        <th width="40%">Nomor Pengajuan</th>
                                        <td><?= $pengajuan['id'] ?></td>
                                    </tr>
                                    <tr>
                                        <th>Jenis Surat</th>
                                        <td><?= getJenisSurat($pengajuan['jenis']) ?></td>
                                    </tr>
                                    <tr>
                                        <th>Tanggal Pengajuan</th>
                                        <td><?= formatTanggal($pengajuan['tanggal_diajukan']) ?></td>
                                    </tr>
                                    <tr>
                                        <th>Status</th>
                                        <td><?= getStatusLabel($pengajuan['status']) ?></td>
                                    </tr>
                                    <tr>
                                        <th>Terakhir Diperbarui</th>
                                        <td><?= isset($pengajuan['tanggal_update']) && $pengajuan['tanggal_update'] ? formatTanggal($pengajuan['tanggal_update']) : '-' ?></td>
                                    </tr>
                                    <tr>
                                        <th>Keterangan</th>
                                        <td><?= !empty($pengajuan['keterangan']) ? nl2br(htmlspecialchars($pengajuan['keterangan'])) : '-' ?></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Informasi Pemohon</h5>
                            </div>
                            <div class="card-body">
                                <table class="table table-bordered">
                                    <tr>
                                        <th width="40%">Nama</th>
                                        <td><?= htmlspecialchars($pengajuan['nama']) ?></td>
                                    </tr>
                                    <tr>
                                        <th>NIK</th>
                                        <td><?= htmlspecialchars($pengajuan['nik']) ?></td>
                                    </tr>
                                    <tr>
                                        <th>Nomor KK</th>
                                        <td><?= htmlspecialchars($pengajuan['no_kk']) ?></td>
                                    </tr>
                                    <tr>
                                        <th>Alamat</th>
                                        <td><?= htmlspecialchars($pengajuan['alamat']) ?></td>
                                    </tr>
                                    <tr>
                                        <th>Email</th>
                                        <td><?= htmlspecialchars($pengajuan['email']) ?></td>
                                    </tr>
                                    <tr>
                                        <th>No. HP</th>
                                        <td><?= htmlspecialchars($pengajuan['no_hp']) ?></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Detail Pengajuan</h5>
                            </div>
                            <div class="card-body">
                                <?php if (isset($pengajuan['data']) && !empty($pengajuan['data'])): ?>
                                    <?php $data_surat = json_decode($pengajuan['data'], true); ?>
                                    
                                    <?php if ($pengajuan['jenis'] == 'domisili'): ?>
                                        <h6>Surat Keterangan Domisili</h6>
                                        <table class="table table-bordered">
                                            <tr>
                                                <th width="30%">Keperluan</th>
                                                <td><?= isset($data_surat['keperluan']) ? htmlspecialchars($data_surat['keperluan']) : '-' ?></td>
                                            </tr>
                                            <tr>
                                                <th>Sejak Tanggal</th>
                                                <td><?= isset($data_surat['sejak_tanggal']) ? formatTanggal($data_surat['sejak_tanggal']) : '-' ?></td>
                                            </tr>
                                        </table>
                                    <?php elseif ($pengajuan['jenis'] == 'pengantar_ktp'): ?>
                                        <h6>Surat Pengantar KTP</h6>
                                        <table class="table table-bordered">
                                            <tr>
                                                <th width="30%">Jenis Permohonan</th>
                                                <td><?= isset($data_surat['jenis_permohonan']) ? htmlspecialchars($data_surat['jenis_permohonan']) : '-' ?></td>
                                            </tr>
                                            <tr>
                                                <th>Alasan</th>
                                                <td><?= isset($data_surat['alasan']) ? htmlspecialchars($data_surat['alasan']) : '-' ?></td>
                                            </tr>
                                        </table>
                                    <?php elseif ($pengajuan['jenis'] == 'sktm'): ?>
                                        <h6>Surat Keterangan Tidak Mampu</h6>
                                        <table class="table table-bordered">
                                            <tr>
                                                <th width="30%">Keperluan</th>
                                                <td><?= isset($data_surat['keperluan']) ? htmlspecialchars($data_surat['keperluan']) : '-' ?></td>
                                            </tr>
                                            <tr>
                                                <th>Pekerjaan</th>
                                                <td><?= isset($data_surat['pekerjaan']) ? htmlspecialchars($data_surat['pekerjaan']) : '-' ?></td>
                                            </tr>
                                            <tr>
                                                <th>Penghasilan</th>
                                                <td>Rp <?= isset($data_surat['penghasilan']) ? number_format($data_surat['penghasilan'], 0, ',', '.') : '-' ?> / bulan</td>
                                            </tr>
                                        </table>
                                    <?php elseif ($pengajuan['jenis'] == 'usaha'): ?>
                                        <h6>Surat Keterangan Usaha</h6>
                                        <table class="table table-bordered">
                                            <tr>
                                                <th width="30%">Nama Usaha</th>
                                                <td><?= isset($data_surat['nama_usaha']) ? htmlspecialchars($data_surat['nama_usaha']) : '-' ?></td>
                                            </tr>
                                            <tr>
                                                <th>Jenis Usaha</th>
                                                <td><?= isset($data_surat['jenis_usaha']) ? htmlspecialchars($data_surat['jenis_usaha']) : '-' ?></td>
                                            </tr>
                                            <tr>
                                                <th>Alamat Usaha</th>
                                                <td><?= isset($data_surat['alamat_usaha']) ? htmlspecialchars($data_surat['alamat_usaha']) : '-' ?></td>
                                            </tr>
                                            <tr>
                                                <th>Tahun Berdiri</th>
                                                <td><?= isset($data_surat['tahun_berdiri']) ? htmlspecialchars($data_surat['tahun_berdiri']) : '-' ?></td>
                                            </tr>
                                            <tr>
                                                <th>Keperluan</th>
                                                <td><?= isset($data_surat['keperluan']) ? htmlspecialchars($data_surat['keperluan']) : '-' ?></td>
                                            </tr>
                                        </table>
                                    <?php elseif ($pengajuan['jenis'] == 'kelahiran'): ?>
                                        <h6>Surat Keterangan Kelahiran</h6>
                                        <table class="table table-bordered">
                                            <tr>
                                                <th width="30%">Nama Bayi</th>
                                                <td><?= isset($data_surat['nama_bayi']) ? htmlspecialchars($data_surat['nama_bayi']) : '-' ?></td>
                                            </tr>
                                            <tr>
                                                <th>Jenis Kelamin</th>
                                                <td><?= isset($data_surat['jenis_kelamin']) ? htmlspecialchars($data_surat['jenis_kelamin']) : '-' ?></td>
                                            </tr>
                                            <tr>
                                                <th>Tempat Lahir</th>
                                                <td><?= isset($data_surat['tempat_lahir']) ? htmlspecialchars($data_surat['tempat_lahir']) : '-' ?></td>
                                            </tr>
                                            <tr>
                                                <th>Tanggal Lahir</th>
                                                <td><?= isset($data_surat['tanggal_lahir']) ? formatTanggal($data_surat['tanggal_lahir']) : '-' ?></td>
                                            </tr>
                                            <tr>
                                                <th>Nama Ayah</th>
                                                <td><?= isset($data_surat['nama_ayah']) ? htmlspecialchars($data_surat['nama_ayah']) : '-' ?></td>
                                            </tr>
                                            <tr>
                                                <th>Nama Ibu</th>
                                                <td><?= isset($data_surat['nama_ibu']) ? htmlspecialchars($data_surat['nama_ibu']) : '-' ?></td>
                                            </tr>
                                        </table>
                                    <?php elseif ($pengajuan['jenis'] == 'kematian'): ?>
                                        <h6>Surat Keterangan Kematian</h6>
                                        <table class="table table-bordered">
                                            <tr>
                                                <th width="30%">Nama Almarhum/ah</th>
                                                <td><?= isset($data_surat['nama_almarhum']) ? htmlspecialchars($data_surat['nama_almarhum']) : '-' ?></td>
                                            </tr>
                                            <tr>
                                                <th>NIK Almarhum/ah</th>
                                                <td><?= isset($data_surat['nik_almarhum']) ? htmlspecialchars($data_surat['nik_almarhum']) : '-' ?></td>
                                            </tr>
                                            <tr>
                                                <th>Tanggal Kematian</th>
                                                <td><?= isset($data_surat['tanggal_kematian']) ? formatTanggal($data_surat['tanggal_kematian']) : '-' ?></td>
                                            </tr>
                                            <tr>
                                                <th>Tempat Kematian</th>
                                                <td><?= isset($data_surat['tempat_kematian']) ? htmlspecialchars($data_surat['tempat_kematian']) : '-' ?></td>
                                            </tr>
                                            <tr>
                                                <th>Penyebab Kematian</th>
                                                <td><?= isset($data_surat['penyebab_kematian']) ? htmlspecialchars($data_surat['penyebab_kematian']) : '-' ?></td>
                                            </tr>
                                            <tr>
                                                <th>Hubungan Pelapor</th>
                                                <td><?= isset($data_surat['hubungan_pelapor']) ? htmlspecialchars($data_surat['hubungan_pelapor']) : '-' ?></td>
                                            </tr>
                                        </table>
                                    <?php else: ?>
                                        <div class="alert alert-info">Detail pengajuan tidak tersedia untuk jenis surat ini.</div>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <div class="alert alert-info">Tidak ada data detail pengajuan.</div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Lampiran</h5>
                            </div>
                            <div class="card-body">
                                <?php if (empty($lampiran)): ?>
                                    <div class="alert alert-info">Tidak ada lampiran.</div>
                                <?php else: ?>
                                    <div class="table-responsive">
                                        <table class="table table-striped">
                                            <thead>
                                                <tr>
                                                    <th>Nama File</th>
                                                    <th>Jenis</th>
                                                    <th>Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($lampiran as $file): ?>
                                                    <tr>
                                                        <td><?= htmlspecialchars($file['nama_file']) ?></td>
                                                        <td><?= htmlspecialchars($file['jenis']) ?></td>
                                                        <td>
                                                            <a href="../uploads/lampiran/<?= htmlspecialchars($file['nama_file']) ?>" class="btn btn-sm btn-primary" target="_blank">
                                                                <i class="bi bi-eye"></i> Lihat
                                                            </a>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Tindakan</h5>
                            </div>
                            <div class="card-body">
                                <?php if ($pengajuan['status'] == 'selesai'): ?>
                                    <div class="alert alert-success">
                                        <h6><i class="bi bi-check-circle"></i> Pengajuan Telah Selesai</h6>
                                        <?php if (!empty($pengajuan['file_surat'])): ?>
                                            <p>Surat telah diterbitkan dan dapat diunduh oleh pemohon.</p>
                                            <a href="../uploads/surat_jadi/<?= htmlspecialchars($pengajuan['file_surat']) ?>" class="btn btn-primary" target="_blank">
                                                <i class="bi bi-file-earmark-pdf"></i> Lihat Surat
                                            </a>
                                        <?php else: ?>
                                            <p>Pengajuan telah selesai diproses.</p>
                                        <?php endif; ?>
                                    </div>
                                <?php elseif ($pengajuan['status'] == 'ditolak'): ?>
                                    <div class="alert alert-danger">
                                        <h6><i class="bi bi-x-circle"></i> Pengajuan Ditolak</h6>
                                        <p>Pengajuan ini telah ditolak dengan alasan: <?= !empty($pengajuan['keterangan']) ? nl2br(htmlspecialchars($pengajuan['keterangan'])) : 'Tidak ada keterangan' ?></p>
                                    </div>
                                <?php else: ?>
                                    <form method="POST" action="">
                                        <div class="mb-3">
                                            <label for="new_status" class="form-label">Update Status</label>
                                            <select class="form-select" id="new_status" name="new_status" required>
                                                <option value="">-- Pilih Status --</option>
                                                <option value="pending" <?= $pengajuan['status'] == 'pending' ? 'selected' : '' ?>>Menunggu Verifikasi</option>
                                                <option value="diproses" <?= $pengajuan['status'] == 'diproses' ? 'selected' : '' ?>>Sedang Diproses</option>
                                                <option value="selesai">Selesai</option>
                                                <option value="ditolak">Ditolak</option>
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label for="keterangan" class="form-label">Keterangan</label>
                                            <textarea class="form-control" id="keterangan" name="keterangan" rows="3"><?= htmlspecialchars($pengajuan['keterangan'] ?? '') ?></textarea>
                                            <div class="form-text">Berikan keterangan tambahan atau alasan jika ditolak.</div>
                                        </div>
                                        <button type="submit" name="update_status" class="btn btn-primary">Update Status</button>
                                    </form>
                                    
                                    <hr>
                                    
                                    <h6 class="mt-4">Upload Surat Jadi</h6>
                                    <form method="POST" action="" enctype="multipart/form-data">
                                        <div class="mb-3">
                                            <label for="file_surat" class="form-label">File Surat (PDF)</label>
                                            <input class="form-control" type="file" id="file_surat" name="file_surat" accept=".pdf" required>
                                            <div class="form-text">Upload file surat yang sudah jadi dalam format PDF (maks. 5MB).</div>
                                        </div>
                                        <button type="submit" name="upload_surat" class="btn btn-success">
                                            <i class="bi bi-upload"></i> Upload & Selesaikan
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
