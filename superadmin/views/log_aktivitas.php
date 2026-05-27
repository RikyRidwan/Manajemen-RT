<?php
require_once '../../includes/auth.php';
redirectIfNotLoggedIn();
if(!hasRole('superadmin')) { echo "Akses ditolak"; exit; }
require_once '../../config/database.php';
global $pdo;


$logs = $pdo->query("SELECT l.*, u.fullname FROM log_aktivitas l LEFT JOIN users u ON l.user_id = u.id ORDER BY l.waktu DESC LIMIT 500")->fetchAll();
?>
<div class="container-fluid">
    <h2 class="mb-4">Log Aktivitas Pengguna</h2>
    <div class="card">
        <div class="card-body table-responsive">
            <table class="table table-bordered table-striped">
                <thead class="table-dark">
                    <tr>
                        <th>Waktu</th>
                        <th>Pengguna</th>
                        <th>Aksi</th>
                        <th>IP</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($logs as $log): ?>
                    <tr>
                        <td><?= htmlspecialchars($log['waktu']) ?></td>
                        <td><?= htmlspecialchars($log['fullname']) ?> (ID: <?= $log['user_id'] ?>)</td>
                        <td><?= htmlspecialchars($log['aksi']) ?></td>
                        <td><?= htmlspecialchars($log['ip']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>