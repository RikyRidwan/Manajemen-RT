<?php
require_once '../includes/auth.php';
redirectIfNotLoggedIn();
if(!hasRole('admin')) { header('Location: ../dashboard.php'); exit; }
$title = "Notifikasi";
global $pdo;

// Notifikasi: warga menunggu verifikasi
$pendingVerif = $pdo->query("SELECT COUNT(*) FROM users WHERE role_id = 4 AND status_aktif = 'nonaktif'")->fetchColumn();

// Notifikasi: pembayaran pending
$pendingPayment = $pdo->query("SELECT COUNT(*) FROM pembayaran WHERE status = 'pending'")->fetchColumn();

// Notifikasi: pengajuan surat pending
$pendingSurat = $pdo->query("SELECT COUNT(*) FROM surat_pengajuan WHERE status = 'pending'")->fetchColumn();

// Daftar warga menunggu verifikasi
$wargaPending = $pdo->query("SELECT id, fullname, username, email, created_at FROM users WHERE role_id = 4 AND status_aktif = 'nonaktif' ORDER BY created_at DESC LIMIT 10")->fetchAll();

// Daftar pembayaran pending
$pembayaranPending = $pdo->query("SELECT p.id, u.fullname, p.jumlah, p.tanggal_pembayaran FROM pembayaran p JOIN users u ON p.user_id = u.id WHERE p.status = 'pending' ORDER BY p.id DESC LIMIT 10")->fetchAll();

include '../includes/header.php';
?>
<div class="container-fluid">
    <h2 class="mb-4">Notifikasi</h2>

    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card text-white bg-warning">
                <div class="card-body">
                    <h5>Verifikasi Warga</h5>
                    <h2><?= $pendingVerif ?></h2>
                    <p>Warga menunggu verifikasi</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-white bg-info">
                <div class="card-body">
                    <h5>Pembayaran Pending</h5>
                    <h2><?= $pendingPayment ?></h2>
                    <p>Menunggu approve</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-white bg-secondary">
                <div class="card-body">
                    <h5>Pengajuan Surat</h5>
                    <h2><?= $pendingSurat ?></h2>
                    <p>Surat menunggu diproses</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    Daftar Warga Menunggu Verifikasi
                </div>
                <div class="card-body">
                    <?php if(count($wargaPending) > 0): ?>
                        <table class="table table-sm">
                            <thead>
                                <tr><th>Nama</th><th>Username</th><th>Tanggal Daftar</th><th>Aksi</th></tr>
                            </thead>
                            <tbody>
                                <?php foreach($wargaPending as $w): ?>
                                <tr>
                                    <td><?= htmlspecialchars($w['fullname']) ?></td>
                                    <td><?= htmlspecialchars($w['username']) ?></td>
                                    <td><?= date('d-m-Y', strtotime($w['created_at'])) ?></td>
                                    <td><a href="verifikasi_warga.php" class="btn btn-sm btn-success">Verifikasi</a></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p class="text-muted">Tidak ada warga menunggu verifikasi.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    Pembayaran Menunggu Approve
                </div>
                <div class="card-body">
                    <?php if(count($pembayaranPending) > 0): ?>
                        <table class="table table-sm">
                            <thead>
                                <tr><th>Warga</th><th>Jumlah</th><th>Tanggal</th><th>Aksi</th></tr>
                            </thead>
                            <tbody>
                                <?php foreach($pembayaranPending as $p): ?>
                                <tr>
                                    <td><?= htmlspecialchars($p['fullname']) ?></td>
                                    <td>Rp <?= number_format($p['jumlah'],0,',','.') ?></td>
                                    <td><?= date('d-m-Y', strtotime($p['tanggal_pembayaran'])) ?></td>
                                    <td><a href="pembayaran.php" class="btn btn-sm btn-info">Detail</a></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p class="text-muted">Tidak ada pembayaran pending.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include '../includes/footer.php'; ?>