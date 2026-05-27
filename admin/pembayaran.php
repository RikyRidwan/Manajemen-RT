<?php
require_once '../includes/auth.php';
redirectIfNotLoggedIn();
if(!hasRole('admin')) { header('Location: ../dashboard.php'); exit; }
$title = "Pembayaran Kas";
global $pdo;

$status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';
$where = ($status_filter != 'all') ? "WHERE p.status = '$status_filter'" : '';

$pembayaran = $pdo->query("SELECT p.*, u.fullname, i.nama_iuran 
                           FROM pembayaran p 
                           JOIN users u ON p.user_id = u.id 
                           JOIN iuran i ON p.iuran_id = i.id 
                           $where
                           ORDER BY p.id DESC")->fetchAll();

include '../includes/header.php';
?>
<div class="container-fluid">
    <h2 class="mb-4">Riwayat Pembayaran Kas</h2>
    
    <!-- Filter Status -->
    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-auto">
                    <label class="form-label">Filter Status</label>
                    <select name="status" class="form-select">
                        <option value="all" <?= $status_filter == 'all' ? 'selected' : '' ?>>Semua</option>
                        <option value="pending" <?= $status_filter == 'pending' ? 'selected' : '' ?>>Pending</option>
                        <option value="disetujui" <?= $status_filter == 'disetujui' ? 'selected' : '' ?>>Disetujui</option>
                        <option value="ditolak" <?= $status_filter == 'ditolak' ? 'selected' : '' ?>>Ditolak</option>
                    </select>
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-primary">Filter</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body table-responsive">
            <table class="table table-bordered table-striped datatable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Warga</th>
                        <th>Jenis Iuran</th>
                        <th>Jumlah</th>
                        <th>Status</th>
                        <th>Tanggal Bayar</th>
                        <th>Bukti</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($pembayaran as $p): ?>
                    <tr>
                        <td><?= $p['id'] ?></td>
                        <td><?= htmlspecialchars($p['fullname']) ?></td>
                        <td><?= htmlspecialchars($p['nama_iuran']) ?></td>
                        <td>Rp <?= number_format($p['jumlah'], 0, ',', '.') ?></td>
                        <td>
                            <?php
                            $badge = '';
                            if ($p['status'] == 'disetujui') $badge = 'bg-success';
                            elseif ($p['status'] == 'pending') $badge = 'bg-warning text-dark';
                            else $badge = 'bg-danger';
                            ?>
                            <span class="badge <?= $badge ?>"><?= ucfirst($p['status']) ?></span>
                        </td>
                        <td><?= $p['tanggal_pembayaran'] ?></td>
                        <td>
                            <?php if($p['bukti_file']): ?>
                                <a href="../assets/uploads/bukti/<?= $p['bukti_file'] ?>" target="_blank" class="btn btn-sm btn-info">Lihat Bukti</a>
                            <?php else: ?>
                                <span class="text-muted">Tidak ada</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php include '../includes/footer.php'; ?>