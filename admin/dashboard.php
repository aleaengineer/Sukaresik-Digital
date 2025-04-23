<?php
require_once '../includes/auth.php';
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Pastikan user sudah login dan role-nya admin
requireAdmin();

$user = getCurrentUser($pdo);

// Statistik untuk dashboard
// Total pengajuan surat
$stmt = $pdo->query("SELECT COUNT(*) FROM surat");
$total_pengajuan = $stmt->fetchColumn();

// Pengajuan surat hari ini
$stmt = $pdo->query("SELECT COUNT(*) FROM surat WHERE DATE(tanggal_diajukan) = CURDATE()");
$pengajuan_hari_ini = $stmt->fetchColumn();

// Total warga terdaftar
$stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'warga'");
$total_warga = $stmt->fetchColumn();

// Pengajuan berdasarkan status
$stmt = $pdo->query("SELECT status, COUNT(*) as jumlah FROM surat GROUP BY status");
$status_counts = [];
while ($row = $stmt->fetch()) {
    $status_counts[$row['status']] = $row['jumlah'];
}

// Pengajuan terbaru
$stmt = $pdo->query("SELECT s.*, u.nama, u.nik FROM surat s 
                    JOIN users u ON s.user_id = u.id 
                    ORDER BY s.tanggal_diajukan DESC LIMIT 5");
$pengajuan_terbaru = $stmt->fetchAll();

// Warga terbaru
$stmt = $pdo->query("SELECT * FROM users WHERE role = 'warga' ORDER BY created_at DESC LIMIT 5");
$warga_terbaru = $stmt->fetchAll();

// Data untuk grafik
// Pengajuan per bulan (6 bulan terakhir)
$stmt = $pdo->query("SELECT DATE_FORMAT(tanggal_diajukan, '%Y-%m') as bulan, COUNT(*) as jumlah 
                     FROM surat 
                     WHERE tanggal_diajukan >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH) 
                     GROUP BY DATE_FORMAT(tanggal_diajukan, '%Y-%m') 
                     ORDER BY bulan ASC");
$pengajuan_per_bulan = $stmt->fetchAll();

$bulan_labels = [];
$bulan_data = [];

foreach ($pengajuan_per_bulan as $data) {
    $bulan = date('M Y', strtotime($data['bulan'] . '-01'));
    $bulan_labels[] = $bulan;
    $bulan_data[] = $data['jumlah'];
}

// Pengajuan berdasarkan jenis surat
$stmt = $pdo->query("SELECT jenis, COUNT(*) as jumlah FROM surat GROUP BY jenis");
$jenis_counts = [];
while ($row = $stmt->fetch()) {
    $jenis_counts[$row['jenis']] = $row['jumlah'];
}

$jenis_labels = [];
$jenis_data = [];

foreach ($jenis_counts as $jenis => $jumlah) {
    $jenis_labels[] = getJenisSurat($jenis);
    $jenis_data[] = $jumlah;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Sukaresik Digital</title>
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
                            <a class="nav-link active" href="dashboard.php">
                                <i class="bi bi-speedometer2 me-2"></i>
                                Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="pengajuan.php">
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
                    <h1 class="h2">Dashboard</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <a href="laporan.php" class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-bar-chart"></i> Lihat Laporan
                            </a>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-3 mb-4">
                        <div class="card bg-primary text-white h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="card-title">Total Pengajuan</h6>
                                        <h2 class="mb-0"><?= $total_pengajuan ?></h2>
                                    </div>
                                    <i class="bi bi-file-earmark-text fs-1"></i>
                                </div>
                            </div>
                            <div class="card-footer d-flex align-items-center justify-content-between">
                                <a href="pengajuan.php" class="text-white text-decoration-none">Lihat Detail</a>
                                <i class="bi bi-arrow-right text-white"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-4">
                        <div class="card bg-success text-white h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="card-title">Pengajuan Hari Ini</h6>
                                        <h2 class="mb-0"><?= $pengajuan_hari_ini ?></h2>
                                    </div>
                                    <i class="bi bi-calendar-check fs-1"></i>
                                </div>
                            </div>
                            <div class="card-footer d-flex align-items-center justify-content-between">
                                <a href="pengajuan.php" class="text-white text-decoration-none">Lihat Detail</a>
                                <i class="bi bi-arrow-right text-white"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-4">
                        <div class="card bg-warning text-dark h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="card-title">Menunggu Verifikasi</h6>
                                        <h2 class="mb-0"><?= isset($status_counts['pending']) ? $status_counts['pending'] : 0 ?></h2>
                                    </div>
                                    <i class="bi bi-hourglass-split fs-1"></i>
                                </div>
                            </div>
                            <div class="card-footer d-flex align-items-center justify-content-between">
                                <a href="pengajuan.php?status=pending" class="text-dark text-decoration-none">Lihat Detail</a>
                                <i class="bi bi-arrow-right text-dark"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-4">
                        <div class="card bg-info text-white h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="card-title">Total Warga</h6>
                                        <h2 class="mb-0"><?= $total_warga ?></h2>
                                    </div>
                                    <i class="bi bi-people fs-1"></i>
                                </div>
                            </div>
                            <div class="card-footer d-flex align-items-center justify-content-between">
                                <a href="warga.php" class="text-white text-decoration-none">Lihat Detail</a>
                                <i class="bi bi-arrow-right text-white"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-8 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Statistik Pengajuan Surat (6 Bulan Terakhir)</h5>
                            </div>
                            <div class="card-body">
                                <canvas id="pengajuanChart"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Statistik Jenis Surat</h5>
                            </div>
                            <div class="card-body">
                                <canvas id="jenisSuratChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-4">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">Pengajuan Terbaru</h5>
                                <a href="pengajuan.php" class="btn btn-sm btn-primary">Lihat Semua</a>
                            </div>
                            <div class="card-body">
                                <?php if (empty($pengajuan_terbaru)): ?>
                                    <div class="alert alert-info">Belum ada pengajuan surat.</div>
                                <?php else: ?>
                                    <div class="table-responsive">
                                        <table class="table table-striped table-sm">
                                            <thead>
                                                <tr>
                                                    <th>Tanggal</th>
                                                    <th>Nama</th>
                                                    <th>Jenis Surat</th>
                                                    <th>Status</th>
                                                    <th>Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($pengajuan_terbaru as $pengajuan): ?>
                                                    <tr>
                                                        <td><?= formatTanggal($pengajuan['tanggal_diajukan']) ?></td>
                                                        <td><?= htmlspecialchars($pengajuan['nama']) ?></td>
                                                        <td><?= getJenisSurat($pengajuan['jenis']) ?></td>
                                                        <td><?= getStatusLabel($pengajuan['status']) ?></td>
                                                        <td>
                                                            <a href="detail_pengajuan.php?id=<?= $pengajuan['id'] ?>" class="btn btn-sm btn-info">
                                                                <i class="bi bi-eye"></i>
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
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">Warga Terbaru</h5>
                                <a href="warga.php" class="btn btn-sm btn-primary">Lihat Semua</a>
                            </div>
                            <div class="card-body">
                                <?php if (empty($warga_terbaru)): ?>
                                    <div class="alert alert-info">Belum ada warga terdaftar.</div>
                                <?php else: ?>
                                    <div class="table-responsive">
                                        <table class="table table-striped table-sm">
                                            <thead>
                                                <tr>
                                                    <th>Tanggal Daftar</th>
                                                    <th>Nama</th>
                                                    <th>NIK</th>
                                                    <th>Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($warga_terbaru as $warga): ?>
                                                    <tr>
                                                        <td><?= formatTanggal($warga['created_at']) ?></td>
                                                        <td><?= htmlspecialchars($warga['nama']) ?></td>
                                                        <td><?= htmlspecialchars($warga['nik']) ?></td>
                                                        <td>
                                                            <?php if ($warga['is_active']): ?>
                                                                <span class="badge bg-success">Aktif</span>
                                                            <?php else: ?>
                                                                <span class="badge bg-danger">Nonaktif</span>
                                                            <?php endif; ?>
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
                </div>

                <div class="row">
                    <div class="col-md-12 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Status Pengajuan Surat</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-3 mb-3">
                                        <div class="card bg-warning text-dark">
                                            <div class="card-body text-center">
                                                <h6 class="card-title">Menunggu Verifikasi</h6>
                                                <h2 class="mb-0"><?= isset($status_counts['pending']) ? $status_counts['pending'] : 0 ?></h2>
                                                <a href="pengajuan.php?status=pending" class="btn btn-sm btn-dark mt-2">Lihat</a>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <div class="card bg-info text-white">
                                            <div class="card-body text-center">
                                                <h6 class="card-title">Sedang Diproses</h6>
                                                <h2 class="mb-0"><?= isset($status_counts['diproses']) ? $status_counts['diproses'] : 0 ?></h2>
                                                <a href="pengajuan.php?status=diproses" class="btn btn-sm btn-dark mt-2">Lihat</a>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <div class="card bg-success text-white">
                                            <div class="card-body text-center">
                                                <h6 class="card-title">Selesai</h6>
                                                <h2 class="mb-0"><?= isset($status_counts['selesai']) ? $status_counts['selesai'] : 0 ?></h2>
                                                <a href="pengajuan.php?status=selesai" class="btn btn-sm btn-dark mt-2">Lihat</a>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <div class="card bg-danger text-white">
                                            <div class="card-body text-center">
                                                <h6 class="card-title">Ditolak</h6>
                                                <h2 class="mb-0"><?= isset($status_counts['ditolak']) ? $status_counts['ditolak'] : 0 ?></h2>
                                                <a href="pengajuan.php?status=ditolak" class="btn btn-sm btn-dark mt-2">Lihat</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Chart untuk pengajuan per bulan
        const pengajuanCtx = document.getElementById('pengajuanChart').getContext('2d');
        const pengajuanChart = new Chart(pengajuanCtx, {
            type: 'line',
            data: {
                labels: <?= json_encode($bulan_labels) ?>,
                datasets: [{
                    label: 'Jumlah Pengajuan',
                    data: <?= json_encode($bulan_data) ?>,
                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 2,
                    tension: 0.1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });

        // Chart untuk jenis surat
        const jenisCtx = document.getElementById('jenisSuratChart').getContext('2d');
        const jenisChart = new Chart(jenisCtx, {
            type: 'doughnut',
            data: {
                labels: <?= json_encode($jenis_labels) ?>,
                datasets: [{
                    data: <?= json_encode($jenis_data) ?>,
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.7)',
                        'rgba(54, 162, 235, 0.7)',
                        'rgba(255, 206, 86, 0.7)',
                        'rgba(75, 192, 192, 0.7)',
                        'rgba(153, 102, 255, 0.7)',
                        'rgba(255, 159, 64, 0.7)',
                        'rgba(199, 199, 199, 0.7)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                    }
                }
            }
        });
    </script>
</body>
</html>
