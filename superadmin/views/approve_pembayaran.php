<?php
require_once '../../includes/auth.php';
redirectIfNotLoggedIn();
if(!hasRole('superadmin')) { echo "Akses ditolak"; exit; }
require_once '../../config/database.php';
global $pdo;

// Proses AJAX approve/tolak
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') {
    $response = ['status' => 'error', 'message' => 'Terjadi kesalahan.'];
    if (isset($_POST['action']) && isset($_POST['id'])) {
        $id = (int)$_POST['id'];
        $action = $_POST['action'];
        $status = ($action == 'approve') ? 'disetujui' : 'ditolak';
        try {
            $stmt = $pdo->prepare("UPDATE pembayaran SET status = ?, approved_by = ? WHERE id = ?");
            $stmt->execute([$status, $_SESSION['user_id'], $id]);
            $response = ['status' => 'success', 'message' => "Pembayaran berhasil di" . (($action == 'approve') ? "setujui." : "tolak.")];
        } catch (PDOException $e) {
            $response = ['status' => 'error', 'message' => 'Gagal: ' . $e->getMessage()];
        }
        echo json_encode($response);
        exit;
    }
}

// Ambil data pembayaran pending
$pending = $pdo->query("
    SELECT p.*, u.fullname as nama_warga, i.nama_iuran 
    FROM pembayaran p 
    JOIN users u ON p.user_id = u.id 
    JOIN iuran i ON p.iuran_id = i.id 
    WHERE p.status = 'pending' 
    ORDER BY p.id DESC
")->fetchAll();
?>
<div class="container-fluid">
    <h2 class="mb-4">Approve Pembayaran</h2>
    <div id="notif"></div>

    <div class="card">
        <div class="card-header bg-white fw-bold">
            <i class="fas fa-clock me-2"></i> Daftar Pembayaran Menunggu Persetujuan
        </div>
        <div class="card-body">
            <?php if(count($pending) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped datatable" id="tabelPending">
                        <thead>
                            <tr>
                                <th>Warga</th>
                                <th>Jenis Iuran</th>
                                <th>Jumlah</th>
                                <th>Tanggal Bayar</th>
                                <th>Bukti</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($pending as $p): ?>
                            <tr id="row-<?= $p['id'] ?>">
                                <td><?= htmlspecialchars($p['nama_warga']) ?></td>
                                <td><?= htmlspecialchars($p['nama_iuran']) ?></td>
                                <td>Rp <?= number_format($p['jumlah'], 0, ',', '.') ?></td>
                                <td><?= date('d-m-Y H:i', strtotime($p['tanggal_pembayaran'])) ?></td>
                                <td>
                                    <?php if($p['bukti_file']): ?>
                                        <a href="../assets/uploads/bukti/<?= $p['bukti_file'] ?>" target="_blank" class="btn btn-sm btn-secondary">
                                            <i class="fas fa-eye"></i> Lihat
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted">Tidak ada</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-success btn-approve" data-id="<?= $p['id'] ?>" data-action="approve">Approve</button>
                                    <button class="btn btn-sm btn-danger btn-reject" data-id="<?= $p['id'] ?>" data-action="reject">Tolak</button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="alert alert-info text-center">
                    <i class="fas fa-info-circle me-2"></i> Tidak ada pembayaran yang menunggu persetujuan.
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Hapus event handler lama untuk mencegah duplikasi
    $(document).off('click', '.btn-approve, .btn-reject');

    function showNotif(message, type) {
        $('#notif').html('<div class="alert alert-' + type + ' alert-dismissible fade show">' + message + '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>');
        setTimeout(() => $('#notif .alert').fadeOut(), 3000);
    }

    function reloadView() {
        $.ajax({
            url: 'views/approve_pembayaran.php',
            type: 'GET',
            success: function(html) {
                $('#mainContent').html(html);
                // Inisialisasi ulang DataTables
                $('.datatable').DataTable({
                    language: { emptyTable: "Tidak ada data", info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ entri", search: "Cari:" },
                    pageLength: 10,
                    destroy: true
                });
            },
            error: () => showNotif('Gagal memuat ulang', 'danger')
        });
    }

    // Pasang event handler baru
    $(document).on('click', '.btn-approve, .btn-reject', function() {
        let id = $(this).data('id');
        let action = $(this).data('action');
        if (confirm('Apakah Anda yakin ingin ' + (action === 'approve' ? 'menyetujui' : 'menolak') + ' pembayaran ini?')) {
            $.ajax({
                url: 'views/approve_pembayaran.php',
                type: 'POST',
                data: { id: id, action: action },
                dataType: 'json',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                success: function(res) {
                    showNotif(res.message, res.status);
                    if (res.status === 'success') reloadView();
                },
                error: () => showNotif('Terjadi kesalahan.', 'danger')
            });
        }
    });
});
</script>