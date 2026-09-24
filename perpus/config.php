<?php
/**
 * config.php - Perpustakaan Desa v2 (UKK 2026)
 * Helper umum: session, security, role, pengaturan, logging, dll.
 */
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

require_once __DIR__ . '/koneksi.php';

/* ============ CONSTANTS ============ */
define('APP_NAME', 'Perpustakaan Desa');
define('APP_VERSION', '2.0');
define('APP_URL',  'http://localhost/perpustakaan-desa');
define('UPLOAD_DIR', __DIR__ . '/uploads');
define('UPLOAD_URL', 'uploads');
define('MAX_UPLOAD_SIZE', 2 * 1024 * 1024);
define('LOG_DIR', __DIR__ . '/logs');

/* ============ ERROR LOGGING TO FILE ============ */
function log_error(string $message, string $level = 'ERROR', string $file = '', int $line = 0): void
{
    // Log ke file
    if (!is_dir(LOG_DIR)) mkdir(LOG_DIR, 0775, true);
    $date = date('Y-m-d H:i:s');
    $file_line = $file ? "[$file:$line] " : '';
    $log_entry = "[$date] [$level] $file_line$message" . PHP_EOL;
    file_put_contents(LOG_DIR . '/error_' . date('Y-m-d') . '.log', $log_entry, FILE_APPEND);

    // Log ke database
    try {
        global $koneksi;
        $stmt = mysqli_prepare($koneksi, "INSERT INTO error_log (level, file_source, line_number, message, ip_address) VALUES (?,?,?,?,?)");
        $ip = $_SERVER['REMOTE_ADDR'] ?? '';
        mysqli_stmt_bind_param($stmt, 'ssiss', $level, $file, $line, $message, $ip);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    } catch (Throwable $e) {
        // Silent fail if DB log fails
    }
}

