<?php
require_once '../includes/auth.php';
redirectIfNotLoggedIn();
if(!hasRole('warga')) { header('Location: ../dashboard.php'); exit; }
$user = getCurrentUser();
$title = "Dashboard Warga";
global $pdo;

// Ambil periode aktif terbaru (untuk tagihan bulan berjalan)
$periode_aktif = $pdo->query("SELECT id, bulan, tahun FROM periode WHERE status = 'aktif' ORDER BY tahun DESC, bulan DESC LIMIT 1")->fetch();
$periode_id = $periode_aktif ? $periode_aktif['id'] : null;

// Total tagihan belum lunas (hanya untuk periode aktif)
if ($periode_id) {
    $tagihan_belum = $pdo->prepare("SELECT SUM(jumlah) FROM pembayaran WHERE user_id = ? AND periode_id = ? AND status IN ('pending', 'ditolak')");
    $tagihan_belum->execute([$user['id'], $periode_id]);
    $total_tagihan = $tagihan_belum->fetchColumn() ?: 0;
} else {
    $total_tagihan = 0;
}

// Total pembayaran lunas (semua periode – opsional, tidak diubah)
$lunas = $pdo->prepare("SELECT SUM(jumlah) FROM pembayaran WHERE user_id = ? AND status = 'disetujui'");
$lunas->execute([$user['id']]);
$total_lunas = $lunas->fetchColumn() ?: 0;

// Data grafik (6 bulan terakhir) – tetap sama
$bulan_labels = [];
$pembayaran_bulan = [];
for ($i = 5; $i >= 0; $i--) {
    $date = new DateTime();
    $date->modify("-$i months");
    $bulan_labels[] = $date->format('M Y');
    $bulan = $date->format('m');
    $tahun = $date->format('Y');
    $stmt = $pdo->prepare("SELECT COALESCE(SUM(jumlah),0) FROM pembayaran WHERE user_id = ? AND status = 'disetujui' AND MONTH(tanggal_pembayaran) = ? AND YEAR(tanggal_pembayaran) = ?");
    $stmt->execute([$user['id'], $bulan, $tahun]);
    $pembayaran_bulan[] = (int) $stmt->fetchColumn();
}

// Informasi iuran bulan depan (periode aktif)
$info_iuran = null;
if ($periode_aktif) {
    // Hanya jumlahkan iuran wajib (Kas Bulanan, Keamanan, Sampah) – ubah sesuai kebutuhan
    $iuran_wajib = ['Kas Bulanan', 'Keamanan', 'Sampah'];
    $placeholders = implode(',', array_fill(0, count($iuran_wajib), '?'));
    $stmt = $pdo->prepare("SELECT SUM(nominal) FROM iuran WHERE nama_iuran IN ($placeholders)");
    $stmt->execute($iuran_wajib);
    $total_iuran = $stmt->fetchColumn() ?: 0;
    if ($total_iuran > 0) {
        $info_iuran = [
            'nama' => 'Total Iuran Wajib',
            'nominal' => $total_iuran,
            'bulan' => $periode_aktif['bulan'],
            'tahun' => $periode_aktif['tahun']
        ];
    } else {
        $total_iuran = $pdo->query("SELECT SUM(nominal) FROM iuran")->fetchColumn();
        if ($total_iuran > 0) {
            $info_iuran = [
                'nama' => 'Total Semua Iuran',
                'nominal' => $total_iuran,
                'bulan' => $periode_aktif['bulan'],
                'tahun' => $periode_aktif['tahun']
            ];
        }
    }
}

// Transaksi terbaru
$transaksi = $pdo->prepare("SELECT p.tanggal_pembayaran, i.nama_iuran, p.jumlah, p.status FROM pembayaran p JOIN iuran i ON p.iuran_id = i.id WHERE p.user_id = ? ORDER BY p.id DESC LIMIT 5");
$transaksi->execute([$user['id']]);
$recent = $transaksi->fetchAll();

