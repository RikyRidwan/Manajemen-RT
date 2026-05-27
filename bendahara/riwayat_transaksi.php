<?php
require_once '../includes/auth.php';
redirectIfNotLoggedIn();
if(!hasRole('bendahara')) { header('Location: ../dashboard.php'); exit; }
$title = "Riwayat Transaksi";
global $pdo;
$riwayat = $pdo->query("(SELECT 'Pemasukan' as tipe, tanggal_pembayaran as tanggal, jumlah, i.nama_iuran as keterangan, u.fullname as nama FROM pembayaran p JOIN users u ON p.user_id=u.id JOIN iuran i ON p.iuran_id=i.id WHERE p.status='disetujui') UNION (SELECT 'Pengeluaran' as tipe, tanggal, jumlah, deskripsi as keterangan, '' as nama FROM pengeluaran) ORDER BY tanggal DESC")->fetchAll();
include '../includes/header.php';
?>
<div class="container-fluid"><h2>Riwayat Transaksi</h2><div class="card"><div class="card-body"><table class="table datatable"><thead><tr><th>Tanggal</th><th>Tipe</th><th>Nama</th><th>Keterangan</th><th>Jumlah</th></tr></thead><tbody><?php foreach($riwayat as $r): ?><tr><td><?= $r['tanggal'] ?></td><td><span class="badge <?= $r['tipe']=='Pemasukan'?'bg-success':'bg-danger' ?>"><?= $r['tipe'] ?></span></td><td><?= htmlspecialchars($r['nama']) ?></td><td><?= htmlspecialchars($r['keterangan']) ?></td><td class="<?= $r['tipe']=='Pemasukan'?'text-success':'text-danger' ?>">Rp <?= number_format($r['jumlah'],0,',','.') ?></td></tr><?php endforeach; ?></tbody></table></div></div></div>
<?php include '../includes/footer.php'; ?>