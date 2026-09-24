# Perpustakaan Desa v2.0

> **Sistem Informasi Perpustakaan Desa** — Aplikasi Peminjaman Buku Berbasis Web  
> **UKK 2026 — Rekayasa Perangkat Lunak (RPL)** | Tahun Pelajaran 2025/2026

---

## 1. Deskripsi Proyek

**Perpustakaan Desa** adalah aplikasi web berbasis PHP Native (tanpa framework) yang dirancang untuk memudahkan warga desa dan pengelola perpustakaan dalam mengelola peminjaman dan pendataan buku secara digital. Aplikasi ini mendukung tiga peran pengguna: **Admin**, **Petugas**, dan **Anggota** dengan fitur lengkap mulai dari manajemen data buku, peminjaman, pengembalian, hingga pelaporan.

Aplikasi dikembangkan sesuai **102 butir checklist UKK 2026** untuk kompetensi Pengembangan Aplikasi Peminjaman Buku, memenuhi standar keamanan (CSRF, XSS, SQL Injection), dan menerapkan konsep database lanjutan (Stored Procedure, Function, Trigger, View, Transaction).

---

## 2. Tech Stack

| Komponen | Teknologi | Versi |
|----------|-----------|-------|
| **Backend** | PHP Native (tanpa framework) | 8.0+ |
| **Database** | MySQL / MariaDB | 5.7+ / 10.3+ |
| **DB Driver** | mysqli (Procedural, Prepared Statement) | — |
| **CSS Framework** | Bootstrap | 5.3.3 |
| **Icon Library** | Bootstrap Icons | 1.11.3 |
| **Chart Library** | Chart.js | 4.x (CDN) |
| **Font** | Inter (Google Fonts) | Weights 300-800 |
| **JavaScript** | Vanilla JS + Bootstrap JS | — |
| **Web Server** | Apache (XAMPP) | — |
| **Password Hashing** | bcrypt (`password_hash`) | — |

---

## 3. Cara Instalasi (Step-by-Step XAMPP)

