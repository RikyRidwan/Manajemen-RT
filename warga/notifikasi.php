<?php
require_once '../includes/auth.php';
redirectIfNotLoggedIn();
if(!hasRole('warga')) { header('Location: ../dashboard.php'); exit; }
$user = getCurrentUser();
global $pdo;
$title = "Notifikasi";
$notif = $pdo->prepare("SELECT * FROM pembayaran WHERE user_id=? AND status IN ('pending','ditolak')");
$notif->execute([$user['id']]);
$pending = $notif->fetchAll();
include '../includes/header.php';
?>
<div class="container-fluid"><h2>Notifikasi</h2><div class="list-group"><?php foreach($pending as $n): ?><div class="list-group-item list-group-item-warning">Pembayaran <?= $n['id'] ?> masih <?= $n['status'] ?>. Silakan upload bukti atau hubungi bendahara.</div><?php endforeach; ?></div></div>
<?php include '../includes/footer.php'; ?>