<?php
if (!isset($noSession)) {
    require_once __DIR__ . '/auth.php';
    redirectIfNotLoggedIn();
    $currentUser = getCurrentUser();
    $role = $currentUser['nama_role'];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Manajemen RT' ?></title>
    <!-- Bootstrap 5 + Icons + DataTables -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <style>
        /* Reset & Font */
        body {
            background: #f0f2f5;
            font-family: 'Segoe UI', Roboto, system-ui, sans-serif;
        }
        /* Sidebar premium */
        .sidebar {
            background: linear-gradient(145deg, #1a2639, #0d1b2a);
            min-height: 100vh;
            box-shadow: 4px 0 20px rgba(0,0,0,0.08);
            transition: all 0.3s;
        }
        .sidebar .nav-link {
            color: #cbd5e6;
            padding: 12px 20px;
            margin: 6px 12px;
            border-radius: 14px;
            font-weight: 500;
            transition: all 0.25s;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .sidebar .nav-link i {
            width: 24px;
            font-size: 1.2rem;
            text-align: center;
        }
        .sidebar .nav-link:hover {
            background: rgba(255,255,255,0.08);
            color: white;
            transform: translateX(5px);
        }
        .sidebar .nav-link.active {
            background: linear-gradient(90deg, #4f46e5, #7c3aed);
            color: white;
            box-shadow: 0 6px 12px rgba(79,70,229,0.25);
        }
        /* Header top */
        .navbar-top {
            background: white;
            padding: 12px 28px;
            border-radius: 0 0 20px 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.03);
            margin-bottom: 24px;
        }
        /* Konten utama */
        .content-wrapper {
            padding: 0 28px 28px 28px;
        }
        /* Footer */
        .footer {
            background: white;
            border-top: 1px solid #e2e8f0;
            padding: 15px 0;
            text-align: center;
            font-size: 13px;
            color: #6c757d;
            margin-top: 30px;
        }
        /* Card statistik (opsional, seragam) */
        .card-stats {
            border-radius: 24px;
            border: none;
            transition: 0.2s;
            box-shadow: 0 5px 12px rgba(0,0,0,0.05);
        }
        .card-stats:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 20px rgba(0,0,0,0.08);
        }
        /* Responsive: sidebar bisa di-toggle di mobile */
        .sidebar-toggle {
            display: none;
            background: none;
            border: none;
            font-size: 1.5rem;
            color: #1e293b;
        }
        @media (max-width: 768px) {
            .sidebar {
                position: fixed;
                left: -280px;
                transition: left 0.3s ease;
                z-index: 1050;
                width: 280px;
            }
            .sidebar.show {
                left: 0;
            }
            .sidebar-toggle {
                display: block;
            }
            .content-wrapper {
                padding: 0 16px 16px 16px;
            }
            .navbar-top {
                padding: 10px 16px;
            }
        }
        .sidebar-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 1040;
        }
        .sidebar-overlay.show {
            display: block;
        }
    </style>
</head>
<body>
<div class="sidebar-overlay" id="sidebarOverlay"></div>
<div class="container-fluid p-0">
    <div class="row g-0">
        <!-- SIDEBAR -->
        <div class="col-auto sidebar" id="sidebar">
            <div class="p-3">
                <h4 class="text-white mb-3 d-flex align-items-center gap-2">
                    <i class="fas fa-building"></i> <span>Manajemen RT</span>
                </h4>
                <hr class="bg-secondary opacity-25">
                <nav class="nav flex-column">
<?php if ($role == 'superadmin'): ?>
    <?php
    $view = isset($_GET['view']) ? $_GET['view'] : 'dashboard';
    ?>
    <a href="../superadmin/index.php?view=dashboard" class="nav-link <?= $view == 'dashboard' ? 'active' : '' ?>">
        <i class="fas fa-tachometer-alt"></i> Dashboard
    </a>
    <a href="../superadmin/kelola_akun.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'kelola_akun.php' ? 'active' : '' ?>">
        <i class="fas fa-users"></i> Kelola Akun
    </a>
    <a href="../superadmin/approve_pembayaran.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'approve_pembayaran.php' ? 'active' : '' ?>">
        <i class="fas fa-check-double"></i> Approve Pembayaran
    </a>
    <a href="../superadmin/data_rt.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'data_rt.php' ? 'active' : '' ?>">
        <i class="fas fa-home"></i> Data RT
    </a>
    <a href="../superadmin/kelola_iuran.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'kelola_iuran.php' ? 'active' : '' ?>">
        <i class="fas fa-money-bill-wave"></i> Iuran
    </a>
    <a href="../superadmin/periode.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'periode.php' ? 'active' : '' ?>">
        <i class="fas fa-calendar-alt"></i> Periode
    </a>
    <a href="../superadmin/transaksi.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'transaksi.php' ? 'active' : '' ?>">
        <i class="fas fa-exchange-alt"></i> Transaksi
    </a>
    <a href="../superadmin/pengeluaran.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'pengeluaran.php' ? 'active' : '' ?>">
        <i class="fas fa-chart-line"></i> Pengeluaran
    </a>
    <a href="../superadmin/laporan.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'laporan.php' ? 'active' : '' ?>">
        <i class="fas fa-print"></i> Laporan
    </a>
    <a href="../superadmin/backup.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'backup.php' ? 'active' : '' ?>">
        <i class="fas fa-database"></i> Backup
    </a>
    <a href="../superadmin/log_aktivitas.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'log_aktivitas.php' ? 'active' : '' ?>">
        <i class="fas fa-history"></i> Log Aktivitas
    </a>
    <a href="../superadmin/pengaturan.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'pengaturan.php' ? 'active' : '' ?>">
        <i class="fas fa-cog"></i> Pengaturan
    </a>
    <a href="../superadmin/generate_tagihan.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'generate_tagihan.php' ? 'active' : '' ?>">
        <i class="fas fa-file-invoice"></i> Generate Tagihan
    </a>
<?php elseif ($role == 'admin'): ?>
    <?php
    $current = basename($_SERVER['PHP_SELF']);
    $dashboard_active = ($current == 'index.php') ? 'active' : '';
    $kelola_warga_active = ($current == 'kelola_warga.php') ? 'active' : '';
    $pengumuman_active = ($current == 'pengumuman.php') ? 'active' : '';
    $agenda_active = ($current == 'agenda.php') ? 'active' : '';
    $verifikasi_active = ($current == 'verifikasi_warga.php') ? 'active' : '';
    $pembayaran_active = ($current == 'pembayaran.php') ? 'active' : '';
    $cetak_kartu_active = ($current == 'cetak_kartu.php') ? 'active' : '';
    $tunggakan_active = ($current == 'monitoring_tunggakan.php') ? 'active' : '';
    $laporan_warga_active = ($current == 'laporan_warga.php') ? 'active' : '';
    $notifikasi_active = ($current == 'notifikasi.php') ? 'active' : '';
    ?>
    <a href="../admin/index.php" class="nav-link <?= $dashboard_active ?>"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
    <a href="../admin/kelola_warga.php" class="nav-link <?= $kelola_warga_active ?>"><i class="fas fa-users"></i> Kelola Warga</a>
    <a href="../admin/pengumuman.php" class="nav-link <?= $pengumuman_active ?>"><i class="fas fa-bullhorn"></i> Pengumuman</a>
    <a href="../admin/agenda.php" class="nav-link <?= $agenda_active ?>"><i class="fas fa-calendar-alt"></i> Agenda</a>
    <a href="../admin/verifikasi_warga.php" class="nav-link <?= $verifikasi_active ?>"><i class="fas fa-check-circle"></i> Verifikasi Warga</a>
    <a href="../admin/pembayaran.php" class="nav-link <?= $pembayaran_active ?>"><i class="fas fa-money-bill-wave"></i> Pembayaran Kas</a>
    <a href="../admin/cetak_kartu.php" class="nav-link <?= $cetak_kartu_active ?>"><i class="fas fa-id-card"></i> Cetak Kartu</a>
    <a href="../admin/monitoring_tunggakan.php" class="nav-link <?= $tunggakan_active ?>"><i class="fas fa-exclamation-triangle"></i> Tunggakan Iuran</a>
    <a href="../admin/laporan_warga.php" class="nav-link <?= $laporan_warga_active ?>"><i class="fas fa-file-alt"></i> Laporan Warga</a>
    <a href="../admin/notifikasi.php" class="nav-link <?= $notifikasi_active ?>"><i class="fas fa-bell"></i> Notifikasi</a>

<?php elseif ($role == 'bendahara'): ?>
    <?php
    $current = basename($_SERVER['PHP_SELF']);
    $dashboard_active = ($current == 'index.php') ? 'active' : '';
    $input_pembayaran_active = ($current == 'input_pembayaran.php') ? 'active' : '';
    $upload_bukti_active = ($current == 'upload_bukti.php') ? 'active' : '';
    $konfirmasi_active = ($current == 'konfirmasi_pembayaran.php') ? 'active' : '';
    $pemasukan_active = ($current == 'kelola_pemasukan.php') ? 'active' : '';
    $pengeluaran_active = ($current == 'kelola_pengeluaran.php') ? 'active' : '';
    $riwayat_active = ($current == 'riwayat_transaksi.php') ? 'active' : '';
    $rekap_active = ($current == 'rekap.php') ? 'active' : '';
    $monitoring_active = ($current == 'monitoring_warga.php') ? 'active' : '';
    $cetak_laporan_active = ($current == 'cetak_laporan.php') ? 'active' : '';
    ?>
    <a href="../bendahara/index.php" class="nav-link <?= $dashboard_active ?>"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
    <a href="../bendahara/input_pembayaran.php" class="nav-link <?= $input_pembayaran_active ?>"><i class="fas fa-hand-holding-usd"></i> Input Pembayaran</a>
    <a href="../bendahara/upload_bukti.php" class="nav-link <?= $upload_bukti_active ?>"><i class="fas fa-upload"></i> Upload Bukti</a>
    <a href="../bendahara/konfirmasi_pembayaran.php" class="nav-link <?= $konfirmasi_active ?>"><i class="fas fa-check-double"></i> Konfirmasi Pembayaran</a>
    <a href="../bendahara/kelola_pemasukan.php" class="nav-link <?= $pemasukan_active ?>"><i class="fas fa-chart-line"></i> Pemasukan</a>
    <a href="../bendahara/kelola_pengeluaran.php" class="nav-link <?= $pengeluaran_active ?>"><i class="fas fa-chart-line"></i> Pengeluaran</a>
    <a href="../bendahara/riwayat_transaksi.php" class="nav-link <?= $riwayat_active ?>"><i class="fas fa-history"></i> Riwayat Transaksi</a>
    <a href="../bendahara/rekap.php" class="nav-link <?= $rekap_active ?>"><i class="fas fa-chart-pie"></i> Rekap Kas</a>
    <a href="../bendahara/monitoring_warga.php" class="nav-link <?= $monitoring_active ?>"><i class="fas fa-users"></i> Monitoring Warga</a>
    <a href="../bendahara/cetak_laporan.php" class="nav-link <?= $cetak_laporan_active ?>"><i class="fas fa-print"></i> Cetak Laporan</a>

<?php elseif ($role == 'warga'): ?>
    <?php
    $current = basename($_SERVER['PHP_SELF']);
    $dashboard_active = ($current == 'index.php') ? 'active' : '';
    $tagihan_active = (in_array($current, ['tagihan.php', 'upload_bukti.php', 'kuitansi.php'])) ? 'active' : '';
    $histori_active = ($current == 'histori.php') ? 'active' : '';
    $pengumuman_active = ($current == 'pengumuman.php') ? 'active' : '';
    $agenda_active = ($current == 'agenda.php') ? 'active' : '';
    $profil_active = (in_array($current, ['profil.php', 'ganti_password.php'])) ? 'active' : '';
    $laporan_kas_active = ($current == 'laporan_kas.php') ? 'active' : '';
    $pengajuan_active = ($current == 'pengajuan_surat.php') ? 'active' : '';
    $chat_active = ($current == 'chat.php') ? 'active' : '';
    $notifikasi_active = ($current == 'notifikasi.php') ? 'active' : '';
    ?>
    <a href="../warga/index.php" class="nav-link <?= $dashboard_active ?>"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
    <a href="../warga/tagihan.php" class="nav-link <?= $tagihan_active ?>"><i class="fas fa-file-invoice"></i> Tagihan</a>
    <a href="../warga/histori.php" class="nav-link <?= $histori_active ?>"><i class="fas fa-history"></i> Histori Pembayaran</a>
    <a href="../warga/pengumuman.php" class="nav-link <?= $pengumuman_active ?>"><i class="fas fa-bullhorn"></i> Pengumuman</a>
    <a href="../warga/agenda.php" class="nav-link <?= $agenda_active ?>"><i class="fas fa-calendar-alt"></i> Agenda</a>
    <a href="../warga/profil.php" class="nav-link <?= $profil_active ?>"><i class="fas fa-user"></i> Profil</a>
    <a href="../warga/laporan_kas.php" class="nav-link <?= $laporan_kas_active ?>"><i class="fas fa-chart-line"></i> Laporan Kas</a>
    <a href="../warga/pengajuan_surat.php" class="nav-link <?= $pengajuan_active ?>"><i class="fas fa-envelope"></i> Pengajuan Surat</a>
    <a href="../warga/chat.php" class="nav-link <?= $chat_active ?>"><i class="fas fa-comments"></i> Chat Admin</a>
    <a href="../warga/notifikasi.php" class="nav-link <?= $notifikasi_active ?>"><i class="fas fa-bell"></i> Notifikasi</a>
<?php endif; ?>
                    <hr class="bg-secondary opacity-25">
                    <a href="../logout.php" class="nav-link text-danger"><i class="fas fa-sign-out-alt"></i> Logout</a>
                </nav>
            </div>
        </div>

        <!-- MAIN CONTENT -->
        <div class="col">
            <div class="navbar-top d-flex justify-content-between align-items-center">
                <div class="d-flex gap-2 align-items-center">
                    <button class="sidebar-toggle" id="sidebarToggleBtn"><i class="fas fa-bars"></i></button>
                    <h5 class="mb-0"><?= $title ?? 'Dashboard' ?></h5>
                </div>
                <div class="d-flex align-items-center gap-3">
                    <span class="text-muted"><i class="fas fa-user-circle"></i> <?= htmlspecialchars($currentUser['fullname'] ?? '') ?></span>
                    <span class="badge bg-secondary"><?= ucfirst($role) ?></span>
                </div>
            </div>
            <div class="content-wrapper">