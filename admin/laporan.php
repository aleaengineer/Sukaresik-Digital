<?php
require_once '../includes/auth.php';
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Pastikan user sudah login dan role-nya admin
requireAdmin();

$user = getCurrentUser($pdo);

// Filter periode
$periode = isset($_GET['periode']) ? $_GET['periode'] : 'bulan_ini';
$jenis = isset($_GET['jenis']) ? $_GET['jenis'] : 'all';

// Tentukan rentang tanggal berdasarkan periode
$start_date = '';
$end_date = '';
$today = date('Y-m-d');

switch ($periode) {
    case 'hari_ini':
        $start_date = $today;
        $end_date = $today;
        break;
    case 'minggu_ini':
        $start_date = date('Y-m-d', strtotime('monday this week'));
        $end_date = date('Y-m-d', strtotime('sunday this week'));
        break;
    case 'bulan_ini':
        $start_date = date('Y-m-01');
        $end_date = date('Y-m-t');
        break;
    case 'tahun_ini':
        $start_date = date('Y-01-01');
        $end_date = date('Y-12-31');
        break;
    case 'custom':
        $start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
        $end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');
        break;
    default:
        $start_date = date('Y-m-01');
        $end_date = date('Y-m-t');
        break;
}

// Buat query filter
$jenis_query = ($jenis != 'all') ? "AND jenis = '$jenis'" : "";
$query = "SELECT s.*, u.nama, u.nik FROM surat s 
          JOIN users u ON s.user_id = u.id 
          WHERE DATE(s.tanggal_diajukan) BETWEEN '$start_date' AND '$end_date' 
          $jenis_query
          ORDER BY s.tanggal_diajukan DESC";
$stmt = $pdo->query($query);
$laporan_data = $stmt->fetchAll();

// Statistik
$total_pengajuan = count($laporan_data);

// Hitung jumlah per status
$status_counts = [
    'pending' => 0,
    'diproses' => 0,
    'selesai' => 0,
    'ditolak' => 0
];

// Hitung jumlah per jenis surat
$jenis_counts = [];

foreach ($laporan_data as $data) {
    $status_counts[$data['status']]++;
    
    if (!isset($jenis_counts[$data['jenis']])) {
        $jenis_counts[$data['jenis']] = 0;
    }
    $jenis_counts[$data['jenis']]++;
}

// Untuk grafik
$status_labels = json_encode(['Menunggu Verifikasi', 'Sedang Diproses', 'Selesai', 'Ditolak']);
$status_values = json_encode([$status_counts['pending'], $status_counts['diproses'], $status_counts['selesai'], $status_counts['ditolak']]);

