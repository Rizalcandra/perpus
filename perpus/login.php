<?php
require_once __DIR__ . '/config.php';

if (is_logged_in()) redirect('index.php');

$error = '';
$login_success = false;
$login_redirect = 'index.php';
$login_nama = '';
$login_role = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        $error = 'Token keamanan tidak valid.';
        log_error('Login gagal: CSRF token invalid', 'WARNING');
    } else {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        if (empty($email) || empty($password)) {
            $error = 'Email dan password harus diisi.';
        } else {
            try {
                // SELECT dengan prepared statement
                $stmt = mysqli_prepare($koneksi, "SELECT id_anggota, nama_lengkap, email, password, role, aktif FROM anggota WHERE email = ?");
                mysqli_stmt_bind_param($stmt, 's', $email);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
                if (mysqli_num_rows($result) === 1) {
                    $user = mysqli_fetch_assoc($result);
                    // Percabangan: cek status aktif
                    if (!$user['aktif']) {
                        $error = 'Akun Anda dinonaktifkan. Hubungi admin.';
                        log_error('Login ditolak: akun nonaktif - ' . $email, 'WARNING');
                    } elseif (password_verify($password, $user['password'])) {
                        // Set session (array asosiatif)
                        $_SESSION['id_anggota'] = $user['id_anggota'];
                        $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
                        $_SESSION['email'] = $user['email'];
                        $_SESSION['role'] = $user['role'];

                        // Operasi non-kritis: gagal tidak menghentikan login
                        try { register_session($koneksi); } catch (Throwable $e) {}
                        try { log_aktivitas($koneksi, 'LOGIN', 'User ' . $user['nama_lengkap'] . ' berhasil login sebagai ' . $user['role']); } catch (Throwable $e) {}
                        try { cleanup_old_sessions($koneksi); } catch (Throwable $e) {}

                        // Percabangan: redirect berdasarkan role
                        if ($user['role'] === 'admin') {
                            $login_redirect = 'admin/index.php';
                        } elseif ($user['role'] === 'petugas') {
                            $login_redirect = 'admin/petugas_dashboard.php';
                        } else {
                            $login_redirect = 'index.php';
                        }
                        $login_success = true;
                        $login_nama = $user['nama_lengkap'];
                        $login_role = $user['role'];
                    } else {
                        $error = 'Email atau password salah.';
                        log_error('Login gagal: password salah - ' . $email, 'WARNING');
                    }
                } else {
                    $error = 'Email atau password salah.';
                }
                mysqli_stmt_close($stmt);
            } catch (Throwable $e) {
                $error = 'Terjadi kesalahan sistem.';
                log_error('Login error: ' . $e->getMessage(), 'ERROR', __FILE__, __LINE__);
            }
        }
    }
}

$page_title = 'Masuk';
require_once __DIR__ . '/partials/header.php';
?>

<?php if ($login_success): ?>
<!-- ============ CONGRATULATIONS PAGE ============ -->
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 text-center">
            <div class="card shadow border-0">
                <div class="card-body p-5">
                    <!-- Animated Checkmark -->
                    <div class="mb-4">
                        <div id="congrats-icon" style="display:inline-block;opacity:0;transform:scale(0.3);transition:all 0.6s cubic-bezier(0.175,0.885,0.32,1.275);">
                            <div style="width:100px;height:100px;border-radius:50%;background:linear-gradient(135deg,#198754,#20c997);display:inline-flex;align-items:center;justify-content:center;box-shadow:0 8px 30px rgba(25,135,84,0.3);">
                                <i class="bi bi-check-lg text-white" style="font-size:3rem;"></i>
                            </div>
                        </div>
                    </div>

                    <h2 class="fw-bold text-success mb-2" id="congrats-title" style="opacity:0;transform:translateY(20px);transition:all 0.5s ease 0.4s;">
                        Congratulations!
                    </h2>
                    <h4 class="fw-semibold mb-3" id="congrats-name" style="opacity:0;transform:translateY(20px);transition:all 0.5s ease 0.6s;">
                        Selamat datang, <?= e($login_nama) ?>!
                    </h4>
                    <p class="text-muted mb-1" id="congrats-role" style="opacity:0;transform:translateY(20px);transition:all 0.5s ease 0.8s;">
                        Login berhasil sebagai <span class="badge bg-primary"><?= strtoupper(e($login_role)) ?></span>
                    </p>
                    <p class="text-muted small" id="congrats-msg" style="opacity:0;transform:translateY(20px);transition:all 0.5s ease 1s;">
                        Anda akan dialihkan secara otomatis...
                    </p>

                    <!-- Progress Bar -->
                    <div class="mt-4" id="congrats-progress" style="opacity:0;transition:all 0.5s ease 1.1s;">
                        <div class="progress" style="height:4px;border-radius:2px;">
                            <div class="progress-bar bg-success" id="redirect-progress" style="width:0%;transition:width 2s linear;border-radius:2px;"></div>
                        </div>
                    </div>

                    <a href="<?= e($login_redirect) ?>" class="btn btn-primary mt-3" id="congrats-btn" style="opacity:0;transition:all 0.5s ease 1.2s;">
                        <i class="bi bi-arrow-right me-2"></i>Lanjutkan Sekarang
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Trigger congrats animations
setTimeout(function(){
    document.getElementById('congrats-icon').style.opacity='1';
    document.getElementById('congrats-icon').style.transform='scale(1)';
}, 100);
setTimeout(function(){
    document.getElementById('congrats-title').style.opacity='1';
    document.getElementById('congrats-title').style.transform='translateY(0)';
}, 300);
setTimeout(function(){
    document.getElementById('congrats-name').style.opacity='1';
    document.getElementById('congrats-name').style.transform='translateY(0)';
}, 500);
setTimeout(function(){
    document.getElementById('congrats-role').style.opacity='1';
    document.getElementById('congrats-role').style.transform='translateY(0)';
}, 700);
setTimeout(function(){
    document.getElementById('congrats-msg').style.opacity='1';
    document.getElementById('congrats-msg').style.transform='translateY(0)';
}, 900);
setTimeout(function(){
    document.getElementById('congrats-progress').style.opacity='1';
    document.getElementById('redirect-progress').style.width='100%';
}, 1000);
setTimeout(function(){
    document.getElementById('congrats-btn').style.opacity='1';
}, 1200);

