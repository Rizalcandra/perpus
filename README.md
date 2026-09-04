# perpustakaan-desa

├── admin/ # Panel Admin & Petugas
│ ├── _footer.php # Footer layout admin
│ ├── _layout.php # Layout utama admin (sidebar + header)
│ ├── anggota.php # Kelola data anggota
│ ├── berita.php # Kelola berita/artikel
│ ├── buku.php # Kelola data buku (CRUD)
│ ├── edit_buku.php # Form tambah/edit buku
│ ├── hapus_buku.php # Proses hapus buku
│ ├── index.php # Dashboard admin
│ ├── kategori.php # Kelola kategori buku
│ ├── laporan.php # Laporan & cetak laporan
│ ├── peminjaman.php # Kelola peminjaman & pengembalian
│ ├── penerbit.php # Kelola data penerbit
│ ├── pengaturan.php # Pengaturan sistem (denda, max pinjam)
│ ├── pengumuman.php # Kelola pengumuman
│ ├── petugas_dashboard.php # Dashboard petugas
│ └── user_online.php # Monitoring user online
│
├── assets/ # File statis
│ ├── css/
│ │ └── style.css # Custom stylesheet
│ ├── icon/
│ │ └── favicon.svg # Favicon website
│ ├── img/ # Gambar umum (kosong)
│ ├── js/
│ │ └── main.js # Custom JavaScript
│ ├── pict/ # Gambar tambahan (kosong)
│ ├── sound/
│ │ └── notif.mp3 # Suara notifikasi
│ └── video/ # Video (kosong)
│
├── dokumentasi/ # Dokumentasi project
│ ├── algoritma.md # Dokumentasi algoritma
│ ├── erd.md # ERD (Entity Relationship Diagram)
│ ├── flowchart.md # Flowchart sistem
│ └── wireframe.md # Wireframe halaman
│
├── gambar/ # Cover gambar buku
│ └── .gitkeep
│
├── logs/ # Log aktivitas & error
│ ├── aktivitas_2026-08-26.log
│ ├── aktivitas_2026-08-27.log
│ └── error_2026-08-26.log
│
├── partials/ # Komponen template (reusable)
│ ├── book_card.php # Card buku (katalog & beranda)
│ ├── footer.php # Footer publik
│ └── header.php # Header & navbar publik
│
├── pengujian/ # Dokumentasi pengujian
│ ├── data_uji.md # Data untuk pengujian
│ ├── evaluasi.md # Hasil evaluasi
│ ├── skenario_pengujian.md # Skenario pengujian
│ └── test_case.md # Daftar test case
│
├── .htaccess # Konfigurasi Apache
├── config.js # Konfigurasi frontend
├── config.php # Helper: session, CSRF, auth, upload
├── data.json # Data statis frontend
├── database.sql # Schema & seed data database
├── koneksi.php # Koneksi database (mysqli)
│
├── index.php # Router utama → Beranda
├── home.php # Konten beranda (hero, statistik, grafik)
├── katalog_buku.php # Katalog buku (filter, sort, paginasi)
├── detail_buku.php # Detail buku + komentar + rating
├── cari.php # Pencarian buku
├── pinjam.php # Form pinjam buku
├── pengembalian.php # Form pengembalian buku
├── tambah.php # Form tambah buku (publik)
├── struk.php # Cetak struk peminjaman
├── akun.php # Profil & akun anggota
├── help.php # Halaman bantuan
├── newsletter.php # Berlangganan newsletter
│
├── login.php # Login user
├── logout.php # Logout user
├── register.php # Registrasi anggota baru
├── proses_pinjam.php # Proses peminjaman buku
└── proses_tambah.php # Proses tambah buku (upload gambar)


## 🔧 Teknologi

| Komponen | Teknologi |
|----------|-----------|
| Backend | PHP 8+ (Native) |
| Database | MySQL (mysqli) |
| Frontend | Bootstrap 5.3, Bootstrap Icons |
| Font | Google Fonts (Inter) |
| Chart | Chart.js |
| Keamanan | CSRF Token, Prepared Statement, Session |

