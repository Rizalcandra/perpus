<?php
/**
 * Perpustakaan Desa - Katalog Buku
 * File: katalog_buku.php
 * Deskripsi: Menampilkan daftar semua buku dengan filter dan sorting
 */

require_once __DIR__ . '/config.php';

$page_title = 'Katalog Buku';

// Ambil parameter filter
$kategori_id = isset($_GET['kategori']) ? (int)$_GET['kategori'] : 0;
$penerbit_id = isset($_GET['penerbit']) ? (int)$_GET['penerbit'] : 0;
$filter = isset($_GET['filter']) ? $_GET['filter'] : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'terbaru';
$halaman = isset($_GET['halaman']) ? max(1, (int)$_GET['halaman']) : 1;
$per_halaman = 12;

// Bangun query
$where = ["b.stok >= 0"]; // Tampilkan semua buku
$params = [];
$types = '';

if ($kategori_id > 0) {
    $where[] = "b.id_kategori = ?";
    $params[] = $kategori_id;
    $types .= 'i';
}

if ($penerbit_id > 0) {
    $where[] = "b.id_penerbit = ?";
    $params[] = $penerbit_id;
    $types .= 'i';
}

if ($filter === 'populer') {
    $where[] = "b.populer = '1'";
} elseif ($filter === 'baru') {
    $where[] = "b.baru = '1'";
}

$where_clause = implode(' AND ', $where);

// Sorting
$order_by = match($sort) {
    'terlama' => 'b.created_at ASC',
    'judul_az' => 'b.judul_buku ASC',
    'judul_za' => 'b.judul_buku DESC',
    'tahun_terbaru' => 'b.tahun_terbit DESC',
    'tahun_terlama' => 'b.tahun_terbit ASC',
    default => 'b.created_at DESC',
};

// Hitung total data
$count_query = "SELECT COUNT(*) as total FROM buku b WHERE $where_clause";
$stmt = mysqli_prepare($koneksi, $count_query);
if (!empty($params)) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}
mysqli_stmt_execute($stmt);
$total_data = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt))['total'];
mysqli_stmt_close($stmt);

$total_halaman = ceil($total_data / $per_halaman);
$offset = ($halaman - 1) * $per_halaman;

// Query data buku
$query = "SELECT b.*, k.nama_kategori, p.nama_penerbit 
          FROM buku b 
          LEFT JOIN kategori_buku k ON b.id_kategori = k.id_kategori 
          LEFT JOIN penerbit p ON b.id_penerbit = p.id_penerbit 
          WHERE $where_clause 
          ORDER BY $order_by 
          LIMIT ?, ?";

// Tambah parameter limit dan offset
$params_with_limit = array_merge($params, [$offset, $per_halaman]);
$types_with_limit = $types . 'ii';

$stmt = mysqli_prepare($koneksi, $query);
mysqli_stmt_bind_param($stmt, $types_with_limit, ...$params_with_limit);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
mysqli_stmt_close($stmt);

// Ambil data kategori dan penerbit untuk filter
$kategori_list = mysqli_query($koneksi, "SELECT * FROM kategori_buku ORDER BY nama_kategori");
$penerbit_list = mysqli_query($koneksi, "SELECT * FROM penerbit ORDER BY nama_penerbit");

require_once __DIR__ . '/partials/header.php';
?>

<!-- Page Header -->
<section class="bg-primary text-white py-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h1 class="fw-bold mb-2"><i class="bi bi-grid me-2"></i>Katalog Buku</h1>
                <p class="mb-0 opacity-75">Jelajahi seluruh koleksi buku perpustakaan desa kami</p>
            </div>
            <div class="col-md-4 text-md-end mt-3 mt-md-0">
                <span class="badge bg-light text-primary fs-6">
                    <i class="bi bi-journal-bookmark me-1"></i><?= $total_data ?> Buku Ditemukan
                </span>
            </div>
        </div>
    </div>
</section>