// Auto redirect setelah 3 detik
setTimeout(function(){
    window.location.href = '<?= e($login_redirect) ?>';
}, 3000);

// Confetti effect
(function(){
    var colors = ['#0d6efd','#198754','#ffc107','#dc3545','#0dcaf0','#6f42c1','#d63384'];
    for(var i=0;i<40;i++){
        (function(i){
            setTimeout(function(){
                var confetti = document.createElement('div');
                confetti.style.cssText = 'position:fixed;width:'+(Math.random()*8+4)+'px;height:'+(Math.random()*8+4)+'px;background:'+colors[Math.floor(Math.random()*colors.length)]+';left:'+(Math.random()*100)+'vw;top:-10px;border-radius:'+(Math.random()>0.5?'50%':'2px')+';z-index:9999;pointer-events:none;opacity:0.9;';
                document.body.appendChild(confetti);
                var x = parseFloat(confetti.style.left);
                var y = -10;
                var speed = Math.random()*2+1;
                var drift = (Math.random()-0.5)*3;
                var rot = 0;
                var rotSpeed = (Math.random()-0.5)*10;
                function fall(){
                    y += speed;
                    x += drift;
                    rot += rotSpeed;
                    confetti.style.top = y+'px';
                    confetti.style.left = x+'px';
                    confetti.style.transform = 'rotate('+rot+'deg)';
                    confetti.style.opacity = Math.max(0, 1 - y/window.innerHeight);
                    if(y < window.innerHeight + 20){
                        requestAnimationFrame(fall);
                    } else {
                        confetti.remove();
                    }
                }
                requestAnimationFrame(fall);
            }, i*50);
        })(i);
    }
})();
</script>

<?php else: ?>
<!-- ============ LOGIN FORM ============ -->
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card shadow border-0">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <i class="bi bi-book-half display-4 text-primary"></i>
                        <h3 class="fw-bold mt-2">Masuk ke Akun</h3>
                        <p class="text-muted">Masuk untuk meminjam buku dan mengakses fitur perpustakaan</p>
                    </div>
                    <?php if ($error): ?>
                    <div class="alert alert-danger"><i class="bi bi-exclamation-circle me-2"></i><?= e($error) ?></div>
                    <?php endif; ?>
                    <form method="POST" action="" novalidate>
                        <?= csrf_field() ?>
                        <div class="mb-3">
                            <label for="email" class="form-label fw-semibold">Email / Name</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                <input type="text" class="form-control" id="email" name="email" placeholder="Email atau nama" required value="<?= e($_POST['email'] ?? '') ?>">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label fw-semibold">Password</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                <input type="password" class="form-control" id="password" name="password" placeholder="Masukkan password" required>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">
                            <i class="bi bi-box-arrow-in-right me-2"></i>Masuk
                        </button>
                    </form>
                    <div class="text-center mt-4">
                        <p class="text-muted mb-0">Belum punya akun? <a href="register.php" class="text-primary fw-semibold">Daftar Sekarang</a></p>
                    </div>
                    <div class="mt-4 p-3 bg-light rounded">
                        <small class="text-muted">
                            <strong>Akun Demo:</strong><br>
                            Admin: admin@perpustakaan.id / admin123<br>
                            Petugas: petugas@perpustakaan.id / admin123<br>
                            Anggota: budi@email.com / admin123
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/partials/footer.php'; ?>
