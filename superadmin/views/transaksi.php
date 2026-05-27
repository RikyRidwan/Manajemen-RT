<?php
require_once '../../includes/auth.php';
redirectIfNotLoggedIn();
if(!hasRole('superadmin')) { echo "Akses ditolak"; exit; }
require_once '../../config/database.php';
global $pdo;


// Ambil data transaksi pembayaran (join dengan user, iuran, periode)
$transaksi = $pdo->query("
    SELECT p.*, 
           u.fullname as nama_warga, 
           i.nama_iuran, 
           pr.tahun, 
           pr.bulan,
           ap.fullname as approved_by_name
    FROM pembayaran p
    JOIN users u ON p.user_id = u.id
    JOIN iuran i ON p.iuran_id = i.id
    JOIN periode pr ON p.periode_id = pr.id
    LEFT JOIN users ap ON p.approved_by = ap.id
    ORDER BY p.id DESC
")->fetchAll();
?>
<div class="container-fluid">
    <h2 class="mb-4">Semua Transaksi Pembayaran</h2>

    <?php if(isset($_SESSION['success'])): ?>
        <div class="alert alert-success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
    <?php endif; ?>

    <div class="card">
        <div class="card-header bg-primary text-white">Riwayat Transaksi</div>
        <div class="card-body table-responsive">
            <table class="table table-bordered table-striped datatable" width="100%">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Warga</th>
                        <th>Jenis Iuran</th>
                        <th>Periode</th>
                        <th>Jumlah</th>
                        <th>Status</th>
                        <th>Tanggal Bayar</th>
                        <th>Disetujui Oleh</th>
                        <th>Bukti</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($transaksi as $t): ?>
                    <tr>
                        <td><?= $t['id'] ?></td>
                        <td><?= htmlspecialchars($t['nama_warga']) ?></td>
                        <td><?= htmlspecialchars($t['nama_iuran']) ?></td>
                        <td><?= $t['bulan'] . ' / ' . $t['tahun'] ?></td>
                        <td>Rp <?= number_format($t['jumlah'], 0, ',', '.') ?></td>
                        <td>
                            <?php 
                            $statusClass = '';
                            if($t['status'] == 'disetujui') $statusClass = 'bg-success';
                            elseif($t['status'] == 'pending') $statusClass = 'bg-warning text-dark';
                            else $statusClass = 'bg-danger';
                            ?>
                            <span class="badge <?= $statusClass ?>"><?= ucfirst($t['status']) ?></span>
                        </td>
                        <td><?= $t['tanggal_pembayaran'] ?: '-' ?></td>
                        <td><?= htmlspecialchars($t['approved_by_name']) ?: '-' ?></td>
                        <td>
                            <?php if($t['bukti_file']): ?>
                                <a href="../assets/uploads/bukti/<?= $t['bukti_file'] ?>" target="_blank" class="btn btn-sm btn-info">Lihat</a>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>