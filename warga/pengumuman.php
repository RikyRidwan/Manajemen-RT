<?php
require_once '../includes/auth.php';
redirectIfNotLoggedIn();
if(!hasRole('warga')) { header('Location: ../dashboard.php'); exit; }
global $pdo;
$title = "Pengumuman";
$pengumuman = $pdo->query("SELECT p.*, u.fullname FROM pengumuman p LEFT JOIN users u ON p.created_by = u.id WHERE p.target_role_id IS NULL OR p.target_role_id = 4 ORDER BY p.created_at DESC")->fetchAll();
include '../includes/header.php';
?>
<div class="container-fluid"><h2>Pengumuman RT</h2><div class="list-group"><?php foreach($pengumuman as $p): ?><div class="list-group-item"><h5><?= htmlspecialchars($p['judul']) ?></h5><p><?= nl2br(htmlspecialchars($p['isi'])) ?></p><small>Dibuat: <?= $p['created_at'] ?> oleh <?= htmlspecialchars($p['fullname']) ?></small></div><?php endforeach; ?></div></div>
<?php include '../includes/footer.php'; ?>