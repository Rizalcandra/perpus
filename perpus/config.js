/**
 * config.js — Konfigurasi Aplikasi Perpustakaan Desa
 * File ini berisi pengaturan global yang digunakan oleh JavaScript di sisi client.
 * UKK 2026 — RPL: Pengembangan Aplikasi Peminjaman Buku
 */

const APP_CONFIG = {
    name: 'Perpustakaan Desa',
    version: '2.0',
    apiUrl: '',              // Base URL API (kosong jika native PHP tanpa REST API)
    maxBorrow: 2,            // Maksimal buku yang dapat dipinjam per anggota
    finePerDay: 5000,        // Denda keterlambatan per hari (Rupiah)
    loanDays: 14,            // Lama peminjaman default (hari)
    currency: 'Rp',          // Simbol mata uang
    locale: 'id-ID',         // Lokalisasi (Bahasa Indonesia)
    dateFormat: 'DD/MM/YYYY', // Format tanggal tampilan
    alertTimeout: 5000,      // Durasi auto-hide alert (ms)
    soundEnabled: true,      // Notifikasi suara aktif/nonaktif
    csrfTokenName: 'csrf_token', // Nama field token CSRF
    roles: {
        admin: 'Administrator',
        petugas: 'Petugas',
        anggota: 'Anggota'
    },
    statusPeminjaman: {
        dipinjam: { label: 'Dipinjam', color: 'warning' },
        dikembalikan: { label: 'Dikembalikan', color: 'success' },
        terlambat: { label: 'Terlambat', color: 'danger' },
        menunggu_konfirmasi: { label: 'Menunggu Konfirmasi', color: 'info' },
        ditolak: { label: 'Ditolak', color: 'secondary' }
    }
};

// Export untuk modul ES (opsional, jika digunakan sebagai module)
if (typeof module !== 'undefined' && module.exports) {
    module.exports = APP_CONFIG;
}

// Log ke console saat config dimuat
console.log(`[${APP_CONFIG.name} v${APP_CONFIG.version}] Config loaded`);
