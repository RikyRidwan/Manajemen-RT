<?php
require_once '../../includes/auth.php';
redirectIfNotLoggedIn();
if(!hasRole('superadmin')) { echo "Akses ditolak"; exit; }
require_once '../../config/database.php';
global $pdo;


$filter = isset($_GET['filter']) ? $_GET['filter'] : 'bulanan';
$bulan = isset($_GET['bulan']) ? $_GET['bulan'] : date('m');
$tahun = isset($_GET['tahun']) ? $_GET['tahun'] : date('Y');
$warga_id = isset($_GET['warga_id']) ? $_GET['warga_id'] : '';

$pemasukan = [];
$pengeluaran = [];
$judul = '';
$total_pemasukan = 0;
$total_pengeluaran = 0;

if ($filter == 'bulanan') {
    $stmt = $pdo->prepare("SELECT p.tanggal_pembayaran as tanggal, u.fullname as nama, i.nama_iuran as keterangan, p.jumlah, 'Pemasukan' as tipe
                          FROM pembayaran p 
                          JOIN users u ON p.user_id = u.id 
                          JOIN iuran i ON p.iuran_id = i.id 
                          WHERE MONTH(p.tanggal_pembayaran) = ? AND YEAR(p.tanggal_pembayaran) = ? 
                          AND p.status = 'disetujui'");
    $stmt->execute([$bulan, $tahun]);
    $pemasukan = $stmt->fetchAll();

    $stmt2 = $pdo->prepare("SELECT tanggal, deskripsi as keterangan, jumlah, 'Pengeluaran' as tipe FROM pengeluaran 
                            WHERE MONTH(tanggal) = ? AND YEAR(tanggal) = ?");
    $stmt2->execute([$bulan, $tahun]);
    $pengeluaran = $stmt2->fetchAll();

    $judul = "Laporan Keuangan Bulanan - Bulan $bulan / $tahun";
} elseif ($filter == 'tahunan') {
    $stmt = $pdo->prepare("SELECT p.tanggal_pembayaran as tanggal, u.fullname as nama, i.nama_iuran as keterangan, p.jumlah, 'Pemasukan' as tipe
                          FROM pembayaran p 
                          JOIN users u ON p.user_id = u.id 
                          JOIN iuran i ON p.iuran_id = i.id 
                          WHERE YEAR(p.tanggal_pembayaran) = ? AND p.status = 'disetujui'");
    $stmt->execute([$tahun]);
    $pemasukan = $stmt->fetchAll();

    $stmt2 = $pdo->prepare("SELECT tanggal, deskripsi as keterangan, jumlah, 'Pengeluaran' as tipe FROM pengeluaran 
                            WHERE YEAR(tanggal) = ?");
    $stmt2->execute([$tahun]);
    $pengeluaran = $stmt2->fetchAll();

    $judul = "Laporan Keuangan Tahunan - Tahun $tahun";
} elseif ($filter == 'per_warga' && $warga_id) {
    $stmt = $pdo->prepare("SELECT p.tanggal_pembayaran as tanggal, u.fullname as nama, i.nama_iuran as keterangan, p.jumlah, 'Pemasukan' as tipe
                          FROM pembayaran p 
                          JOIN users u ON p.user_id = u.id 
                          JOIN iuran i ON p.iuran_id = i.id 
                          WHERE u.id = ? AND p.status = 'disetujui'");
    $stmt->execute([$warga_id]);
    $pemasukan = $stmt->fetchAll();
    $pengeluaran = [];
    
    $warga_nama = $pdo->prepare("SELECT fullname FROM users WHERE id = ?");
    $warga_nama->execute([$warga_id]);
    $nama = $warga_nama->fetchColumn();
    $judul = "Laporan Keuangan Per Warga: " . htmlspecialchars($nama) . " (Hanya Pemasukan)";
}

$total_pemasukan = array_sum(array_column($pemasukan, 'jumlah'));
$total_pengeluaran = array_sum(array_column($pengeluaran, 'jumlah'));
$saldo = $total_pemasukan - $total_pengeluaran;

$semua_transaksi = array_merge($pemasukan, $pengeluaran);
usort($semua_transaksi, function($a, $b) {
    return strtotime($a['tanggal']) - strtotime($b['tanggal']);
});

$daftar_warga = $pdo->query("SELECT id, fullname FROM users WHERE role_id = 4 ORDER BY fullname")->fetchAll();
?>
<div class="container-fluid">
    <h2 class="mb-4">Laporan Keuangan RT</h2>

    <!-- Form Filter -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">Filter Laporan</div>
        <div class="card-body">
            <form method="GET" action="index.php" class="row g-2">
                <input type="hidden" name="page" value="laporan">
                <div class="col-md-2">
                    <select name="filter" class="form-select">
                        <option value="bulanan" <?= $filter == 'bulanan' ? 'selected' : '' ?>>Bulanan</option>
                        <option value="tahunan" <?= $filter == 'tahunan' ? 'selected' : '' ?>>Tahunan</option>
                        <option value="per_warga" <?= $filter == 'per_warga' ? 'selected' : '' ?>>Per Warga</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="bulan" class="form-select">
                        <?php for ($i = 1; $i <= 12; $i++): ?>
                            <option value="<?= $i ?>" <?= $bulan == $i ? 'selected' : '' ?>><?= $i ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="number" name="tahun" class="form-control" value="<?= $tahun ?>">
                </div>
                <div class="col-md-3">
                    <select name="warga_id" class="form-select">
                        <option value="">Pilih Warga</option>
                        <?php foreach ($daftar_warga as $w): ?>
                            <option value="<?= $w['id'] ?>" <?= $warga_id == $w['id'] ? 'selected' : '' ?>><?= htmlspecialchars($w['fullname']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary w-100">Tampilkan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Ringkasan Kartu -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card text-white bg-success">
                <div class="card-body">
                    <h5 class="card-title">Total Pemasukan</h5>
                    <p class="card-text h3">Rp <?= number_format($total_pemasukan, 0, ',', '.') ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-white bg-danger">
                <div class="card-body">
                    <h5 class="card-title">Total Pengeluaran</h5>
                    <p class="card-text h3">Rp <?= number_format($total_pengeluaran, 0, ',', '.') ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-white bg-primary">
                <div class="card-body">
                    <h5 class="card-title">Sisa Saldo</h5>
                    <p class="card-text h3">Rp <?= number_format($saldo, 0, ',', '.') ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Detail Transaksi (Tanpa DataTables) -->
    <div class="card">
        <div class="card-header bg-success text-white"><?= $judul ?></div>
        <div class="card-body table-responsive" style="max-height: 500px; overflow-y: auto;">
            <table class="table table-bordered table-striped">
                <thead class="table-dark">
                    <tr>
                        <th>Tanggal</th>
                        <th>Tipe</th>
                        <th>Keterangan</th>
                        <th>Jumlah</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($semua_transaksi) > 0): ?>
                        <?php foreach ($semua_transaksi as $tr): ?>
                        <tr>
                            <td><?= htmlspecialchars($tr['tanggal']) ?></td>
                            <td>
                                <span class="badge <?= $tr['tipe'] == 'Pemasukan' ? 'bg-success' : 'bg-danger' ?>">
                                    <?= $tr['tipe'] ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($tr['tipe'] == 'Pemasukan'): ?>
                                    <?= htmlspecialchars($tr['nama'] ?? '') ?> - <?= htmlspecialchars($tr['keterangan'] ?? '') ?>
                                <?php else: ?>
                                    <?= htmlspecialchars($tr['keterangan'] ?? '') ?>
                                <?php endif; ?>
                            </td>
                            <td class="<?= $tr['tipe'] == 'Pemasukan' ? 'text-success' : 'text-danger' ?> fw-bold">
                                Rp <?= number_format($tr['jumlah'], 0, ',', '.') ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="4" class="text-center text-muted">Tidak ada transaksi untuk periode ini</td></tr>
                    <?php endif; ?>
                </tbody>
                <tfoot class="table-secondary">
                    <tr>
                        <th colspan="3" class="text-end">Total Saldo Akhir:</th>
                        <th class="text-primary">Rp <?= number_format($saldo, 0, ',', '.') ?></th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>