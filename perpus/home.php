<?php
/**
 * Perpustakaan Desa - Halaman Beranda (Landing Page)
 * Deskripsi: Konten beranda dengan hero, statistik, grafik, buku populer, buku baru,
 *             pengumuman, video, dan notifikasi suara
 * 
 * File ini di-include oleh index.php, jangan akses langsung
 */
// Percabangan: cek constant
if (!defined('APP_NAME')) {
    die('Akses langsung tidak diizinkan');
}

$page_title = 'Beranda';

// --- QUERY: Ambil buku populer (SELECT + LEFT JOIN) ---
$populer_query = "SELECT b.*, k.nama_kategori, p.nama_penerbit 
                  FROM buku b 
                  LEFT JOIN kategori_buku k ON b.id_kategori = k.id_kategori 
                  LEFT JOIN penerbit p ON b.id_penerbit = p.id_penerbit 
                  WHERE b.populer = '1' 
                  ORDER BY b.created_at DESC LIMIT 4";
$populer_result = mysqli_query($koneksi, $populer_query);

// --- QUERY: Ambil buku baru ---
$baru_query = "SELECT b.*, k.nama_kategori, p.nama_penerbit 
               FROM buku b 
               LEFT JOIN kategori_buku k ON b.id_kategori = k.id_kategori 
               LEFT JOIN penerbit p ON b.id_penerbit = p.id_penerbit 
               WHERE b.baru = '1' 
               ORDER BY b.created_at DESC LIMIT 4";
$baru_result = mysqli_query($koneksi, $baru_query);

// --- QUERY: Kategori dengan COUNT (GROUP BY, JOIN) ---
$kategori_query = "SELECT k.*, COUNT(b.id_buku) as jumlah_buku 
                   FROM kategori_buku k 
                   LEFT JOIN buku b ON k.id_kategori = b.id_kategori 
                   GROUP BY k.id_kategori 
                   ORDER BY k.nama_kategori";
$kategori_result = mysqli_query($koneksi, $kategori_query);

// --- QUERY: Statistik (SELECT COUNT, aggregation) ---
$total_buku = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM buku"))['total'];
$total_anggota = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM anggota WHERE role = 'anggota'"))['total'];
$total_dipinjam = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM peminjaman WHERE status IN ('dipinjam','menunggu_konfirmasi')"))['total'];
$total_kategori = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM kategori_buku"))['total'];

// --- QUERY: Pengumuman (SELECT WHERE tampil) ---
$pengumuman_result = mysqli_query($koneksi,
    "SELECT * FROM pengumuman WHERE tampil = 'ya' ORDER BY created_at DESC LIMIT 3"
);

// --- QUERY: Data untuk Grafik (Chart.js) - peminjaman per kategori ---
$chart_data = mysqli_query($koneksi,
    "SELECT k.nama_kategori, COUNT(p.id_peminjaman) AS total 
     FROM kategori_buku k 
     LEFT JOIN buku b ON k.id_kategori = b.id_kategori 
     LEFT JOIN peminjaman p ON b.id_buku = p.id_buku 
     GROUP BY k.id_kategori 
     ORDER BY total DESC"
);
// Perulangan: build array for chart
$chart_labels = [];
$chart_values = [];
while ($cd = mysqli_fetch_assoc($chart_data)) {
    $chart_labels[] = $cd['nama_kategori']; // Array
    $chart_values[] = (int)$cd['total']; // Tipe data int
}
?>

<!-- MP3/Suara/Notif: Audio notification (UKK 10) -->
<audio id="notif-sound" preload="none">
    <source src="assets/sound/notif.mp3" type="audio/mpeg">
</audio>

