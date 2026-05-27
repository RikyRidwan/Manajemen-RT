<?php
require_once '../includes/auth.php';
redirectIfNotLoggedIn();
if(!hasRole('bendahara')) { header('Location: ../dashboard.php'); exit; }
$title = "Input Pembayaran";
global $pdo;

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['simpan'])) {
    $user_id = $_POST['user_id'];
    $iuran_id = $_POST['iuran_id'];
    $jumlah = $_POST['jumlah'];
    $catatan = trim($_POST['catatan']);
    $periode = $pdo->query("SELECT id FROM periode WHERE status='aktif' LIMIT 1")->fetch();
    if (!$periode) {
        $_SESSION['error'] = "Tidak ada periode aktif. Hubungi Superadmin.";
        header('Location: input_pembayaran.php'); exit;
    }
    $periode_id = $periode['id'];
    $stmt = $pdo->prepare("INSERT INTO pembayaran (user_id, iuran_id, periode_id, jumlah, catatan, status, tanggal_pembayaran) VALUES (?,?,?,?,?,'pending', NOW())");
    $stmt->execute([$user_id, $iuran_id, $periode_id, $jumlah, $catatan]);
    $_SESSION['success'] = "Pembayaran dicatat, menunggu persetujuan Superadmin.";
    header('Location: input_pembayaran.php'); exit;
}

$warga = $pdo->query("SELECT id, fullname FROM users WHERE role_id = 4 ORDER BY fullname")->fetchAll();
$iuran = $pdo->query("SELECT * FROM iuran")->fetchAll();
include '../includes/header.php';
?>
<div class="container-fluid">
    <h2>Input Pembayaran Kas</h2>
    <?php if(isset($_SESSION['success'])): ?><div class="alert alert-success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div><?php endif; ?>
    <?php if(isset($_SESSION['error'])): ?><div class="alert alert-danger"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div><?php endif; ?>
    <div class="card"><div class="card-header bg-primary text-white">Form Pembayaran</div><div class="card-body"><form method="POST"><div class="mb-3"><label>Pilih Warga</label><select name="user_id" class="form-select" required><option value="">-- Pilih --</option><?php foreach($warga as $w): ?><option value="<?= $w['id'] ?>"><?= htmlspecialchars($w['fullname']) ?></option><?php endforeach; ?></select></div><div class="mb-3"><label>Jenis Iuran</label><select name="iuran_id" class="form-select" required><option value="">-- Pilih --</option><?php foreach($iuran as $i): ?><option value="<?= $i['id'] ?>" data-nominal="<?= $i['nominal'] ?>"><?= htmlspecialchars($i['nama_iuran']) ?> - Rp <?= number_format($i['nominal'],0,',','.') ?></option><?php endforeach; ?></select></div><div class="mb-3"><label>Jumlah Dibayar</label><input type="number" name="jumlah" class="form-control" required></div><div class="mb-3"><label>Catatan</label><textarea name="catatan" class="form-control"></textarea></div><button type="submit" name="simpan" class="btn btn-success">Simpan Pembayaran</button></form></div></div>
</div>
<script>document.querySelector('select[name="iuran_id"]').addEventListener('change', function(){ let nominal = this.options[this.selectedIndex].getAttribute('data-nominal'); if(nominal) document.querySelector('input[name="jumlah"]').value = nominal; });</script>
<?php include '../includes/footer.php'; ?>