<?php
require_once '../../includes/auth.php';
redirectIfNotLoggedIn();
if(!hasRole('superadmin')) { echo "Akses ditolak"; exit; }
require_once '../../config/database.php';
global $pdo;


// Proses simpan pengaturan
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    foreach ($_POST as $key => $value) {
        if (strpos($key, 'setting_') === 0) {
            $k = substr($key, 8);
            $stmt = $pdo->prepare("INSERT INTO pengaturan (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?");
            $stmt->execute([$k, $value, $value]);
        }
    }
    $_SESSION['success'] = "Pengaturan berhasil disimpan.";
    header('Location: index.php?page=pengaturan');
    exit;
}

// Ambil data pengaturan
$settings = [];
$stmt = $pdo->query("SELECT setting_key, setting_value FROM pengaturan");
while ($row = $stmt->fetch()) {
    $settings[$row['setting_key']] = $row['setting_value'];
}
?>

<div class="container-fluid">
    <h2 class="mb-4">Pengaturan Aplikasi</h2>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= $_SESSION['success']; unset($_SESSION['success']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-header bg-primary text-white">Konfigurasi Sistem</div>
        <div class="card-body">
            <form method="POST" action="index.php?page=pengaturan">
                <div class="mb-3">
                    <label for="app_name" class="form-label">Nama Aplikasi</label>
                    <input type="text" name="setting_app_name" id="app_name" class="form-control" 
                           value="<?= htmlspecialchars($settings['app_name'] ?? 'Manajemen RT') ?>">
                </div>
                <div class="mb-3">
                    <label for="admin_email" class="form-label">Email Administrator</label>
                    <input type="email" name="setting_admin_email" id="admin_email" class="form-control" 
                           value="<?= htmlspecialchars($settings['admin_email'] ?? 'admin@rt.com') ?>">
                </div>
                <div class="mb-3">
                    <label for="wa_rt" class="form-label">Nomor WhatsApp RT</label>
                    <input type="text" name="setting_wa_rt" id="wa_rt" class="form-control" 
                           value="<?= htmlspecialchars($settings['wa_rt'] ?? '') ?>">
                </div>
                <button type="submit" class="btn btn-primary">Simpan Pengaturan</button>
            </form>
        </div>
    </div>
</div>