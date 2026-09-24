<?php
/**
 * pinjam.php - Form Pinjam Buku
 * Validasi: cek login, stok, limit pinjam (menggunakan Stored Procedure)
 */
require_once __DIR__ . '/config.php';
require_login();

$id_buku = (int)($_GET['id'] ?? 0);
if ($id_buku === 0) redirect('katalog_buku.php');

// SELECT dengan JOIN
$buku = mysqli_fetch_assoc(mysqli_query($koneksi,
    "SELECT b.*, k.nama_kategori, fn_buku_tersedia(b.id_buku) AS tersedia
     FROM buku b LEFT JOIN kategori_buku k ON b.id_kategori = k.id_kategori
     WHERE b.id_buku = $id_buku"
));
if (!$buku) redirect('katalog_buku.php');

// Validasi: stok (menggunakan FUNCTION fn_buku_tersedia)
if (!$buku['tersedia']) {
    set_flash('error', 'Stok buku habis.');
    redirect('detail_buku.php?id=' . $id_buku);
}

// Validasi: limit pinjam (menggunakan STORED PROCEDURE sp_hitung_dipinjam)
$dipinjam = hitung_buku_dipinjam($koneksi, (int)$_SESSION['id_anggota']);
$max_pinjam = get_max_pinjam($koneksi);
if ($dipinjam >= $max_pinjam) {
    set_flash('error', "Maksimal meminjam $max_pinjam buku. Anda punya $dipinjam buku aktif.");
    redirect('detail_buku.php?id=' . $id_buku);
}

// Validasi: cek duplikat (SELECT + percabangan)
$dup = mysqli_fetch_assoc(mysqli_query($koneksi,
    "SELECT id_peminjaman FROM peminjaman WHERE id_anggota = " . (int)$_SESSION['id_anggota'] . " AND id_buku = $id_buku AND status IN ('dipinjam','menunggu_konfirmasi')"
));
if ($dup) {
    set_flash('error', 'Buku ini sudah Anda pinjam.');
    redirect('detail_buku.php?id=' . $id_buku);
}

$lama_pinjam = get_lama_pinjam($koneksi);
$tgl_kembali = date('Y-m-d', strtotime("+$lama_pinjam days"));
$page_title = 'Pinjam Buku';

require_once __DIR__ . '/partials/header.php';
?>

<div class="container py-5">
    <h2 class="fw-bold mb-4"><i class="bi bi-book text-primary me-2"></i>Pinjam Buku</h2>
    <div class="row g-4">
        <div class="col-md-4">
            <img src="<?= e($buku['gambar']) ?>" class="img-fluid rounded shadow" onerror="this.src='https://via.placeholder.com/300x400?text=No+Image'">
        </div>
        <div class="col-md-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <h4 class="fw-bold"><?= e($buku['judul_buku']) ?></h4>
                    <p class="text-muted"><?= e($buku['nama_kategori'] ?? '') ?> | <?= e($buku['nama_penerbit'] ?? '') ?></p>
                    <div class="mb-3">
                        <span class="badge bg-success">Stok: <?= (int)$buku['stok'] ?></span>
                        <span class="badge bg-info">Dipinjam: <?= $dipinjam ?>/<?= $max_pinjam ?></span>
                    </div>
                    <form method="POST" action="proses_pinjam.php">
                        <?= csrf_field() ?>
                        <input type="hidden" name="id_buku" value="<?= $buku['id_buku'] ?>">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Tanggal Pinjam</label>
                            <input type="text" class="form-control" value="<?= format_tanggal(date('Y-m-d')) ?>" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Tenggat Pengembalian</label>
                            <input type="text" class="form-control" value="<?= format_tanggal($tgl_kembali) ?> (<?= $lama_pinjam ?> hari)" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Catatan (opsional)</label>
                            <textarea class="form-control" name="catatan" rows="2" placeholder="Catatan tambahan..."></textarea>
                        </div>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary" onclick="return confirm('Konfirmasi pinjam buku ini?')">
                                <i class="bi bi-check-circle me-1"></i>Konfirmasi Pinjam
                            </button>
                            <a href="detail_buku.php?id=<?= $buku['id_buku'] ?>" class="btn btn-outline-secondary">Batal</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/partials/footer.php'; ?>
