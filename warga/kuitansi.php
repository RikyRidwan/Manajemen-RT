<?php
require_once '../includes/auth.php';
redirectIfNotLoggedIn();
if(!hasRole('warga')) { header('Location: ../dashboard.php'); exit; }
$user = getCurrentUser();
global $pdo;

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) {
    header('Location: tagihan.php');
    exit;
}

$stmt = $pdo->prepare("SELECT p.*, i.nama_iuran, pr.tahun, pr.bulan, u.fullname 
                       FROM pembayaran p 
                       JOIN iuran i ON p.iuran_id = i.id 
                       JOIN periode pr ON p.periode_id = pr.id 
                       JOIN users u ON p.user_id = u.id 
                       WHERE p.id = ? AND p.user_id = ? AND p.status = 'disetujui'");
$stmt->execute([$id, $user['id']]);
$data = $stmt->fetch();
if (!$data) {
    echo "<script>alert('Kuitansi tidak tersedia.'); window.location.href='tagihan.php';</script>";
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kuitansi Pembayaran</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { padding: 20px; }
        .kuitansi { max-width: 600px; margin: auto; border: 1px solid #ddd; padding: 20px; border-radius: 10px; background: #fff; }
        .header { text-align: center; margin-bottom: 20px; }
        .footer { text-align: center; margin-top: 20px; font-size: 12px; color: #777; }
        hr { margin: 15px 0; }
        @media print {
            .btn-print { display: none; }
            body { padding: 0; margin: 0; }
            .kuitansi { border: none; }
        }
    </style>
</head>
<body>
<div class="kuitansi">
    <div class="header">
        <h3>KUITANSI PEMBAYARAN</h3>
        <p>Sistem Manajemen RT</p>
    </div>
    <hr>
    <table class="table table-borderless">
        <tr><td width="150">Nama Warga</td><td>: <?= htmlspecialchars($data['fullname']) ?></td></tr>
        <tr><td>Jenis Iuran</td><td>: <?= htmlspecialchars($data['nama_iuran']) ?></td></tr>
        <tr><td>Periode</td><td>: <?= $data['bulan'] . '/' . $data['tahun'] ?></td></tr>
        <tr><td>Jumlah Dibayar</td><td>: Rp <?= number_format($data['jumlah'], 0, ',', '.') ?></td></tr>
        <tr><td>Tanggal Bayar</td><td>: <?= date('d-m-Y H:i:s', strtotime($data['tanggal_pembayaran'])) ?></td></tr>
        <tr><td>Status</td><td>: <span class="badge bg-success">LUNAS</span></td></tr>
    </table>
    <hr>
    <div class="footer">
        <p>Dikeluarkan oleh Sistem Manajemen RT<br>Terima kasih atas partisipasi Anda.</p>
    </div>
    <div class="text-center mt-3 btn-print">
        <button onclick="window.print()" class="btn btn-primary">Cetak Kuitansi</button>
        <a href="tagihan.php" class="btn btn-secondary">Kembali</a>
    </div>
</div>
</body>
</html>