<?php
require_once __DIR__ . '/config.php';

$id = (int)($_GET['id'] ?? 0);
if ($id === 0) redirect('katalog_buku.php');

// SELECT dengan JOIN
$buku_result = mysqli_query($koneksi, 
    "SELECT b.*, k.nama_kategori, p.nama_penerbit,
            fn_buku_tersedia(b.id_buku) AS tersedia,
            (SELECT COUNT(*) FROM komentar_buku WHERE id_buku = b.id_buku) AS jumlah_komentar
     FROM buku b
     LEFT JOIN kategori_buku k ON b.id_kategori = k.id_kategori
     LEFT JOIN penerbit p ON b.id_penerbit = p.id_penerbit
     WHERE b.id_buku = $id"
);
$buku = mysqli_fetch_assoc($buku_result);
if (!$buku) { redirect('proses_pinjam.php'); }

// Buku terkait (perulangan & array)
$terkait_result = mysqli_query($koneksi,
    "SELECT * FROM buku WHERE id_kategori = " . (int)$buku['id_kategori'] . " AND id_buku != $id LIMIT 4"
);

// Komentar
$komentar_result = mysqli_query($koneksi,
    "SELECT k.*, DATE_FORMAT(k.created_at, '%d %M %Y') AS tgl FROM komentar_buku k WHERE k.id_buku = $id ORDER BY k.created_at DESC"
);

// Proses tambah komentar
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_komentar'])) {
    if (!is_logged_in()) {
        set_flash('error', 'Silakan login untuk memberi komentar.');
    } elseif (!csrf_verify()) {
        set_flash('error', 'Token tidak valid.');
    } else {
        $isi = trim($_POST['isi_komentar'] ?? '');
        $rating = (int)($_POST['rating'] ?? 0);
        // Percabangan: validasi
        if (empty($isi)) {
            set_flash('error', 'Komentar tidak boleh kosong.');
        } elseif ($rating < 1 || $rating > 5) {
            set_flash('error', 'Rating harus 1-5 bintang.');
        } else {
            try {
                $stmt = mysqli_prepare($koneksi, "INSERT INTO komentar_buku (id_buku, id_anggota, nama_komentar, isi_komentar, rating) VALUES (?,?,?,?,?)");
                mysqli_stmt_bind_param($stmt, 'iissi', $id, (int)$_SESSION['id_anggota'], $_SESSION['nama_lengkap'], $isi, $rating);
                mysqli_autocommit($koneksi, false);
                mysqli_stmt_execute($stmt);
                mysqli_commit($koneksi);
                set_flash('success', 'Komentar berhasil ditambahkan.');
                log_aktivitas($koneksi, 'KOMENTAR', "Komentar pada buku ID=$id");
            } catch (Throwable $e) {
                mysqli_rollback($koneksi);
                log_error('Gagal tambah komentar: ' . $e->getMessage(), 'ERROR', __FILE__, __LINE__);
            } finally {
                mysqli_autocommit($koneksi, true);
                mysqli_stmt_close($stmt);
                redirect("detail_buku.php?id=$id");
            }
        }
    }
}

// Hitung rata-rata rating
$rating_data = mysqli_fetch_assoc(mysqli_query($koneksi,
    "SELECT AVG(rating) AS rata, COUNT(*) AS total FROM komentar_buku WHERE id_buku = $id AND rating IS NOT NULL"
));
$avg_rating = round((float)($rating_data['rata'] ?? 0), 1);
$total_rating = (int)($rating_data['total'] ?? 0);

$page_title = e($buku['judul_buku']);
require_once __DIR__ . '/partials/header.php';
?>

