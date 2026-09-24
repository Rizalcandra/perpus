<?php
require_once __DIR__ . '/config.php';
require_login();

$page_title = 'Pengembalian Buku';

// Proses request kembalikan
if (isset($_POST['request_kembali'])) {
    if (!csrf_verify()) {
        set_flash('error', 'Token tidak valid.');
    } else {
        $id_pm = (int)$_POST['id_peminjaman'];
        try {
            mysqli_autocommit($koneksi, false);
            $stmt = mysqli_prepare($koneksi, "UPDATE peminjaman SET status='menunggu_konfirmasi', tanggal_request_kembali=CURDATE() WHERE id_peminjaman=? AND id_anggota=? AND status='dipinjam'");
            mysqli_stmt_bind_param($stmt, 'ii', $id_pm, (int)$_SESSION['id_anggota']);
            mysqli_stmt_execute($stmt);
            if (mysqli_stmt_affected_rows($stmt) === 0) {
                throw new RuntimeException('Gagal update status.');
            }
            mysqli_commit($koneksi);
            log_aktivitas($koneksi, 'REQUEST_KEMBALI', "Request kembali peminjaman ID=$id_pm");
            set_flash('success', 'Permintaan pengembalian berhasil. Menunggu konfirmasi admin/petugas.');
        } catch (Throwable $e) {
            mysqli_rollback($koneksi);
            log_error('Request kembali error: ' . $e->getMessage(), 'ERROR', __FILE__, __LINE__);
            set_flash('error', 'Gagal memproses permintaan.');
        } finally {
            mysqli_autocommit($koneksi, true);
            mysqli_stmt_close($stmt);
        }
    }
}

// Ambil data peminjaman aktif user
$dipinjam_result = mysqli_query($koneksi, 
    "SELECT p.*, b.judul_buku, b.gambar, b.isbn, k.nama_kategori, 
            fn_hitung_denda(p.tanggal_kembali) AS denda_terhitung
     FROM peminjaman p
     LEFT JOIN buku b ON p.id_buku = b.id_buku
     LEFT JOIN kategori_buku k ON b.id_kategori = k.id_kategori
     WHERE p.id_anggota = " . (int)$_SESSION['id_anggota'] . " 
       AND p.status IN ('dipinjam','menunggu_konfirmasi','terlambat')
     ORDER BY p.tanggal_pinjam DESC"
);

// Riwayat
$riwayat_result = mysqli_query($koneksi,
    "SELECT p.*, b.judul_buku, b.gambar, k.nama_kategori
     FROM peminjaman p
     LEFT JOIN buku b ON p.id_buku = b.id_buku
     LEFT JOIN kategori_buku k ON b.id_kategori = k.id_kategori
     WHERE p.id_anggota = " . (int)$_SESSION['id_anggota'] . " 
       AND p.status = 'dikembalikan'
     ORDER BY p.tanggal_pengembalian DESC"
);

require_once __DIR__ . '/partials/header.php';
?>

<div class="container py-5">
    <h2 class="fw-bold mb-4"><i class="bi bi-arrow-return-left text-primary me-2"></i>Pengembalian Buku</h2>

    <?php if (mysqli_num_rows($dipinjam_result) > 0): ?>
    <h5 class="mb-3">Buku Sedang Dipinjam</h5>
    <div class="row g-3">
        <?php while ($pm = mysqli_fetch_assoc($dipinjam_result)): 
            $is_late = $pm['tanggal_kembali'] < date('Y-m-d') && $pm['status'] === 'dipinjam';
        ?>
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex gap-3">
                        <img src="<?= e($pm['gambar']) ?>" class="rounded" width="60" height="80" onerror="this.src='https://via.placeholder.com/60x80?text=Buku'">
                        <div class="flex-grow-1">
                            <h6 class="fw-bold mb-1"><?= e($pm['judul_buku']) ?></h6>
                            <small class="text-muted">Tenggat: <?= format_tanggal($pm['tanggal_kembali']) ?></small>
                            <?php if ($pm['status'] === 'menunggu_konfirmasi'): ?>
                                <span class="badge bg-warning text-dark ms-2">Menunggu Konfirmasi</span>
                            <?php elseif ($is_late): ?>
                                <span class="badge bg-danger ms-2">Terlambat - Denda: <?= format_rupiah((int)$pm['denda_terhitung']) ?></span>
                            <?php else: ?>
                                <span class="badge bg-success ms-2">Aktif</span>
                            <?php endif; ?>
                            <div class="mt-2">
                                <?php if ($pm['status'] === 'dipinjam'): ?>
                                <form method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin mengembalikan buku ini?')">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="id_peminjaman" value="<?= $pm['id_peminjaman'] ?>">
                                    <button type="submit" name="request_kembali" class="btn btn-sm btn-primary">
                                        <i class="bi bi-arrow-return-left me-1"></i>Kembalikan
                                    </button>
                                </form>
                                <?php endif; ?>
                                <a href="struk.php?id=<?= $pm['id_peminjaman'] ?>" class="btn btn-sm btn-outline-secondary">
                                    <i class="bi bi-printer me-1"></i>Struk
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endwhile; ?>
    </div>
    <?php else: ?>
    <div class="alert alert-info"><i class="bi bi-info-circle me-2"></i>Anda tidak memiliki buku yang sedang dipinjam.</div>
    <?php endif; ?>

    <?php if (mysqli_num_rows($riwayat_result) > 0): ?>
    <h5 class="mt-5 mb-3">Riwayat Pengembalian</h5>
    <div class="table-responsive">
        <table class="table table-bordered">
            <thead class="table-light">
                <tr><th>Buku</th><th>Pinjam</th><th>Kembali</th><th>Denda</th><th>Status</th></tr>
            </thead>
            <tbody>
                <?php while ($r = mysqli_fetch_assoc($riwayat_result)): ?>
                <tr>
                    <td><?= e($r['judul_buku']) ?></td>
                    <td><?= format_tanggal($r['tanggal_pinjam']) ?></td>
                    <td><?= format_tanggal($r['tanggal_pengembalian']) ?></td>
                    <td><?= $r['denda'] > 0 ? format_rupiah((int)$r['denda']) : '-' ?></td>
                    <td><span class="badge bg-success">Dikembalikan</span></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/partials/footer.php'; ?>
