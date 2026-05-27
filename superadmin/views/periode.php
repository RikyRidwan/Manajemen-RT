<?php
require_once '../../includes/auth.php';
redirectIfNotLoggedIn();
if(!hasRole('superadmin')) { echo "Akses ditolak"; exit; }
require_once '../../config/database.php';
global $pdo;

// Proses tambah periode (AJAX)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['tambah'])) {
    $tahun = $_POST['tahun'];
    $bulan = $_POST['bulan'];
    $status = $_POST['status'];
    try {
        $stmt = $pdo->prepare("INSERT INTO periode (tahun, bulan, status) VALUES (?,?,?)");
        $stmt->execute([$tahun, $bulan, $status]);
        echo json_encode(['status' => 'success', 'message' => 'Periode berhasil ditambahkan.']);
    } catch(PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Gagal: ' . $e->getMessage()]);
    }
    exit;
}

// Ubah status (AJAX)
if (isset($_GET['ubah_status'])) {
    $id = $_GET['ubah_status'];
    $status = $_GET['status'];
    try {
        $pdo->prepare("UPDATE periode SET status=? WHERE id=?")->execute([$status, $id]);
        echo json_encode(['status' => 'success', 'message' => "Status periode diubah menjadi $status."]);
    } catch(PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Gagal: ' . $e->getMessage()]);
    }
    exit;
}

// Hapus periode (AJAX)
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    try {
        $pdo->prepare("DELETE FROM periode WHERE id=?")->execute([$id]);
        echo json_encode(['status' => 'success', 'message' => 'Periode dihapus.']);
    } catch(PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Gagal: ' . $e->getMessage()]);
    }
    exit;
}

// Jika bukan request AJAX, tampilkan konten HTML
$periode = $pdo->query("SELECT * FROM periode ORDER BY tahun DESC, bulan DESC")->fetchAll();
?>
<div class="container-fluid">
    <h2 class="mb-4">Periode Pembayaran</h2>
    
    <div id="alert-container"></div>
    
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">Tambah Periode</div>
        <div class="card-body">
            <form id="formTambahPeriode" class="row g-2">
                <div class="col-md-3">
                    <input type="number" name="tahun" class="form-control" placeholder="Tahun" required>
                </div>
                <div class="col-md-3">
                    <select name="bulan" class="form-select" required>
                        <option value="">Pilih Bulan</option>
                        <?php for($i=1; $i<=12; $i++): ?>
                            <option value="<?= $i ?>"><?= $i ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-select">
                        <option value="aktif">Aktif</option>
                        <option value="tutup">Tutup</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" name="tambah" class="btn btn-primary w-100">Tambah Periode</button>
                </div>
            </form>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header">Daftar Periode</div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered datatable">
                    <thead>
                        <tr>
                            <th>Tahun</th>
                            <th>Bulan</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="tabel-periode">
                        <?php foreach($periode as $p): ?>
                        <tr id="row-<?= $p['id'] ?>">
                            <td><?= $p['tahun'] ?></td>
                            <td><?= $p['bulan'] ?></td>
                            <td>
                                <span class="badge <?= $p['status'] == 'aktif' ? 'bg-success' : 'bg-secondary' ?>">
                                    <?= ucfirst($p['status']) ?>
                                </span>
                            </td>
                            <td>
                                <?php if($p['status'] == 'aktif'): ?>
                                    <button class="btn btn-sm btn-warning ubah-status" data-id="<?= $p['id'] ?>" data-status="tutup">Tutup</button>
                                <?php else: ?>
                                    <button class="btn btn-sm btn-success ubah-status" data-id="<?= $p['id'] ?>" data-status="aktif">Aktifkan</button>
                                <?php endif; ?>
                                <button class="btn btn-sm btn-danger hapus-periode" data-id="<?= $p['id'] ?>">Hapus</button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Fungsi reload konten periode (tanpa reload halaman induk)
    function reloadPeriode() {
        $.ajax({
            url: 'views/periode.php', // perbaiki path: karena base dari index.php adalah folder superadmin/
            type: 'GET',
            success: function(response) {
                $('#mainContent').html(response);
                // Re-inisialisasi DataTables
                $('.datatable').DataTable({
                    language: {
                        "decimal": "",
                        "emptyTable": "Tidak ada data",
                        "info": "Menampilkan _START_ sampai _END_ dari _TOTAL_ entri",
                        "infoEmpty": "Menampilkan 0 sampai 0 dari 0 entri",
                        "infoFiltered": "(disaring dari _MAX_ total entri)",
                        "lengthMenu": "Tampilkan _MENU_ entri",
                        "loadingRecords": "Memuat...",
                        "processing": "Memproses...",
                        "search": "Cari:",
                        "zeroRecords": "Tidak ada data yang cocok",
                        "paginate": {
                            "first": "Pertama",
                            "last": "Terakhir",
                            "next": "Selanjutnya",
                            "previous": "Sebelumnya"
                        }
                    },
                    pageLength: 10,
                    destroy: true
                });
            }
        });
    }

    // Submit form tambah periode via AJAX
    $('#formTambahPeriode').submit(function(e) {
        e.preventDefault();
        $.ajax({
            url: 'views/periode.php',
            type: 'POST',
            data: $(this).serialize() + '&tambah=1',
            dataType: 'json',
            success: function(res) {
                if (res.status === 'success') {
                    $('#alert-container').html('<div class="alert alert-success">' + res.message + '</div>');
                    reloadPeriode();
                } else {
                    $('#alert-container').html('<div class="alert alert-danger">' + res.message + '</div>');
                }
            },
            error: function() {
                $('#alert-container').html('<div class="alert alert-danger">Terjadi kesalahan.</div>');
            }
        });
    });

    // Ubah status via AJAX
    $(document).on('click', '.ubah-status', function() {
        let id = $(this).data('id');
        let status = $(this).data('status');
        $.ajax({
            url: 'views/periode.php',
            type: 'GET',
            data: { ubah_status: id, status: status },
            dataType: 'json',
            success: function(res) {
                if (res.status === 'success') {
                    $('#alert-container').html('<div class="alert alert-success">' + res.message + '</div>');
                    reloadPeriode();
                } else {
                    $('#alert-container').html('<div class="alert alert-danger">' + res.message + '</div>');
                }
            }
        });
    });

    // Hapus periode via AJAX
    $(document).on('click', '.hapus-periode', function() {
        let id = $(this).data('id');
        if (confirm('Yakin hapus periode ini?')) {
            $.ajax({
                url: 'views/periode.php',
                type: 'GET',
                data: { hapus: id },
                dataType: 'json',
                success: function(res) {
                    if (res.status === 'success') {
                        $('#alert-container').html('<div class="alert alert-success">' + res.message + '</div>');
                        reloadPeriode();
                    } else {
                        $('#alert-container').html('<div class="alert alert-danger">' + res.message + '</div>');
                    }
                }
            });
        }
    });
});
</script>