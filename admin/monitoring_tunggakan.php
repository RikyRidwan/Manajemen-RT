<?php
require_once '../includes/auth.php';
redirectIfNotLoggedIn();
if(!hasRole('admin')) { header('Location: ../dashboard.php'); exit; }
$title = "Monitoring Tunggakan";
global $pdo;
$tunggakan = $pdo->query("SELECT u.fullname, u.id, wd.status_warga, wd.blok_rumah FROM users u JOIN warga_detail wd ON u.id=wd.user_id WHERE wd.status_warga='menunggak'")->fetchAll();
include '../includes/header.php';
?>
<div class="container-fluid"><h2>Warga Menunggak Iuran</h2><div class="card"><div class="card-body"><table class="table datatable"><thead><tr><th>Nama</th><th>Blok Rumah</th><th>Status</th></tr></thead><tbody><?php foreach($tunggakan as $t): ?><tr><td><?= htmlspecialchars($t['fullname']) ?></td><td><?= $t['blok_rumah'] ?></td><td><span class="badge bg-danger">Menunggak</span></td></tr><?php endforeach; ?></tbody></table></div></div></div>
<?php include '../includes/footer.php'; ?>