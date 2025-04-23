<?php
// Fungsi untuk sanitasi input
function sanitize($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Fungsi untuk validasi NIK
function validateNIK($nik) {
    return (strlen($nik) == 16 && is_numeric($nik));
}

// Fungsi untuk validasi upload file
function validateFile($file, $maxSize = 2097152) { // 2MB
    $allowedTypes = ['image/jpeg', 'image/png', 'application/pdf'];
    
    if ($file['size'] > $maxSize) {
        return "File terlalu besar (maksimal 2MB)";
    }
    
    if (!in_array($file['type'], $allowedTypes)) {
        return "Tipe file tidak diizinkan (hanya JPG, PNG, PDF)";
    }
    
    return true;
}

// Fungsi untuk generate nama file unik
function generateUniqueFilename($file) {
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    return uniqid() . '_' . time() . '.' . $extension;
}

// Fungsi untuk mendapatkan nama jenis surat
function getJenisSurat($jenis) {
    $jenisSurat = [
        'domisili' => 'Surat Keterangan Domisili',
        'sktm' => 'Surat Keterangan Tidak Mampu',
        'usaha' => 'Surat Keterangan Usaha',
        'nikah' => 'Surat Keterangan Nikah',
        'ahli_waris' => 'Surat Keterangan Ahli Waris'
    ];
    
    return isset($jenisSurat[$jenis]) ? $jenisSurat[$jenis] : 'Tidak Diketahui';
}

// Fungsi untuk mendapatkan nama status
function getStatusLabel($status) {
    $statusLabels = [
        'pending' => '<span class="badge bg-warning">Menunggu</span>',
        'diproses' => '<span class="badge bg-info">Diproses</span>',
        'selesai' => '<span class="badge bg-success">Selesai</span>',
        'ditolak' => '<span class="badge bg-danger">Ditolak</span>'
    ];
    
    return isset($statusLabels[$status]) ? $statusLabels[$status] : '';
}

// Fungsi untuk format tanggal Indonesia
function formatTanggal($tanggal) {
    if (empty($tanggal)) return '-';
    $date = new DateTime($tanggal);
    $formattedDate = $date->format('d-m-Y H:i');
    return $formattedDate;
}

// Fungsi untuk kirim notifikasi email
function sendEmailNotification($to, $subject, $message) {
    require_once __DIR__ . '/../vendor/autoload.php';
    
    $mail = new PHPMailer\PHPMailer\PHPMailer(true);
    
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'sukaresik.digital@gmail.com'; // Ganti dengan email Anda
        $mail->Password   = 'password_aplikasi'; // Ganti dengan password aplikasi
        $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        
        // Recipients
        $mail->setFrom('sukaresik.digital@gmail.com', 'Sukaresik Digital');
        $mail->addAddress($to);
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $message;
        
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Email tidak dapat dikirim. Error: {$mail->ErrorInfo}");
        return false;
    }
}
?>
