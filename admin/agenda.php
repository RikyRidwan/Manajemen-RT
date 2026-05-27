<?php
require_once '../includes/auth.php';
redirectIfNotLoggedIn();
if(!hasRole('admin')) { header('Location: ../dashboard.php'); exit; }
$title = "Agenda Kegiatan";
global $pdo;

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['tambah'])) {
    $judul = trim($_POST['judul']); $tanggal = $_POST['tanggal']; $lokasi = trim($_POST['lokasi']); $deskripsi = trim($_POST['deskripsi']);
    $pdo->prepare("INSERT INTO agenda (judul, tanggal, lokasi, deskripsi, created_by) VALUES (?,?,?,?,?)")->execute([$judul, $tanggal, $lokasi, $deskripsi, $_SESSION['user_id']]);
    $_SESSION['success'] = "Agenda ditambahkan."; header('Location: agenda.php'); exit;
}
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit'])) {
    $id = $_POST['id']; $judul = trim($_POST['judul']); $tanggal = $_POST['tanggal']; $lokasi = trim($_POST['lokasi']); $deskripsi = trim($_POST['deskripsi']);
    $pdo->prepare("UPDATE agenda SET judul=?, tanggal=?, lokasi=?, deskripsi=? WHERE id=?")->execute([$judul, $tanggal, $lokasi, $deskripsi, $id]);
    $_SESSION['success'] = "Agenda diperbarui."; header('Location: agenda.php'); exit;
}
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    $pdo->prepare("DELETE FROM agenda WHERE id=?")->execute([$id]);
    $_SESSION['success'] = "Agenda dihapus."; header('Location: agenda.php'); exit;
}
$agenda = $pdo->query("SELECT * FROM agenda ORDER BY tanggal DESC")->fetchAll();
include '../includes/header.php';
?>
<div class="container-fluid">
    <h2>Agenda Kegiatan</h2>
    <?php if(isset($_SESSION['success'])): ?><div class="alert alert-success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div><?php endif; ?>
    <div class="card mb-4"><div class="card-header bg-primary text-white">Tambah Agenda</div><div class="card-body"><form method="POST" class="row g-2"><div class="col-md-4"><input type="text" name="judul" class="form-control" placeholder="Judul" required></div><div class="col-md-2"><input type="date" name="tanggal" class="form-control" required></div><div class="col-md-3"><input type="text" name="lokasi" class="form-control" placeholder="Lokasi"></div><div class="col-md-3"><textarea name="deskripsi" class="form-control" placeholder="Deskripsi"></textarea></div><div class="col-md-12"><button type="submit" name="tambah" class="btn btn-primary">Simpan</button></div></form></div></div>
    <div class="card"><div class="card-header">Daftar Agenda</div><div class="card-body"><table class="table datatable"><thead><tr><th>Tanggal</th><th>Judul</th><th>Lokasi</th><th>Deskripsi</th><th>Aksi</th></tr></thead><tbody><?php foreach($agenda as $a): ?><tr><td><?= $a['tanggal'] ?></td><td><?= htmlspecialchars($a['judul']) ?></td><td><?= htmlspecialchars($a['lokasi']) ?></td><td><?= htmlspecialchars($a['deskripsi']) ?></td><td><a href="?hapus=<?= $a['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Hapus?')">Hapus</a> <button class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#editModal<?= $a['id'] ?>">Edit</button></td></tr>
<div class="modal fade" id="editModal<?= $a['id'] ?>"><div class="modal-dialog"><div class="modal-content"><form method="POST"><div class="modal-header"><h5>Edit Agenda</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body"><input type="hidden" name="id" value="<?= $a['id'] ?>"><div class="mb-2"><label>Judul</label><input type="text" name="judul" class="form-control" value="<?= htmlspecialchars($a['judul']) ?>"></div><div class="mb-2"><label>Tanggal</label><input type="date" name="tanggal" class="form-control" value="<?= $a['tanggal'] ?>"></div><div class="mb-2"><label>Lokasi</label><input type="text" name="lokasi" class="form-control" value="<?= htmlspecialchars($a['lokasi']) ?>"></div><div class="mb-2"><label>Deskripsi</label><textarea name="deskripsi" class="form-control"><?= htmlspecialchars($a['deskripsi']) ?></textarea></div></div><div class="modal-footer"><button type="submit" name="edit" class="btn btn-primary">Simpan</button></div></form></div></div></div>
<?php endforeach; ?></tbody></table></div></div>
</div>
<?php include '../includes/footer.php'; ?>