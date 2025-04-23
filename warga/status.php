<?php
require_once '../includes/auth.php';
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Pastikan user sudah login dan role-nya warga
requireWarga();

$user = getCurrentUser($pdo);

// Ambil data pengajuan surat
$stmt = $pdo->prepare("SELECT * FROM surat WHERE user_id = ? ORDER BY tanggal_diajukan DESC");
$stmt->execute([$_SESSION['user_id']]);
$pengajuan_list = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Status Pengajuan - Sukaresik Digital</title>
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
                        <a class="nav-link active" href="status.php">Status Pengajuan</a>
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
                        <h5 class="mb-0">Status Pengajuan Surat</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($pengajuan_list)): ?>
                            <div class="alert alert-info">
                                Anda belum memiliki pengajuan surat. <a href="pengajuan.php" class="alert-link">Buat pengajuan baru</a>.
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Jenis Surat</th>
                                            <th>Tanggal Pengajuan</th>
                                            <th>Status</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $no = 1; foreach ($pengajuan_list as $pengajuan): ?>
                                            <tr>
                                                <td><?= $no++ ?></td>
                                                <td><?= getJenisSurat($pengajuan['jenis']) ?></td>
                                                <td><?= formatTanggal($pengajuan['tanggal_diajukan']) ?></td>
                                                <td><?= getStatusLabel($pengajuan['status']) ?></td>
                                                <td>
                                                    <button type="button" class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#detailModal<?= $pengajuan['id'] ?>">
                                                        <i class="bi bi-eye"></i> Detail
                                                    </button>
                                                    
                                                    <?php if ($pengajuan['status'] == 'selesai'): ?>
                                                        <a href="cetak_surat.php?id=<?= $pengajuan['id'] ?>" class="btn btn-sm btn-success">
                                                            <i class="bi bi-printer"></i> Cetak
                                                        </a>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                            
                                            <!-- Modal Detail -->
                                            <div class="modal fade" id="detailModal<?= $pengajuan['id'] ?>" tabindex="-1" aria-labelledby="detailModalLabel<?= $pengajuan['id'] ?>" aria-hidden="true">
                                                <div class="modal-dialog modal-lg">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title" id="detailModalLabel<?= $pengajuan['id'] ?>">Detail Pengajuan</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <div class="row">
                                                                <div class="col-md-6">
                                                                    <h6>Informasi Pengajuan:</h6>
                                                                    <table class="table table-bordered">
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
                                                                        <?php if (!empty($pengajuan['keterangan'])): ?>
                                                                        <tr>
                                                                            <th>Keterangan</th>
                                                                            <td><?= nl2br(htmlspecialchars($pengajuan['keterangan'])) ?></td>
                                                                        </tr>
                                                                        <?php endif; ?>
                                                                    </table>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <h6>Dokumen Pendukung:</h6>
                                                                    <?php 
                                                                    $file_ext = pathinfo($pengajuan['dokumen_path'], PATHINFO_EXTENSION);
                                                                    if (in_array(strtolower($file_ext), ['jpg', 'jpeg', 'png'])):
                                                                    ?>
                                                                        <img src="../assets/uploads/<?= $pengajuan['dokumen_path'] ?>" class="img-fluid document-preview" alt="Dokumen Pendukung">
                                                                    <?php else: ?>
                                                                        <p>Dokumen PDF: <a href="../assets/uploads/<?= $pengajuan['dokumen_path'] ?>" target="_blank">Lihat Dokumen</a></p>
                                                                    <?php endif; ?>
                                                                </div>
                                                            </div>
                                                            
                                                            <div class="mt-4">
                                                                <h6>Status Pengajuan:</h6>
                                                                <div class="timeline">
                                                                    <div class="timeline-item <?= $pengajuan['status'] != 'ditolak' ? 'completed' : '' ?>">
                                                                        <div class="card">
                                                                            <div class="card-body py-2">
                                                                                <strong>Pengajuan Diterima</strong>
                                                                                <p class="mb-0 text-muted"><?= formatTanggal($pengajuan['tanggal_diajukan']) ?></p>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    
                                                                    <?php if ($pengajuan['status'] == 'diproses' || $pengajuan['status'] == 'selesai'): ?>
                                                                    <div class="timeline-item completed">
                                                                        <div class="card">
                                                                            <div class="card-body py-2">
                                                                                <strong>Sedang Diproses</strong>
                                                                                <p class="mb-0 text-muted"><?= formatTanggal($pengajuan['tanggal_diproses']) ?></p>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <?php elseif ($pengajuan['status'] == 'pending'): ?>
                                                                    <div class="timeline-item">
                                                                        <div class="card">
                                                                            <div class="card-body py-2">
                                                                                <strong>Sedang Diproses</strong>
                                                                                <p class="mb-0 text-muted">Menunggu verifikasi admin</p>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <?php endif; ?>
                                                                    
                                                                    <?php if ($pengajuan['status'] == 'selesai'): ?>
                                                                    <div class="timeline-item completed">
                                                                        <div class="card">
                                                                            <div class="card-body py-2">
                                                                                <strong>Pengajuan Selesai</strong>
                                                                                <p class="mb-0 text-muted"><?= formatTanggal($pengajuan['tanggal_selesai']) ?></p>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <?php elseif ($pengajuan['status'] == 'ditolak'): ?>
                                                                    <div class="timeline-item">
                                                                        <div class="card bg-danger text-white">
                                                                            <div class="card-body py-2">
                                                                                <strong>Pengajuan Ditolak</strong>
                                                                                <p class="mb-0"><?= formatTanggal($pengajuan['tanggal_diproses']) ?></p>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <?php else: ?>
                                                                    <div class="timeline-item">
                                                                        <div class="card">
                                                                            <div class="card-body py-2">
                                                                                <strong>Pengajuan Selesai</strong>
                                                                                <p class="mb-0 text-muted">Menunggu proses</p>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <?php endif; ?>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                                                            <?php if ($pengajuan['status'] == 'selesai'): ?>
                                                                <a href="cetak_surat.php?id=<?= $pengajuan['id'] ?>" class="btn btn-success">
                                                                    <i class="bi bi-printer"></i> Cetak Surat
                                                                </a>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
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