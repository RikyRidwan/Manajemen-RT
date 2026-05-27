<?php
require_once '../config/database.php';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $fullname = $_POST['fullname'];
    $no_kk = $_POST['no_kk'];
    $blok = $_POST['blok_rumah'];
    $no_hp = $_POST['no_hp'];
    $alamat = $_POST['alamat'];
    try {
        $pdo->beginTransaction();
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role_id, fullname, no_hp, alamat, status_aktif) VALUES (?,?,?,4,?,?,?,'nonaktif')");
        $stmt->execute([$username, $email, $password, $fullname, $no_hp, $alamat]);
        $user_id = $pdo->lastInsertId();
        $stmt2 = $pdo->prepare("INSERT INTO warga_detail (user_id, no_kk, blok_rumah) VALUES (?,?,?)");
        $stmt2->execute([$user_id, $no_kk, $blok]);
        $pdo->commit();
        $_SESSION['success'] = "Registrasi berhasil. Tunggu verifikasi admin.";
        header('Location: ../index.php'); exit;
    } catch(Exception $e) { $pdo->rollBack(); $error = "Gagal: ".$e->getMessage(); }
}
?>
<!DOCTYPE html>
<html>
<head><title>Registrasi Warga</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet"></head>
<body>
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-primary text-white">Registrasi Warga Baru</div>
                <div class="card-body">
                    <?php if(isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>
                    <form method="POST">
                        <div class="mb-2"><input type="text" name="username" class="form-control" placeholder="Username" required></div>
                        <div class="mb-2"><input type="email" name="email" class="form-control" placeholder="Email" required></div>
                        <div class="mb-2"><input type="password" name="password" class="form-control" placeholder="Password" required></div>
                        <div class="mb-2"><input type="text" name="fullname" class="form-control" placeholder="Nama Lengkap (Kepala Keluarga)" required></div>
                        <div class="mb-2"><input type="text" name="no_kk" class="form-control" placeholder="Nomor Kartu Keluarga" required></div>
                        <div class="mb-2"><input type="text" name="blok_rumah" class="form-control" placeholder="Blok Rumah"></div>
                        <div class="mb-2"><input type="text" name="no_hp" class="form-control" placeholder="No HP"></div>
                        <div class="mb-2"><textarea name="alamat" class="form-control" placeholder="Alamat"></textarea></div>
                        <button type="submit" class="btn btn-primary w-100">Daftar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>