<section class="py-5">
    <div class="container">
        <div class="row">
            <!-- Sidebar Filter -->
            <div class="col-lg-3 mb-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <i class="bi bi-funnel me-2"></i>Filter Buku
                    </div>
                    <div class="card-body">
                        <form method="GET" action="">
                            <!-- Kategori -->
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Kategori</label>
                                <select name="kategori" class="form-select form-select-sm">
                                    <option value="">Semua Kategori</option>
                                    <?php $kat_res = mysqli_query($koneksi, "SELECT * FROM kategori_buku ORDER BY nama_kategori"); ?>
                                    <?php while ($kat = mysqli_fetch_assoc($kat_res)): ?>
                                    <option value="<?= $kat['id_kategori'] ?>" <?= $kategori_id == $kat['id_kategori'] ? 'selected' : '' ?>>
                                        <?= e($kat['nama_kategori']) ?>
                                    </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>

                            <!-- Penerbit -->
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Penerbit</label>
                                <select name="penerbit" class="form-select form-select-sm">
                                    <option value="">Semua Penerbit</option>
                                    <?php $pen_res = mysqli_query($koneksi, "SELECT * FROM penerbit ORDER BY nama_penerbit"); ?>
                                    <?php while ($pen = mysqli_fetch_assoc($pen_res)): ?>
                                    <option value="<?= $pen['id_penerbit'] ?>" <?= $penerbit_id == $pen['id_penerbit'] ? 'selected' : '' ?>>
                                        <?= e($pen['nama_penerbit']) ?>
                                    </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>

                            <!-- Filter Cepat -->
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Filter Cepat</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="filter" id="filter_all" value="" <?= !$filter ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="filter_all">Semua</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="filter" id="filter_populer" value="populer" <?= $filter === 'populer' ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="filter_populer">Populer</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="filter" id="filter_baru" value="baru" <?= $filter === 'baru' ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="filter_baru">Baru</label>
                                </div>
                            </div>

                            <!-- Urutkan -->
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Urutkan</label>
                                <select name="sort" class="form-select form-select-sm">
                                    <option value="terbaru" <?= $sort === 'terbaru' ? 'selected' : '' ?>>Terbaru</option>
                                    <option value="terlama" <?= $sort === 'terlama' ? 'selected' : '' ?>>Terlama</option>
                                    <option value="judul_az" <?= $sort === 'judul_az' ? 'selected' : '' ?>>Judul A-Z</option>
                                    <option value="judul_za" <?= $sort === 'judul_za' ? 'selected' : '' ?>>Judul Z-A</option>
                                    <option value="tahun_terbaru" <?= $sort === 'tahun_terbaru' ? 'selected' : '' ?>>Tahun Terbaru</option>
                                    <option value="tahun_terlama" <?= $sort === 'tahun_terlama' ? 'selected' : '' ?>>Tahun Terlama</option>
                                </select>
                            </div>

                            <button type="submit" class="btn btn-primary w-100 btn-sm">
                                <i class="bi bi-search me-1"></i>Terapkan Filter
                            </button>
                            <a href="katalog_buku.php" class="btn btn-outline-secondary w-100 btn-sm mt-2">
                                <i class="bi bi-arrow-counterclockwise me-1"></i>Reset
                            </a>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Buku Grid -->
            <div class="col-lg-9">
                <?php if (mysqli_num_rows($result) > 0): ?>
                <div class="row">
                    <?php while ($buku = mysqli_fetch_assoc($result)): ?>
                        <?php include __DIR__ . '/partials/book_card.php'; ?>
                    <?php endwhile; ?>
                </div>

                <!-- Pagination -->
                <?php if ($total_halaman > 1): ?>
                <nav class="mt-4">
                    <ul class="pagination justify-content-center">
                        <?php if ($halaman > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['halaman' => $halaman - 1])) ?>">
                                <i class="bi bi-chevron-left"></i>
                            </a>
                        </li>
                        <?php endif; ?>

                        <?php for ($i = 1; $i <= $total_halaman; $i++): ?>
                        <li class="page-item <?= $i === $halaman ? 'active' : '' ?>">
                            <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['halaman' => $i])) ?>">
                                <?= $i ?>
                            </a>
                        </li>
                        <?php endfor; ?>

                        <?php if ($halaman < $total_halaman): ?>
                        <li class="page-item">
                            <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['halaman' => $halaman + 1])) ?>">
                                <i class="bi bi-chevron-right"></i>
                            </a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </nav>
                <?php endif; ?>

                <?php else: ?>
                <div class="text-center py-5">
                    <i class="bi bi-book display-1 text-muted"></i>
                    <h4 class="mt-3 text-muted">Tidak ada buku ditemukan</h4>
                    <p class="text-muted">Coba ubah filter pencarian Anda</p>
                    <a href="katalog_buku.php" class="btn btn-primary">
                        <i class="bi bi-arrow-counterclockwise me-1"></i>Reset Filter
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/partials/footer.php'; ?>
