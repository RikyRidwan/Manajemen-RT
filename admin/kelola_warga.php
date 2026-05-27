<?php
require_once '../includes/auth.php';
redirectIfNotLoggedIn();
if(!hasRole('admin')) { header('Location: ../dashboard.php'); exit; }
$title = "Kelola Warga";
global $pdo;

// Proses Tambah
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['tambah'])) {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $fullname = trim($_POST['fullname']);
    $no_kk = trim($_POST['no_kk']);
    $blok_rumah = trim($_POST['blok_rumah']);
    $no_hp = trim($_POST['no_hp']);
    $alamat = trim($_POST['alamat']);
    try {
        $pdo->beginTransaction();
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role_id, fullname, no_hp, alamat, status_aktif) VALUES (?,?,?,4,?,?,?,'aktif')");
        $stmt->execute([$username, $email, $password, $fullname, $no_hp, $alamat]);
        $user_id = $pdo->lastInsertId();
        $stmt2 = $pdo->prepare("INSERT INTO warga_detail (user_id, no_kk, blok_rumah) VALUES (?,?,?)");
        $stmt2->execute([$user_id, $no_kk, $blok_rumah]);
        $pdo->commit();
        $_SESSION['success'] = "Warga berhasil ditambahkan. Password: " . $_POST['password'];
    } catch(Exception $e) {
        $pdo->rollBack();
        $_SESSION['error'] = "Gagal: " . $e->getMessage();
    }
    header('Location: kelola_warga.php');
    exit;
}

// Proses Edit
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit'])) {
    $id = $_POST['id'];
    $fullname = trim($_POST['fullname']);
    $no_hp = trim($_POST['no_hp']);
    $alamat = trim($_POST['alamat']);
    $no_kk = trim($_POST['no_kk']);
    $blok_rumah = trim($_POST['blok_rumah']);
    $status_warga = $_POST['status_warga'];
    try {
        $pdo->beginTransaction();
        $pdo->prepare("UPDATE users SET fullname=?, no_hp=?, alamat=? WHERE id=?")->execute([$fullname, $no_hp, $alamat, $id]);
        $pdo->prepare("UPDATE warga_detail SET no_kk=?, blok_rumah=?, status_warga=? WHERE user_id=?")->execute([$no_kk, $blok_rumah, $status_warga, $id]);
        $pdo->commit();
        $_SESSION['success'] = "Data warga diperbarui.";
    } catch(Exception $e) {
        $pdo->rollBack();
        $_SESSION['error'] = "Gagal: " . $e->getMessage();
    }
    header('Location: kelola_warga.php');
    exit;
}

// Hapus Warga (dengan menghapus relasi foreign key terlebih dahulu)
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    try {
        $pdo->beginTransaction();
        // Hapus log aktivitas (foreign key ke users)
        $pdo->prepare("DELETE FROM log_aktivitas WHERE user_id = ?")->execute([$id]);
        // Hapus warga_detail
        $pdo->prepare("DELETE FROM warga_detail WHERE user_id = ?")->execute([$id]);
        // Hapus pengajuan surat jika ada
        $pdo->prepare("DELETE FROM surat_pengajuan WHERE user_id = ?")->execute([$id]);
        // Hapus user
        $pdo->prepare("DELETE FROM users WHERE id = ? AND role_id = 4")->execute([$id]);
        $pdo->commit();
        $_SESSION['success'] = "Warga dihapus.";
    } catch (PDOException $e) {
        $pdo->rollBack();
        $_SESSION['error'] = "Gagal hapus: " . $e->getMessage();
    }
    header('Location: kelola_warga.php');
    exit;
}

// Ambil data warga
$warga = $pdo->query("SELECT u.*, wd.no_kk, wd.blok_rumah, wd.status_warga FROM users u LEFT JOIN warga_detail wd ON u.id = wd.user_id WHERE u.role_id = 4 ORDER BY u.id DESC")->fetchAll();

