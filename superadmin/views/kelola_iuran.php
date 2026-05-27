<?php
require_once '../../includes/auth.php';
redirectIfNotLoggedIn();
if(!hasRole('superadmin')) { echo "Akses ditolak"; exit; }
require_once '../../config/database.php';
global $pdo;

// Proses AJAX
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') {
    $response = ['status' => 'error', 'message' => 'Terjadi kesalahan.'];
    
    if (isset($_POST['tambah'])) {
        $nama_iuran = trim($_POST['nama_iuran']);
        $nominal = trim($_POST['nominal']);
        $deskripsi = trim($_POST['deskripsi']);
        try {
            $stmt = $pdo->prepare("INSERT INTO iuran (nama_iuran, nominal, deskripsi) VALUES (?, ?, ?)");
            $stmt->execute([$nama_iuran, $nominal, $deskripsi]);
            $response = ['status' => 'success', 'message' => 'Iuran berhasil ditambahkan.'];
        } catch(PDOException $e) {
            $response = ['status' => 'error', 'message' => 'Gagal: ' . $e->getMessage()];
        }
        echo json_encode($response);
        exit;
    }
    
    if (isset($_POST['edit'])) {
        $id = $_POST['id'];
        $nama_iuran = trim($_POST['nama_iuran']);
        $nominal = trim($_POST['nominal']);
        $deskripsi = trim($_POST['deskripsi']);
        try {
            $stmt = $pdo->prepare("UPDATE iuran SET nama_iuran=?, nominal=?, deskripsi=? WHERE id=?");
            $stmt->execute([$nama_iuran, $nominal, $deskripsi, $id]);
            $response = ['status' => 'success', 'message' => 'Iuran berhasil diperbarui.'];
        } catch(PDOException $e) {
            $response = ['status' => 'error', 'message' => 'Gagal: ' . $e->getMessage()];
        }
        echo json_encode($response);
        exit;
    }
    
    if (isset($_POST['hapus'])) {
        $id = $_POST['id'];
        try {
            $stmt = $pdo->prepare("DELETE FROM iuran WHERE id=?");
            $stmt->execute([$id]);
            $response = ['status' => 'success', 'message' => 'Iuran dihapus.'];
        } catch(PDOException $e) {
            $response = ['status' => 'error', 'message' => 'Gagal: ' . $e->getMessage()];
        }
        echo json_encode($response);
        exit;
    }
}