### Prasyarat
- [XAMPP](https://www.apachefriends.org/) sudah terinstal (Apache + MySQL)
- Browser modern (Chrome, Firefox, Edge)
- Editor kode (VS Code recommended)

### Langkah Instalasi

1. **Hidupkan XAMPP**  
   Buka XAMPP Control Panel, klik **Start** pada Apache dan MySQL.

2. **Clone / Salin Project**  
   Salin seluruh folder `perpustakaan-desa` ke dalam:  
   ```
   C:\xampp\htdocs\perpustakaan-desa\
   ```

3. **Buat Database**  
   - Buka browser, akses `http://localhost/phpmyadmin`
   - Klik tab **Import**
   - Pilih file `database.sql` dari folder project
   - Klik **Go** (Execute)
   - Database `perpustakaan_desa` beserta tabel, stored procedure, function, trigger, dan data awal akan otomatis terbuat

4. **Konfigurasi Koneksi** (Opsional)  
   Buka file `koneksi.php`, sesuaikan jika diperlukan:
   ```php
   $DB_HOST = 'localhost';
   $DB_USER = 'root';
   $DB_PASS = '';        // Default XAMPP kosong
   $DB_NAME = 'perpustakaan_desa';
   ```

5. **Akses Aplikasi**  
   Buka browser:  
   ```
   http://localhost/perpustakaan-desa/
   ```

6. **Login dengan Akun Demo** (lihat tabel di bawah)

---

## 4. Struktur Folder Lengkap

```
perpustakaan-desa/
│
├── config.js                        # Konfigurasi JavaScript (UKK 68)
├── data.json                        # Data konfigurasi sampel (UKK 71)
├── database.sql                     # Schema DB, SP, Function, Trigger, VIEW, Seed Data
├── config.php                       # Helper: session, auth, CSRF, format, upload, logging
├── koneksi.php                       # Koneksi database mysqli procedural
├── index.php                         # Router utama → include header + home + footer
├── home.php                          # Konten beranda (hero, statistik, grafik, buku populer)
├── login.php                         # Form login multi-role (admin/petugas/anggota)
├── register.php                       # Form registrasi anggota baru (validasi NIK)
├── logout.php                         # Hapus session & redirect
├── katalog_buku.php                   # Katalog buku + filter kategori + search + pinjam
├── detail_buku.php                    # Detail buku + komentar + rating + tombol pinjam
├── cari.php                           # Hasil pencarian buku
├── pinjam.php                         # Form peminjaman buku (anggota)
├── proses_pinjam.php                  # Proses peminjaman via Stored Procedure (commit/rollback)
├── pengembalian.php                   # Pengembalian buku + riwayat + request kembali
├── struk.php                          # Bukti peminjaman (printable)
├── tambah.php                         # Form tambah buku (admin)
├── proses_tambah.php                  # Proses insert buku (admin)
├── akun.php                           # Profil anggota + riwayat peminjaman
├── help.php                           # Halaman bantuan / FAQ
├── newsletter.php                     # Endpoint newsletter subscription
│
├── assets/
│   ├── css/
│   │   └── style.css                  # Custom CSS (tema perpustakaan, variabel CSS, sidebar)
│   ├── js/
│   │   └── main.js                    # JS: form validation, search debounce, sidebar toggle
│   ├── sound/
│   │   └── notif.mp3                  # Audio notifikasi suara (UKK 10)
│   └── icon/
│       └── favicon.svg                # Ikon aplikasi SVG
│
├── partials/
│   ├── header.php                     # Navbar + Bootstrap + Google Fonts + flash message
│   ├── footer.php                     # Footer + newsletter + social links
│   └── book_card.php                  # Komponen kartu buku (reusable)
│
├── admin/
│   ├── _layout.php                    # Layout admin: sidebar + topbar + auth check
│   ├── _footer.php                    # Footer admin + Bootstrap JS + Chart.js
│   ├── index.php                      # Dashboard admin (statistik + grafik)
│   ├── petugas_dashboard.php         # Dashboard petugas
│   ├── buku.php                       # CRUD data buku (tambah, edit, hapus, search, filter)
│   ├── edit_buku.php                  # Form edit buku
│   ├── hapus_buku.php                 # Proses hapus buku (prepared statement)
│   ├── kategori.php                   # CRUD kategori buku
│   ├── penerbit.php                   # CRUD penerbit
│   ├── anggota.php                    # CRUD anggota + manajemen role
│   ├── peminjaman.php                 # Data peminjaman (VIEW, SP kembalikan, commit/rollback)
│   ├── laporan.php                    # Laporan & grafik statistik
│   ├── berita.php                     # CRUD berita
│   ├── pengumuman.php                 # CRUD pengumuman
│   ├── pengaturan.php                 # Pengaturan sistem (key-value)
│   └── user_online.php                 # Monitoring user yang sedang online
│
├── dokumentasi/
│   ├── erd.md                         # Dokumentasi ERD (Entity Relationship Diagram)
│   ├── flowchart.md                   # Dokumentasi Flowchart alur sistem
│   ├── algoritma.md                   # Dokumentasi Algoritma & Pseudocode
│   └── wireframe.md                   # Dokumentasi Wireframe desain UI
│
├── pengujian/
│   ├── test_case.md                   # Test Case lengkap (20+ kasus)
│   ├── skenario_pengujian.md          # Skenario pengujian sistem
│   ├── data_uji.md                    # Dokumentasi data uji / seed data
│   └── evaluasi.md                     # Evaluasi diri checklist UKK 102 butir
│
├── gambar/                            # Gambar cover buku (bk1.jpg - bk11.jpg, bk1.png)
├── uploads/                           # Folder upload gambar buku (generated)
├── logs/                              # Log error & aktivitas (auto-generated)
└── README.md                          # File ini — Dokumentasi deployment (UKK 67-80)
```

---

## 5. Penjelasan Singkat Setiap File / Modul

### File Konfigurasi & Koneksi
| File | Penjelasan |
|------|-----------|
| `koneksi.php` | Koneksi ke MySQL via mysqli procedural. Menggunakan `mysqli_init()`, `mysqli_options()`, dan `mysqli_real_connect()`. Charset utf8mb4. Error handling dengan pesan user-friendly. |
| `config.php` | Helper utama: session management (secure cookie params), constant APP_NAME/VERSION, fungsi auth (`is_logged_in`, `is_admin`, `require_login`), CSRF protection (`csrf_token`, `csrf_verify`), format helper (`e()`, `format_rupiah`, `format_tanggal`), error logging ke file + DB, flash message, upload gambar aman. |
| `config.js` | Konfigurasi JavaScript untuk frontend (nama app, max pinjam, denda). |
| `data.json` | Data konfigurasi sampel dalam format JSON. |

### Halaman Publik (User-Facing)
| File | Penjelasan |
|------|-----------|
| `index.php` | Router utama. Include header, home, dan footer. |
| `home.php` | Beranda: hero section, statistik (SELECT COUNT), kategori (GROUP BY), grafik Chart.js (peminjaman per kategori), buku populer, buku baru, video, pengumuman, notifikasi suara. |
| `login.php` | Autentikasi multi-role. Validasi email + password (bcrypt), redirect berdasarkan role, session tracking, logging. |
| `register.php` | Registrasi anggota: validasi NIK 16 digit, nama, email (filter_var), password (min 6 karakter), cek duplikat, INSERT transaksional. |
| `katalog_buku.php` | Katalog lengkap: filter kategori, penerbit, search, sorting, tombol pinjam. |
| `detail_buku.php` | Detail buku: sinopsis, info lengkap, komentar & rating, tombol pinjam. |
| `cari.php` | Pencarian buku berdasarkan judul, ISBN, penulis. |
| `pinjam.php` | Form peminjaman buku oleh anggota. |
| `proses_pinjam.php` | Proses peminjaman via Stored Procedure `sp_pinjam_buku` dengan transaction commit/rollback. |
| `pengembalian.php` | Daftar buku dipinjam + request kembalikan + riwayat pengembalian + perhitungan denda via Function. |
| `struk.php` | Bukti peminjaman yang dapat dicetak (print-friendly). |
| `akun.php` | Profil pengguna + edit profil + riwayat peminjaman. |
| `logout.php` | Unregister session, hapus session, redirect ke beranda. |

### Panel Admin (Backend)
| File | Penjelasan |
|------|-----------|
| `admin/_layout.php` | Layout admin: sidebar navigasi dengan icon (Bootstrap Icons), topbar, auth check, flash message. |
| `admin/index.php` | Dashboard admin: statistik (total buku, anggota, dipinjam, terlambat, dikembalikan), peminjaman terbaru, buku populer, stok menipis. |
| `admin/petugas_dashboard.php` | Dashboard petugas: ringkasan peminjaman, buku baru, daftar peminjaman aktif. |
| `admin/buku.php` | CRUD buku: tambah, edit, hapus, search, filter kategori/penerbit, upload gambar. |
| `admin/edit_buku.php` | Form edit buku (populated dari SELECT WHERE). |
| `admin/hapus_buku.php` | Proses hapus buku via prepared statement. |
| `admin/kategori.php` | CRUD kategori buku (INSERT, UPDATE, DELETE). |
| `admin/penerbit.php` | CRUD penerbit buku. |
| `admin/anggota.php` | CRUD anggota + manajemen role (admin/petugas/anggota) + toggle aktif/nonaktif. |
| `admin/peminjaman.php` | Data peminjaman via VIEW `v_peminjaman_detail`, filter status, search, approve pengembalian via SP `sp_kembalikan_buku`, hapus peminjaman dengan transaction. |
| `admin/laporan.php` | Laporan statistik dengan grafik Chart.js. |
| `admin/berita.php` | CRUD konten berita. |
| `admin/pengumuman.php` | CRUD pengumuman (info/penting/darurat). |
| `admin/pengaturan.php` | Pengaturan sistem dinamis (key-value: max_pinjam, denda_per_hari, lama_pinjam). |
| `admin/user_online.php` | Monitoring user yang sedang online (dari tabel user_sessions). |

### Partial / Komponen Reusable
| File | Penjelasan |
|------|-----------|
| `partials/header.php` | Navbar responsif, Bootstrap CSS + Icons + Inter font, flash message dengan notifikasi suara. |
| `partials/footer.php` | Footer, form newsletter, social links, copyright. |
| `partials/book_card.php` | Komponen kartu buku yang digunakan di beranda dan katalog. |

---

## 6. Akun Demo

| Role | Email | Password | NIK |
|------|-------|----------|-----|
| 🔑 **Admin** | admin@perpustakaan.id | admin123 | 3201010101010001 |
| 📋 **Petugas** | petugas@perpustakaan.id | admin123 | 3201010101010002 |
| 👤 **Anggota** | budi@email.com | admin123 | 3201010101010003 |
| 👤 **Anggota** | siti@email.com | admin123 | 3201010101010004 |
| 👤 **Anggota** | ahmad@email.com | admin123 | 3201010101010005 |

---

## 7. Database Overview

**Nama Database:** `perpustakaan_desa`  
**Charset:** `utf8mb4_unicode_ci`

### Tabel Utama
| Tabel | Deskripsi | Jumlah Kolom |
|-------|-----------|:---:|
| `pengaturan` | Konfigurasi sistem (key-value) | 4 |
| `kategori_buku` | Kategori buku (Fiksi, Sains, dll) | 5 |
| `penerbit` | Data penerbit | 5 |
| `buku` | Data buku perpustakaan | 18 |
| `anggota` | Data anggota & admin | 9 |
| `peminjaman` | Transaksi peminjaman buku | 14 |
| `pengumuman` | Pengumuman sistem | 7 |
| `berita` | Konten berita | 8 |
| `komentar_buku` | Komentar & rating buku | 7 |
| `user_sessions` | Tracking user online | 8 |
| `aktivitas_log` | Audit trail aktivitas | 7 |
| `error_log` | Log error sistem | 8 |

### Database Lanjutan
| Jenis | Nama | Deskripsi |
|-------|------|-----------|
| Stored Procedure | `sp_hitung_dipinjam` | Hitung jumlah buku yang sedang dipinjam anggota |
| Stored Procedure | `sp_pinjam_buku` | Proses peminjaman buku (transaksional) |
| Stored Procedure | `sp_hitung_denda` | Hitung denda keterlambatan |
| Stored Procedure | `sp_kembalikan_buku` | Proses pengembalian buku (transaksional) |
| Stored Procedure | `sp_dashboard_statistik` | Statistik ringkasan dashboard |
| Stored Procedure | `sp_buku_populer` | Buku terpopuler berdasarkan total pinjam |
| Function | `fn_selisih_hari` | Menghitung selisih 2 tanggal |
| Function | `fn_format_rupiah` | Format angka ke Rupiah |
| Function | `fn_buku_tersedia` | Cek ketersediaan buku |
| Function | `fn_hitung_denda` | Hitung denda berdasarkan tanggal kembali |
| Trigger | `trg_peminjaman_after_insert` | Log aktivitas saat peminjaman baru |
| Trigger | `trg_peminjaman_after_update` | Log aktivitas saat status peminjaman berubah |
| Trigger | `trg_error_log_after_insert` | Log error masuk ke sistem |
| VIEW | `v_peminjaman_detail` | Gabungan data peminjaman + buku + anggota + denda |

---

## 8. Keamanan

- **CSRF Protection** — Token CSRF pada semua form POST (`csrf_token`, `csrf_field`, `csrf_verify`)
- **XSS Prevention** — Fungsi `e()` (htmlspecialchars) pada semua output
- **SQL Injection Prevention** — Prepared Statement (`mysqli_prepare`, `mysqli_stmt_bind_param`) pada semua query
- **Password Hashing** — bcrypt via `password_hash()` / `password_verify()`
- **Session Security** — Secure cookie params (httponly, samesite=Lax), session tracking
- **Upload Security** — Validasi MIME type (finfo), ukuran maks 2MB, random filename
- **Error Handling** — Custom exception handler, log error ke file + database
- **Security Headers** — X-Content-Type-Options, X-Frame-Options, X-XSS-Protection

---

## 9. Link Deployment

| Lingkungan | URL |
|-----------|-----|
| **Lokal (XAMPP)** | `http://localhost/perpustakaan-desa/` |
| **phpMyAdmin** | `http://localhost/phpmyadmin` |
| **Login** | `http://localhost/perpustakaan-desa/login.php` |
| **Admin Panel** | `http://localhost/perpustakaan-desa/admin/index.php` |
| **Katalog Buku** | `http://localhost/perpustakaan-desa/katalog_buku.php` |

---

## 10. Dokumentasi Pendukung

| Dokumen | Lokasi | Deskripsi |
|---------|--------|-----------|
| ERD | `dokumentasi/erd.md` | Entity Relationship Diagram seluruh tabel |
| Flowchart | `dokumentasi/flowchart.md` | Flowchart alur login, pinjam, kembalikan |
| Algoritma | `dokumentasi/algoritma.md` | Pseudocode untuk validasi login, pinjam, denda |
| Wireframe | `dokumentasi/wireframe.md` | Wireframe ASCII setiap halaman |
| Test Case | `pengujian/test_case.md` | 20+ test case lengkap |
| Skenario Pengujian | `pengujian/skenario_pengujian.md` | Skenario pengujian per modul |
| Data Uji | `pengujian/data_uji.md` | Dokumentasi seed data & data uji |
| Evaluasi | `pengujian/evaluasi.md` | Evaluasi diri 102 butir UKK |

---

## 11. Debugging

Jika terjadi error:

1. Pastikan Apache dan MySQL berjalan di XAMPP Control Panel
2. Pastikan database `perpustakaan_desa` sudah di-import di phpMyAdmin
3. Cek koneksi database di `koneksi.php` (host, user, password)
4. Cek log error: `logs/error_YYYY-MM-DD.log`
5. Cek log aktivitas: `logs/aktivitas_YYYY-MM-DD.log`
6. Pastikan folder `uploads/` dan `logs/` writable (CHMOD 775)

---

## 12. Lisensi

Proyek ini dikembangkan untuk keperluan **Ujian Kompetensi Keahlian (UKK) 2026** — Kompetensi Rekayasa Perangkat Lunak (RPL).

---

*Perpustakaan Desa v2.0 — UKK 2026 RPL*
