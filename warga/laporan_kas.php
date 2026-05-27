<?php
require_once '../includes/auth.php';
redirectIfNotLoggedIn();
if(!hasRole('warga')) { header('Location: ../dashboard.php'); exit; }
$user = getCurrentUser();
global $pdo;
$title = "Laporan Kas Pribadi";
$transaksi = $pdo->prepare("SELECT p.*, i.nama_iuran FROM pembayaran p JOIN iuran i ON p.iuran_id = i.id WHERE p.user_id=? ORDER BY p.id DESC");
$transaksi->execute([$user['id']]);
$data = $transaksi->fetchAll();
include '../includes/header.php';
?>
<div class="container-fluid"><h2>Laporan Kas Pribadi</h2><table class="table datatable"><thead><tr><th>Tanggal</th><th>Jenis Iuran</th><th>Jumlah</th><th>Status</th></tr></thead><tbody><?php foreach($data as $d): ?><tr><td><?= $d['tanggal_pembayaran'] ?? '-' ?></td><td><?= htmlspecialchars($d['nama_iuran']) ?></td><td>Rp <?= number_format($d['jumlah'],0,',','.') ?></td><td><?= $d['status'] ?></td></tr><?php endforeach; ?></tbody></table></div>
<?php include '../includes/footer.php'; ?>