<?php
require_once '../includes/auth.php';
redirectIfNotLoggedIn();
if(!hasRole('bendahara')) { header('Location: ../dashboard.php'); exit; }
$title = "Rekap Kas";
global $pdo;

$filter = isset($_GET['filter']) ? $_GET['filter'] : 'bulanan';
$bulan = isset($_GET['bulan']) ? $_GET['bulan'] : date('m');
$tahun = isset($_GET['tahun']) ? $_GET['tahun'] : date('Y');

if ($filter == 'bulanan') {
    // Pemasukan bulan tertentu
    $stmt_masuk = $pdo->prepare("SELECT SUM(jumlah) FROM pembayaran WHERE status='disetujui' AND MONTH(tanggal_pembayaran) = ? AND YEAR(tanggal_pembayaran) = ?");
    $stmt_masuk->execute([$bulan, $tahun]);
    $total_masuk = $stmt_masuk->fetchColumn();
    if ($total_masuk === false) $total_masuk = 0;

    // Pengeluaran bulan tertentu
    $stmt_keluar = $pdo->prepare("SELECT SUM(jumlah) FROM pengeluaran WHERE MONTH(tanggal) = ? AND YEAR(tanggal) = ?");
    $stmt_keluar->execute([$bulan, $tahun]);
    $total_keluar = $stmt_keluar->fetchColumn();
    if ($total_keluar === false) $total_keluar = 0;

    $judul = "Bulan $bulan / $tahun";
} else {
    // Pemasukan tahun tertentu
    $stmt_masuk = $pdo->prepare("SELECT SUM(jumlah) FROM pembayaran WHERE status='disetujui' AND YEAR(tanggal_pembayaran) = ?");
    $stmt_masuk->execute([$tahun]);
    $total_masuk = $stmt_masuk->fetchColumn();
    if ($total_masuk === false) $total_masuk = 0;

    // Pengeluaran tahun tertentu
    $stmt_keluar = $pdo->prepare("SELECT SUM(jumlah) FROM pengeluaran WHERE YEAR(tanggal) = ?");
    $stmt_keluar->execute([$tahun]);
    $total_keluar = $stmt_keluar->fetchColumn();
    if ($total_keluar === false) $total_keluar = 0;

    $judul = "Tahun $tahun";
}

$saldo = $total_masuk - $total_keluar;

include '../includes/header.php';
?>
<div class="container-fluid">
    <h2 class="mb-4">Rekap Kas RT</h2>
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
            </form>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4">
            <div class="card text-white bg-success mb-3">
                <div class="card-body">
                    <h5 class="card-title">Total Pemasukan</h5>
                    <h3 class="card-text">Rp <?= number_format($total_masuk, 0, ',', '.') ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-white bg-danger mb-3">
                <div class="card-body">
                    <h5 class="card-title">Total Pengeluaran</h5>
                    <h3 class="card-text">Rp <?= number_format($total_keluar, 0, ',', '.') ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-white bg-primary mb-3">
                <div class="card-body">
                    <h5 class="card-title">Sisa Saldo</h5>
                    <h3 class="card-text">Rp <?= number_format($saldo, 0, ',', '.') ?></h3>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include '../includes/footer.php'; ?>