<?php
require_once __DIR__ . '/config.php';
require_login();
$page_title = 'Profil Saya';

$id_anggota = (int)$_SESSION['id_anggota'];
$user = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT * FROM anggota WHERE id_anggota = $id_anggota"));

// Menggunakan STORED PROCEDURE sp_hitung_dipinjam
$dipinjam = hitung_buku_dipinjam($koneksi, $id_anggota);
$max_pinjam = get_max_pinjam($koneksi);

// Total semua peminjaman (SELECT COUNT)
$total_pm = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM peminjaman WHERE id_anggota = $id_anggota"))['total'];

// Riwayat menggunakan VIEW v_peminjaman_detail
$riwayat = mysqli_query($koneksi, "SELECT * FROM v_peminjaman_detail WHERE id_anggota = $id_anggota ORDER BY tanggal_pinjam DESC");

require_once __DIR__ . '/partials/header.php';
?>

<div class="container py-5">
    <h2 class="fw-bold mb-4"><i class="bi bi-person-circle text-primary me-2"></i>Profil Saya</h2>
    <div class="row g-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm text-center">
                <div class="card-body p-4">
                    <div class="bg-primary bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center" style="width:100px;height:100px">
                        <i class="bi bi-person-fill display-3 text-primary"></i>
                    </div>
                    <h5 class="fw-bold mt-3"><?= e($user['nama_lengkap']) ?></h5>
                    <span class="badge bg-primary"><?= strtoupper($user['role']) ?></span>
                    <div class="text-start mt-3 small">
                        <p class="mb-1"><strong>NIK:</strong> <?= e($user['nik'] ?? '-') ?></p>
                        <p class="mb-1"><strong>Email:</strong> <?= e($user['email']) ?></p>
                        <p class="mb-1"><strong>HP:</strong> <?= e($user['no_hp'] ?? '-') ?></p>
                        <p class="mb-0"><strong>Alamat:</strong> <?= e($user['alamat'] ?? '-') ?></p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-8">
            <!-- Stat Cards - User Dashboard -->
            <div class="row g-3 mb-4">
                <div class="col-4">
                    <div class="card border-0 shadow-sm text-center py-3">
                        <h3 class="fw-bold text-primary mb-0"><?= $dipinjam ?></h3>
                        <small class="text-muted">Dipinjam (max <?= $max_pinjam ?>)</small>
                    </div>
                </div>
                <div class="col-4">
                    <div class="card border-0 shadow-sm text-center py-3">
                        <h3 class="fw-bold text-success mb-0"><?= $total_pm ?></h3>
                        <small class="text-muted">Total Peminjaman</small>
                    </div>
                </div>
                <div class="col-4">
                    <div class="card border-0 shadow-sm text-center py-3">
                        <h3 class="fw-bold text-warning mb-0"><?= $dipinjam ?>/<?= $max_pinjam ?></h3>
                        <small class="text-muted">Kuota Pinjaman</small>
                    </div>
                </div>
            </div>
            <div class="d-flex gap-2 mb-3">
                <a href="pengembalian.php" class="btn btn-primary"><i class="bi bi-arrow-return-left me-1"></i>Pengembalian</a>
                <a href="katalog_buku.php" class="btn btn-outline-primary"><i class="bi bi-grid me-1"></i>Katalog Buku</a>
            </div>
            <!-- Riwayat Table -->
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-light"><tr><th>Kode</th><th>Buku</th><th>Pinjam</th><th>Kembali</th><th>Denda</th><th>Status</th><th>Aksi</th></tr></thead>
                    <tbody>
                    <?php while ($r = mysqli_fetch_assoc($riwayat)): 
                        $status_class = match($r['status']) {
                            'dikembalikan' => 'bg-success',
                            'dipinjam' => 'bg-primary',
                            'menunggu_konfirmasi' => 'bg-warning text-dark',
                            default => 'bg-secondary'
                        };
                    ?>
                    <tr>
                        <td><small><?= e($r['kode_peminjaman']) ?></small></td>
                        <td><?= e($r['judul_buku']) ?></td>
                        <td><?= format_tanggal($r['tanggal_pinjam']) ?></td>
                        <td><?= format_tanggal($r['tanggal_pengembalian']) ?></td>
                        <td><?= (int)$r['denda'] > 0 ? format_rupiah((int)$r['denda']) : '-' ?></td>
                        <td><span class="badge <?= $status_class ?>"><?= e($r['status']) ?></span></td>
                        <td><a href="struk.php?id=<?= $r['id_peminjaman'] ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-printer"></i></a></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/partials/footer.php'; ?>
