<?php
require_once '../includes/auth.php';
redirectIfNotLoggedIn();
if(!hasRole('bendahara')) { header('Location: ../dashboard.php'); exit; }
$title = "Pemasukan";
global $pdo;
$pemasukan = $pdo->query("SELECT p.*, u.fullname, i.nama_iuran FROM pembayaran p JOIN users u ON p.user_id=u.id JOIN iuran i ON p.iuran_id=i.id WHERE p.status='disetujui' ORDER BY p.tanggal_pembayaran DESC")->fetchAll();
include '../includes/header.php';
?>
<div class="container-fluid"><h2>Pemasukan Kas</h2><div class="card"><div class="card-body"><table class="table datatable"><thead><tr><th>Tanggal</th><th>Warga</th><th>Iuran</th><th>Jumlah</th></tr></thead><tbody><?php foreach($pemasukan as $p): ?><tr><td><?= $p['tanggal_pembayaran'] ?></td><td><?= htmlspecialchars($p['fullname']) ?></td><td><?= htmlspecialchars($p['nama_iuran']) ?></td><td>Rp <?= number_format($p['jumlah'],0,',','.') ?></td></tr><?php endforeach; ?></tbody></table></div></div></div>
<?php include '../includes/footer.php'; ?>