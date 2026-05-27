<?php
require_once '../includes/auth.php';
redirectIfNotLoggedIn();
if(!hasRole('warga')) { header('Location: ../dashboard.php'); exit; }
$user = getCurrentUser();
global $pdo;
$title = "Histori Pembayaran";
$histori = $pdo->prepare("SELECT p.*, i.nama_iuran, pr.tahun, pr.bulan FROM pembayaran p JOIN iuran i ON p.iuran_id = i.id JOIN periode pr ON p.periode_id = pr.id WHERE p.user_id=? ORDER BY p.id DESC");
$histori->execute([$user['id']]);
$historis = $histori->fetchAll();
include '../includes/header.php';
?>
<div class="container-fluid"><h2>Histori Pembayaran</h2><table class="table datatable"><thead><tr><th>Tanggal</th><th>Iuran</th><th>Periode</th><th>Jumlah</th><th>Status</th></tr></thead><tbody><?php foreach($historis as $h): ?><tr><td><?= $h['tanggal_pembayaran'] ?? '-' ?></td><td><?= htmlspecialchars($h['nama_iuran']) ?></td><td><?= $h['bulan'] . '/' . $h['tahun'] ?></td><td>Rp <?= number_format($h['jumlah'],0,',','.') ?></td><td><?= $h['status'] ?></td></tr><?php endforeach; ?></tbody></table></div>
<?php include '../includes/footer.php'; ?>