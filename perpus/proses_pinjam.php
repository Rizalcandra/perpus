<?php
/**
 * proses_pinjam.php - Proses peminjaman menggunakan Stored Procedure
 * Menggunakan: sp_pinjam_buku, mysqli_commit, mysqli_rollback
 */
require_once __DIR__ . '/config.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_verify()) {
    redirect('katalog_buku.php');
}

// Penentuan tipe data: int
$id_buku = (int)($_POST['id_buku'] ?? 0);
$catatan = trim($_POST['catatan'] ?? '');

// Validasi: cek stok (FUNCTION fn_buku_tersedia)
$tersedia = buku_tersedia($koneksi, $id_buku);
if (!$tersedia) {
    set_flash('error', 'Stok buku habis.');
    redirect('katalog_buku.php');
}

// Validasi: cek limit pinjam (STORED PROCEDURE sp_hitung_dipinjam)
$dipinjam = hitung_buku_dipinjam($koneksi, (int)$_SESSION['id_anggota']);
$max_pinjam = get_max_pinjam($koneksi);
if ($dipinjam >= $max_pinjam) {
    set_flash('error', "Maksimal meminjam $max_pinjam buku. Anda punya $dipinjam buku aktif.");
    redirect('katalog_buku.php');
}

try {
    mysqli_autocommit($koneksi, false);

    $lama_pinjam = get_lama_pinjam($koneksi);
    $kode = generate_kode_peminjaman($koneksi);
    $tgl_pinjam = date('Y-m-d');
    $tgl_kembali = date('Y-m-d', strtotime("+$lama_pinjam days"));
    $id_anggota = (int)$_SESSION['id_anggota'];
    $nama = $_SESSION['nama_lengkap'];

    // Percabangan: cek duplikat pinjam (SELECT + percabangan)
    $dup = mysqli_fetch_assoc(mysqli_query($koneksi,
        "SELECT id_peminjaman FROM peminjaman WHERE id_anggota=$id_anggota AND id_buku=$id_buku AND status IN ('dipinjam','menunggu_konfirmasi')"
    ));
    if ($dup) {
        set_flash('error', 'Buku ini sudah Anda pinjam.');
        mysqli_rollback($koneksi);
        redirect('katalog_buku.php');
    }

    // Menggunakan STORED PROCEDURE sp_pinjam_buku
    // Penentuan tipe data: string, string, string, int, string, int, string
    $stmt = mysqli_prepare($koneksi, "CALL sp_pinjam_buku(?,?,?,?,?,?,?,@sukses,@pesan)");
    mysqli_stmt_bind_param($stmt, 'sssisis', $kode, $tgl_pinjam, $tgl_kembali, $id_anggota, $nama, $id_buku, $catatan);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    // Ambil output parameter (SELECT)
    $res = mysqli_query($koneksi, "SELECT @sukses AS sukses, @pesan AS pesan");
    $out = mysqli_fetch_assoc($res);

    //bellow is conection to database

    if ($out && $out['sukses']) {
        // COMMIT
        mysqli_commit($koneksi);
        log_aktivitas($koneksi, 'PINJAM', "Pinjam buku ID=$id_buku kode=$kode");
        set_flash('success', 'Buku berhasil dipinjam! Kode: ' . $kode);
    } else {
        // ROLLBACK
        mysqli_rollback($koneksi);
        set_flash('error', $out['pesan'] ?? 'Gagal meminjam buku.');
    }
} catch (Throwable $e) {
    // Error handling: ROLLBACK + log
    mysqli_rollback($koneksi);
    log_error('Proses pinjam error: ' . $e->getMessage(), 'ERROR', __FILE__, __LINE__);
    set_flash('error', 'Terjadi kesalahan. Silakan coba lagi.');
} finally {
    mysqli_autocommit($koneksi, true);
}

redirect('struk.php');
