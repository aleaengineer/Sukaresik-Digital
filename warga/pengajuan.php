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
    $jenis_surat = sanitize($_POST['jenis_surat']);
    $keterangan = sanitize($_POST['keterangan']);
    
    // Validasi file upload
    if (!isset($_FILES['dokumen']) || $_FILES['dokumen']['error'] == UPLOAD_ERR_NO_FILE) {
        $error = "Dokumen pendukung wajib diunggah!";
    } else {
        $file = $_FILES['dokumen'];
        $fileValidation = validateFile($file);
        
        if ($fileValidation !== true) {
            $error = $fileValidation;
        } else {
            // Generate nama file unik
            $filename = generateUniqueFilename($file);
            $target_dir = "../assets/uploads/";
            $target_file = $target_dir . $filename;
            
            // Upload file
            if (move_uploaded_file($file['tmp_name'], $target_file)) {
                // Simpan data pengajuan ke database
                $stmt = $pdo->prepare("INSERT INTO surat (user_id, jenis, status, dokumen_path, keterangan) VALUES (?, ?, 'pending', ?, ?)");
                $result = $stmt->execute([$_SESSION['user_id'], $jenis_surat, $filename, $keterangan]);
                
                if ($result) {
                    $success = "Pengajuan surat berhasil dikirim! Silahkan pantau status pengajuan Anda.";
                    
                    // Kirim notifikasi email jika ada email
                    if (!empty($user['email'])) {
                        $subject = "Pengajuan Surat " . getJenisSurat($jenis_surat);
                        $message = "
                            <h2>Pengajuan Surat Berhasil</h2>
                            <p>Halo {$user['nama']},</p>
                            <p>Pengajuan surat Anda telah kami terima dan sedang dalam proses verifikasi. Detail pengajuan:</p>
                            <ul>
                                <li>Jenis Surat: " . getJenisSurat($jenis_surat) . "</li>
                                <li>Tanggal Pengajuan: " . date('d-m-Y H:i') . "</li>
                                <li>Status: Menunggu Verifikasi</li>
                            </ul>
                            <p>Silahkan pantau status pengajuan Anda melalui aplikasi Sukaresik Digital.</p>
                            <p>Terima kasih,<br>Tim Sukaresik Digital</p>
                        ";
                        sendEmailNotification($user['email'], $subject, $message);
                    }
                } else {
                    $error = "Terjadi kesalahan, silahkan coba lagi!";
                }
            } else {
                $error = "Gagal mengunggah file!";
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
    <title>Pengajuan Surat - Sukaresik Digital</title>
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
                        <a class="nav-link active" href="pengajuan.php">Pengajuan Surat</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="status.php">Status Pengajuan</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="profil.php">Profil</a>
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
                        <h5 class="mb-0">Pengajuan Surat</h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($error)): ?>
                            <div class="alert alert-danger"><?= $error ?></div>
                        <?php endif; ?>
                        
                        <?php if (!empty($success)): ?>
                            <div class="alert alert-success"><?= $success ?></div>
                            <div class="text-center mb-3">
                                <a href="status.php" class="btn btn-primary">Lihat Status Pengajuan</a>
                            </div>
                        <?php else: ?>
                            <form method="POST" action="" enctype="multipart/form-data">
                                <div class="mb-3">
                                    <label for="jenis_surat" class="form-label">Jenis Surat</label>
                                    <select class="form-select" id="jenis_surat" name="jenis_surat" required>
                                        <option value="" selected disabled>Pilih Jenis Surat</option>
                                        <option value="domisili">Surat Keterangan Domisili</option>
                                        <option value="sktm">Surat Keterangan Tidak Mampu</option>
                                        <option value="usaha">Surat Keterangan Usaha</option>
                                        <option value="nikah">Surat Keterangan Nikah</option>
                                        <option value="ahli_waris">Surat Keterangan Ahli Waris</option>
                                    </select>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="keterangan" class="form-label">Keterangan/Keperluan</label>
                                    <textarea class="form-control" id="keterangan" name="keterangan" rows="3" placeholder="Jelaskan keperluan pengajuan surat ini"></textarea>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="dokumen" class="form-label">Dokumen Pendukung (KTP/KK)</label>
                                    <input class="form-control" type="file" id="dokumen" name="dokumen" accept=".jpg,.jpeg,.png,.pdf" required>
                                    <div class="form-text">Format yang diizinkan: JPG, PNG, PDF. Ukuran maksimal: 2MB.</div>
                                </div>
                                
                                <div class="d-grid gap-2">
                                    <button type="submit" class="btn btn-primary">Kirim Pengajuan</button>
                                </div>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row mt-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0">Informasi Pengajuan Surat</h5>
                    </div>
                    <div class="card-body">
                        <h6>Persyaratan Umum:</h6>
                        <ul>
                            <li>Fotokopi KTP yang masih berlaku</li>
                            <li>Fotokopi Kartu Keluarga</li>
                        </ul>
                        
                        <h6>Persyaratan Khusus:</h6>
                        <div class="accordion" id="accordionPersyaratan">
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="headingOne">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="false" aria-controls="collapseOne">
                                        Surat Keterangan Domisili
                                    </button>
                                </h2>
                                <div id="collapseOne" class="accordion-collapse collapse" aria-labelledby="headingOne" data-bs-parent="#accordionPersyaratan">
                                    <div class="accordion-body">
                                        <ul>
                                            <li>Surat Pengantar dari RT/RW</li>
                                            <li>Bukti pembayaran PBB terakhir (jika ada)</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="headingTwo">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                                        Surat Keterangan Tidak Mampu
                                    </button>
                                </h2>
                                <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#accordionPersyaratan">
                                    <div class="accordion-body">
                                        <ul>
                                            <li>Surat Pengantar dari RT/RW</li>
                                            <li>Surat pernyataan tidak mampu yang diketahui oleh RT/RW</li>
                                            <li>Foto rumah (tampak depan dan dalam)</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="headingThree">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                                        Surat Keterangan Usaha
                                    </button>
                                </h2>
                                <div id="collapseThree" class="accordion-collapse collapse" aria-labelledby="headingThree" data-bs-parent="#accordionPersyaratan">
                                    <div class="accordion-body">
                                        <ul>
                                            <li>Surat Pengantar dari RT/RW</li>
                                            <li>Foto tempat usaha</li>
                                            <li>Keterangan jenis usaha</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="headingFour">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFour" aria-expanded="false" aria-controls="collapseFour">
                                        Surat Keterangan Nikah
                                    </button>
                                </h2>
                                <div id="collapseFour" class="accordion-collapse collapse" aria-labelledby="headingFour" data-bs-parent="#accordionPersyaratan">
                                    <div class="accordion-body">
                                        <ul>
                                            <li>Surat Pengantar dari RT/RW</li>
                                            <li>Fotokopi Akta Kelahiran</li>
                                            <li>Surat Keterangan Status Perkawinan</li>
                                            <li>Pas foto 3x4 (2 lembar)</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="headingFive">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFive" aria-expanded="false" aria-controls="collapseFive">
                                        Surat Keterangan Ahli Waris
                                    </button>
                                </h2>
                                <div id="collapseFive" class="accordion-collapse collapse" aria-labelledby="headingFive" data-bs-parent="#accordionPersyaratan">
                                    <div class="accordion-body">
                                        <ul>
                                            <li>Surat Pengantar dari RT/RW</li>
                                            <li>Fotokopi Surat Kematian</li>
                                            <li>Fotokopi KTP semua ahli waris</li>
                                            <li>Fotokopi Kartu Keluarga</li>
                                            <li>Surat pernyataan ahli waris yang ditandatangani semua ahli waris</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
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
                    <p class="mb-0">© 2025 Sukaresik Digital. All rights reserved.</p>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>