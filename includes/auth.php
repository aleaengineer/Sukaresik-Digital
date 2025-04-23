<?php
session_start();

// Cek apakah user sudah login
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Cek apakah user adalah admin
function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] == 'admin';
}

// Redirect jika tidak login
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: http://localhost:8000/login.php');
        exit;
    }
}

// Redirect jika bukan admin
function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        header('Location: http://localhost:8000/warga/pengajuan.php');
        exit;
    }
}

// Redirect jika bukan warga
function requireWarga() {
    requireLogin();
    if (isAdmin()) {
        header('Location: http://localhost:8000/admin/dashboard.php');
        exit;
    }
}

// Mendapatkan data user yang sedang login
function getCurrentUser($pdo) {
    if (!isLoggedIn()) return null;
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch();
}
?>