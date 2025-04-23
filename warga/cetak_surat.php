<?php
require_once '../includes/auth.php';
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Pastikan user sudah login dan role-nya admin
requireAdmin();

// Ambil ID surat dari parameter URL
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    // Redirect jika ID tidak valid
    header('Location: pengajuan.php');
    exit;
}

// Ambil data surat
$stmt = $pdo->prepare("SELECT s.*, u.nama, u.nik, u.no_kk, u.tempat_lahir, u.tanggal_lahir, u.jenis_kelamin, u.alamat, u.agama, u.status_perkawinan, u.pekerjaan, u.kewarganegaraan
                      FROM surat s 
                      JOIN users u ON s.user_id = u.id 
                      WHERE s.id = ?");
$stmt->execute([$id]);
$surat = $stmt->fetch();

if (!$surat) {
    // Redirect jika surat tidak ditemukan
    header('Location: pengajuan.php');
    exit;
}

// Ambil data kepala desa
$stmt = $pdo->prepare("SELECT * FROM users WHERE role = 'admin' LIMIT 1");
$stmt->execute();
$kepala_desa = $stmt->fetch();

// Decode data surat (JSON)
$data_surat = json_decode($surat['data'], true);

// Format tanggal Indonesia
function tanggalIndonesia($tanggal) {
    $bulan = array(
        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    );
    
    $tanggal_array = explode('-', $tanggal);
    $tahun = $tanggal_array[0];
    $bulan_angka = intval($tanggal_array[1]);
    $tanggal_angka = intval($tanggal_array[2]);
    
    return $tanggal_angka . ' ' . $bulan[$bulan_angka] . ' ' . $tahun;
}

// Hitung umur berdasarkan tanggal lahir
function hitungUmur($tanggal_lahir) {
    $birthDate = new DateTime($tanggal_lahir);
    $today = new DateTime('today');
    $age = $birthDate->diff($today)->y;
    return $age;
}

// Nomor surat
$nomor_surat = sprintf("%03d", $surat['id']) . "/DS-SKR/" . date('m') . "/" . date('Y');

// Tanggal surat
$tanggal_surat = date('Y-m-d');

// Tanggal lahir format Indonesia
$tanggal_lahir_indo = tanggalIndonesia($surat['tanggal_lahir']);

// Umur pemohon
$umur = hitungUmur($surat['tanggal_lahir']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Surat - <?= getJenisSurat($surat['jenis']) ?></title>
    <style>
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 12pt;
            line-height: 1.5;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 21cm;
            margin: 0 auto;
            padding: 2cm;
            border: 1px solid #ddd;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            border-bottom: 3px solid #000;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .logo {
            float: left;
            width: 80px;
            height: auto;
        }
        .header-text {
            margin-left: 100px;
        }
        .header h2, .header h3, .header h4 {
            margin: 0;
        }
        .header h2 {
            font-size: 16pt;
            text-transform: uppercase;
        }
        .header h3 {
            font-size: 14pt;
        }
        .header h4 {
            font-size: 12pt;
            font-weight: normal;
        }
        .title {
            text-align: center;
            margin: 30px 0;
        }
        .title h1 {
            font-size: 14pt;
            text-transform: uppercase;
            margin: 0;
            text-decoration: underline;
        }
        .title p {
            margin: 5px 0;
            font-size: 12pt;
        }
        .content {
            text-align: justify;
            margin-bottom: 30px;
        }
        .content p {
            text-indent: 40px;
            margin: 10px 0;
        }
        .biodata {
            margin: 20px 0;
            padding-left: 40px;
        }
        .biodata table {
            width: 100%;
        }
        .biodata td {
            padding: 3px 0;
            vertical-align: top;
        }
        .biodata td:first-child {
            width: 150px;
        }
        .signature {
            margin-top: 50px;
            text-align: right;
        }
        .signature-content {
            display: inline-block;
            text-align: center;
            width: 200px;
        }
        .signature-line {
            margin-top: 80px;
            border-bottom: 1px solid #000;
            margin-bottom: 5px;
        }
        @media print {
            body {
                padding: 0;
            }
            .container {
                border: none;
                box-shadow: none;
                padding: 0;
            }
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="no-print" style="text-align: center; margin-bottom: 20px;">
        <button onclick="window.print()">Cetak Surat</button>
        <button onclick="window.history.back()">Kembali</button>
    </div>
    
    <div class="container">
        <div class="header">
            <img src="../assets/img/logo.png" alt="Logo Desa" class="logo">
            <div class="header-text">
                <h2>Pemerintah Kabupaten Garut</h2>
                <h2>Kecamatan Sukaresik</h2>
                <h3>Desa Sukaresik</h3>
                <h4>Jl. Raya Sukaresik No. 123, Kode Pos 44171</h4>
            </div>
        </div>
        
        <div class="title">
            <h1><?= getJenisSurat($surat['jenis']) ?></h1>
            <p>Nomor: <?= $nomor_surat ?></p>
        </div>
        
        <div class="content">
            <p>Yang bertanda tangan di bawah ini, Kepala Desa Sukaresik, Kecamatan Sukaresik, Kabupaten Garut, dengan ini menerangkan bahwa:</p>
            
            <div class="biodata">
                <table>
                    <tr>
                        <td>Nama</td>
                        <td>: <?= htmlspecialchars($surat['nama']) ?></td>
                    </tr>
                    <tr>
                        <td>NIK</td>
                        <td>: <?= htmlspecialchars($surat['nik']) ?></td>
                    </tr>
                    <tr>
                        <td>Tempat, Tgl Lahir</td>
                        <td>: <?= htmlspecialchars($surat['tempat_lahir']) ?>, <?= $tanggal_lahir_indo ?></td>
                    </tr>
                    <tr>
                        <td>Jenis Kelamin</td>
                        <td>: <?= htmlspecialchars($surat['jenis_kelamin']) ?></td>
                    </tr>
                    <tr>
                        <td>Agama</td>
                        <td>: <?= htmlspecialchars($surat['agama']) ?></td>
                    </tr>
                    <tr>
                        <td>Status Perkawinan</td>
                        <td>: <?= htmlspecialchars($surat['status_perkawinan']) ?></td>
                    </tr>
                    <tr>
                        <td>Pekerjaan</td>
                        <td>: <?= htmlspecialchars($surat['pekerjaan']) ?></td>
                    </tr>
                    <tr>
                        <td>Alamat</td>
                        <td>: <?= htmlspecialchars($surat['alamat']) ?></td>
                    </tr>
                </table>
            </div>
            
            <?php if ($surat['jenis'] == 'domisili'): ?>
                <p>Adalah benar-benar penduduk yang berdomisili di Desa Sukaresik, Kecamatan Sukaresik, Kabupaten Garut sejak <?= isset($data_surat['sejak_tanggal']) ? tanggalIndonesia($data_surat['sejak_tanggal']) : '[Tanggal]' ?> sampai dengan sekarang.</p>
                <p>Surat Keterangan ini dibuat untuk keperluan: <?= isset($data_surat['keperluan']) ? htmlspecialchars($data_surat['keperluan']) : '[Keperluan]' ?>.</p>
            
            <?php elseif ($surat['jenis'] == 'pengantar_ktp'): ?>
                <p>Adalah benar-benar penduduk Desa Sukaresik, Kecamatan Sukaresik, Kabupaten Garut. Surat pengantar ini diberikan kepada yang bersangkutan untuk keperluan pengurusan <?= isset($data_surat['jenis_permohonan']) ? htmlspecialchars($data_surat['jenis_permohonan']) : 'KTP' ?>.</p>
                <p>Alasan pengurusan: <?= isset($data_surat['alasan']) ? htmlspecialchars($data_surat['alasan']) : '[Alasan]' ?>.</p>
            
            <?php elseif ($surat['jenis'] == 'sktm'): ?>
                <p>Adalah benar-benar penduduk Desa Sukaresik, Kecamatan Sukaresik, Kabupaten Garut dan berdasarkan pengamatan kami, yang bersangkutan tergolong keluarga tidak mampu dengan pekerjaan sebagai <?= isset($data_surat['pekerjaan']) ? htmlspecialchars($data_surat['pekerjaan']) : '[Pekerjaan]' ?> dan penghasilan sekitar Rp <?= isset($data_surat['penghasilan']) ? number_format($data_surat['penghasilan'], 0, ',', '.') : '[Penghasilan]' ?> per bulan.</p>
                <p>Surat Keterangan ini dibuat untuk keperluan: <?= isset($data_surat['keperluan']) ? htmlspecialchars($data_surat['keperluan']) : '[Keperluan]' ?>.</p>
            
            <?php elseif ($surat['jenis'] == 'usaha'): ?>
                <p>Adalah benar-benar penduduk Desa Sukaresik, Kecamatan Sukaresik, Kabupaten Garut yang memiliki usaha dengan keterangan sebagai berikut:</p>
                <div class="biodata">
                    <table>
                        <tr>
                            <td>Nama Usaha</td>
                            <td>: <?= isset($data_surat['nama_usaha']) ? htmlspecialchars($data_surat['nama_usaha']) : '[Nama Usaha]' ?></td>
                        </tr>
                        <tr>
                            <td>Jenis Usaha</td>
                            <td>: <?= isset($data_surat['jenis_usaha']) ? htmlspecialchars($data_surat['jenis_usaha']) : '[Jenis Usaha]' ?></td>
                        </tr>
                        <tr>
                            <td>Alamat Usaha</td>
                            <td>: <?= isset($data_surat['alamat_usaha']) ? htmlspecialchars($data_surat['alamat_usaha']) : '[Alamat Usaha]' ?></td>
                        </tr>
                        <tr>
                            <td>Tahun Berdiri</td>
                            <td>: <?= isset($data_surat['tahun_berdiri']) ? htmlspecialchars($data_surat['tahun_berdiri']) : '[Tahun Berdiri]' ?></td>
                        </tr>
                    </table>
                </div>
                <p>Surat Keterangan ini dibuat untuk keperluan: <?= isset($data_surat['keperluan']) ? htmlspecialchars($data_surat['keperluan']) : '[Keperluan]' ?>.</p>
            
            <?php elseif ($surat['jenis'] == 'kelahiran'): ?>
                <p>Dengan ini menerangkan bahwa pada:</p>
                <div class="biodata">
                    <table>
                        <tr>
                            <td>Hari/Tanggal</td>
                            <td>: <?= isset($data_surat['tanggal_lahir']) ? tanggalIndonesia($data_surat['tanggal_lahir']) : '[Tanggal Lahir]' ?></td>
                        </tr>
                        <tr>
                            <td>Tempat</td>
                            <td>: <?= isset($data_surat['tempat_lahir']) ? htmlspecialchars($data_surat['tempat_lahir']) : '[Tempat Lahir]' ?></td>
                        </tr>
                    </table>
                </div>
                <p>Telah lahir seorang anak <?= isset($data_surat['jenis_kelamin']) ? strtolower(htmlspecialchars($data_surat['jenis_kelamin'])) : '[Jenis Kelamin]' ?> yang diberi nama:</p>
                <p style="text-align: center; font-weight: bold; font-size: 14pt; margin: 20px 0;"><?= isset($data_surat['nama_bayi']) ? htmlspecialchars($data_surat['nama_bayi']) : '[Nama Bayi]' ?></p>
                <p>Dari pasangan suami istri:</p>
                <div class="biodata">
                    <table>
                        <tr>
                            <td>Nama Ayah</td>
                            <td>: <?= isset($data_surat['nama_ayah']) ? htmlspecialchars($data_surat['nama_ayah']) : '[Nama Ayah]' ?></td>
                        </tr>
                        <tr>
                            <td>Nama Ibu</td>
                            <td>: <?= isset($data_surat['nama_ibu']) ? htmlspecialchars($data_surat['nama_ibu']) : '[Nama Ibu]' ?></td>
                        </tr>
                    </table>
                </div>
            
            <?php elseif ($surat['jenis'] == 'kematian'): ?>
                <p>Dengan ini menerangkan dengan sebenarnya bahwa:</p>
                <div class="biodata">
                    <table>
                        <tr>
                            <td>Nama</td>
                            <td>: <?= isset($data_surat['nama_almarhum']) ? htmlspecialchars($data_surat['nama_almarhum']) : '[Nama Almarhum]' ?></td>
                        </tr>
                        <tr>
                            <td>NIK</td>
                            <td>: <?= isset($data_surat['nik_almarhum']) ? htmlspecialchars($data_surat['nik_almarhum']) : '[NIK Almarhum]' ?></td>
                        </tr>
                    </table>
                </div>
                <p>Telah meninggal dunia pada:</p>
                <div class="biodata">
                    <table>
                        <tr>
                            <td>Tanggal</td>
                            <td>: <?= isset($data_surat['tanggal_kematian']) ? tanggalIndonesia($data_surat['tanggal_kematian']) : '[Tanggal Kematian]' ?></td>
                        </tr>
                        <tr>
                            <td>Tempat</td>
                            <td>: <?= isset($data_surat['tempat_kematian']) ? htmlspecialchars($data_surat['tempat_kematian']) : '[Tempat Kematian]' ?></td>
                        </tr>
                        <tr>
                            <td>Penyebab</td>
                            <td>: <?= isset($data_surat['penyebab_kematian']) ? htmlspecialchars($data_surat['penyebab_kematian']) : '[Penyebab Kematian]' ?></td>
                        </tr>
                    </table>
                </div>
                <p>Surat keterangan ini dibuat berdasarkan laporan dari:</p>
                <div class="biodata">
                    <table>
                        <tr>
                            <td>Nama</td>
                            <td>: <?= htmlspecialchars($surat['nama']) ?></td>
                        </tr>
                        <tr>
                            <td>Hubungan</td>
                            <td>: <?= isset($data_surat['hubungan_pelapor']) ? htmlspecialchars($data_surat['hubungan_pelapor']) : '[Hubungan Pelapor]' ?></td>
                        </tr>
                    </table>
                </div>
            
            <?php else: ?>
                <p>Adalah benar-benar penduduk Desa Sukaresik, Kecamatan Sukaresik, Kabupaten Garut.</p>
                <p>Surat Keterangan ini dibuat untuk keperluan administrasi sesuai dengan permohonan yang bersangkutan.</p>
            <?php endif; ?>
            
            <p>Demikian Surat Keterangan ini dibuat dengan sebenarnya untuk dipergunakan sebagaimana mestinya.</p>
        </div>
        
        <div class="signature">
            <div class="signature-content">
                <p>Sukaresik, <?= tanggalIndonesia($tanggal_surat) ?></p>
                <p>Kepala Desa Sukaresik</p>
                <div class="signature-line"></div>
                <p><strong><?= isset($kepala_desa['nama']) ? htmlspecialchars($kepala_desa['nama']) : 'H. AHMAD SOBARI' ?></strong></p>
            </div>
        </div>
    </div>
</body>
</html>