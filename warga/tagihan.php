<?php
require_once '../includes/auth.php';
redirectIfNotLoggedIn();
if(!hasRole('warga')) { header('Location: ../dashboard.php'); exit; }
$user = getCurrentUser();
global $pdo;
$title = "Tagihan Saya";

// Ambil semua tagihan (termasuk yang ditolak) – namun filter di tampilan
$tagihan = $pdo->prepare("SELECT p.*, i.nama_iuran, i.nominal, pr.tahun, pr.bulan 
    FROM pembayaran p 
    JOIN iuran i ON p.iuran_id = i.id 
    JOIN periode pr ON p.periode_id = pr.id 
    WHERE p.user_id = ? 
    ORDER BY p.id DESC");
$tagihan->execute([$user['id']]);
$tagihans = $tagihan->fetchAll();

include '../includes/header.php';
?>
<div class="container-fluid">
    <h2 class="mb-4">Tagihan dan Pembayaran Saya</h2>
    <div class="table-responsive">
        <table class="table table-bordered datatable">
            <thead>
                <tr>
                    <th>Jenis Iuran</th>
                    <th>Periode</th>
                    <th>Jumlah</th>
                    <th>Status</th>
                    <th>Tanggal Bayar</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($tagihans as $t): 
                    // Normalisasi status (ubah ke huruf kecil untuk perbandingan)
                    $status_lower = strtolower($t['status']);
                ?>
                <tr>
                    <td><?= htmlspecialchars($t['nama_iuran']) ?></td>
                    <td><?= $t['bulan'] . '/' . $t['tahun'] ?></td>
                    <td>Rp <?= number_format($t['jumlah'],0,',','.') ?></td>
                    <td>
                        <?php if($status_lower == 'pending'): ?>
                            <span class="badge bg-warning">Pending</span>
                        <?php elseif($status_lower == 'disetujui'): ?>
                            <span class="badge bg-success">Lunas</span>
                        <?php else: ?>
                            <span class="badge bg-danger"><?= ucfirst($t['status']) ?></span>
                        <?php endif; ?>
                    </td>
                    <td><?= $t['tanggal_pembayaran'] ?? '-' ?></td>
                    <td>
                        <?php if($status_lower == 'pending'): ?>
                            <a href="upload_bukti.php?id=<?= $t['id'] ?>" class="btn btn-sm btn-info">Upload Bukti</a>
                        <?php endif; ?>
                        <?php if($status_lower == 'disetujui'): ?>
                            <a href="kuitansi.php?id=<?= $t['id'] ?>" class="btn btn-sm btn-secondary">Kuitansi</a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php include '../includes/footer.php'; ?>