<!-- Hero Section with Text (UKK 7) and Gambar (UKK 8) -->
<section class="hero-section">
    <div class="container">
        <div class="row align-items-center min-vh-75">
            <div class="col-lg-6 mb-5 mb-lg-0">
                <span class="badge bg-primary bg-opacity-10 text-primary mb-3 px-3 py-2">
                    <i class="bi bi-house-heart me-1"></i>Perpustakaan Desa
                </span>
                <h1 class="display-4 fw-bold mb-3">
                    Perpustakaan<br>
                    <span class="text-primary">Desa</span>
                </h1>
                <p class="lead text-muted mb-4">
                    Akses koleksi buku perpustakaan desa kapan saja dan di mana saja.
                    Pinjam buku dengan mudah, kembalikan tepat waktu, dan tingkatkan literasi warga desa.
                </p>
                <div class="d-flex gap-3 flex-wrap">
                    <a href="katalog_buku.php" class="btn btn-primary btn-lg px-4">
                        <i class="bi bi-grid me-2"></i>Jelajahi Katalog
                    </a>
                    <?php if (!is_logged_in()): ?>
                    <a href="register.php" class="btn btn-outline-dark btn-lg px-4">
                        <i class="bi bi-person-plus me-2"></i>Daftar Anggota
                    </a>
                    <?php endif; ?>
                </div>
            </div>
            <div class="col-lg-6 text-center">
                <img src="https://images.unsplash.com/photo-1524995997946-a1c2e315a42f?w=600&h=450&fit=crop" 
                     alt="Perpustakaan Desa" class="img-fluid rounded-4 shadow-lg hero-img">
            </div>
        </div>
    </div>
</section>

<!-- Statistik Section -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="row g-4 text-center">
            <div class="col-md-3 col-6">
                <div class="card border-0 shadow-sm py-4">
                    <div class="card-body">
                        <i class="bi bi-journal-bookmark-fill display-5 text-primary mb-2"></i>
                        <h3 class="fw-bold mb-0"><?= $total_buku ?></h3>
                        <small class="text-muted">Koleksi Buku</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="card border-0 shadow-sm py-4">
                    <div class="card-body">
                        <i class="bi bi-people-fill display-5 text-success mb-2"></i>
                        <h3 class="fw-bold mb-0"><?= $total_anggota ?></h3>
                        <small class="text-muted">Anggota</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="card border-0 shadow-sm py-4">
                    <div class="card-body">
                        <i class="bi bi-book-half display-5 text-warning mb-2"></i>
                        <h3 class="fw-bold mb-0"><?= $total_dipinjam ?></h3>
                        <small class="text-muted">Sedang Dipinjam</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="card border-0 shadow-sm py-4">
                    <div class="card-body">
                        <i class="bi bi-collection-fill display-5 text-info mb-2"></i>
                        <h3 class="fw-bold mb-0"><?= $total_kategori ?></h3>
                        <small class="text-muted">Kategori</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Pengumuman Section -->
<?php if (mysqli_num_rows($pengumuman_result) > 0): ?>
<section class="py-3">
    <div class="container">
        <?php while ($pg = mysqli_fetch_assoc($pengumuman_result)): 
            // Percabangan: warna berdasarkan tipe
            $tipe_class = match($pg['tipe']) {
                'darurat' => 'alert-danger',
                'penting' => 'alert-warning',
                default => 'alert-info'
            };
            $tipe_icon = match($pg['tipe']) {
                'darurat' => 'bi-exclamation-triangle-fill',
                'penting' => 'bi-exclamation-circle-fill',
                default => 'bi-info-circle-fill'
            };
        ?>
        <div class="alert <?= $tipe_class ?> d-flex align-items-center mb-2">
            <i class="bi <?= $tipe_icon ?> fs-4 me-3"></i>
            <div>
                <strong><?= e($pg['judul']) ?></strong>
                <p class="mb-0 small"><?= nl2br(e($pg['isi'])) ?></p>
            </div>
        </div>
        <?php endwhile; ?>
    </div>
</section>
<?php endif; ?>

<!-- Kategori Section with Icon -->
<section class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-bold">Kategori Buku</h2>
            <p class="text-muted">Jelajahi koleksi berdasarkan kategori yang kamu minati</p>
        </div>
        <div class="row g-3">
            <?php 
            // Array asosiatif untuk icon kategori
            $kategori_icons = [
                'Fiksi' => 'bi-book',
                'Non-Fiksi' => 'bi-journal-text',
                'Sains' => 'bi-lightbulb',
                'Biografi' => 'bi-person-badge',
                'Pelajaran' => 'bi-mortarboard',
                'Ensiklopedia' => 'bi-collection'
            ];
            // Perulangan while
            while ($kat = mysqli_fetch_assoc($kategori_result)):
                // Percabangan: pilih icon
                $icon = $kategori_icons[$kat['nama_kategori']] ?? 'bi-bookmark';
            ?>
            <div class="col-lg-2 col-md-4 col-6">
                <a href="katalog_buku.php?kategori=<?= $kat['id_kategori'] ?>" class="text-decoration-none">
                    <div class="card border-0 shadow-sm text-center py-4 kategori-card h-100">
                        <div class="card-body">
                            <i class="bi <?= $icon ?> display-4 text-primary mb-2"></i>
                            <h6 class="fw-semibold mb-1 text-dark"><?= e($kat['nama_kategori']) ?></h6>
                            <small class="text-muted"><?= $kat['jumlah_buku'] ?> buku</small>
                        </div>
                    </div>
                </a>
            </div>
            <?php endwhile; ?>
        </div>
    </div>