include '../includes/header.php';
?>
<div class="container-fluid">
    <h2 class="mb-4">Data Warga</h2>
    <?php if(isset($_SESSION['success'])): ?>
        <div class="alert alert-success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
    <?php endif; ?>
    <?php if(isset($_SESSION['error'])): ?>
        <div class="alert alert-danger"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <!-- Form Tambah -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">Tambah Warga</div>
        <div class="card-body">
            <form method="POST" class="row g-2">
                <div class="col-md-3"><input type="text" name="username" class="form-control" placeholder="Username" required></div>
                <div class="col-md-3"><input type="email" name="email" class="form-control" placeholder="Email" required></div>
                <div class="col-md-3"><input type="password" name="password" class="form-control" placeholder="Password" required></div>
                <div class="col-md-3"><input type="text" name="fullname" class="form-control" placeholder="Nama Lengkap" required></div>
                <div class="col-md-3"><input type="text" name="no_kk" class="form-control" placeholder="No KK" required></div>
                <div class="col-md-3"><input type="text" name="blok_rumah" class="form-control" placeholder="Blok Rumah"></div>
                <div class="col-md-3"><input type="text" name="no_hp" class="form-control" placeholder="No HP"></div>
                <div class="col-md-3"><input type="text" name="alamat" class="form-control" placeholder="Alamat"></div>
                <div class="col-md-12"><button type="submit" name="tambah" class="btn btn-primary">Tambah Warga</button></div>
            </form>
        </div>
    </div>

    <!-- Tabel Daftar Warga -->
    <div class="card">
        <div class="card-header">Daftar Warga</div>
        <div class="card-body table-responsive">
            <table class="table table-bordered table-striped datatable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nama</th>
                        <th>Username</th>
                        <th>No KK</th>
                        <th>Blok</th>
                        <th>No HP</th>
                        <th>Alamat</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($warga as $w): ?>
                    <tr>
                        <td><?= $w['id'] ?></td>
                        <td><?= htmlspecialchars($w['fullname']) ?></td>
                        <td><?= htmlspecialchars($w['username']) ?></td>
                        <td><?= htmlspecialchars($w['no_kk']) ?></td>
                        <td><?= htmlspecialchars($w['blok_rumah']) ?></td>
                        <td><?= htmlspecialchars($w['no_hp']) ?></td>
                        <td><?= htmlspecialchars($w['alamat']) ?></td>
                        <td><span class="badge <?= $w['status_warga']=='aktif'?'bg-success':'bg-warning' ?>"><?= $w['status_warga'] ?></span></td>
                        <td>
                            <button class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#editModal<?= $w['id'] ?>">Edit</button>
                            <a href="?hapus=<?= $w['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Hapus warga?')">Hapus</a>
                        </td>
                    </tr>

                    <!-- Modal Edit -->
                    <div class="modal fade" id="editModal<?= $w['id'] ?>" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <form method="POST">
                                    <div class="modal-header"><h5>Edit Warga</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                                    <div class="modal-body">
                                        <input type="hidden" name="id" value="<?= $w['id'] ?>">
                                        <div class="mb-2"><label>Nama Lengkap</label><input type="text" name="fullname" class="form-control" value="<?= htmlspecialchars($w['fullname']) ?>"></div>
                                        <div class="mb-2"><label>No HP</label><input type="text" name="no_hp" class="form-control" value="<?= htmlspecialchars($w['no_hp']) ?>"></div>
                                        <div class="mb-2"><label>Alamat</label><textarea name="alamat" class="form-control"><?= htmlspecialchars($w['alamat']) ?></textarea></div>
                                        <div class="mb-2"><label>No KK</label><input type="text" name="no_kk" class="form-control" value="<?= htmlspecialchars($w['no_kk']) ?>"></div>
                                        <div class="mb-2"><label>Blok</label><input type="text" name="blok_rumah" class="form-control" value="<?= htmlspecialchars($w['blok_rumah']) ?>"></div>
                                        <div class="mb-2"><label>Status</label>
                                            <select name="status_warga" class="form-select">
                                                <option value="aktif" <?= $w['status_warga']=='aktif'?'selected':'' ?>>Aktif</option>
                                                <option value="pindah" <?= $w['status_warga']=='pindah'?'selected':'' ?>>Pindah</option>
                                                <option value="menunggak" <?= $w['status_warga']=='menunggak'?'selected':'' ?>>Menunggak</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="modal-footer"><button type="submit" name="edit" class="btn btn-primary">Simpan</button></div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php include '../includes/footer.php'; ?>