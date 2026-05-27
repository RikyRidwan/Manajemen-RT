<?php
redirectIfNotLoggedIn();
if(!hasRole('superadmin')) { header('Location: ../dashboard.php'); exit; }
global $pdo;

$roles = $pdo->query("SELECT r.*, COUNT(u.id) as jml FROM roles r LEFT JOIN users u ON r.id = u.role_id GROUP BY r.id")->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Hak Akses Role</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-4">
    <h2>Hak Akses Role</h2>
    <table class="table table-bordered">
        <thead><tr><th>Role</th><th>Jumlah Pengguna</th><th>Deskripsi</th></tr></thead>
        <tbody>
            <tr><td>Superadmin</td><td><?= $roles[0]['jml'] ?? 0 ?></td><td>Akses penuh semua fitur</td></tr>
            <tr><td>Admin</td><td><?= $roles[1]['jml'] ?? 0 ?></td><td>Kelola data warga, pengumuman, agenda</td></tr>
            <tr><td>Bendahara</td><td><?= $roles[2]['jml'] ?? 0 ?></td><td>Kelola keuangan, pembayaran</td></tr>
            <tr><td>Warga</td><td><?= $roles[3]['jml'] ?? 0 ?></td><td>Lihat tagihan, bayar, profil</td></tr>
        </tbody>
    </table>
    <p><em>Untuk mengubah role pengguna, gunakan menu Kelola Akun -> Edit.</em></p>
</div>
</body>
</html>