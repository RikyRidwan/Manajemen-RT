<?php
require_once '../includes/auth.php';
redirectIfNotLoggedIn();
if(!hasRole('bendahara')) { header('Location: ../dashboard.php'); exit; }
$title = "Dashboard Bendahara";
global $pdo;

// Statistik
$total_pemasukan = $pdo->query("SELECT COALESCE(SUM(jumlah),0) FROM pembayaran WHERE status='disetujui'")->fetchColumn();
$total_pengeluaran = $pdo->query("SELECT COALESCE(SUM(jumlah),0) FROM pengeluaran")->fetchColumn();
$saldo = $total_pemasukan - $total_pengeluaran;
$pending = $pdo->query("SELECT COUNT(*) FROM pembayaran WHERE status='pending'")->fetchColumn();

// Data grafik 6 bulan terakhir
$bulan_labels = [];
$pemasukan_chart = [];
$pengeluaran_chart = [];
for ($i = 5; $i >= 0; $i--) {
    $date = new DateTime();
    $date->modify("-$i months");
    $bulan_labels[] = $date->format('M Y');
    $bulan = $date->format('m');
    $tahun = $date->format('Y');
    
    $stmt = $pdo->prepare("SELECT COALESCE(SUM(jumlah),0) FROM pembayaran WHERE status='disetujui' AND MONTH(tanggal_pembayaran)=? AND YEAR(tanggal_pembayaran)=?");
    $stmt->execute([$bulan, $tahun]);
    $pemasukan_chart[] = (int) $stmt->fetchColumn();
    
    $stmt2 = $pdo->prepare("SELECT COALESCE(SUM(jumlah),0) FROM pengeluaran WHERE MONTH(tanggal)=? AND YEAR(tanggal)=?");
    $stmt2->execute([$bulan, $tahun]);
    $pengeluaran_chart[] = (int) $stmt2->fetchColumn();
}

// 5 pemasukan terbaru (disetujui)
$pemasukan_terbaru = $pdo->query("
    SELECT p.tanggal_pembayaran, u.fullname, i.nama_iuran, p.jumlah 
    FROM pembayaran p 
    JOIN users u ON p.user_id = u.id 
    JOIN iuran i ON p.iuran_id = i.id 
    WHERE p.status = 'disetujui' 
    ORDER BY p.id DESC LIMIT 5
")->fetchAll();

// 5 pengeluaran terbaru
$pengeluaran_terbaru = $pdo->query("
    SELECT tanggal, deskripsi, jumlah 
    FROM pengeluaran 
    ORDER BY tanggal DESC LIMIT 5
")->fetchAll();

include '../includes/header.php';
?>
<div class="container-fluid">
    <!-- Kartu Statistik -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-white bg-success">
                <div class="card-body">
                    <h5 class="card-title">Total Pemasukan</h5>
                    <h3 class="card-text">Rp <?= number_format($total_pemasukan, 0, ',', '.') ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-danger">
                <div class="card-body">
                    <h5 class="card-title">Total Pengeluaran</h5>
                    <h3 class="card-text">Rp <?= number_format($total_pengeluaran, 0, ',', '.') ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-primary">
                <div class="card-body">
                    <h5 class="card-title">Sisa Saldo</h5>
                    <h3 class="card-text">Rp <?= number_format($saldo, 0, ',', '.') ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-warning">
                <div class="card-body">
                    <h5 class="card-title">Pending Konfirmasi</h5>
                    <h3 class="card-text"><?= $pending ?></h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Grafik -->
    <div class="row mb-4">
        <div class="col-md-8">
            <div class="card h-100">
                <div class="card-header bg-white fw-bold">Grafik Pemasukan & Pengeluaran (6 Bulan Terakhir)</div>
                <div class="card-body">
                    <canvas id="keuanganChart" style="height: 300px; width: 100%;"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-header bg-white fw-bold">Akses Cepat</div>
                <div class="card-body d-flex flex-wrap gap-2 justify-content-center align-items-center" style="min-height: 200px;">
                    <a href="input_pembayaran.php" class="btn btn-primary w-100 mb-2"><i class="fas fa-hand-holding-usd"></i> Input Pembayaran</a>
                    <a href="kelola_pengeluaran.php" class="btn btn-danger w-100 mb-2"><i class="fas fa-chart-line"></i> Kelola Pengeluaran</a>
                    <a href="rekap.php" class="btn btn-info w-100 mb-2"><i class="fas fa-chart-pie"></i> Rekap Kas</a>
                    <a href="cetak_laporan.php" class="btn btn-secondary w-100"><i class="fas fa-print"></i> Cetak Laporan</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Pengeluaran di kiri, Pemasukan di kanan -->
    <div class="row">
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header bg-white fw-bold">5 Pengeluaran Terbaru</div>
                <div class="card-body table-responsive" style="max-height: 350px; overflow-y: auto;">
                    <?php if($pengeluaran_terbaru): ?>
                        <table class="table table-bordered table-striped">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 100px;">Tanggal</th>
                                    <th>Deskripsi</th>
                                    <th style="width: 120px;">Jumlah</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($pengeluaran_terbaru as $p): ?>
                                <tr>
                                    <td><?= date('d-m-Y', strtotime($p['tanggal'])) ?></td>
                                    <td><?= htmlspecialchars($p['deskripsi']) ?></td>
                                    <td class="text-end">Rp <?= number_format($p['jumlah'], 0, ',', '.') ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p class="text-muted text-center">Belum ada pengeluaran</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header bg-white fw-bold">5 Pemasukan Terbaru (Disetujui)</div>
                <div class="card-body table-responsive" style="max-height: 350px; overflow-y: auto;">
                    <?php if($pemasukan_terbaru): ?>
                        <table class="table table-bordered table-striped">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 100px;">Tanggal</th>
                                    <th>Warga</th>
                                    <th>Jenis Iuran</th>
                                    <th style="width: 120px;">Jumlah</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($pemasukan_terbaru as $p): ?>
                                <tr>
                                    <td><?= date('d-m-Y', strtotime($p['tanggal_pembayaran'])) ?></td>
                                    <td><?= htmlspecialchars($p['fullname']) ?></td>
                                    <td><?= htmlspecialchars($p['nama_iuran']) ?></td>
                                    <td class="text-end">Rp <?= number_format($p['jumlah'], 0, ',', '.') ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p class="text-muted text-center">Belum ada pemasukan</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
new Chart(document.getElementById('keuanganChart'), {
    type: 'bar',
    data: {
        labels: <?= json_encode($bulan_labels) ?>,
        datasets: [
            { label: 'Pemasukan', data: <?= json_encode($pemasukan_chart) ?>, backgroundColor: '#28a745' },
            { label: 'Pengeluaran', data: <?= json_encode($pengeluaran_chart) ?>, backgroundColor: '#dc3545' }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        scales: {
            y: {
                beginAtZero: true,
                ticks: { callback: (value) => 'Rp ' + value.toLocaleString('id-ID') }
            }
        },
        plugins: {
            tooltip: {
                callbacks: {
                    label: (ctx) => `${ctx.dataset.label}: Rp ${ctx.raw.toLocaleString('id-ID')}`
                }
            }
        }
    }
});
</script>
<?php include '../includes/footer.php'; ?>