<?php
require_once '../includes/auth.php';
redirectIfNotLoggedIn();
if(!hasRole('bendahara')) { header('Location: ../dashboard.php'); exit; }
$title = "Cetak Laporan";
global $pdo;

$filter = isset($_GET['filter']) ? $_GET['filter'] : 'bulanan';
$bulan = isset($_GET['bulan']) ? $_GET['bulan'] : date('m');
$tahun = isset($_GET['tahun']) ? $_GET['tahun'] : date('Y');

if ($filter == 'bulanan') {
    // Data pemasukan
    $stmt_masuk = $pdo->prepare("
        SELECT p.tanggal_pembayaran, u.fullname, i.nama_iuran, p.jumlah
        FROM pembayaran p
        JOIN users u ON p.user_id = u.id
        JOIN iuran i ON p.iuran_id = i.id
        WHERE p.status = 'disetujui'
        AND MONTH(p.tanggal_pembayaran) = ?
        AND YEAR(p.tanggal_pembayaran) = ?
        ORDER BY p.tanggal_pembayaran DESC
    ");
    $stmt_masuk->execute([$bulan, $tahun]);
    $list_masuk = $stmt_masuk->fetchAll();

    // Data pengeluaran
    $stmt_keluar = $pdo->prepare("
        SELECT tanggal, deskripsi, jumlah
        FROM pengeluaran
        WHERE MONTH(tanggal) = ? AND YEAR(tanggal) = ?
        ORDER BY tanggal DESC
    ");
    $stmt_keluar->execute([$bulan, $tahun]);
    $list_keluar = $stmt_keluar->fetchAll();

    $judul = "Laporan Keuangan Bulan $bulan / $tahun";
} else {
    // Pemasukan tahunan
    $stmt_masuk = $pdo->prepare("
        SELECT p.tanggal_pembayaran, u.fullname, i.nama_iuran, p.jumlah
        FROM pembayaran p
        JOIN users u ON p.user_id = u.id
        JOIN iuran i ON p.iuran_id = i.id
        WHERE p.status = 'disetujui'
        AND YEAR(p.tanggal_pembayaran) = ?
        ORDER BY p.tanggal_pembayaran DESC
    ");
    $stmt_masuk->execute([$tahun]);
    $list_masuk = $stmt_masuk->fetchAll();

    // Pengeluaran tahunan
    $stmt_keluar = $pdo->prepare("
        SELECT tanggal, deskripsi, jumlah
        FROM pengeluaran
        WHERE YEAR(tanggal) = ?
        ORDER BY tanggal DESC
    ");
    $stmt_keluar->execute([$tahun]);
    $list_keluar = $stmt_keluar->fetchAll();

    $judul = "Laporan Keuangan Tahun $tahun";
}

$total_masuk = array_sum(array_column($list_masuk, 'jumlah'));
$total_keluar = array_sum(array_column($list_keluar, 'jumlah'));
$saldo = $total_masuk - $total_keluar;

include '../includes/header.php';
?>
<div class="container-fluid">
    <h2 class="mb-4">Cetak Laporan Keuangan</h2>
    
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-2">
                    <label class="form-label">Filter</label>
                    <select name="filter" class="form-select">
                        <option value="bulanan" <?= $filter == 'bulanan' ? 'selected' : '' ?>>Bulanan</option>
                        <option value="tahunan" <?= $filter == 'tahunan' ? 'selected' : '' ?>>Tahunan</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Bulan</label>
                    <select name="bulan" class="form-select">
                        <?php for ($i = 1; $i <= 12; $i++): ?>
                            <option value="<?= $i ?>" <?= $bulan == $i ? 'selected' : '' ?>><?= $i ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Tahun</label>
                    <input type="number" name="tahun" class="form-control" value="<?= $tahun ?>">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">Tampilkan</button>
                </div>
                <div class="col-md-2">
                    <button type="button" onclick="window.print()" class="btn btn-success w-100">Cetak / PDF</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header bg-primary text-white">
            <h4><?= $judul ?></h4>
        </div>
        <div class="card-body">
            <!-- Tabel Pemasukan -->
            <h5 class="text-success">Pemasukan (Iuran Disetujui)</h5>
            <div class="table-responsive mb-4">
                <table class="table table-bordered table-striped">
                    <thead class="table-light">
                        <tr><th>Tanggal</th><th>Warga</th><th>Jenis Iuran</th><th>Jumlah</th></tr>
                    </thead>
                    <tbody>
                        <?php if (count($list_masuk) > 0): ?>
                            <?php foreach ($list_masuk as $row): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['tanggal_pembayaran']) ?></td>
                                <td><?= htmlspecialchars($row['fullname']) ?></td>
                                <td><?= htmlspecialchars($row['nama_iuran']) ?></td>
                                <td>Rp <?= number_format($row['jumlah'], 0, ',', '.') ?></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="4" class="text-center text-muted">Tidak ada data pemasukan</td></tr>
                        <?php endif; ?>
                    </tbody>
                    <tfoot class="table-secondary">
                        <tr><th colspan="3" class="text-end">Total Pemasukan</th><th>Rp <?= number_format($total_masuk, 0, ',', '.') ?></th></tr>
                    </tfoot>
                </table>
            </div>

            <!-- Tabel Pengeluaran -->
            <h5 class="text-danger mt-4">Pengeluaran</h5>
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead class="table-light">
                        <tr><th>Tanggal</th><th>Deskripsi</th><th>Jumlah</th></tr>
                    </thead>
                    <tbody>
                        <?php if (count($list_keluar) > 0): ?>
                            <?php foreach ($list_keluar as $row): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['tanggal']) ?></td>
                                <td><?= htmlspecialchars($row['deskripsi']) ?></td>
                                <td>Rp <?= number_format($row['jumlah'], 0, ',', '.') ?></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="3" class="text-center text-muted">Tidak ada data pengeluaran</td></tr>
                        <?php endif; ?>
                    </tbody>
                    <tfoot class="table-secondary">
                        <tr><th colspan="2" class="text-end">Total Pengeluaran</th><th>Rp <?= number_format($total_keluar, 0, ',', '.') ?></th></tr>
                        <tr class="table-primary"><th colspan="2" class="text-end">Sisa Saldo</th><th>Rp <?= number_format($saldo, 0, ',', '.') ?></th></tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Style untuk cetak -->
<style media="print">
    .btn, form, .card-header .btn, .navbar-top, .sidebar, .footer { display: none; }
    .card { border: none; box-shadow: none; }
    .card-header { background-color: #f8f9fa !important; color: black !important; }
    body { background: white; padding: 0; margin: 0; }
    .content-wrapper { padding: 0; }
</style>

<?php include '../includes/footer.php'; ?>