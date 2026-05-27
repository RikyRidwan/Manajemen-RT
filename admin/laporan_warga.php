<?php
require_once '../includes/auth.php';
redirectIfNotLoggedIn();
if(!hasRole('admin')) { header('Location: ../dashboard.php'); exit; }
$title = "Laporan Data Warga";
global $pdo;
// Hapus wd.nik dari SELECT
$warga = $pdo->query("SELECT u.fullname, u.email, u.no_hp, u.alamat, wd.no_kk, wd.blok_rumah, wd.status_warga FROM users u LEFT JOIN warga_detail wd ON u.id=wd.user_id WHERE u.role_id=4")->fetchAll();
include '../includes/header.php';
?>
<div class="container-fluid">
    <h2>Laporan Data Warga</h2>
    <div class="card">
        <div class="card-body">
            <table class="table table-bordered datatable">
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>No HP</th>
                        <th>Alamat</th>
                        <th>No KK</th>
                        <th>Blok</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($warga as $w): ?>
                    <tr>
                        <td><?= htmlspecialchars($w['fullname']) ?></td>
                        <td><?= htmlspecialchars($w['email']) ?></td>
                        <td><?= htmlspecialchars($w['no_hp']) ?></td>
                        <td><?= htmlspecialchars($w['alamat']) ?></td>
                        <td><?= htmlspecialchars($w['no_kk']) ?></td>
                        <td><?= htmlspecialchars($w['blok_rumah']) ?></td>
                        <td><?= $w['status_warga'] ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <button onclick="window.print()" class="btn btn-success mt-3">Cetak Laporan</button>
        </div>
    </div>
</div>
<?php include '../includes/footer.php'; ?>