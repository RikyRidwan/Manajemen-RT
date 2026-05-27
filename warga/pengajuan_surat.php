<?php
require_once '../includes/auth.php';
redirectIfNotLoggedIn();
if(!hasRole('warga')) { header('Location: ../dashboard.php'); exit; }
$user = getCurrentUser();
global $pdo;
$title = "Pengajuan Surat";
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $jenis = $_POST['jenis_surat'];
    $pdo->prepare("INSERT INTO surat_pengajuan (user_id, jenis_surat) VALUES (?,?)")->execute([$user['id'], $jenis]);
    $_SESSION['success'] = "Pengajuan surat berhasil dikirim.";
    header('Location: pengajuan_surat.php'); exit;
}
$pengajuan = $pdo->prepare("SELECT * FROM surat_pengajuan WHERE user_id=? ORDER BY created_at DESC");
$pengajuan->execute([$user['id']]);
$list = $pengajuan->fetchAll();
include '../includes/header.php';
?>
<div class="container-fluid"><h2>Pengajuan Surat</h2><div class="card mb-3"><div class="card-header">Ajukan Surat</div><div class="card-body"><form method="POST"><div class="mb-3"><label>Jenis Surat</label><select name="jenis_surat" class="form-select"><option value="domisili">Surat Domisili</option><option value="pengantar">Surat Pengantar</option></select></div><button type="submit" class="btn btn-primary">Ajukan</button></form></div></div><div class="card"><div class="card-header">Riwayat Pengajuan</div><div class="card-body"><table class="table"><thead><tr><th>Tanggal</th><th>Jenis</th><th>Status</th><th>File</th></tr></thead><tbody><?php foreach($list as $l): ?><tr><td><?= $l['created_at'] ?></td><td><?= $l['jenis_surat'] ?></td><td><?= $l['status'] ?></td><td><?php if($l['file_hasil']): ?><a href="../assets/uploads/surat/<?= $l['file_hasil'] ?>" target="_blank">Download</a><?php else: ?>Menunggu<?php endif; ?></td></tr><?php endforeach; ?></tbody></table></div></div></div>
<?php include '../includes/footer.php'; ?>