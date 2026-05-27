<?php
require_once '../includes/auth.php';
redirectIfNotLoggedIn();
if(!hasRole('admin')) { header('Location: ../dashboard.php'); exit; }
$title = "Dashboard Admin";
global $pdo;

// Statistik
$total_warga = $pdo->query("SELECT COUNT(*) FROM users WHERE role_id = 4")->fetchColumn();
$warga_aktif = $pdo->query("SELECT COUNT(*) FROM users u JOIN warga_detail wd ON u.id = wd.user_id WHERE u.role_id = 4 AND wd.status_warga = 'aktif'")->fetchColumn();
$warga_menunggak = $pdo->query("SELECT COUNT(*) FROM users u JOIN warga_detail wd ON u.id = wd.user_id WHERE u.role_id = 4 AND wd.status_warga = 'menunggak'")->fetchColumn();

// Data untuk pie chart (status warga)
$status_data = $pdo->query("SELECT status_warga, COUNT(*) as total FROM warga_detail GROUP BY status_warga")->fetchAll();
$status_labels = [];
$status_counts = [];
foreach($status_data as $s) {
    $status_labels[] = ucfirst($s['status_warga']);
    $status_counts[] = (int)$s['total'];
}

// Data untuk bar chart (perkembangan warga per bulan, 6 bulan terakhir)
$bulan_labels = [];
$warga_per_bulan = [];
for ($i = 5; $i >= 0; $i--) {
    $date = new DateTime();
    $date->modify("-$i months");
    $bulan_labels[] = $date->format('M Y');
    $bulan = $date->format('m');
    $tahun = $date->format('Y');
    
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE role_id = 4 AND MONTH(created_at) = ? AND YEAR(created_at) = ?");
    $stmt->execute([$bulan, $tahun]);
    $warga_per_bulan[] = (int) $stmt->fetchColumn();
}

// Pengumuman terbaru (5)
$pengumuman = $pdo->query("SELECT judul, isi, created_at FROM pengumuman ORDER BY created_at DESC LIMIT 5")->fetchAll();

// Agenda mendatang (5)
$agenda = $pdo->query("SELECT judul, tanggal, lokasi FROM agenda WHERE tanggal >= CURDATE() ORDER BY tanggal ASC LIMIT 5")->fetchAll();

include '../includes/header.php';
?>
<div class="container-fluid">
    <!-- Kartu Statistik -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card text-white bg-primary">
                <div class="card-body">
                    <h5 class="card-title">Total Warga</h5>
                    <h2 class="card-text"><?= $total_warga ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-white bg-success">
                <div class="card-body">
                    <h5 class="card-title">Warga Aktif</h5>
                    <h2 class="card-text"><?= $warga_aktif ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-white bg-warning">
                <div class="card-body">
                    <h5 class="card-title">Warga Menunggak</h5>
                    <h2 class="card-text"><?= $warga_menunggak ?></h2>
                </div>
            </div>
        </div>
    </div>

    <!-- Grafik -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header bg-white fw-bold">Komposisi Status Warga</div>
                <div class="card-body d-flex justify-content-center align-items-center">
                    <canvas id="statusChart" style="max-height: 280px; width: auto; max-width: 100%;"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header bg-white fw-bold">Perkembangan Warga (6 Bulan Terakhir)</div>
                <div class="card-body">
                    <canvas id="wargaChart" style="height: 280px; width: 100%;"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Pengumuman Terbaru -->
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header bg-white fw-bold">Pengumuman Terbaru</div>
                <div class="card-body" style="max-height: 300px; overflow-y: auto;">
                    <?php if($pengumuman): ?>
                        <ul class="list-group list-group-flush">
                            <?php foreach($pengumuman as $p): ?>
                                <li class="list-group-item">
                                    <strong><?= htmlspecialchars($p['judul']) ?></strong><br>
                                    <small class="text-muted"><?= date('d-m-Y H:i', strtotime($p['created_at'])) ?></small>
                                    <p class="mb-0 mt-1"><?= htmlspecialchars(substr($p['isi'], 0, 100)) ?>...</p>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <p class="text-muted">Belum ada pengumuman</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <!-- Agenda Mendatang -->
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header bg-white fw-bold">Agenda Mendatang</div>
                <div class="card-body" style="max-height: 300px; overflow-y: auto;">
                    <?php if($agenda): ?>
                        <ul class="list-group list-group-flush">
                            <?php foreach($agenda as $a): ?>
                                <li class="list-group-item">
                                    <strong><?= htmlspecialchars($a['judul']) ?></strong><br>
                                    <small><?= date('d-m-Y', strtotime($a['tanggal'])) ?> • <?= htmlspecialchars($a['lokasi']) ?></small>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <p class="text-muted">Tidak ada agenda mendatang</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Akses Cepat -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-white fw-bold">Akses Cepat</div>
                <div class="card-body d-flex flex-wrap gap-2">
                    <a href="kelola_warga.php" class="btn btn-primary"><i class="fas fa-users"></i> Kelola Warga</a>
                    <a href="pengumuman.php" class="btn btn-secondary"><i class="fas fa-bullhorn"></i> Pengumuman</a>
                    <a href="agenda.php" class="btn btn-info"><i class="fas fa-calendar-alt"></i> Agenda</a>
                    <a href="verifikasi_warga.php" class="btn btn-warning"><i class="fas fa-check-circle"></i> Verifikasi Warga</a>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Pie chart komposisi status
    const ctx1 = document.getElementById('statusChart').getContext('2d');
    new Chart(ctx1, {
        type: 'pie',
        data: {
            labels: <?= json_encode($status_labels) ?>,
            datasets: [{
                data: <?= json_encode($status_counts) ?>,
                backgroundColor: ['#4e73df', '#1cc88a', '#f6c23e']
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: { legend: { position: 'bottom' } }
        }
    });

    // Bar chart perkembangan warga
    const ctx2 = document.getElementById('wargaChart').getContext('2d');
    new Chart(ctx2, {
        type: 'bar',
        data: {
            labels: <?= json_encode($bulan_labels) ?>,
            datasets: [{
                label: 'Jumlah Warga Baru',
                data: <?= json_encode($warga_per_bulan) ?>,
                backgroundColor: '#36b9cc'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { stepSize: 1 }
                }
            },
            plugins: {
                tooltip: {
                    callbacks: {
                        label: (ctx) => `${ctx.raw} warga`
                    }
                }
            }
        }
    });
});
</script>
<?php include '../includes/footer.php'; ?>