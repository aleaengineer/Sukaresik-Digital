<?php
require_once '../includes/auth.php';
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Pastikan user sudah login dan role-nya admin
requireAdmin();

$user = getCurrentUser($pdo);

// Pencarian
$search = isset($_GET['search']) ? $_GET['search'] : '';
$search_query = !empty($search) ? "WHERE (nama LIKE '%$search%' OR nik LIKE '%$search%' OR email LIKE '%$search%')" : "WHERE role = 'warga'";
if (empty($search)) {
    $search_query = "WHERE role = 'warga'";
} else {
    $search_query = "WHERE role = 'warga' AND (nama LIKE '%$search%' OR nik LIKE '%$search%' OR email LIKE '%$search%')";
}

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

// Query untuk menghitung total data
$count_query = "SELECT COUNT(*) FROM users $search_query";
$stmt = $pdo->query($count_query);
$total_records = $stmt->fetchColumn();
$total_pages = ceil($total_records / $per_page);

// Query untuk mengambil data warga
$query = "SELECT * FROM users $search_query ORDER BY created_at DESC LIMIT $offset, $per_page";
$stmt = $pdo->query($query);
$warga_list = $stmt->fetchAll();

// Proses aktivasi/deaktivasi akun
if (isset($_POST['toggle_status'])) {
    $warga_id = $_POST['warga_id'];
    $new_status = $_POST['new_status'];
    
    $stmt = $pdo->prepare("UPDATE users SET is_active = ? WHERE id = ?");
    $result = $stmt->execute([$new_status, $warga_id]);
    
    if ($result) {
        // Refresh data
        header('Location: warga.php?page=' . $page . '&search=' . urlencode($search));
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Warga - Sukaresik Digital</title>
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
                            <a class="nav-link active" href="warga.php">
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
                    <h1 class="h2">Data Warga</h1>
                </div>

                <div class="card mb-4">
                    <div class="card-body">
                        <form action="" method="GET" class="d-flex">
                            <input type="text" name="search" class="form-control me-2" placeholder="Cari nama, NIK, atau email..." value="<?= htmlspecialchars($search) ?>">
                            <button type="submit" class="btn btn-primary">Cari</button>
                        </form>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body">
                        <?php if (empty($warga_list)): ?>
                            <div class="alert alert-info">Tidak ada data warga yang ditemukan.</div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>NIK</th>
                                            <th>Nama</th>
                                            <th>Email</th>
                                            <th>No. HP</th>
                                            <th>Status</th>
                                            <th>Terdaftar</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        $no = ($page - 1) * $per_page + 1;
                                        foreach ($warga_list as $warga): 
                                        ?>
                                            <tr>
                                                <td><?= $no++ ?></td>
                                                <td><?= htmlspecialchars($warga['nik']) ?></td>
                                                <td><?= htmlspecialchars($warga['nama']) ?></td>
                                                <td><?= htmlspecialchars($warga['email']) ?></td>
                                                <td><?= htmlspecialchars($warga['no_hp']) ?></td>
                                                <td>
                                                    <?php if ($warga['is_active']): ?>
                                                        <span class="badge bg-success">Aktif</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-danger">Nonaktif</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?= formatTanggal($warga['created_at']) ?></td>
                                                <td>
                                                    <button type="button" class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#detailModal<?= $warga['id'] ?>">
                                                        <i class="bi bi-eye"></i> Detail
                                                    </button>
                                                    
                                                    <form method="POST" action="" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin <?= $warga['is_active'] ? 'menonaktifkan' : 'mengaktifkan' ?> akun ini?');">
                                                        <input type="hidden" name="warga_id" value="<?= $warga['id'] ?>">
                                                        <input type="hidden" name="new_status" value="<?= $warga['is_active'] ? '0' : '1' ?>">
                                                        <button type="submit" name="toggle_status" class="btn btn-sm <?= $warga['is_active'] ? 'btn-warning' : 'btn-success' ?>">
                                                            <i class="bi <?= $warga['is_active'] ? 'bi-x-circle' : 'bi-check-circle' ?>"></i> 
                                                            <?= $warga['is_active'] ? 'Nonaktifkan' : 'Aktifkan' ?>
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                            
                                            <!-- Modal Detail -->
                                            <div class="modal fade" id="detailModal<?= $warga['id'] ?>" tabindex="-1" aria-labelledby="detailModalLabel<?= $warga['id'] ?>" aria-hidden="true">
                                                <div class="modal-dialog modal-lg">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title" id="detailModalLabel<?= $warga['id'] ?>">Detail Warga</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <div class="row">
                                                                <div class="col-md-6">
                                                                    <table class="table table-bordered">
                                                                        <tr>
                                                                            <th width="40%">NIK</th>
                                                                            <td><?= htmlspecialchars($warga['nik']) ?></td>
                                                                        </tr>
                                                                        <tr>
                                                                            <th>Nama</th>
                                                                            <td><?= htmlspecialchars($warga['nama']) ?></td>
                                                                        </tr>
                                                                        <tr>
                                                                            <th>Nomor KK</th>
                                                                            <td><?= htmlspecialchars($warga['no_kk']) ?></td>
                                                                        </tr>
                                                                        <tr>
                                                                            <th>Alamat</th>
                                                                            <td><?= htmlspecialchars($warga['alamat']) ?></td>
                                                                        </tr>
                                                                    </table>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <table class="table table-bordered">
                                                                        <tr>
                                                                            <th width="40%">Email</th>
                                                                            <td><?= htmlspecialchars($warga['email']) ?></td>
                                                                        </tr>
                                                                        <tr>
                                                                            <th>No. HP</th>
                                                                            <td><?= htmlspecialchars($warga['no_hp']) ?></td>
                                                                        </tr>
                                                                        <tr>
                                                                            <th>Status</th>
                                                                            <td>
                                                                                <?php if ($warga['is_active']): ?>
                                                                                    <span class="badge bg-success">Aktif</span>
                                                                                <?php else: ?>
                                                                                    <span class="badge bg-danger">Nonaktif</span>
                                                                                <?php endif; ?>
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <th>Terdaftar</th>
                                                                            <td><?= formatTanggal($warga['created_at']) ?></td>
                                                                        </tr>
                                                                    </table>
                                                                </div>
                                                            </div>
                                                            
                                                            <?php
                                                            // Ambil data pengajuan surat
                                                            $stmt = $pdo->prepare("SELECT * FROM surat WHERE user_id = ? ORDER BY tanggal_diajukan DESC LIMIT 5");
                                                            $stmt->execute([$warga['id']]);
                                                            $pengajuan_list = $stmt->fetchAll();
                                                            ?>
                                                            
                                                            <h6 class="mt-4">Riwayat Pengajuan Surat</h6>
                                                            <?php if (empty($pengajuan_list)): ?>
                                                                <div class="alert alert-info">Belum ada pengajuan surat.</div>
                                                            <?php else: ?>
                                                                <div class="table-responsive">
                                                                    <table class="table table-striped table-sm">
                                                                        <thead>
                                                                            <tr>
                                                                                <th>Tanggal</th>
                                                                                <th>Jenis Surat</th>
                                                                                <th>Status</th>
                                                                                <th>Aksi</th>
                                                                            </tr>
                                                                        </thead>
                                                                        <tbody>
                                                                            <?php foreach ($pengajuan_list as $pengajuan): ?>
                                                                                <tr>
                                                                                    <td><?= formatTanggal($pengajuan['tanggal_diajukan']) ?></td>
                                                                                    <td><?= getJenisSurat($pengajuan['jenis']) ?></td>
                                                                                    <td><?= getStatusLabel($pengajuan['status']) ?></td>
                                                                                    <td>
                                                                                        <a href="detail_pengajuan.php?id=<?= $pengajuan['id'] ?>" class="btn btn-sm btn-info">
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
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                                                            <form method="POST" action="" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin <?= $warga['is_active'] ? 'menonaktifkan' : 'mengaktifkan' ?> akun ini?');">
                                                                <input type="hidden" name="warga_id" value="<?= $warga['id'] ?>">
                                                                <input type="hidden" name="new_status" value="<?= $warga['is_active'] ? '0' : '1' ?>">
                                                                <button type="submit" name="toggle_status" class="btn <?= $warga['is_active'] ? 'btn-warning' : 'btn-success' ?>">
                                                                    <i class="bi <?= $warga['is_active'] ? 'bi-x-circle' : 'bi-check-circle' ?>"></i> 
                                                                    <?= $warga['is_active'] ? 'Nonaktifkan Akun' : 'Aktifkan Akun' ?>
                                                                </button>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Pagination -->
                            <?php if ($total_pages > 1): ?>
                                <nav aria-label="Page navigation">
                                    <ul class="pagination justify-content-center">
                                        <?php if ($page > 1): ?>
                                            <li class="page-item">
                                                <a class="page-link" href="?page=<?= $page-1 ?>&search=<?= urlencode($search) ?>">
                                                    <i class="bi bi-chevron-left"></i>
                                                </a>
                                            </li>
                                        <?php endif; ?>
                                        
                                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                            <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                                <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>">
                                                    <?= $i ?>
                                                </a>
                                            </li>
                                        <?php endfor; ?>
                                        
                                        <?php if ($page < $total_pages): ?>
                                            <li class="page-item">
                                                <a class="page-link" href="?page=<?= $page+1 ?>&search=<?= urlencode($search) ?>">
                                                    <i class="bi bi-chevron-right"></i>
                                                </a>
                                            </li>
                                        <?php endif; ?>
                                    </ul>
                                </nav>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>