$jenis_surat_labels = [];
$jenis_surat_values = [];
foreach ($jenis_counts as $jenis_key => $count) {
    $jenis_surat_labels[] = getJenisSurat($jenis_key);
    $jenis_surat_values[] = $count;
}
$jenis_surat_labels_json = json_encode($jenis_surat_labels);
$jenis_surat_values_json = json_encode($jenis_surat_values);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan - Sukaresik Digital</title>
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
                            <a class="nav-link active" href="laporan.php">
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
                    <h1 class="h2">Laporan Pengajuan Surat</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="window.print()">
                            <i class="bi bi-printer"></i> Cetak Laporan
                        </button>
                    </div>
                </div>

                <div class="card mb-4 print-hide">
                    <div class="card-body">
                        <form action="" method="GET" class="row g-3">
                            <div class="col-md-4">
                                <label for="periode" class="form-label">Periode</label>
                                <select class="form-select" id="periode" name="periode" onchange="toggleCustomDate()">
                                    <option value="hari_ini" <?= $periode == 'hari_ini' ? 'selected' : '' ?>>Hari Ini</option>
                                    <option value="minggu_ini" <?= $periode == 'minggu_ini' ? 'selected' : '' ?>>Minggu Ini</option>
                                    <option value="bulan_ini" <?= $periode == 'bulan_ini' ? 'selected' : '' ?>>Bulan Ini</option>
                                    <option value="tahun_ini" <?= $periode == 'tahun_ini' ? 'selected' : '' ?>>Tahun Ini</option>
                                    <option value="custom" <?= $periode == 'custom' ? 'selected' : '' ?>>Kustom</option>
                                </select>
                            </div>
                            <div class="col-md-3 custom-date" style="display: <?= $periode == 'custom' ? 'block' : 'none' ?>;">
                                <label for="start_date" class="form-label">Tanggal Mulai</label>
                                <input type="date" class="form-control" id="start_date" name="start_date" value="<?= $start_date ?>">
                            </div>
                            <div class="col-md-3 custom-date" style="display: <?= $periode == 'custom' ? 'block' : 'none' ?>;">
                                <label for="end_date" class="form-label">Tanggal Akhir</label>
                                <input type="date" class="form-control" id="end_date" name="end_date" value="<?= $end_date ?>">
                            </div>
                            <div class="col-md-2">
                                <label for="jenis" class="form-label">Jenis Surat</label>
                                <select class="form-select" id="jenis" name="jenis">
                                    <option value="all" <?= $jenis == 'all' ? 'selected' : '' ?>>Semua</option>
                                    <option value="domisili" <?= $jenis == 'domisili' ? 'selected' : '' ?>>Surat Domisili</option>
                                    <option value="pengantar_ktp" <?= $jenis == 'pengantar_ktp' ? 'selected' : '' ?>>Pengantar KTP</option>
                                    <option value="sktm" <?= $jenis == 'sktm' ? 'selected' : '' ?>>SKTM</option>
                                    <option value="skck" <?= $jenis == 'skck' ? 'selected' : '' ?>>SKCK</option>
                                    <option value="usaha" <?= $jenis == 'usaha' ? 'selected' : '' ?>>Keterangan Usaha</option>
                                    <option value="kelahiran" <?= $jenis == 'kelahiran' ? 'selected' : '' ?>>Keterangan Kelahiran</option>
                                    <option value="kematian" <?= $jenis == 'kematian' ? 'selected' : '' ?>>Keterangan Kematian</option>
                                </select>
                            </div>
                            <div class="col-md-12">
                                <button type="submit" class="btn btn-primary">Tampilkan</button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="print-header d-none">
                    <div class="text-center mb-4">
                        <h2>LAPORAN PENGAJUAN SURAT</h2>
                        <h3>DESA SUKARESIK</h3>
                        <p>Periode: <?= formatTanggal($start_date) ?> s/d <?= formatTanggal($end_date) ?></p>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Statistik Status Pengajuan</h5>
                            </div>
                            <div class="card-body">
                                <canvas id="statusChart"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
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

                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Ringkasan</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <div class="card bg-primary text-white">
                                    <div class="card-body">
                                        <h6 class="card-title">Total Pengajuan</h6>
                                        <h2 class="mb-0"><?= $total_pengajuan ?></h2>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <div class="card bg-warning text-dark">
                                    <div class="card-body">
                                        <h6 class="card-title">Menunggu Verifikasi</h6>
                                        <h2 class="mb-0"><?= $status_counts['pending'] ?></h2>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <div class="card bg-info text-white">
                                    <div class="card-body">
                                        <h6 class="card-title">Sedang Diproses</h6>
                                        <h2 class="mb-0"><?= $status_counts['diproses'] ?></h2>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <div class="card bg-success text-white">
                                    <div class="card-body">
                                        <h6 class="card-title">Selesai</h6>
                                        <h2 class="mb-0"><?= $status_counts['selesai'] ?></h2>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Data Pengajuan Surat</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($laporan_data)): ?>
                            <div class="alert alert-info">Tidak ada data pengajuan surat pada periode yang dipilih.</div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-striped table-bordered">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Tanggal</th>
                                            <th>Nama</th>
                                            <th>NIK</th>
                                            <th>Jenis Surat</th>
                                            <th>Status</th>
                                            <th class="print-hide">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        $no = 1;
                                        foreach ($laporan_data as $data): 
                                        ?>
                                            <tr>
                                                <td><?= $no++ ?></td>
                                                <td><?= formatTanggal($data['tanggal_diajukan']) ?></td>
                                                <td><?= htmlspecialchars($data['nama']) ?></td>
                                                <td><?= htmlspecialchars($data['nik']) ?></td>
                                                <td><?= getJenisSurat($data['jenis']) ?></td>
                                                <td><?= getStatusLabel($data['status']) ?></td>
                                                <td class="print-hide">
                                                    <a href="detail_pengajuan.php?id=<?= $data['id'] ?>" class="btn btn-sm btn-info">
                                                        <i class="bi bi-eye"></i> Detail
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

                <div class="print-footer d-none mt-5">
                    <div class="row">
                        <div class="col-md-6 offset-md-6 text-center">
                            <p>Sukaresik, <?= date('d F Y') ?></p>
                            <p>Kepala Desa Sukaresik</p>
                            <br><br><br>
                            <p><u>NAMA KEPALA DESA</u></p>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        function toggleCustomDate() {
            const periode = document.getElementById('periode').value;
            const customDateFields = document.querySelectorAll('.custom-date');
            
            if (periode === 'custom') {
                customDateFields.forEach(field => field.style.display = 'block');
            } else {
                customDateFields.forEach(field => field.style.display = 'none');
            }
        }

        // Chart untuk status
        const statusCtx = document.getElementById('statusChart').getContext('2d');
        const statusChart = new Chart(statusCtx, {
            type: 'pie',
            data: {
                labels: <?= $status_labels ?>,
                datasets: [{
                    data: <?= $status_values ?>,
                    backgroundColor: [
                        '#ffc107', // warning - pending
                        '#17a2b8', // info - diproses
                        '#28a745', // success - selesai
                        '#dc3545'  // danger - ditolak
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

        // Chart untuk jenis surat
        const jenisCtx = document.getElementById('jenisSuratChart').getContext('2d');
        const jenisChart = new Chart(jenisCtx, {
            type: 'bar',
            data: {
                labels: <?= $jenis_surat_labels_json ?>,
                datasets: [{
                    label: 'Jumlah Pengajuan',
                    data: <?= $jenis_surat_values_json ?>,
                    backgroundColor: 'rgba(54, 162, 235, 0.5)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
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

        // Print style
        window.onbeforeprint = function() {
            document.querySelectorAll('.print-hide').forEach(el => {
                el.style.display = 'none';
            });
            document.querySelectorAll('.print-header, .print-footer').forEach(el => {
                el.classList.remove('d-none');
            });
        };

        window.onafterprint = function() {
            document.querySelectorAll('.print-hide').forEach(el => {
                el.style.display = '';
            });
            document.querySelectorAll('.print-header, .print-footer').forEach(el => {
                el.classList.add('d-none');
            });
        };
    </script>
</body>
</html>