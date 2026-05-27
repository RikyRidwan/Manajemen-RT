# Manajemen RT - Sistem Informasi Pengelolaan Rukun Tetangga

Sistem Manajemen RT adalah aplikasi web berbasis PHP & MySQL yang dirancang untuk membantu pengurus RT (Rukun Tetangga) dalam mengelola administrasi kependudukan, keuangan, pengumuman, agenda, serta iuran warga secara online. Aplikasi ini mendukung **4 role pengguna**: Superadmin, Admin, Bendahara, dan Warga.

## ✨ Fitur Utama

### Superadmin
- Dashboard dengan grafik pemasukan & pengeluaran
- Kelola semua akun (tambah, edit, hapus, reset password)
- Atur hak akses role
- Kelola data RT (nama, ketua, alamat, logo)
- Kelola jenis iuran & periode pembayaran
- Approve/tolak pembayaran iuran
- Laporan keuangan (bulanan, tahunan, per warga)
- Backup & restore database
- Log aktivitas pengguna
- Generate tagihan massal

### Admin
- Dashboard dengan statistik warga & grafik komposisi status
- Kelola data warga (CRUD, verifikasi)
- Kelola pengumuman & agenda kegiatan
- Monitoring tunggakan iuran
- Cetak kartu iuran warga
- Laporan data warga

### Bendahara
- Dashboard keuangan (pemasukan, pengeluaran, saldo, grafik)
- Input pembayaran iuran manual
- Upload bukti pembayaran
- Konfirmasi pembayaran warga
- Kelola pemasukan & pengeluaran
- Rekap kas (bulanan/tahunan)
- Cetak laporan keuangan

### Warga
- Dashboard pribadi (tagihan bulan berjalan, grafik pembayaran)
- Lihat tagihan & histori pembayaran
- Upload bukti transfer
- Download kuitansi pembayaran
- Lihat pengumuman & agenda RT
- Edit profil & ganti password
- Pengajuan surat (domisili/pengantar)
- Notifikasi tagihan

## 🛠️ Teknologi yang Digunakan

- **Backend**: PHP 8.0 (native, OOP)
- **Database**: MySQL (PDO)
- **Frontend**: Bootstrap 5, Tailwind CSS (halaman login), Chart.js
- **JavaScript**: jQuery, DataTables, AJAX
- **Server**: XAMPP / Apache

## 📁 Struktur Folder
