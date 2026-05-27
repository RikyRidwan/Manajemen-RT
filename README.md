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
```
manajemen_rt/
├── admin/
├── bendahara/
├── warga/
├── superadmin/
│   └── views/
├── includes/
├── config/
├── assets/
└── index.php
```


## 🚀 Cara Instalasi

### Prasyarat
- XAMPP (PHP >= 8.0, MySQL) atau web server lain yang mendukung PHP dan MySQL.
- Web browser modern.

### Langkah-langkah

1. **Clone repository** (atau download zip)
   ```bash
   git clone https://github.com/RikyRidwan/Manajemen-RT.git
   ```
2. Pindahkan folder hasil clone ke dalam direktori htdocs XAMPP (jika belum berada di sana):
   Atau ekstrak file ZIP yang diunduh ke C:\xampp\htdocs\manajemen_rt.
3. Buat database:
   Buka http://localhost/phpmyadmin.
   Buat database baru dengan nama manajemen_rt (pilih collation utf8_general_ci).
   Klik tab Import, pilih file database.sql yang ada di folder proyek, lalu klik Go.
4. Konfigurasi koneksi database (jika diperlukan):
   Buka file config/database.php dan pastikan parameter berikut sesuai dengan pengaturan MySQL Anda:
   $host = 'localhost';
   $dbname = 'manajemen_rt';
   $username = 'root';
   $password = '';
5. Jalankan aplikasi:
   Start Apache dan MySQL melalui XAMPP Control Panel.
   Buka browser dan akses: http://localhost/manajemen_rt
6. Login menggunakan akun default (lihat tabel di bawah).
  | Role       | Username          | Password  |
  |------------|-------------------|-----------|
  | Superadmin | superadmin        | 12345678  |
  | Admin      | admin             | 12345678  |
  | Bendahara  | bendahara         | 12345678  |
  | Warga      | warga1...warga100 | 12345678  |

📌 Catatan:
Akun warga lainnya (warga2, warga3, ... warga100) juga memiliki password 12345678. Superadmin dapat menambah atau mengedit akun melalui menu Kelola Akun.

📌 Catatan Penting:
Pastikan folder assets/uploads/bukti/ dan assets/uploads/logo/ memiliki izin tulis agar proses upload bukti dan logo berhasil.
Periode aktif harus dibuat oleh superadmin (menu Periode). Setelah periode aktif, superadmin perlu men-generate tagihan melalui menu Generate Tagihan agar warga dapat melihat tagihan.
Grafik pada dashboard membutuhkan koneksi internet untuk memuat library Chart.js dari CDN. Jika offline, ganti dengan file lokal.
Untuk keamanan, jangan biarkan file config/database.php terekspos di repository publik jika berisi password asli. Pada contoh ini, password dikosongkan karena menggunakan XAMPP default.

🤝 Kontribusi
Silakan buat issue atau pull request jika ingin mengembangkan fitur baru.
📄 Lisensi
Proyek ini dilisensikan di bawah MIT License – bebas digunakan, dimodifikasi, dan didistribusikan.
👨‍💻 Pengembang
Dibuat oleh Riky Ridwan – untuk keperluan tugas akhir / sistem informasi RT.

Selamat menggunakan! 🎉
