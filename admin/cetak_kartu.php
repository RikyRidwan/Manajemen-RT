<?php
require_once '../includes/auth.php';
redirectIfNotLoggedIn();
if(!hasRole('admin')) { header('Location: ../dashboard.php'); exit; }
global $pdo;

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    // Hapus nik dari SELECT
    $warga = $pdo->prepare("SELECT u.*, wd.no_kk, wd.blok_rumah FROM users u JOIN warga_detail wd ON u.id=wd.user_id WHERE u.id=? AND u.role_id=4");
    $warga->execute([$id]);
    $w = $warga->fetch();
    if (!$w) die("Warga tidak ditemukan.");
    ?>
    <!DOCTYPE html>
    <html>
    <head><title>Kartu Iuran - <?= htmlspecialchars($w['fullname']) ?></title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet"><style>body{padding:20px;} .card-kartu{border:1px solid #ccc; border-radius:10px; padding:20px; width:300px; margin:auto;}</style></head>
    <body><div class="card-kartu"><h4>Kartu Iuran RT</h4><hr><p><strong>Nama:</strong> <?= htmlspecialchars($w['fullname']) ?></p><p><strong>No KK:</strong> <?= htmlspecialchars($w['no_kk']) ?></p><p><strong>Blok:</strong> <?= htmlspecialchars($w['blok_rumah']) ?></p><hr><p class="text-muted">Dikeluarkan oleh RT</p><button onclick="window.print()" class="btn btn-primary mt-2">Cetak</button></div></body></html>
    <?php
    exit;
}

// Hapus nik dari SELECT, gunakan no_kk
$warga = $pdo->query("SELECT u.id, u.fullname, u.username, wd.no_kk, wd.blok_rumah FROM users u JOIN warga_detail wd ON u.id=wd.user_id WHERE u.role_id=4")->fetchAll();
include '../includes/header.php';
?>
<div class="container-fluid"><h2>Cetak Kartu Iuran</h2><div class="card"><div class="card-body"><table class="table datatable"><thead><tr><th>Nama</th><th>No KK</th><th>Blok</th><th>Aksi</th></tr></thead><tbody><?php foreach($warga as $w): ?><td><?= htmlspecialchars($w['fullname']) ?></td><td><?= htmlspecialchars($w['no_kk']) ?></td><td><?= htmlspecialchars($w['blok_rumah']) ?></td><td><a href="cetak_kartu.php?id=<?= $w['id'] ?>" target="_blank" class="btn btn-sm btn-primary">Cetak Kartu</a></td></tr><?php endforeach; ?></tbody></table></div></div></div>
<?php include '../includes/footer.php'; ?>