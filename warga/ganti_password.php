<?php
require_once '../includes/auth.php';
redirectIfNotLoggedIn();
if(!hasRole('warga')) { header('Location: ../dashboard.php'); exit; }
$user = getCurrentUser();
global $pdo;
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $old = $_POST['old_password'];
    $new = $_POST['new_password'];
    $confirm = $_POST['confirm_password'];
    $stmt = $pdo->prepare("SELECT password FROM users WHERE id=?");
    $stmt->execute([$user['id']]);
    $hash = $stmt->fetchColumn();
    if (!password_verify($old, $hash)) {
        $_SESSION['error'] = "Password lama salah.";
    } elseif ($new != $confirm) {
        $_SESSION['error'] = "Konfirmasi password tidak cocok.";
    } else {
        $pdo->prepare("UPDATE users SET password=? WHERE id=?")->execute([password_hash($new, PASSWORD_DEFAULT), $user['id']]);
        $_SESSION['success'] = "Password berhasil diubah.";
    }
    header('Location: ganti_password.php'); exit;
}
include '../includes/header.php';
?>
<div class="container-fluid"><h2>Ganti Password</h2><form method="POST"><div class="mb-3"><label>Password Lama</label><input type="password" name="old_password" class="form-control" required></div><div class="mb-3"><label>Password Baru</label><input type="password" name="new_password" class="form-control" required></div><div class="mb-3"><label>Konfirmasi Password Baru</label><input type="password" name="confirm_password" class="form-control" required></div><button type="submit" class="btn btn-primary">Ganti Password</button></form></div>
<?php include '../includes/footer.php'; ?>