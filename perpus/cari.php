<?php
/**
 * Perpustakaan Desa - Pencarian Buku
 * File: cari.php
 * Deskripsi: Halaman pencarian buku berdasarkan judul, ISBN, kategori, penerbit
 */

require_once __DIR__ . '/config.php';

$page_title = 'Cari Buku';

$q = trim($_GET['q'] ?? '');
$kategori_id = isset($_GET['kategori']) ? (int)$_GET['kategori'] : 0;
$hasil = [];
$total_hasil = 0;

if ($q !== '' || $kategori_id > 0) {
    // Bangun query pencarian
    $where = [];
    $params = [];
    $types = '';

    if ($q !== '') {
        $where[] = "(b.judul_buku LIKE ? OR b.isbn LIKE ? OR b.sinopsis LIKE ?)";
        $search_term = "%$q%";
        $params[] = $search_term;
        $params[] = $search_term;
        $params[] = $search_term;
        $types .= 'sss';
    }

    if ($kategori_id > 0) {
        $where[] = "b.id_kategori = ?";
        $params[] = $kategori_id;
        $types .= 'i';
    }

    $where_clause = implode(' AND ', $where);

    $query = "SELECT b.*, k.nama_kategori, p.nama_penerbit 
              FROM buku b 
              LEFT JOIN kategori_buku k ON b.id_kategori = k.id_kategori 
              LEFT JOIN penerbit p ON b.id_penerbit = p.id_penerbit 
              WHERE $where_clause 
              ORDER BY b.judul_buku ASC";

    $stmt = mysqli_prepare($koneksi, $query);
    if (!empty($params)) {
        mysqli_stmt_bind_param($stmt, $types, ...$params);
    }
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $total_hasil = mysqli_num_rows($result);
    mysqli_stmt_close($stmt);

    // Re-query untuk mendapatkan data (karena num_rows sudah di-fetch)
    $stmt = mysqli_prepare($koneksi, $query);
    if (!empty($params)) {
        mysqli_stmt_bind_param($stmt, $types, ...$params);
    }
    mysqli_stmt_execute($stmt);
    $hasil = mysqli_stmt_get_result($stmt);
    mysqli_stmt_close($stmt);
}

// Ambil daftar kategori untuk dropdown
$kategori_list = mysqli_query($koneksi, "SELECT * FROM kategori_buku ORDER BY nama_kategori");

require_once __DIR__ . '/partials/header.php';
?>

<!-- Page Header -->
<section class="bg-primary text-white py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 text-center">
                <h1 class="fw-bold mb-3"><i class="bi bi-search me-2"></i>Cari Buku</h1>
                <p class="opacity-75 mb-4">Temukan buku berdasarkan judul, ISBN, atau kategori</p>
                
                <!-- Search Form -->
                <form method="GET" action="cari.php" class="row g-2 justify-content-center">
                    <div class="col-md-6">
                        <input type="text" class="form-control form-control-lg" name="q" 
                               placeholder="Cari judul buku, ISBN..."
                               value="<?= e($q) ?>" autofocus>
                    </div>
                    <div class="col-md-3">
                        <select name="kategori" class="form-select form-select-lg">
                            <option value="">Semua Kategori</option>
                            <?php $kat_res = mysqli_query($koneksi, "SELECT * FROM kategori_buku ORDER BY nama_kategori"); ?>
                            <?php while ($kat = mysqli_fetch_assoc($kat_res)): ?>
                            <option value="<?= $kat['id_kategori'] ?>" <?= $kategori_id == $kat['id_kategori'] ? 'selected' : '' ?>>
                                <?= e($kat['nama_kategori']) ?>
                            </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn btn-light btn-lg text-primary fw-semibold">
                            <i class="bi bi-search me-1"></i>Cari
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>

<section class="py-5">
    <div class="container">
        <?php if ($q !== '' || $kategori_id > 0): ?>
            <!-- Hasil Pencarian -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="mb-0">
                    <?php if ($q): ?>
                        Hasil pencarian untuk "<strong><?= e($q) ?></strong>"
                    <?php endif; ?>
                    <?php if ($kategori_id > 0): 
                        $kat_name_res = mysqli_query($koneksi, "SELECT nama_kategori FROM kategori_buku WHERE id_kategori = $kategori_id");
                        $kat_name = mysqli_fetch_assoc($kat_name_res)['nama_kategori'] ?? '';
                    ?>
                        <?php if ($q): ?> dan <?php endif; ?>
                        Kategori: "<strong><?= e($kat_name) ?></strong>"
                    <?php endif; ?>
                </h5>
                <span class="badge bg-primary fs-6"><?= $total_hasil ?> buku ditemukan</span>
            </div>

            <?php if ($total_hasil > 0): ?>
            <div class="row">
                <?php while ($buku = mysqli_fetch_assoc($hasil)): ?>
                    <?php include __DIR__ . '/partials/book_card.php'; ?>
                <?php endwhile; ?>
            </div>
            <?php else: ?>
            <div class="text-center py-5">
                <i class="bi bi-search display-1 text-muted"></i>
                <h4 class="mt-3 text-muted">Tidak ada buku ditemukan</h4>
                <p class="text-muted">Coba gunakan kata kunci yang berbeda</p>
                <a href="cari.php" class="btn btn-primary">
                    <i class="bi bi-arrow-counterclockwise me-1"></i>Cari Lagi
                </a>
            </div>
            <?php endif; ?>
        <?php else: ?>
            <!-- Tampilkan saran pencarian -->
            <div class="text-center py-5">
                <i class="bi bi-book display-1 text-muted"></i>
                <h4 class="mt-3 text-muted">Mulai pencarian Anda</h4>
                <p class="text-muted">Ketik judul buku, ISBN, atau pilih kategori untuk mulai mencari</p>
            </div>

            <!-- Kategori Populer -->
            <div class="mt-4">
                <h5 class="fw-bold mb-3">Kategori Buku</h5>
                <div class="row g-3">
                    <?php 
                    $kategori_icons = [
                        'Fiksi' => 'bi-book',
                        'Non-Fiksi' => 'bi-journal-text',
                        'Sains' => 'bi-lightbulb',
                        'Biografi' => 'bi-person-badge',
                        'Pelajaran' => 'bi-mortarboard',
                        'Ensiklopedia' => 'bi-collection'
                    ];
                    while ($kat = mysqli_fetch_assoc($kategori_list)):
                        $icon = $kategori_icons[$kat['nama_kategori']] ?? 'bi-bookmark';
                    ?>
                    <div class="col-md-2 col-6">
                        <a href="cari.php?kategori=<?= $kat['id_kategori'] ?>" class="text-decoration-none">
                            <div class="card border-0 shadow-sm text-center py-3 kategori-card">
                                <i class="bi <?= $icon ?> display-5 text-primary"></i>
                                <small class="fw-semibold text-dark mt-2 d-block"><?= e($kat['nama_kategori']) ?></small>
                            </div>
                        </a>
                    </div>
                    <?php endwhile; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php require_once __DIR__ . '/partials/footer.php'; ?>
