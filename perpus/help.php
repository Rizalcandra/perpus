<?php
require_once __DIR__ . '/config.php';
$page_title = 'Bantuan';
require_once __DIR__ . '/partials/header.php';
?>

<div class="container py-5">
    <h2 class="fw-bold mb-4"><i class="bi bi-question-circle text-primary me-2"></i>Pusat Bantuan</h2>

    <div class="row g-4">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <h5 class="fw-bold"><i class="bi bi-person-plus text-primary me-2"></i>Cara Mendaftar</h5>
                    <ol>
                        <li>Klik tombol <strong>Daftar</strong> di halaman utama.</li>
                        <li>Isi form: NIK, Nama, Email, Password.</li>
                        <li>Klik <strong>Daftar Sekarang</strong>.</li>
                        <li>Login menggunakan email dan password.</li>
                    </ol>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <h5 class="fw-bold"><i class="bi bi-book text-success me-2"></i>Cara Meminjam Buku</h5>
                    <ol>
                        <li>Login ke akun anggota.</li>
                        <li>Buka <strong>Katalog Buku</strong> dan pilih buku.</li>
                        <li>Klik <strong>Pinjam Buku</strong> (maks. 2 buku).</li>
                        <li>Tunggu konfirmasi dan ambil buku di perpustakaan.</li>
                    </ol>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <h5 class="fw-bold"><i class="bi bi-arrow-return-left text-warning me-2"></i>Cara Mengembalikan</h5>
                    <ol>
                        <li>Klik <strong>Profil Saya</strong> lalu <strong>Riwayat</strong>.</li>
                        <li>Klik tombol <strong>Kembalikan</strong> pada buku yang dipinjam.</li>
                        <li>Admin akan memverifikasi pengembalian.</li>
                        <li>Jika terlambat, denda Rp 5.000/hari berlaku.</li>
                    </ol>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <h5 class="fw-bold"><i class="bi bi-star text-danger me-2"></i>Sistem Bintang & Komentar</h5>
                    <p>Setiap anggota dapat memberikan <strong>rating bintang (1-5)</strong> dan <strong>komentar</strong> pada buku yang sudah pernah dipinjam. Masuk ke halaman detail buku, lalu isi form komentar di bagian bawah.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/partials/footer.php'; ?>
