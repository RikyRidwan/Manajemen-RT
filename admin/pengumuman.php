<?php
require_once '../includes/auth.php';
redirectIfNotLoggedIn();
if(!hasRole('admin')) { header('Location: ../dashboard.php'); exit; }
$title = "Pengumuman";
global $pdo;

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['tambah'])) {
    $judul = trim($_POST['judul']); $isi = trim($_POST['isi']); $target_role = $_POST['target_role'] ?: null;
    $pdo->prepare("INSERT INTO pengumuman (judul, isi, created_by, target_role_id) VALUES (?,?,?,?)")->execute([$judul, $isi, $_SESSION['user_id'], $target_role]);
    $_SESSION['success'] = "Pengumuman ditambahkan."; header('Location: pengumuman.php'); exit;
}
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit'])) {
    $id = $_POST['id']; $judul = trim($_POST['judul']); $isi = trim($_POST['isi']); $target_role = $_POST['target_role'] ?: null;
    $pdo->prepare("UPDATE pengumuman SET judul=?, isi=?, target_role_id=? WHERE id=?")->execute([$judul, $isi, $target_role, $id]);
    $_SESSION['success'] = "Pengumuman diperbarui."; header('Location: pengumuman.php'); exit;
}
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    $pdo->prepare("DELETE FROM pengumuman WHERE id=?")->execute([$id]);
    $_SESSION['success'] = "Pengumuman dihapus."; header('Location: pengumuman.php'); exit;
}
$pengumuman = $pdo->query("SELECT p.*, u.fullname as pembuat, r.nama_role as target FROM pengumuman p LEFT JOIN users u ON p.created_by = u.id LEFT JOIN roles r ON p.target_role_id = r.id ORDER BY p.created_at DESC")->fetchAll();
$roles = $pdo->query("SELECT id, nama_role FROM roles")->fetchAll();
include '../includes/header.php';
?>
<div class="container-fluid">
    <h2>Pengumuman RT</h2>
    <?php if(isset($_SESSION['success'])): ?><div class="alert alert-success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div><?php endif; ?>
    <div class="card mb-4"><div class="card-header bg-primary text-white">Tambah Pengumuman</div><div class="card-body"><form method="POST"><div class="mb-2"><input type="text" name="judul" class="form-control" placeholder="Judul" required></div><div class="mb-2"><textarea name="isi" class="form-control" rows="3" placeholder="Isi pengumuman" required></textarea></div><div class="mb-2"><select name="target_role" class="form-select"><option value="">Semua Role</option><?php foreach($roles as $r): ?><option value="<?= $r['id'] ?>"><?= ucfirst($r['nama_role']) ?></option><?php endforeach; ?></select></div><button type="submit" name="tambah" class="btn btn-primary">Simpan</button></form></div></div>
    <div class="card"><div class="card-header">Daftar Pengumuman</div><div class="card-body"><table class="table datatable"><thead><tr><th>Judul</th><th>Isi</th><th>Target</th><th>Pembuat</th><th>Tanggal</th><th>Aksi</th></tr></thead><tbody><?php foreach($pengumuman as $p): ?><tr><td><?= htmlspecialchars($p['judul']) ?></td><td><?= htmlspecialchars(substr($p['isi'],0,100)) ?>...</td><td><?= $p['target'] ?? 'Semua' ?></td><td><?= $p['pembuat'] ?></td><td><?= $p['created_at'] ?></td><td><a href="?hapus=<?= $p['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Hapus?')">Hapus</a> <button class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#editModal<?= $p['id'] ?>">Edit</button></td></tr>
<div class="modal fade" id="editModal<?= $p['id'] ?>"><div class="modal-dialog"><div class="modal-content"><form method="POST"><div class="modal-header"><h5>Edit Pengumuman</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body"><input type="hidden" name="id" value="<?= $p['id'] ?>"><div class="mb-2"><label>Judul</label><input type="text" name="judul" class="form-control" value="<?= htmlspecialchars($p['judul']) ?>"></div><div class="mb-2"><label>Isi</label><textarea name="isi" class="form-control"><?= htmlspecialchars($p['isi']) ?></textarea></div><div class="mb-2"><label>Target</label><select name="target_role" class="form-select"><option value="">Semua</option><?php foreach($roles as $r): ?><option value="<?= $r['id'] ?>" <?= $p['target_role_id']==$r['id']?'selected':'' ?>><?= ucfirst($r['nama_role']) ?></option><?php endforeach; ?></select></div></div><div class="modal-footer"><button type="submit" name="edit" class="btn btn-primary">Simpan</button></div></form></div></div></div>
<?php endforeach; ?></tbody></table></div></div>
</div>
<?php include '../includes/footer.php'; ?>