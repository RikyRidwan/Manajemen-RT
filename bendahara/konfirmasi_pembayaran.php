<?php
require_once '../includes/auth.php';
redirectIfNotLoggedIn();
if(!hasRole('bendahara')) { header('Location: ../dashboard.php'); exit; }
$title = "Konfirmasi Pembayaran";
global $pdo;

if (isset($_GET['approve'])) {
    $id = $_GET['approve'];
    $pdo->prepare("UPDATE pembayaran SET status='disetujui', approved_by=? WHERE id=?")->execute([$_SESSION['user_id'], $id]);
    $_SESSION['success'] = "Pembayaran disetujui.";
    header('Location: konfirmasi_pembayaran.php'); exit;
}
$pending = $pdo->query("SELECT p.id, u.fullname, p.jumlah, p.bukti_file FROM pembayaran p JOIN users u ON p.user_id=u.id WHERE p.status='pending' ORDER BY p.id DESC")->fetchAll();
include '../includes/header.php';
?>
<div class="container-fluid">
    <h2>Konfirmasi Pembayaran</h2>
    <?php if(isset($_SESSION['success'])): ?><div class="alert alert-success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div><?php endif; ?>
    <div class="card"><div class="card-body"><table class="table datatable"><thead><tr><th>Warga</th><th>Jumlah</th><th>Bukti</th><th>Aksi</th></tr></thead><tbody><?php foreach($pending as $p): ?><tr><td><?= htmlspecialchars($p['fullname']) ?></td><td>Rp <?= number_format($p['jumlah'],0,',','.') ?></td><td><?php if($p['bukti_file']): ?><a href="../assets/uploads/bukti/<?= $p['bukti_file'] ?>" target="_blank">Lihat</a><?php else: ?>Tidak ada<?php endif; ?></td><td><a href="?approve=<?= $p['id'] ?>" class="btn btn-sm btn-success" onclick="return confirm('Setujui pembayaran ini?')">Setujui</a></td></tr><?php endforeach; ?></tbody></table></div></div>
</div>
<?php include '../includes/footer.php'; ?>