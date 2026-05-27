<?php
require_once '../includes/auth.php';
redirectIfNotLoggedIn();
if(!hasRole('warga')) { header('Location: ../dashboard.php'); exit; }
$user = getCurrentUser();
global $pdo;
$title = "Profil Saya";
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $fullname = $_POST['fullname'];
    $no_hp = $_POST['no_hp'];
    $alamat = $_POST['alamat'];
    $pdo->prepare("UPDATE users SET fullname=?, no_hp=?, alamat=? WHERE id=?")->execute([$fullname, $no_hp, $alamat, $user['id']]);
    $_SESSION['success'] = "Profil diperbarui.";
    header('Location: profil.php'); exit;
}
include '../includes/header.php';
?>
<div class="container-fluid"><h2>Edit Profil</h2><form method="POST"><div class="mb-3"><label>Nama Lengkap</label><input type="text" name="fullname" class="form-control" value="<?= htmlspecialchars($user['fullname']) ?>" required></div><div class="mb-3"><label>No HP</label><input type="text" name="no_hp" class="form-control" value="<?= htmlspecialchars($user['no_hp']) ?>"></div><div class="mb-3"><label>Alamat</label><textarea name="alamat" class="form-control"><?= htmlspecialchars($user['alamat']) ?></textarea></div><button type="submit" class="btn btn-primary">Simpan</button></form></div>
<?php include '../includes/footer.php'; ?>  