<?php
require_once '../../includes/auth.php';
redirectIfNotLoggedIn();
if(!hasRole('superadmin')) { echo "Akses ditolak"; exit; }
require_once '../../config/database.php';
global $pdo;


// Backup
if(isset($_GET['backup'])) {
    // Nama file backup
    $backup_file = '../backup_rt_' . date('Y-m-d_H-i-s') . '.sql';
    $command = "mysqldump --user=root --password= --host=localhost manajemen_rt > " . escapeshellarg($backup_file);
    // Karena Windows, gunakan path mysqldump
    $mysqldump = "C:\\xampp\\mysql\\bin\\mysqldump.exe";
    $command = "\"$mysqldump\" --user=root --password= --host=localhost manajemen_rt > \"$backup_file\"";
    exec($command, $output, $return);
    if($return === 0) {
        $_SESSION['success'] = "Backup berhasil. File: " . basename($backup_file);
        logAktivitas("Backup database");
    } else {
        $_SESSION['error'] = "Backup gagal. Pastikan mysqldump tersedia.";
    }
    header('Location: backup.php');
    exit;
}

// Restore (upload file .sql)
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['sql_file'])) {
    $file = $_FILES['sql_file']['tmp_name'];
    if($file) {
        $sql = file_get_contents($file);
        $pdo->exec("USE manajemen_rt");
        $pdo->exec($sql);
        $_SESSION['success'] = "Restore database berhasil.";
        logAktivitas("Restore database");
    } else {
        $_SESSION['error'] = "Gagal upload file.";
    }
    header('Location: backup.php');
    exit;
}

// Daftar file backup
$backup_files = glob('../backup_rt_*.sql');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Backup & Restore</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-4">
    <h2>Backup & Restore Database</h2>
    <?php if(isset($_SESSION['success'])): ?><div class="alert alert-success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div><?php endif; ?>
    <?php if(isset($_SESSION['error'])): ?><div class="alert alert-danger"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div><?php endif; ?>
    
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">Backup Database</div>
                <div class="card-body">
                    <p>Klik tombol berikut untuk membuat backup database.</p>
                    <a href="?backup=1" class="btn btn-primary" onclick="return confirm('Mulai backup?')">Backup Sekarang</a>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">Restore Database</div>
                <div class="card-body">
                    <form method="POST" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label>Pilih file .sql</label>
                            <input type="file" name="sql_file" class="form-control" accept=".sql" required>
                        </div>
                        <button type="submit" class="btn btn-warning" onclick="return confirm('Restore akan menimpa data saat ini. Lanjutkan?')">Restore</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <div class="card mt-3">
        <div class="card-header">File Backup Tersimpan</div>
        <div class="card-body">
            <ul>
                <?php foreach($backup_files as $bf): ?>
                    <li><a href="<?= $bf ?>" download><?= basename($bf) ?></a></li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
</div>
</body>
</html>