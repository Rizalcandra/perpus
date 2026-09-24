<?php
/**
 * Perpustakaan Desa - Tambah Buku
 * File: tambah.php
 * Deskripsi: Form untuk menambahkan buku baru ke perpustakaan
 * Hanya bisa diakses oleh admin
 */

require_once __DIR__ . '/config.php';

// Cek login dan role admin
if (!is_logged_in() || !is_admin()) {
    set_flash('error', 'Hanya admin yang dapat menambahkan buku.');
    redirect('login.php');
}

$page_title = 'Tambah Buku Baru';

// Ambil data kategori dan penerbit untuk dropdown
$kategori_list = mysqli_query($koneksi, "SELECT * FROM kategori_buku ORDER BY nama_kategori");
$penerbit_list = mysqli_query($koneksi, "SELECT * FROM penerbit ORDER BY nama_penerbit");

require_once __DIR__ . '/partials/header.php';
?>

<section class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card border-0 shadow">
                    <div class="card-header bg-primary text-white">
                        <i class="bi bi-plus-circle me-2"></i>Tambah Buku Baru
                    </div>
                    <div class="card-body p-4">
                        <form method="POST" action="proses_tambah.php" enctype="multipart/form-data" novalidate>
                            <?= csrf_field() ?>

                            <!-- Judul Buku -->
                            <div class="mb-3">
                                <label for="judul_buku" class="form-label fw-semibold">Judul Buku <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="judul_buku" name="judul_buku" 
                                       placeholder="Masukkan judul buku" required>
                            </div>

                            <!-- ISBN -->
                            <div class="mb-3">
                                <label for="isbn" class="form-label fw-semibold">ISBN</label>
                                <input type="text" class="form-control" id="isbn" name="isbn" 
                                       placeholder="Contoh: 978-979-1227-01-2">
                            </div>

                            <div class="row">
                                <!-- Bahasa -->
                                <div class="col-md-4 mb-3">
                                    <label for="bahasa" class="form-label fw-semibold">Bahasa</label>
                                    <select class="form-select" id="bahasa" name="bahasa">
                                        <option value="Indonesia" selected>Indonesia</option>
                                        <option value="Inggris">Inggris</option>
                                        <option value="Jawa">Jawa</option>
                                        <option value="Lainnya">Lainnya</option>
                                    </select>
                                </div>

                                <!-- Tahun Terbit -->
                                <div class="col-md-4 mb-3">
                                    <label for="tahun_terbit" class="form-label fw-semibold">Tahun Terbit</label>
                                    <input type="number" class="form-control" id="tahun_terbit" name="tahun_terbit" 
                                           placeholder="Contoh: 2023" min="1900" max="<?= date('Y') + 1 ?>">
                                </div>

                                <!-- Jumlah Halaman -->
                                <div class="col-md-4 mb-3">
                                    <label for="jumlah_halaman" class="form-label fw-semibold">Jumlah Halaman</label>
                                    <input type="number" class="form-control" id="jumlah_halaman" name="jumlah_halaman" 
                                           placeholder="Contoh: 300" min="1">
                                </div>
                            </div>

                            <!-- Kondisi -->
                            <div class="mb-3">
                                <label for="kondisi" class="form-label fw-semibold">Kondisi Buku <span class="text-danger">*</span></label>
                                <select class="form-select" id="kondisi" name="kondisi" required>
                                    <option value="Baru">Baru</option>
                                    <option value="Baik" selected>Baik</option>
                                    <option value="Cukup">Cukup</option>
                                    <option value="Rusak">Rusak</option>
                                </select>
                            </div>

                            <div class="row">
                                <!-- Kategori -->
                                <div class="col-md-6 mb-3">
                                    <label for="id_kategori" class="form-label fw-semibold">Kategori <span class="text-danger">*</span></label>
                                    <select class="form-select" id="id_kategori" name="id_kategori" required>
                                        <option value="">Pilih Kategori</option>
                                        <?php while ($kat = mysqli_fetch_assoc($kategori_list)): ?>
                                        <option value="<?= $kat['id_kategori'] ?>"><?= e($kat['nama_kategori']) ?></option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>

                                <!-- Penerbit -->
                                <div class="col-md-6 mb-3">
                                    <label for="id_penerbit" class="form-label fw-semibold">Penerbit <span class="text-danger">*</span></label>
                                    <select class="form-select" id="id_penerbit" name="id_penerbit" required>
                                        <option value="">Pilih Penerbit</option>
                                        <?php while ($pen = mysqli_fetch_assoc($penerbit_list)): ?>
                                        <option value="<?= $pen['id_penerbit'] ?>"><?= e($pen['nama_penerbit']) ?></option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                            </div>

                            <!-- Stok -->
                            <div class="mb-3">
                                <label for="stok" class="form-label fw-semibold">Stok <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="stok" name="stok" 
                                       placeholder="Jumlah buku tersedia" min="0" value="1" required>
                            </div>

                            <!-- Gambar -->
                            <div class="mb-3">
                                <label for="gambar" class="form-label fw-semibold">Gambar Cover Buku</label>
                                <input type="file" class="form-control" id="gambar" name="gambar" 
                                       accept="image/*">
                                <div class="form-text">Format: JPG, PNG, GIF, WebP. Maks. 2MB.</div>
                            </div>

                            <!-- Populer & Baru -->
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="populer" name="populer" value="1">
                                        <label class="form-check-label fw-semibold" for="populer">Tandai sebagai Populer</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="baru" name="baru" value="1" checked>
                                        <label class="form-check-label fw-semibold" for="baru">Tandai sebagai Baru (New Arrival)</label>
                                    </div>
                                </div>
                            </div>

                            <!-- Sinopsis -->
                            <div class="mb-3">
                                <label for="sinopsis" class="form-label fw-semibold">Sinopsis</label>
                                <textarea class="form-control" id="sinopsis" name="sinopsis" rows="5" 
                                          placeholder="Tulis sinopsis buku..."></textarea>
                            </div>

                            <div class="d-flex gap-3">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="bi bi-check-circle me-2"></i>Simpan Buku
                                </button>
                                <a href="katalog_buku.php" class="btn btn-outline-secondary btn-lg">
                                    <i class="bi bi-arrow-left me-2"></i>Batal
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/partials/footer.php'; ?>
