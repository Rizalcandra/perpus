<?php
require_once __DIR__ . '/config.php';
require_login();

$id = (int)($_GET['id'] ?? 0);
// SELECT menggunakan VIEW v_peminjaman_detail (JOIN)
$pm = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT * FROM v_peminjaman_detail WHERE id_peminjaman = $id"));
if (!$pm) redirect('akun.php');

// Validasi: hanya pemilik atau admin/petugas yang bisa lihat
if (!is_admin_or_petugas() && (int)$pm['id_anggota'] !== (int)$_SESSION['id_anggota']) {
    http_response_code(403);
    die('Akses ditolak.');
}

$page_title = 'Struk Peminjaman';
require_once __DIR__ . '/partials/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-7">
            <div class="card border-0 shadow" id="struk-print">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <i class="bi bi-book-half display-4 text-primary"></i>
                        <h3 class="fw-bold"><?= APP_NAME ?></h3>
                        <p class="text-muted">Struk Peminjaman Buku</p>
                    </div>
                    <hr>
                    <table class="table table-borderless">
                        <tr><td class="text-muted" width="40%">Kode Peminjaman</td><td class="fw-bold"><?= e($pm['kode_peminjaman']) ?></td></tr>
                        <tr><td class="text-muted">Nama Peminjam</td><td><?= e($pm['nama_peminjam']) ?></td></tr>
                        <tr><td class="text-muted">Email</td><td><?= e($pm['email_peminjam'] ?? '-') ?></td></tr>
                        <tr><td class="text-muted">Judul Buku</td><td class="fw-bold"><?= e($pm['judul_buku']) ?></td></tr>
                        <tr><td class="text-muted">ISBN</td><td><?= e($pm['isbn']) ?></td></tr>
                        <tr><td class="text-muted">Kategori</td><td><?= e($pm['nama_kategori'] ?? '-') ?></td></tr>
                        <tr><td class="text-muted">Tanggal Pinjam</td><td><?= format_tanggal($pm['tanggal_pinjam']) ?></td></tr>
                        <tr><td class="text-muted">Tenggat Kembali</td><td><?= format_tanggal($pm['tanggal_kembali']) ?></td></tr>
                        <?php if ($pm['tanggal_pengembalian']): ?>
                        <tr><td class="text-muted">Tanggal Dikembalikan</td><td><?= format_tanggal($pm['tanggal_pengembalian']) ?></td></tr>
                        <?php endif; ?>
                        <tr><td class="text-muted">Denda</td><td class="fw-bold text-danger"><?= $pm['denda'] > 0 ? format_rupiah((int)$pm['denda']) : 'Rp 0' ?></td></tr>
                        <tr><td class="text-muted">Status</td><td><span class="badge bg-primary"><?= e($pm['status']) ?></span></td></tr>
                        <?php if ($pm['catatan']): ?>
                        <tr><td class="text-muted">Catatan</td><td><?= e($pm['catatan']) ?></td></tr>
                        <?php endif; ?>
                    </table>
                    <hr>
                    <div class="text-center small text-muted">
                        <p>Dicetak: <?= date('d M Y H:i:s') ?></p>
                        <p>Simpan struk ini sebagai bukti peminjaman.</p>
                    </div>
                </div>
            </div>
            <div class="d-flex gap-2 mt-3 no-print">
                <button onclick="window.print()" class="btn btn-primary"><i class="bi bi-printer me-1"></i>Cetak Struk (Print)</button>
                <a href="akun.php" class="btn btn-outline-secondary">Kembali</a>
            </div>
        </div>
    </div>
</div>

<style>@media print{.no-print{display:none!important}.navbar,.footer{display:none!important}}</style>

<?php require_once __DIR__ . '/partials/footer.php'; ?>
