<?php
/**
 * Perpustakaan Desa - Proses Tambah Buku
 * File: proses_tambah.php
 * Deskripsi: Memproses form penambahan buku baru
 */

require_once __DIR__ . '/config.php';

// Cek login dan role admin
if (!is_logged_in() || !is_admin()) {
    set_flash('error', 'Hanya admin yang dapat menambahkan buku.');
    redirect('login.php');
}

// Cek method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('tambah.php');
}

// Verifikasi CSRF
if (!csrf_verify()) {
    set_flash('error', 'Token keamanan tidak valid.');
    redirect('tambah.php');
}

// Ambil data dari form
$judul_buku = trim($_POST['judul_buku'] ?? '');
$isbn = trim($_POST['isbn'] ?? '');
$bahasa = trim($_POST['bahasa'] ?? 'Indonesia');
$tahun_terbit = !empty($_POST['tahun_terbit']) ? (int)$_POST['tahun_terbit'] : null;
$jumlah_halaman = !empty($_POST['jumlah_halaman']) ? (int)$_POST['jumlah_halaman'] : null;
$kondisi = $_POST['kondisi'] ?? 'Baik';
$id_kategori = (int)($_POST['id_kategori'] ?? 0);
$id_penerbit = (int)($_POST['id_penerbit'] ?? 0);
$stok = (int)($_POST['stok'] ?? 0);
$populer = isset($_POST['populer']) ? '1' : '0';
$baru = isset($_POST['baru']) ? '1' : '0';
$sinopsis = trim($_POST['sinopsis'] ?? '');

// Validasi
$errors = [];
if (empty($judul_buku)) {
    $errors[] = 'Judul buku harus diisi.';
}
if ($id_kategori <= 0) {
    $errors[] = 'Kategori harus dipilih.';
}
if ($id_penerbit <= 0) {
    $errors[] = 'Penerbit harus dipilih.';
}
if ($stok < 0) {
    $errors[] = 'Stok tidak boleh negatif.';
}
if (!in_array($kondisi, ['Baru', 'Baik', 'Cukup', 'Rusak'])) {
    $errors[] = 'Kondisi tidak valid.';
}

if (!empty($errors)) {
    set_flash('error', implode('<br>', $errors));
    redirect('tambah.php');
}

// Proses upload gambar
$gambar_name = 'default.jpg';
if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
    try {
        $gambar_name = upload_gambar($_FILES['gambar'], 'buku');
        // upload_gambar returns relative path like 'uploads/buku-xxx.jpg'
        // We need just the filename for database
        $gambar_name = basename($gambar_name);
    } catch (RuntimeException $e) {
        set_flash('error', $e->getMessage());
        redirect('tambah.php');
    }
}

// Insert data buku
$stmt = mysqli_prepare($koneksi, "INSERT INTO buku (judul_buku, isbn, bahasa, tahun_terbit, jumlah_halaman, kondisi, id_kategori, id_penerbit, stok, gambar, populer, baru, sinopsis) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
mysqli_stmt_bind_param($stmt, 'sssiisiiissss', $judul_buku, $isbn, $bahasa, $tahun_terbit, $jumlah_halaman, $kondisi, $id_kategori, $id_penerbit, $stok, $gambar_name, $populer, $baru, $sinopsis);

if (mysqli_stmt_execute($stmt)) {
    set_flash('success', 'Buku "' . $judul_buku . '" berhasil ditambahkan!');
    redirect('katalog_buku.php');
} else {
    set_flash('error', 'Gagal menambahkan buku.');
    redirect('tambah.php');
}
mysqli_stmt_close($stmt);
