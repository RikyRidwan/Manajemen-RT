<?php
require_once '../../includes/auth.php';
redirectIfNotLoggedIn();
if(!hasRole('superadmin')) { echo "Akses ditolak"; exit; }
require_once '../../config/database.php';
global $pdo;

// Statistik
$total_warga = $pdo->query("SELECT COUNT(*) FROM users WHERE role_id = 4")->fetchColumn();
$total_pemasukan = $pdo->query("SELECT COALESCE(SUM(jumlah),0) FROM pembayaran WHERE status='disetujui'")->fetchColumn();
$total_pengeluaran = $pdo->query("SELECT COALESCE(SUM(jumlah),0) FROM pengeluaran")->fetchColumn();
$saldo = $total_pemasukan - $total_pengeluaran;

// Data bar chart 6 bulan terakhir
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

// Komposisi iuran (semua iuran)
$iuran_data = $pdo->query("SELECT i.nama_iuran, COALESCE(SUM(p.jumlah),0) as total FROM iuran i LEFT JOIN pembayaran p ON i.id = p.iuran_id AND p.status='disetujui' GROUP BY i.id")->fetchAll();
$iuran_labels = array_column($iuran_data, 'nama_iuran');
$iuran_values = array_column($iuran_data, 'total');

// Pengumuman: ambil semua
$pengumuman = $pdo->query("SELECT judul, isi, created_at FROM pengumuman ORDER BY created_at DESC")->fetchAll();
// Transaksi terbaru
$transaksi = $pdo->query("SELECT p.tanggal_pembayaran, u.fullname, i.nama_iuran, p.jumlah FROM pembayaran p JOIN users u ON p.user_id = u.id JOIN iuran i ON p.iuran_id = i.id WHERE p.status='disetujui' ORDER BY p.id DESC LIMIT 10")->fetchAll();
?>
<style>
.equal-height-card {
    height: 100%;
    display: flex;
    flex-direction: column;
}
.equal-height-card .card-body {
    flex: 1;
}
</style>
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-3"><div class="card text-white bg-primary h-100"><div class="card-body"><h5>Total Warga</h5><h2><?= $total_warga ?></h2></div></div></div>
        <div class="col-md-3"><div class="card text-white bg-success h-100"><div class="card-body"><h5>Total Pemasukan</h5><h4>Rp <?= number_format($total_pemasukan,0,',','.') ?></h4></div></div></div>
        <div class="col-md-3"><div class="card text-white bg-danger h-100"><div class="card-body"><h5>Total Pengeluaran</h5><h4>Rp <?= number_format($total_pengeluaran,0,',','.') ?></h4></div></div></div>
        <div class="col-md-3"><div class="card text-white bg-info h-100"><div class="card-body"><h5>Sisa Saldo</h5><h4>Rp <?= number_format($saldo,0,',','.') ?></h4></div></div></div>
    </div>

    <div class="row mb-4">
        <div class="col-md-8">
            <div class="card h-100">
                <div class="card-header bg-white fw-bold">Grafik Pemasukan & Pengeluaran (6 Bulan Terakhir)</div>
                <div class="card-body">
                    <canvas id="keuanganChart" style="height: 100%; width: 100%; min-height: 300px;"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-header bg-white fw-bold">Komposisi Iuran</div>
                <div class="card-body d-flex justify-content-center align-items-center">
                    <canvas id="komposisiChart" style="max-height: 250px; width: 100%;"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-5">
            <div class="card h-100">
                <div class="card-header bg-white fw-bold">Pengumuman Terbaru</div>
                <div class="card-body" style="max-height: 500px; overflow-y: auto;">
                    <?php if($pengumuman): ?>
                        <div class="list-group">
                            <?php foreach($pengumuman as $p): ?>
                                <div class="list-group-item">
                                    <strong><?= htmlspecialchars($p['judul']) ?></strong><br>
                                    <small><?= date('d-m-Y H:i', strtotime($p['created_at'])) ?></small>
                                    <p class="mb-0 mt-1"><?= nl2br(htmlspecialchars($p['isi'])) ?></p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-muted">Belum ada pengumuman.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-md-7">
            <div class="card h-100">
                <div class="card-header bg-white fw-bold">Transaksi Terbaru (Disetujui)</div>
                <div class="card-body table-responsive" style="max-height: 500px; overflow-y: auto;">
                    <table class="table table-sm table-bordered">
                        <thead>
                            <tr><th>Tanggal</th><th>Warga</th><th>Iuran</th><th>Jumlah</th></tr>
                        </thead>
                        <tbody>
                            <?php if($transaksi): foreach($transaksi as $t): ?>
                            <td>
                                <td><?= date('d-m-Y', strtotime($t['tanggal_pembayaran'])) ?></td>
                                <td><?= htmlspecialchars($t['fullname']) ?></td>
                                <td><?= htmlspecialchars($t['nama_iuran']) ?></td>
                                <td>Rp <?= number_format($t['jumlah'],0,',','.') ?></td>
                            </tr>
                            <?php endforeach; else: ?>
                            <td><td colspan="4" class="text-center">Belum ada transaksi</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
(function() {
    function initCharts() {
        var keuanganCanvas = document.getElementById('keuanganChart');
        var komposisiCanvas = document.getElementById('komposisiChart');
        if (keuanganCanvas && komposisiCanvas) {
            var maxValue = Math.max(0, ...<?= json_encode($pemasukan_chart) ?>, ...<?= json_encode($pengeluaran_chart) ?>);
            var stepSize = Math.ceil(maxValue / 5 / 10000) * 10000;
            if (stepSize < 10000) stepSize = 10000;
            new Chart(keuanganCanvas, {
                type: 'bar',
                data: {
                    labels: <?= json_encode($bulan_labels) ?>,
                    datasets: [
                        { label: 'Pemasukan (Rp)', data: <?= json_encode($pemasukan_chart) ?>, backgroundColor: 'rgba(40,167,69,0.7)', borderColor: '#28a745', borderWidth: 1 },
                        { label: 'Pengeluaran (Rp)', data: <?= json_encode($pengeluaran_chart) ?>, backgroundColor: 'rgba(220,53,69,0.7)', borderColor: '#dc3545', borderWidth: 1 }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    scales: { y: { beginAtZero: true, ticks: { stepSize: stepSize, callback: function(v) { return 'Rp ' + v.toLocaleString('id-ID'); } } } }
                }
            });
            new Chart(komposisiCanvas, {
                type: 'pie',
                data: {
                    labels: <?= json_encode($iuran_labels) ?>,
                    datasets: [{ data: <?= json_encode($iuran_values) ?>, backgroundColor: ['#4e73df','#1cc88a','#36b9cc','#f6c23e','#e74a3b','#858796'] }]
                },
                options: { responsive: true, maintainAspectRatio: true, plugins: { legend: { position: 'bottom' } } }
            });
        } else {
            setTimeout(initCharts, 100);
        }
    }
    initCharts();
})();
</script>