<div class="container py-5">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb"><li class="breadcrumb-item"><a href="index.php">Beranda</a></li>
        <li class="breadcrumb-item"><a href="katalog_buku.php">Katalog</a></li>
        <li class="breadcrumb-item active"><?= e($buku['judul_buku']) ?></li></ol>
    </nav>

    <div class="row g-5">
        <div class="col-lg-4">
            <img src="<?= e($buku['gambar']) ?>" class="card-img-top rounded shadow" alt="<?= e($buku['judul_buku']) ?>" onerror="this.src='https://via.placeholder.com/300x400?text=No+Image'">
        </div>
        <div class="col-lg-8">
            <div class="d-flex align-items-center gap-2 mb-2">
                <span class="badge bg-primary"><?= e($buku['nama_kategori']) ?></span>
                <span class="badge <?= $buku['kondisi']==='Baru'?'bg-success':($buku['kondisi']==='Baik'?'bg-info':($buku['kondisi']==='Cukup'?'bg-warning':'bg-danger')) ?>"><?= e($buku['kondisi']) ?></span>
                <?php if ($buku['populer']): ?><span class="badge bg-danger"><i class="bi bi-fire"></i> Populer</span><?php endif; ?>
                <?php if ($buku['baru']): ?><span class="badge bg-success"><i class="bi bi-star-fill"></i> Baru</span><?php endif; ?>
                <!-- Badge/Bintang -->
                <?php if ($total_rating > 0): ?>
                <span class="badge bg-warning text-dark">
                    <i class="bi bi-star-fill"></i> <?= $avg_rating ?>/5 (<?= $total_rating ?>)
                </span>
                <?php endif; ?>
            </div>
            <h2 class="fw-bold mb-2"><?= e($buku['judul_buku']) ?></h2>
            <p class="text-muted mb-3">ISBN: <?= e($buku['isbn']) ?> | Penerbit: <?= e($buku['nama_penerbit'] ?? '-') ?> | <?= e($buku['tahun_terbit'] ?? '-') ?></p>

            <!-- Informasi: Availability, Kualitas Barang, Sold/total_dipinjam -->
            <div class="row g-2 mb-3">
                <div class="col-auto"><span class="badge bg-<?= $buku['tersedia']?'success':'danger' ?> fs-6 px-3 py-2"><i class="bi bi-<?= $buku['tersedia']?'check-circle':'x-circle' ?> me-1"></i><?= $buku['tersedia']?'Tersedia':'Habis' ?></span></div>
                <div class="col-auto"><span class="badge bg-secondary fs-6 px-3 py-2"><i class="bi bi-box-seam me-1"></i>Stok: <?= $buku['stok'] ?></span></div>
                <div class="col-auto"><span class="badge bg-info fs-6 px-3 py-2"><i class="bi bi-bar-chart me-1"></i>Dipinjam: <?= $buku['total_dipinjam'] ?>x</span></div>
            </div>

            <p class="mb-3"><?= nl2br(e($buku['sinopsis'] ?? 'Belum ada sinopsis.')) ?></p>
            <p class="text-muted small">Bahasa: <?= e($buku['bahasa']) ?> | <?= $buku['jumlah_halaman'] ?> halaman</p>

            <?php if (is_logged_in() && is_anggota() && $buku['tersedia']): ?>
            <form method="POST" action="proses_pinjam.php" class="d-inline">
                <?= csrf_field() ?>
                <input type="hidden" name="id_buku" value="<?= $buku['id_buku'] ?>">
                <button type="submit" class="btn btn-primary btn-lg" onclick="return confirm('Pinjam buku ini?')">
                    <i class="bi bi-book me-2"></i>Pinjam Buku
                </button>
            </form>
            <?php endif; ?>
        </div>
    </div>

    <!-- comment Section -->
    <div class="mt-5">
        <h4 class="fw-bold mb-3"><i class="bi bi-chat-dots text-primary me-2"></i>Komentar (<?= $total_rating ?>)</h4>

        <?php if (is_logged_in()): ?>
        <form method="POST" class="card border-0 shadow-sm p-4 mb-4">
            <?= csrf_field() ?>
            <input type="hidden" name="id_buku" value="<?= $id ?>">
            <div class="mb-3">
                <label class="form-label fw-semibold">Rating (Bintang)</label>
                <div class="d-flex gap-1" id="star-rating">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                    <span class="star-btn fs-3 text-muted" data-val="<?= $i ?>" style="cursor:pointer" onclick="setRating(<?= $i ?>)"><i class="bi bi-star-fill"></i></span>
                    <?php endfor; ?>
                    <input type="hidden" name="rating" id="rating-input" value="0">
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold">Komentar</label>
                <textarea class="form-control" name="isi_komentar" rows="3" placeholder="Tulis komentar Anda..." required></textarea>
            </div>
            <button type="submit" name="submit_komentar" class="btn btn-primary"><i class="bi bi-send me-1"></i>Kirim</button>
        </form>
        <?php else: ?>
        <div class="alert alert-info"><i class="bi bi-info-circle me-2"></i><a href="login.php">Login</a> untuk memberi komentar.</div>
        <?php endif; ?>

        <?php if (mysqli_num_rows($komentar_result) > 0): ?>
        <?php while ($k = mysqli_fetch_assoc($komentar_result)): ?>
        <div class="card border-0 shadow-sm mb-2">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <strong><?= e($k['nama_komentar']) ?></strong>
                    <small class="text-muted"><?= $k['tgl'] ?></small>
                </div>
                <?php if ($k['rating']): ?>
                <div class="mb-1"><?php for ($s = 1; $s <= 5; $s++): ?><i class="bi bi-star-fill text-<?= $s <= $k['rating'] ? 'warning' : 'muted' ?>"></i> <?php endfor; ?></div>
                <?php endif; ?>
                <p class="mb-0"><?= e($k['isi_komentar']) ?></p>
            </div>
        </div>
        <?php endwhile; ?>
        <?php else: ?>
        <p class="text-muted">Belum ada komentar.</p>
        <?php endif; ?>
    </div>

    <!-- Buku Terkait -->
    <?php if (mysqli_num_rows($terkait_result) > 0): ?>
    <div class="mt-5">
        <h4 class="fw-bold mb-3">Buku Terkait</h4>
        <div class="row g-3">
            <?php while ($tb = mysqli_fetch_assoc($terkait_result)): ?>
            <div class="col-md-3 col-6">
                <a href="detail_buku.php?id=<?= $tb['id_buku'] ?>" class="text-decoration-none">
                    <div class="card h-100">
                        <img src="<?= e($tb['gambar']) ?>" class="card-img-top" height="180" onerror="this.src='https://via.placeholder.com/180?text=Buku'">
                        <div class="card-body"><h6 class="card-title text-dark small"><?= e($tb['judul_buku']) ?></h6></div>
                    </div>
                </a>
            </div>
            <?php endwhile; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<script>
function setRating(val) {
    document.getElementById('rating-input').value = val;
    document.querySelectorAll('.star-btn').forEach((s, i) => {
        s.classList.toggle('text-warning', i < val);
        s.classList.toggle('text-muted', i >= val);
    });
}
</script>

<?php require_once __DIR__ . '/partials/footer.php'; ?>
