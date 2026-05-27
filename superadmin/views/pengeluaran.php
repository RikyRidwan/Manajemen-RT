<?php
require_once '../../includes/auth.php';
redirectIfNotLoggedIn();
if(!hasRole('superadmin')) { echo "Akses ditolak"; exit; }
require_once '../../config/database.php';
global $pdo;


// Proses Tambah Pengeluaran
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['tambah'])) {
    $deskripsi = trim($_POST['deskripsi']);
    $jumlah = trim($_POST['jumlah']);
    $tanggal = $_POST['tanggal'];
    $stmt = $pdo->prepare("INSERT INTO pengeluaran (deskripsi, jumlah, tanggal, input_by) VALUES (?, ?, ?, ?)");
    $stmt->execute([$deskripsi, $jumlah, $tanggal, $_SESSION['user_id']]);
    $_SESSION['success'] = "Pengeluaran berhasil ditambahkan.";
    header('Location: index.php?page=pengeluaran');
    exit;
}

// Hapus Pengeluaran
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    $stmt = $pdo->prepare("DELETE FROM pengeluaran WHERE id = ?");
    $stmt->execute([$id]);
    $_SESSION['success'] = "Pengeluaran dihapus.";
    header('Location: index.php?page=pengeluaran');
    exit;
}

// Ambil data pengeluaran
$pengeluaran = $pdo->query("SELECT * FROM pengeluaran ORDER BY tanggal DESC")->fetchAll();
?>
<div class="container-fluid">
    <h2 class="mb-4">Kelola Pengeluaran RT</h2>
    
    <?php if(isset($_SESSION['success'])): ?>
        <div class="alert alert-success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
    <?php endif; ?>

    <!-- Form Tambah Pengeluaran -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">Tambah Pengeluaran</div>
        <div class="card-body">
            <form method="POST" action="index.php?page=pengeluaran" class="row g-2">
                <div class="col-md-4">
                    <input type="text" name="deskripsi" class="form-control" placeholder="Deskripsi" required>
                </div>
                <div class="col-md-3">
                    <input type="number" name="jumlah" class="form-control" placeholder="Jumlah (Rp)" required>
                </div>
                <div class="col-md-3">
                    <input type="date" name="tanggal" class="form-control" value="<?= date('Y-m-d') ?>" required>
                </div>
                <div class="col-md-2">
                    <button type="submit" name="tambah" class="btn btn-primary w-100">Tambah</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabel Daftar Pengeluaran -->
    <div class="card">
        <div class="card-header">Daftar Pengeluaran</div>
        <div class="card-body">
            <table class="table table-bordered datatable">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Deskripsi</th>
                        <th>Jumlah</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($pengeluaran as $p): ?>
                    <tr>
                        <td><?= htmlspecialchars($p['tanggal']) ?></td>
                        <td><?= htmlspecialchars($p['deskripsi']) ?></td>
                        <td>Rp <?= number_format($p['jumlah'], 0, ',', '.') ?></td>
                        <td>
                            <a href="index.php?page=pengeluaran&hapus=<?= $p['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin hapus pengeluaran ini?')">Hapus</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>