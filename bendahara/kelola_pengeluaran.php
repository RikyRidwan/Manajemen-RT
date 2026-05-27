<?php
require_once '../includes/auth.php';
redirectIfNotLoggedIn();
if(!hasRole('bendahara')) { header('Location: ../dashboard.php'); exit; }
$title = "Pengeluaran";
global $pdo;

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['tambah'])) {
    $deskripsi = trim($_POST['deskripsi']); $jumlah = $_POST['jumlah']; $tanggal = $_POST['tanggal'];
    $pdo->prepare("INSERT INTO pengeluaran (deskripsi, jumlah, tanggal, input_by) VALUES (?,?,?,?)")->execute([$deskripsi, $jumlah, $tanggal, $_SESSION['user_id']]);
    $_SESSION['success'] = "Pengeluaran ditambahkan."; header('Location: kelola_pengeluaran.php'); exit;
}
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    $pdo->prepare("DELETE FROM pengeluaran WHERE id=?")->execute([$id]);
    $_SESSION['success'] = "Pengeluaran dihapus."; header('Location: kelola_pengeluaran.php'); exit;
}
$pengeluaran = $pdo->query("SELECT * FROM pengeluaran ORDER BY tanggal DESC")->fetchAll();
include '../includes/header.php';
?>
<div class="container-fluid"><h2>Pengeluaran RT</h2>
<?php if(isset($_SESSION['success'])): ?><div class="alert alert-success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div><?php endif; ?>
<div class="card mb-3"><div class="card-header bg-primary text-white">Tambah Pengeluaran</div><div class="card-body"><form method="POST" class="row g-2"><div class="col-md-4"><input type="text" name="deskripsi" class="form-control" placeholder="Deskripsi" required></div><div class="col-md-3"><input type="number" name="jumlah" class="form-control" placeholder="Jumlah" required></div><div class="col-md-3"><input type="date" name="tanggal" class="form-control" value="<?= date('Y-m-d') ?>" required></div><div class="col-md-2"><button type="submit" name="tambah" class="btn btn-primary">Tambah</button></div></form></div></div>
<div class="card"><div class="card-body"><table class="table datatable"><thead><tr><th>Tanggal</th><th>Deskripsi</th><th>Jumlah</th><th>Aksi</th></tr></thead><tbody><?php foreach($pengeluaran as $p): ?><tr><td><?= $p['tanggal'] ?></td><td><?= htmlspecialchars($p['deskripsi']) ?></td><td>Rp <?= number_format($p['jumlah'],0,',','.') ?></td><td><a href="?hapus=<?= $p['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Hapus?')">Hapus</a></td></tr><?php endforeach; ?></tbody></table></div></div></div>
<?php include '../includes/footer.php'; ?>