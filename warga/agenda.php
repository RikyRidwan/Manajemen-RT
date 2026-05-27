<?php
require_once '../includes/auth.php';
redirectIfNotLoggedIn();
if(!hasRole('warga')) { header('Location: ../dashboard.php'); exit; }
global $pdo;
$title = "Agenda";
$agenda = $pdo->query("SELECT * FROM agenda WHERE tanggal >= CURDATE() ORDER BY tanggal ASC")->fetchAll();
include '../includes/header.php';
?>
<div class="container-fluid"><h2>Agenda Kegiatan</h2><div class="list-group"><?php foreach($agenda as $a): ?><div class="list-group-item"><h5><?= htmlspecialchars($a['judul']) ?></h5><p>Tanggal: <?= $a['tanggal'] ?>, Lokasi: <?= htmlspecialchars($a['lokasi']) ?></p><p><?= nl2br(htmlspecialchars($a['deskripsi'])) ?></p></div><?php endforeach; ?></div></div>
<?php include '../includes/footer.php'; ?>