<?php
/**
 * koneksi.php
 * ----------
 * Koneksi database mysqli untuk Perpustakaan Desa.
 * PHP 8+ compatible, charset utf8mb4, error reporting mysqli penuh.
 * Menggunakan gaya procedural mysqli sesuai project original.
 * Konfigurasi default sesuai XAMPP (root tanpa password).
 */

declare(strict_types=1);

/* ---------- KONFIGURASI ---------- */
$DB_HOST = 'localhost';
$DB_USER = 'root';
$DB_PASS = '';
$DB_NAME = 'perpustakaan_desa';
$DB_PORT = 3306;

/* ---------- KONEKSI ---------- */
$koneksi = mysqli_init();
if ($koneksi === false) {
    die('Gagal menginisialisasi mysqli: ' . mysqli_connect_error());
}

mysqli_options($koneksi, MYSQLI_OPT_INT_AND_FLOAT_NATIVE, true);

if (!@mysqli_real_connect($koneksi, $DB_HOST, $DB_USER, $DB_PASS, $DB_NAME, $DB_PORT)) {
    die(
        '<div style="font-family:Arial;color:#721c24;background:#f8d7da;border:1px solid #f5c6cb;padding:16px;border-radius:6px;max-width:560px;margin:40px auto">'
        . '<h3 style="margin:0 0 8px">Koneksi Database Gagal</h3>'
        . '<p style="margin:0">Pastikan database <b>' . htmlspecialchars($DB_NAME) . '</b> sudah di-import '
        . 'dan MySQL berjalan di server <b>' . htmlspecialchars($DB_HOST) . '</b>.</p>'
        . '<p style="margin:8px 0 0;color:#6c757d;font-size:12px">'
        . htmlspecialchars(mysqli_connect_error()) . '</p></div>'
    );
}

mysqli_set_charset($koneksi, 'utf8mb4');
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);


date_default_timezone_set('Asia/Jakarta');