</section>

<!-- Grafik Section (UKK 9) -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-4">
            <h2 class="fw-bold"><i class="bi bi-bar-chart-line text-primary me-2"></i>Statistik Peminjaman</h2>
            <p class="text-muted">Grafik peminjaman buku berdasarkan kategori</p>
        </div>
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <canvas id="chartKategori" height="300"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Buku Populer Section with Badge/Bintang (UKK 18) -->
<section class="py-5">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="fw-bold mb-1">
                    <i class="bi bi-fire text-danger me-2"></i>Buku Populer
                </h2>
                <p class="text-muted mb-0">Buku yang paling banyak diminati pembaca</p>
            </div>
            <a href="katalog_buku.php?filter=populer" class="btn btn-outline-primary">
                Lihat Semua <i class="bi bi-arrow-right ms-1"></i>
            </a>
        </div>
        <div class="row">
            <?php if (mysqli_num_rows($populer_result) > 0): ?>
                <?php while ($buku = mysqli_fetch_assoc($populer_result)): ?>
                    <?php include __DIR__ . '/partials/book_card.php'; ?>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-12"><div class="alert alert-info"><i class="bi bi-info-circle me-2"></i>Belum ada buku populer.</div></div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Buku Baru Section -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="fw-bold mb-1">
                    <i class="bi bi-star text-success me-2"></i>Koleksi Terbaru
                </h2>
                <p class="text-muted mb-0">Buku baru yang baru saja ditambahkan</p>
            </div>
            <a href="katalog_buku.php?filter=baru" class="btn btn-outline-primary">
                Lihat Semua <i class="bi bi-arrow-right ms-1"></i>
            </a>
        </div>
        <div class="row">
            <?php if (mysqli_num_rows($baru_result) > 0): ?>
                <?php while ($buku = mysqli_fetch_assoc($baru_result)): ?>
                    <?php include __DIR__ . '/partials/book_card.php'; ?>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-12"><div class="alert alert-info"><i class="bi bi-info-circle me-2"></i>Belum ada buku baru.</div></div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Video Section (UKK 11) -->
<section class="py-5">
    <div class="container">
        <div class="text-center mb-4">
            <h2 class="fw-bold"><i class="bi bi-play-circle text-danger me-2"></i>Video Perpustakaan</h2>
            <p class="text-muted">Tentang layanan perpustakaan desa</p>
        </div>
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="ratio ratio-16x9 rounded shadow">
                    <iframe src="https://www.youtube.com/embed/dQw4w9WgXcQ" title="Video Perpustakaan Desa" allowfullscreen></iframe>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="py-5 bg-primary text-white">
    <div class="container text-center">
        <h2 class="fw-bold mb-3">Mulai Membaca Sekarang!</h2>
        <p class="lead mb-4 opacity-75">
            Daftar sebagai anggota dan nikmati akses ke seluruh koleksi perpustakaan desa kami.
        </p>
        <?php if (!is_logged_in()): ?>
        <a href="register.php" class="btn btn-light btn-lg px-5">
            <i class="bi bi-person-plus me-2"></i>Daftar Sekarang
        </a>
        <?php else: ?>
        <a href="katalog_buku.php" class="btn btn-light btn-lg px-5">
            <i class="bi bi-grid me-2"></i>Jelajahi Katalog
        </a>
        <?php endif; ?>
    </div>
</section>

<!-- Grafik Chart.js Script -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Grafik: Bar chart peminjaman per kategori (UKK 9)
const ctx = document.getElementById('chartKategori');
if (ctx) {
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: <?= json_encode($chart_labels) ?>,
            datasets: [{
                label: 'Total Peminjaman',
                data: <?= json_encode($chart_values) ?>,
                backgroundColor: ['#0d6efd','#198754','#ffc107','#dc3545','#0dcaf0','#6610f2'],
                borderRadius: 8
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
        }
    });
}

// MP3/Suara notifikasi (UKK 10) - play on flash message
<?php if (get_flash()): ?>
try { document.getElementById('notif-sound').play(); } catch(e) {}
<?php endif; ?>
</script>
