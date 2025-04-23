<?php
require_once '../includes/auth.php';
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Pastikan user sudah login dan role-nya admin
requireAdmin();

$user = getCurrentUser($pdo);

// Filter status
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';
$status_query = ($status_filter != 'all') ? "WHERE status = '$status_filter'" : "";

// Pencarian
$search = isset($_GET['search']) ? $_GET['search'] : '';
$search_query = !empty($search) ? 
    ($status_filter != 'all' ? 
        " AND (u.nama LIKE '%$search%' OR u.nik LIKE '%$search%')" : 
        " WHERE (u.nama LIKE '%$search%' OR u.nik LIKE '%$search%')") : 
    "";

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

// Query untuk menghitung total data
$count_query = "SELECT COUNT(*) FROM surat s JOIN users u ON s.user_id = u.id $status_query $search_query";
$stmt = $pdo->query($count_query);
$total_records = $stmt->fetchColumn();
$total_pages = ceil($total_records / $per_page);

// Query untuk mengambil data pengajuan
$query = "SELECT s.*, u.nama, u.nik FROM surat s 
          JOIN users u ON s.user_id = u.id 
          $status_query $search_query 
          ORDER BY s.tanggal_diajukan DESC 
          LIMIT $offset, $per_page";
$stmt = $pdo->query($query);
$pengajuan_list = $stmt->fetchAll();
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
                    <h1 class="h2">Pengajuan Surat</h1>
                </div>

                <div class="card mb-4">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-8">
                                <form action="" method="GET" class="d-flex">
                                    <input type="hidden" name="status" value="<?= $status_filter ?>">
                                    <input type="text" name="search" class="form-control me-2" placeholder="Cari nama atau NIK..." value="<?= htmlspecialchars($search) ?>">
                                    <button type="submit" class="btn btn-primary">Cari</button>
                                </form>
                            </div>
                            <div class="col-md-4">
                                <form action="" method="GET" class="d-flex">
                                    <?php if (!empty($search)): ?>
                                        <input type="hidden" name="search" value="<?= htmlspecialchars($search) ?>">
                                    <?php endif; ?>
                                    <select name="status" class="form-select me-2" onchange="this.form.submit()">
                                        <option value="all" <?= $status_filter == 'all' ? 'selected' : '' ?>>Semua Status</option>
                                        <option value="pending" <?= $status_filter == 'pending' ? 'selected' : '' ?>>Menunggu Verifikasi</option>
                                        <option value="diproses" <?= $status_filter == 'diproses' ? 'selected' : '' ?>>Sedang Diproses</option>
                                        <option value="selesai" <?= $status_filter == 'selesai' ? 'selected' : '' ?>>Selesai</option>
                                        <option value="ditolak" <?= $status_filter == 'ditolak' ? 'selected' : '' ?>>Ditolak</option>
                                    </select>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body">
                        <?php if (empty($pengajuan_list)): ?>
                            <div class="alert alert-info">Tidak ada data pengajuan surat yang ditemukan.</div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Tanggal</th>
                                            <th>Nama</th>
                                            <th>NIK</th>
                                            <th>Jenis Surat</th>
                                            <th>Status</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        $no = ($page - 1) * $per_page + 1;
                                        foreach ($pengajuan_list as $pengajuan): 
                                        ?>
                                            <tr>
                                                <td><?= $no++ ?></td>
                                                <td><?= formatTanggal($pengajuan['tanggal_diajukan']) ?></td>
                                                <td><?= htmlspecialchars($pengajuan['nama']) ?></td>
                                                <td><?= htmlspecialchars($pengajuan['nik']) ?></td>
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

                            <!-- Pagination -->
                            <?php if ($total_pages > 1): ?>
                                <nav aria-label="Page navigation">
                                    <ul class="pagination justify-content-center">
                                        <?php if ($page > 1): ?>
                                            <li class="page-item">
                                                <a class="page-link" href="?page=<?= $page-1 ?>&status=<?= $status_filter ?>&search=<?= urlencode($search) ?>">
                                                    <i class="bi bi-chevron-left"></i>
                                                </a>
                                            </li>
                                        <?php endif; ?>
                                        
                                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                            <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                                <a class="page-link" href="?page=<?= $i ?>&status=<?= $status_filter ?>&search=<?= urlencode($search) ?>">
                                                    <?= $i ?>
                                                </a>
                                            </li>
                                        <?php endfor; ?>
                                        
                                        <?php if ($page < $total_pages): ?>
                                            <li class="page-item">
                                                <a class="page-link" href="?page=<?= $page+1 ?>&status=<?= $status_filter ?>&search=<?= urlencode($search) ?>">
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
