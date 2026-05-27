<?php
require_once '../../includes/auth.php';
redirectIfNotLoggedIn();
if(!hasRole('superadmin')) { echo "Akses ditolak"; exit; }
require_once '../../config/database.php';
global $pdo;

// Ambil data RT dari pengaturan
$nama_rt = $pdo->query("SELECT setting_value FROM pengaturan WHERE setting_key='nama_rt'")->fetchColumn();
$ketua_rt = $pdo->query("SELECT setting_value FROM pengaturan WHERE setting_key='ketua_rt'")->fetchColumn();
$alamat_rt = $pdo->query("SELECT setting_value FROM pengaturan WHERE setting_key='alamat_rt'")->fetchColumn();
?>
<div class="card">
    <div class="card-header bg-primary text-white">Data RT</div>
    <div class="card-body">
        <table class="table table-bordered">
            <tr><th>Nama RT</th><td><?= htmlspecialchars($nama_rt) ?></td></tr>
            <tr><th>Ketua RT</th><td><?= htmlspecialchars($ketua_rt) ?></td></tr>
            <tr><th>Alamat</th><td><?= htmlspecialchars($alamat_rt) ?></td></tr>
        </table>
        <p class="text-muted">Halaman ini untuk mengelola profil RT (sedang dalam pengembangan).</p>
    </div>
</div>