/* ============ EXCEPTION HANDLER ============ */
set_exception_handler(function (Throwable $e) {
    log_error($e->getMessage(), 'FATAL', $e->getFile(), $e->getLine());
    die('<div style="font-family:Arial;padding:40px;text-align:center;max-width:600px;margin:40px auto">
        <h2 style="color:#dc3545">Terjadi Kesalahan Sistem</h2>
        <p>Error telah dicatat. Silakan coba lagi nanti atau hubungi admin.</p>
        <a href="index.php" class="btn btn-primary">Kembali ke Beranda</a></div>');
});

/* ============ PENGATURAN DINAMIS ============ */
function get_setting(mysqli $koneksi, string $key, string $default = ''): string
{
    static $cache = [];
    if (isset($cache[$key])) return $cache[$key];
    try {
        $stmt = mysqli_prepare($koneksi, "SELECT nilai FROM pengaturan WHERE kunci = ?");
        mysqli_stmt_bind_param($stmt, 's', $key);
        mysqli_stmt_execute($stmt);
        $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
        mysqli_stmt_close($stmt);
        $val = $row ? $row['nilai'] : $default;
    } catch (Throwable $e) {
        $val = $default;
        log_error('Gagal ambil setting: ' . $key . ' - ' . $e->getMessage());
    }
    $cache[$key] = $val;
    return $val;
}

function get_max_pinjam(mysqli $koneksi): int
{
    return (int) get_setting($koneksi, 'max_pinjam', '2');
}

function get_denda_per_hari(mysqli $koneksi): int
{
    return (int) get_setting($koneksi, 'denda_per_hari', '5000');
}

function get_lama_pinjam(mysqli $koneksi): int
{
    return (int) get_setting($koneksi, 'lama_pinjam', '14');
}

/* ============ FORMAT HELPERS ============ */
function e($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

function format_rupiah(int $nilai): string
{
    return 'Rp ' . number_format($nilai, 0, ',', '.');
}

function format_tanggal(string $date): string
{
    if (!$date || $date === '-' || $date === '0000-00-00') return '-';
    $bln = [1=>'Januari','Februari','Maret','April','Mei','Juni',
            'Juli','Agustus','September','Oktober','November','Desember'];
    $ts = strtotime($date);
    if ($ts === false) return $date;
    return date('d', $ts).' '.$bln[(int)date('n', $ts)].' '.date('Y', $ts);
}

function tgl_id(string $date): string
{
    if (!$date || $date === '0000-00-00') return '-';
    $ts = strtotime($date);
    if ($ts === false) return $date;
    return date('d M Y', $ts);
}

function selisih_hari(string $date1, string $date2): int
{
    // Percabangan: cek validitas tanggal
    $d1 = new DateTime($date1);
    $d2 = new DateTime($date2);
    return (int) $d2->diff($d1)->days;  
}

/* ============ CSRF ============ */
function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . csrf_token() . '">';
}

function csrf_verify(): bool
{
    $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    if (!is_string($token) || $token === '') return false;
    return hash_equals($_SESSION['csrf_token'] ?? '', $token);
}

/* ============ AUTH & ROLE (3 ROLE: admin, petugas, anggota) ============ */
function is_logged_in(): bool
{
    return !empty($_SESSION['id_anggota']);
}

function is_admin(): bool
{
    return ($_SESSION['role'] ?? '') === 'admin';
}

function is_petugas(): bool
{
    return ($_SESSION['role'] ?? '') === 'petugas';
}

function is_anggota(): bool
{
    return ($_SESSION['role'] ?? '') === 'anggota';
}

function is_admin_or_petugas(): bool
{
    $r = $_SESSION['role'] ?? '';
    return $r === 'admin' || $r === 'petugas';
}

function require_login(): void
{
    if (!is_logged_in()) {
        header('Location: login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
        exit;
    }
}

function require_admin(): void
{
    require_login();
    if (!is_admin()) {
        http_response_code(403);
        log_error('Akses ditolak - bukan admin. User: ' . ($_SESSION['nama_lengkap'] ?? 'guest'));
        die('<div style="font-family:Arial;padding:40px;text-align:center">
          <h2>403 - Akses Ditolak</h2><p>Halaman ini khusus administrator.</p>
          <a href="../index.php">Kembali ke Beranda</a></div>');
    }
}

function require_petugas(): void
{
    require_login();
    if (!is_petugas() && !is_admin()) {
        http_response_code(403);
        log_error('Akses ditolak - bukan petugas. User: ' . ($_SESSION['nama_lengkap'] ?? 'guest'));
        die('<div style="font-family:Arial;padding:40px;text-align:center">
          <h2>403 - Akses Ditolak</h2><p>Halaman ini khusus petugas dan admin.</p>
          <a href="../index.php">Kembali ke Beranda</a></div>');
    }
}

function require_admin_or_petugas(): void
{
    require_login();
    if (!is_admin_or_petugas()) {
        http_response_code(403);
        log_error('Akses ditolak - bukan admin/petugas. User: ' . ($_SESSION['nama_lengkap'] ?? 'guest'));
        die('<div style="font-family:Arial;padding:40px;text-align:center">
          <h2>403 - Akses Ditolak</h2><p>Halaman ini khusus admin dan petugas.</p>
          <a href="../index.php">Kembali ke Beranda</a></div>');
    }
}

function current_user(): ?array
{
    if (!is_logged_in()) return null;
    return [
        'id'    => $_SESSION['id_anggota'],
        'nama'  => $_SESSION['nama_lengkap'] ?? 'User',
        'email' => $_SESSION['email'] ?? '',
        'role'  => $_SESSION['role'] ?? 'anggota',
    ];
}

/* ============ SESSION TRACKING ============ */
function register_session(mysqli $koneksi): void
{
    if (!is_logged_in()) return;
    $sid = session_id();
    mysqli_query($koneksi, "DELETE FROM user_sessions WHERE id_anggota = " . (int)$_SESSION['id_anggota']);
    $stmt = mysqli_prepare($koneksi, "INSERT INTO user_sessions (session_id, id_anggota, nama_lengkap, role, ip_address, user_agent) VALUES (?,?,?,?,?,?)");
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
    mysqli_stmt_bind_param($stmt, 'sisssss', $sid, $_SESSION['id_anggota'], $_SESSION['nama_lengkap'], $_SESSION['role'], $ip, $ua);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

function unregister_session(mysqli $koneksi): void
{
    $sid = session_id();
    mysqli_query($koneksi, "DELETE FROM user_sessions WHERE session_id = '" . mysqli_real_escape_string($koneksi, $sid) . "'");
}

function cleanup_old_sessions(mysqli $koneksi): void
{
    mysqli_query($koneksi, "DELETE FROM user_sessions WHERE last_activity < NOW() - INTERVAL 2 HOUR");
}

/* ============ AKTIVITAS LOG (FILE + DB) ============ */
function log_aktivitas(mysqli $koneksi, string $aksi, string $detail = ''): void
{
    $stmt = mysqli_prepare($koneksi, "INSERT INTO aktivitas_log (id_anggota, nama_user, aksi, detail, ip_address) VALUES (?,?,?,?,?)");
    $id = $_SESSION['id_anggota'] ?? null;
    $nama = $_SESSION['nama_lengkap'] ?? 'Guest';
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    mysqli_stmt_bind_param($stmt, 'issss', $id, $nama, $aksi, $detail, $ip);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    // Juga log ke file
    if (!is_dir(LOG_DIR)) mkdir(LOG_DIR, 0775, true);
    $log_line = date('Y-m-d H:i:s') . " [$aksi] $nama - $detail (IP: $ip)" . PHP_EOL;
    file_put_contents(LOG_DIR . '/aktivitas_' . date('Y-m-d') . '.log', $log_line, FILE_APPEND);
}

/* ============ FLASH MESSAGE ============ */
function set_flash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function get_flash(): ?array
{
    if (!empty($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/* ============ REDIRECT ============ */
function redirect(string $url): void
{
    header("Location: $url");
    exit;
}

/* ============ SAFE UPLOAD (FILE ACCESS) ============ */
function upload_gambar(array $file, string $prefix = 'buku'): ?string
{
    if (empty($file['name']) || $file['error'] === UPLOAD_ERR_NO_FILE) return null;
    if ($file['error'] !== UPLOAD_ERR_OK) throw new RuntimeException('Upload error: ' . $file['error']);
    if ($file['size'] > MAX_UPLOAD_SIZE) throw new RuntimeException('Ukuran file maksimal 2MB.');
    $allowed = ['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp'];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    if (!isset($allowed[$mime])) throw new RuntimeException('Format tidak didukung. Gunakan JPG/PNG/WEBP.');
    if (!is_dir(UPLOAD_DIR)) mkdir(UPLOAD_DIR, 0775, true);
    $ext = $allowed[$mime];
    $name = $prefix.'-'.date('Ymd-His').'-'.bin2hex(random_bytes(4)).'.'.$ext;
    $dest = UPLOAD_DIR.'/'.$name;
    if (!move_uploaded_file($file['tmp_name'], $dest)) throw new RuntimeException('Gagal upload.');
    return UPLOAD_URL.'/'.$name;
}

/* ============ GENERATE KODE ============ */
function generate_kode_peminjaman(mysqli $koneksi): string
{
    $prefix = 'PMJ-'.date('Ymd-');
    $stmt = mysqli_prepare($koneksi, "SELECT COUNT(*) AS cnt FROM peminjaman WHERE kode_peminjaman LIKE ? AND tanggal_pinjam = CURDATE()");
    $like = $prefix.'%';
    mysqli_stmt_bind_param($stmt, 's', $like);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($res);
    mysqli_stmt_close($stmt);
    // Percabangan: handle counter
    return $prefix . str_pad((string)(((int)$row['cnt'])+1), 3, '0', STR_PAD_LEFT);
}

/* ============ HELPER QUERY ============ */
function fetch_all_assoc($result): array
{
    // Menggunakan perulangan while
    $rows = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $rows[] = $row;
    }
    return $rows;
}

/* ============ BUKU HELPERS ============ */
function buku_tersedia(mysqli $koneksi, int $id_buku): bool
{
    $stmt = mysqli_prepare($koneksi, "SELECT stok FROM buku WHERE id_buku=?");
    mysqli_stmt_bind_param($stmt, 'i', $id_buku);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $stok);
    mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);
    return $stok > 0;
}

/**
 * Menggunakan STORED PROCEDURE: sp_hitung_dipinjam
 */
function hitung_buku_dipinjam(mysqli $koneksi, int $id_anggota): int
{
    $stmt = mysqli_prepare($koneksi, "CALL sp_hitung_dipinjam(?, @total)");
    mysqli_stmt_bind_param($stmt, 'i', $id_anggota);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    // Bersihkan result set tambahan dari Stored Procedure (mencegah "Commands out of sync")
    while (mysqli_more_results($koneksi) && mysqli_next_result($koneksi)) {}
    $res = mysqli_query($koneksi, "SELECT @total AS total");
    $row = mysqli_fetch_assoc($res);
    return (int)($row['total'] ?? 0);
}

/**
 * Menggunakan FUNCTION: fn_hitung_denda
 */
function hitung_denda_db(mysqli $koneksi, string $tanggal_kembali): int
{
    $res = mysqli_query($koneksi, "SELECT fn_hitung_denda('$tanggal_kembali') AS denda");
    $row = mysqli_fetch_assoc($res);
    return (int)($row['denda'] ?? 0);
}

function hitung_denda(mysqli $koneksi, string $tanggal_kembali): int
{
    // Percabangan: cek apakah function DB tersedia
    try {
        return hitung_denda_db($koneksi, $tanggal_kembali);
    } catch (Throwable $e) {
        // Fallback: hitung manual dengan array dan perulangan
        $denda_per_hari = get_denda_per_hari($koneksi);
        $selisih = selisih_hari($tanggal_kembali, date('Y-m-d'));
        if ($selisih <= 0) return 0;
        return $selisih * $denda_per_hari;
    }
}

/**
 * Menggunakan FUNCTION: fn_selisih_hari
 */
function selisih_hari_db(mysqli $koneksi, string $date1, string $date2): int
{
    $res = mysqli_query($koneksi, "SELECT fn_selisih_hari('$date1', '$date2') AS selisih");
    $row = mysqli_fetch_assoc($res);
    return (int)($row['selisih'] ?? 0);
}
