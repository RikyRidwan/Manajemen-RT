<?php
require_once '../../includes/auth.php';
redirectIfNotLoggedIn();
if(!hasRole('superadmin')) { echo "Akses ditolak"; exit; }
require_once '../../config/database.php';
global $pdo;

// Proses Tambah
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['tambah'])) {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role_id = $_POST['role_id'];
    $fullname = trim($_POST['fullname']);
    $no_hp = trim($_POST['no_hp']);
    $alamat = trim($_POST['alamat']);
    try {
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role_id, fullname, no_hp, alamat, status_aktif) VALUES (?,?,?,?,?,?,?,'aktif')");
        $stmt->execute([$username, $email, $password, $role_id, $fullname, $no_hp, $alamat]);
        $_SESSION['success'] = "Akun berhasil ditambahkan.";
    } catch(PDOException $e) {
        $_SESSION['error'] = "Gagal: " . $e->getMessage();
    }
    header('Location: index.php?view=kelola_akun');
    exit;
}

// Proses Edit
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit'])) {
    $id = $_POST['id'];
    $fullname = trim($_POST['fullname']);
    $no_hp = trim($_POST['no_hp']);
    $alamat = trim($_POST['alamat']);
    $status_aktif = $_POST['status_aktif'];
    $role_id = $_POST['role_id'];
    try {
        $stmt = $pdo->prepare("UPDATE users SET fullname=?, no_hp=?, alamat=?, status_aktif=?, role_id=? WHERE id=?");
        $stmt->execute([$fullname, $no_hp, $alamat, $status_aktif, $role_id, $id]);
        $_SESSION['success'] = "Akun berhasil diperbarui.";
    } catch(PDOException $e) {
        $_SESSION['error'] = "Gagal: " . $e->getMessage();
    }
    header('Location: index.php?view=kelola_akun');
    exit;
}

// Reset Password
if (isset($_GET['reset'])) {
    $id = (int)$_GET['reset'];
    $hash = password_hash('12345678', PASSWORD_DEFAULT);
    $pdo->prepare("UPDATE users SET password=? WHERE id=?")->execute([$hash, $id]);
    $_SESSION['success'] = "Password direset menjadi 12345678.";
    header('Location: index.php?view=kelola_akun');
    exit;
}

// Hapus Akun
if (isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];
    if ($id == $_SESSION['user_id']) {
        $_SESSION['error'] = "Tidak bisa menghapus akun sendiri.";
    } else {
        $pdo->prepare("DELETE FROM log_aktivitas WHERE user_id=?")->execute([$id]);
        $pdo->prepare("DELETE FROM warga_detail WHERE user_id=?")->execute([$id]);
        $pdo->prepare("DELETE FROM surat_pengajuan WHERE user_id=?")->execute([$id]);
        $pdo->prepare("DELETE FROM users WHERE id=?")->execute([$id]);
        $_SESSION['success'] = "Akun dihapus.";
    }
    header('Location: index.php?view=kelola_akun');
    exit;
}