// Tampilkan konten HTML
$iuran = $pdo->query("SELECT * FROM iuran ORDER BY id")->fetchAll();
?>
<div class="container-fluid">
    <h2 class="mb-4">Kelola Jenis Iuran</h2>
    <div id="notif"></div>

    <!-- Form Tambah Iuran -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">Tambah Iuran Baru</div>
        <div class="card-body">
            <form id="formTambahIuran" class="row g-2">
                <div class="col-md-4"><input type="text" name="nama_iuran" class="form-control" placeholder="Nama Iuran" required></div>
                <div class="col-md-3"><input type="number" name="nominal" class="form-control" placeholder="Nominal (Rp)" required></div>
                <div class="col-md-4"><input type="text" name="deskripsi" class="form-control" placeholder="Deskripsi"></div>
                <div class="col-md-1"><button type="submit" class="btn btn-primary w-100">Tambah</button></div>
            </form>
        </div>
    </div>

    <!-- Tabel Daftar Iuran -->
    <div class="card">
        <div class="card-header">Daftar Iuran</div>
        <div class="card-body">
            <table class="table table-bordered datatable" id="tabelIuran">
                <thead>
                    <tr><th>ID</th><th>Nama Iuran</th><th>Nominal</th><th>Deskripsi</th><th>Aksi</th></tr>
                </thead>
                <tbody>
                    <?php foreach($iuran as $i): ?>
                    <tr id="row-<?= $i['id'] ?>">
                        <td><?= $i['id'] ?></td>
                        <td><?= htmlspecialchars($i['nama_iuran']) ?></td>
                        <td>Rp <?= number_format($i['nominal'],0,',','.') ?></td>
                        <td><?= htmlspecialchars($i['deskripsi']) ?></td>
                        <td>
                            <button class="btn btn-sm btn-info btn-edit" 
                                data-id="<?= $i['id'] ?>"
                                data-nama="<?= htmlspecialchars($i['nama_iuran']) ?>"
                                data-nominal="<?= $i['nominal'] ?>"
                                data-deskripsi="<?= htmlspecialchars($i['deskripsi']) ?>">Edit</button>
                            <button class="btn btn-sm btn-danger btn-hapus" data-id="<?= $i['id'] ?>">Hapus</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Edit Iuran (tunggal) -->
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Edit Iuran</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <form id="formEditIuran">
                    <input type="hidden" name="id" id="edit_id">
                    <div class="mb-2"><label>Nama Iuran</label><input type="text" name="nama_iuran" id="edit_nama" class="form-control" required></div>
                    <div class="mb-2"><label>Nominal</label><input type="number" name="nominal" id="edit_nominal" class="form-control" required></div>
                    <div class="mb-2"><label>Deskripsi</label><textarea name="deskripsi" id="edit_deskripsi" class="form-control"></textarea></div>
                </form>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-primary" id="simpanEditBtn">Simpan</button></div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    function showNotif(message, type) {
        $('#notif').html('<div class="alert alert-' + type + ' alert-dismissible fade show">' + message + '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>');
        setTimeout(() => $('#notif .alert').fadeOut(), 3000);
    }

    function reloadView() {
        $.ajax({
            url: 'views/kelola_iuran.php',
            type: 'GET',
            success: function(html) {
                $('#mainContent').html(html);
                $('.datatable').DataTable({
                    language: { emptyTable: "Tidak ada data", info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ entri", search: "Cari:" },
                    pageLength: 10,
                    destroy: true
                });
            },
            error: () => showNotif('Gagal memuat ulang', 'danger')
        });
    }

    // Hapus event handler lama lalu pasang ulang
    $(document).off('submit', '#formTambahIuran');
    $(document).off('click', '.btn-edit');
    $(document).off('click', '#simpanEditBtn');
    $(document).off('click', '.btn-hapus');

    // Tambah Iuran
    $(document).on('submit', '#formTambahIuran', function(e) {
        e.preventDefault();
        $.ajax({
            url: 'views/kelola_iuran.php',
            type: 'POST',
            data: $(this).serialize() + '&tambah=1',
            dataType: 'json',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            success: function(res) {
                showNotif(res.message, res.status);
                if (res.status === 'success') reloadView();
            },
            error: () => showNotif('Terjadi kesalahan', 'danger')
        });
    });

    // Buka modal edit
    $(document).on('click', '.btn-edit', function() {
        let btn = $(this);
        $('#edit_id').val(btn.data('id'));
        $('#edit_nama').val(btn.data('nama'));
        $('#edit_nominal').val(btn.data('nominal'));
        $('#edit_deskripsi').val(btn.data('deskripsi'));
        $('#editModal').modal('show');
    });

    // Simpan Edit
    $(document).on('click', '#simpanEditBtn', function() {
        $.ajax({
            url: 'views/kelola_iuran.php',
            type: 'POST',
            data: $('#formEditIuran').serialize() + '&edit=1',
            dataType: 'json',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            success: function(res) {
                showNotif(res.message, res.status);
                $('#editModal').modal('hide');
                if (res.status === 'success') reloadView();
            },
            error: () => showNotif('Gagal menyimpan', 'danger')
        });
    });

    // Hapus Iuran
    $(document).on('click', '.btn-hapus', function() {
        let id = $(this).data('id');
        if (confirm('Yakin hapus iuran ini?')) {
            $.ajax({
                url: 'views/kelola_iuran.php',
                type: 'POST',
                data: { hapus: 1, id: id },
                dataType: 'json',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                success: function(res) {
                    showNotif(res.message, res.status);
                    if (res.status === 'success') reloadView();
                }
            });
        }
    });
});
</script>