include '../includes/header.php';
?>
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card text-white bg-warning">
                <div class="card-body">
                    <h5 class="card-title">Total Tagihan Belum Lunas (Bulan Ini)</h5>
                    <h2 class="card-text">Rp <?= number_format($total_tagihan, 0, ',', '.') ?></h2>
                    <p>Segera lakukan pembayaran</p>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card text-white bg-success">
                <div class="card-body">
                    <h5 class="card-title">Total Pembayaran Lunas (Semua Periode)</h5>
                    <h2 class="card-text">Rp <?= number_format($total_lunas, 0, ',', '.') ?></h2>
                    <p>Terima kasih telah membayar iuran</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-7">
            <div class="card h-100">
                <div class="card-header bg-white fw-bold">Riwayat Pembayaran per Bulan (6 Bulan Terakhir)</div>
                <div class="card-body">
                    <canvas id="wargaChart" style="height: 300px; width: 100%;"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-5">
            <div class="card h-100">
                <div class="card-header bg-white fw-bold">Informasi Iuran Bulan Depan</div>
                <div class="card-body d-flex flex-column justify-content-center">
                    <?php if ($info_iuran): ?>
                        <div class="text-center">
                            <i class="fas fa-calendar-alt fa-3x text-primary mb-3"></i>
                            <h5>Periode: <?= $info_iuran['bulan'] . '/' . $info_iuran['tahun'] ?></h5>
                            <p class="mb-1">Jenis Iuran: <strong><?= htmlspecialchars($info_iuran['nama']) ?></strong></p>
                            <h4 class="text-primary">Rp <?= number_format($info_iuran['nominal'], 0, ',', '.') ?></h4>
                            <p class="text-muted mt-2">Segera siapkan pembayaran iuran bulan depan.</p>
                        </div>
                    <?php else: ?>
                        <div class="text-center text-muted">
                            <i class="fas fa-info-circle fa-3x mb-3"></i>
                            <p>Belum ada informasi iuran untuk bulan depan. Silakan hubungi pengurus RT.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-7">
            <div class="card h-100">
                <div class="card-header bg-white fw-bold">Transaksi Terbaru</div>
                <div class="card-body table-responsive" style="max-height: 300px; overflow-y: auto;">
                    <table class="table table-sm table-bordered">
                        <thead>
                            <tr><th>Tanggal</th><th>Jenis Iuran</th><th>Jumlah</th><th>Status</th></tr>
                        </thead>
                        <tbody>
                            <?php if($recent): foreach($recent as $r): ?>
                            <tr>
                                <td><?= date('d-m-Y', strtotime($r['tanggal_pembayaran'])) ?></td>
                                <td><?= htmlspecialchars($r['nama_iuran']) ?></td>
                                <td>Rp <?= number_format($r['jumlah'], 0, ',', '.') ?></td>
                                <td>
                                    <?php if($r['status'] == 'disetujui'): ?>
                                        <span class="badge bg-success">Lunas</span>
                                    <?php elseif($r['status'] == 'pending'): ?>
                                        <span class="badge bg-warning">Pending</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">Ditolak</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; else: ?>
                            <tr><td colspan="4" class="text-center">Belum ada transaksi</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-5">
            <div class="card h-100">
                <div class="card-header bg-white fw-bold">Akses Cepat</div>
                <div class="card-body d-flex flex-wrap gap-2 justify-content-center align-items-center" style="min-height: 200px;">
                    <a href="tagihan.php" class="btn btn-primary w-100 mb-2"><i class="fas fa-file-invoice"></i> Tagihan</a>
                    <a href="upload_bukti.php" class="btn btn-info w-100 mb-2"><i class="fas fa-upload"></i> Upload Bukti</a>
                    <a href="pengajuan_surat.php" class="btn btn-secondary w-100 mb-2"><i class="fas fa-envelope"></i> Pengajuan Surat</a>
                    <a href="histori.php" class="btn btn-outline-secondary w-100"><i class="fas fa-history"></i> Histori Lengkap</a>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('wargaChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: <?= json_encode($bulan_labels) ?>,
            datasets: [{
                label: 'Pembayaran Lunas (Rp)',
                data: <?= json_encode($pembayaran_bulan) ?>,
                backgroundColor: 'rgba(40,167,69,0.6)',
                borderColor: '#28a745',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            scales: { y: { beginAtZero: true, ticks: { callback: (v) => 'Rp ' + v.toLocaleString('id-ID') } } },
            plugins: { tooltip: { callbacks: { label: (ctx) => 'Rp ' + ctx.raw.toLocaleString('id-ID') } } }
        }
    });
});
</script>
<?php include '../includes/footer.php'; ?>