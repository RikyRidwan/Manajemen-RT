<?php
require_once '../includes/auth.php';
redirectIfNotLoggedIn();
if(!hasRole('admin')) { header('Location: ../dashboard.php'); exit; }
$title = "Verifikasi Warga";
global $pdo;

if (isset($_GET['verif'])) {
    $id = $_GET['verif'];
    $pdo->prepare("UPDATE users SET status_aktif='aktif' WHERE id=? AND role_id=4")->execute([$id]);
    $_SESSION['success'] = "Warga diverifikasi.";
    header('Location: verifikasi_warga.php');
    exit;
}

// Hapus wd.nik dari SELECT
$pending = $pdo->query("SELECT u.* FROM users u LEFT JOIN warga_detail wd ON u.id = wd.user_id WHERE u.role_id = 4 AND u.status_aktif = 'nonaktif'")->fetchAll();

include '../includes/header.php';
?>
<div class="container-fluid">
    <h2>Verifikasi Registrasi Warga</h2>
    <?php if(isset($_SESSION['success'])): ?>
        <div class="alert alert-success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
    <?php endif; ?>
    <div class="card">
        <div class="card-header">Daftar Warga Menunggu Verifikasi</div>
        <div class="card-body">
            <table class="table table-bordered datatable">
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($pending as $w): ?>
                    <tr>
                        <td><?= htmlspecialchars($w['fullname']) ?></td>
                        <td><?= htmlspecialchars($w['username']) ?></td>
                        <td><?= htmlspecialchars($w['email']) ?></td>
                        <td><a href="?verif=<?= $w['id'] ?>" class="btn btn-sm btn-success" onclick="return confirm('Verifikasi warga ini?')">Verifikasi</a></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php include '../includes/footer.php'; ?>