<?php
/**
 * Perpustakaan Desa - Newsletter
 * File: newsletter.php
 * Deskripsi: Menangani subscribtion newsletter dari footer
 */

require_once __DIR__ . '/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        set_flash('error', 'Email tidak valid.');
    } else {
        // Simpan email newsletter ke session (demo)
        // Di implementasi real, simpan ke tabel newsletter
        set_flash('success', 'Terima kasih! Email ' . $email . ' berhasil terdaftar untuk newsletter.');
    }
}

// Redirect ke halaman sebelumnya
$referer = $_SERVER['HTTP_REFERER'] ?? 'index.php';
redirect($referer);