// Ambil data user
$users = $pdo->query("SELECT u.id, u.username, u.email, u.fullname, u.no_hp, u.alamat, u.status_aktif, u.created_at, r.nama_role 
                      FROM users u 
                      JOIN roles r ON u.role_id = r.id 
                      ORDER BY u.id DESC")->fetchAll();
$roles = $pdo->query("SELECT id, nama_role FROM roles")->fetchAll();
?>
<div class="container-fluid">
    <h2 class="mb-4">Kelola Akun</h2>
    <?php if(isset($_SESSION['success'])): ?>
        <div class="alert alert-success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
    <?php endif; ?>
    <?php if(isset($_SESSION['error'])): ?>
        <div class="alert alert-danger"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <!-- Form Tambah -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">Tambah Akun Baru</div>
        <div class="card-body">
            <form method="POST" class="row g-2" action="index.php?view=kelola_akun">
                <div class="col-md-3"><input type="text" name="username" class="form-control" placeholder="Username" required></div>
                <div class="col-md-3"><input type="email" name="email" class="form-control" placeholder="Email" required></div>
                <div class="col-md-3"><input type="password" name="password" class="form-control" placeholder="Password" required></div>
                <div class="col-md-3">
                    <select name="role_id" class="form-select" required>
                        <option value="">Pilih Role</option>
                        <?php foreach($roles as $role): ?>
                            <option value="<?= $role['id'] ?>"><?= ucfirst($role['nama_role']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3"><input type="text" name="fullname" class="form-control" placeholder="Nama Lengkap" required></div>
                <div class="col-md-3"><input type="text" name="no_hp" class="form-control" placeholder="No HP"></div>
                <div class="col-md-6"><textarea name="alamat" class="form-control" placeholder="Alamat"></textarea></div>
                <div class="col-md-12"><button type="submit" name="tambah" class="btn btn-primary">Tambah Akun</button></div>
            </form>
        </div>
    </div>

    <!-- Tabel Daftar Akun -->
    <div class="card">
        <div class="card-header">Daftar Akun</div>
        <div class="card-body table-responsive">
            <table class="table table-bordered table-striped datatable" width="100%">
                <thead>
                    <tr>
                        <th>ID</th><th>Username</th><th>Email</th><th>Nama Lengkap</th><th>No HP</th><th>Alamat</th><th>Role</th><th>Status</th><th>Tanggal Dibuat</th><th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($users as $user): ?>
                    <tr>
                        <td><?= $user['id'] ?></td>
                        <td><?= htmlspecialchars($user['username']) ?></td>
                        <td><?= htmlspecialchars($user['email']) ?></td>
                        <td><?= htmlspecialchars($user['fullname']) ?></td>
                        <td><?= htmlspecialchars($user['no_hp']) ?></td>
                        <td><?= htmlspecialchars($user['alamat']) ?></td>
                        <td><?= $user['nama_role'] ?></td>
                        <td><?= $user['status_aktif'] ?></td>
                        <td><?= date('d-m-Y', strtotime($user['created_at'])) ?></td>
                        <td>
                            <button type="button" class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#editModal<?= $user['id'] ?>">Edit</button>
                            <a href="index.php?view=kelola_akun&reset=<?= $user['id'] ?>" class="btn btn-sm btn-warning" onclick="return confirm('Reset password user ini menjadi 12345678?')">Reset</a>
                            <a href="index.php?view=kelola_akun&hapus=<?= $user['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin hapus akun ini?')">Hapus</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Edit per user -->
<?php foreach($users as $user): ?>
<div class="modal fade" id="editModal<?= $user['id'] ?>" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="index.php?view=kelola_akun">
                <div class="modal-header"><h5 class="modal-title">Edit Akun</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <input type="hidden" name="id" value="<?= $user['id'] ?>">
                    <div class="mb-2"><label>Nama Lengkap</label><input type="text" name="fullname" class="form-control" value="<?= htmlspecialchars($user['fullname']) ?>" required></div>
                    <div class="mb-2"><label>No HP</label><input type="text" name="no_hp" class="form-control" value="<?= htmlspecialchars($user['no_hp']) ?>"></div>
                    <div class="mb-2"><label>Alamat</label><textarea name="alamat" class="form-control"><?= htmlspecialchars($user['alamat']) ?></textarea></div>
                    <div class="mb-2"><label>Status</label>
                        <select name="status_aktif" class="form-select">
                            <option value="aktif" <?= $user['status_aktif'] == 'aktif' ? 'selected' : '' ?>>Aktif</option>
                            <option value="nonaktif" <?= $user['status_aktif'] == 'nonaktif' ? 'selected' : '' ?>>Nonaktif</option>
                        </select>
                    </div>
                    <div class="mb-2"><label>Role</label>
                        <select name="role_id" class="form-select">
                            <?php foreach($roles as $role): ?>
                                <option value="<?= $role['id'] ?>" <?= $user['role_id'] == $role['id'] ? 'selected' : '' ?>><?= ucfirst($role['nama_role']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer"><button type="submit" name="edit" class="btn btn-primary">Simpan</button></div>
            </form>
        </div>
    </div>
</div>
<?php endforeach; ?>