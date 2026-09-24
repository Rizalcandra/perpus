<?php
/**
 * register.php - Halaman Pendaftaran Anggota Baru
 * Deskripsi: Form registrasi dengan Name, ID Number (NIK), Passkey
 */
require_once __DIR__ . '/config.php';

if (is_logged_in()) redirect('index.php');

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        $error = 'Token keamanan tidak valid.';
    } else {
        // Penentuan tipe data & validasi input
        $nik = trim($_POST['nik'] ?? '');
        $nama = trim($_POST['nama_lengkap'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $konfirmasi = $_POST['konfirmasi_password'] ?? '';
        $no_hp = trim($_POST['no_hp'] ?? '');
        $alamat = trim($_POST['alamat'] ?? '');

        // Array untuk menyimpan error
        $errors = [];

        // Validasi: Name (nama_lengkap)
        if (empty($nama)) {
            $errors[] = 'Nama lengkap harus diisi.';
        } elseif (strlen($nama) < 3) {
            $errors[] = 'Nama minimal 3 karakter.';
        }

        // Validasi: ID Number (NIK)
        if (empty($nik)) {
            $errors[] = 'NIK harus diisi.';
        } elseif (!preg_match('/^[0-9]{16}$/', $nik)) {
            $errors[] = 'NIK harus 16 digit angka.';
        }

        // Validasi: Email (tipe data string, filter_var)
        if (empty($email)) {
            $errors[] = 'Email harus diisi.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Format email tidak valid.';
        }

        // Validasi: Passkey (password)
        if (empty($password)) {
            $errors[] = 'Password harus diisi.';
        } elseif (strlen($password) < 6) {
            $errors[] = 'Password minimal 6 karakter.';
        }

        // Percabangan: cek password cocok
        if ($password !== $konfirmasi) {
            $errors[] = 'Konfirmasi password tidak cocok.';
        }

        if (empty($errors)) {
            // Cek duplikat email
            $stmt = mysqli_prepare($koneksi, "SELECT id_anggota FROM anggota WHERE email = ?");
            mysqli_stmt_bind_param($stmt, 's', $email);
            mysqli_stmt_execute($stmt);
            $dup = mysqli_stmt_get_result($stmt);
            mysqli_stmt_close($stmt);

            if (mysqli_num_rows($dup) > 0) {
                $error = 'Email sudah terdaftar.';
            } else {
                // INSERT data anggota baru
                $hashed = password_hash($password, PASSWORD_DEFAULT);
                $stmt = mysqli_prepare($koneksi, "INSERT INTO anggota (nik, nama_lengkap, email, password, no_hp, alamat, role) VALUES (?,?,?,?,?,?,'anggota')");
                mysqli_stmt_bind_param($stmt, 'sssssss', $nik, $nama, $email, $hashed, $no_hp, $alamat);
                try {
                    mysqli_autocommit($koneksi, false);
                    mysqli_stmt_execute($stmt);
                    mysqli_commit($koneksi);
                    $success = 'Pendaftaran berhasil! Silakan login.';
                    log_aktivitas($koneksi, 'REGISTER', "Anggota baru: $nama ($email)");
                } catch (Throwable $e) {
                    mysqli_rollback($koneksi);
                    log_error('Gagal register: ' . $e->getMessage(), 'ERROR', __FILE__, __LINE__);
                    $error = 'Gagal mendaftar. Silakan coba lagi.';
                } finally {
                    mysqli_autocommit($koneksi, true);
                    mysqli_stmt_close($stmt);
                }
            }
        } else {
            $error = '<ul class="mb-0">' . implode('<li>', array_map('e', $errors)) . '</ul>';
        }
    }
}

$page_title = 'Daftar Anggota';
require_once __DIR__ . '/partials/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow border-0">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <i class="bi bi-person-plus display-4 text-primary"></i>
                        <h3 class="fw-bold mt-2">Daftar Anggota</h3>
                        <p class="text-muted">Isi formulir berikut untuk mendaftar</p>
                    </div>
                    <?php if ($error): ?>
                    <div class="alert alert-danger"><i class="bi bi-exclamation-circle me-2"></i><?= $error ?></div>
                    <?php endif; ?>
                    <?php if ($success): ?>
                    <div class="alert alert-success"><i class="bi bi-check-circle me-2"></i><?= e($success) ?></div>
                    <?php endif; ?>
                    <form method="POST" action="" novalidate>
                        <?= csrf_field() ?>
                        <div class="mb-3">
                            <label for="nik" class="form-label fw-semibold">ID Number (NIK)</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-card-text"></i></span>
                                <input type="text" class="form-control" id="nik" name="nik" placeholder="16 digit NIK" maxlength="16" pattern="[0-9]{16}" required value="<?= e($_POST['nik'] ?? '') ?>">
                            </div>
                            <small class="text-muted">Masukkan 16 digit Nomor Induk Kependudukan</small>
                        </div>
                        <div class="mb-3">
                            <label for="nama" class="form-label fw-semibold">Name (Nama Lengkap)</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-person"></i></span>
                                <input type="text" class="form-control" id="nama" name="nama_lengkap" placeholder="Nama lengkap" required value="<?= e($_POST['nama_lengkap'] ?? '') ?>">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label fw-semibold">Email</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                <input type="email" class="form-control" id="email" name="email" placeholder="nama@email.com" required value="<?= e($_POST['email'] ?? '') ?>">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="no_hp" class="form-label fw-semibold">No. HP</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-telephone"></i></span>
                                <input type="text" class="form-control" id="no_hp" name="no_hp" placeholder="08xxxxxxxxxx" value="<?= e($_POST['no_hp'] ?? '') ?>">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="alamat" class="form-label fw-semibold">Alamat</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-geo-alt"></i></span>
                                <textarea class="form-control" id="alamat" name="alamat" rows="2" placeholder="Alamat lengkap"><?= e($_POST['alamat'] ?? '') ?></textarea>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label fw-semibold">Passkey (Password)</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                <input type="password" class="form-control" id="password" name="password" placeholder="Min. 6 karakter" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="konfirmasi" class="form-label fw-semibold">Konfirmasi Password</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                                <input type="password" class="form-control" id="konfirmasi" name="konfirmasi_password" placeholder="Ulangi password" required>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">
                            <i class="bi bi-person-check me-2"></i>Daftar Sekarang
                        </button>
                    </form>
                    <div class="text-center mt-4">
                        <p class="text-muted mb-0">Sudah punya akun? <a href="login.php" class="text-primary fw-semibold">Masuk</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/partials/footer.php'; ?>
