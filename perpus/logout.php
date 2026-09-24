<?php
/**
 * Perpustakaan Desa - Logout
 * File: logout.php
 * Deskripsi: Menghapus session dan redirect ke halaman utama
 */

require_once __DIR__ . '/config.php';

// Hapus semua data session
$_SESSION = [];

// Hapus session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destroy session
session_destroy();

// Mulai session baru untuk flash message
session_start();
set_flash('success', 'Anda berhasil keluar. Sampai jumpa!');

// Redirect ke halaman utama
header("Location: index.php");
exit;
