<?php
require_once '../includes/auth.php';
redirectIfNotLoggedIn();
if(!hasRole('bendahara')) { header('Location: ../dashboard.php'); exit; }
$title = "Upload Bukti Pembayaran";
global $pdo;

// Proses upload bukti
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['upload'])) {
    $pembayaran_id = $_POST['pembayaran_id'];
    $file = $_FILES['bukti'];
    if ($file['error'] == 0) {
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'bukti_bendahara_' . $pembayaran_id . '_' . time() . '.' . $ext;
        $target = '../assets/uploads/bukti/' . $filename;
        if (move_uploaded_file($file['tmp_name'], $target)) {
            $stmt = $pdo->prepare("UPDATE pembayaran SET bukti_file = ? WHERE id = ?");
            $stmt->execute([$filename, $pembayaran_id]);
            $_SESSION['success'] = "Bukti berhasil diupload untuk pembayaran ID $pembayaran_id.";
        } else {
            $_SESSION['error'] = "Gagal upload file.";
        }
    } else {
        $_SESSION['error'] = "Error upload file.";
    }
    header('Location: upload_bukti.php');
    exit;
}

// Ambil daftar pembayaran pending yang belum punya bukti
$query = $pdo->prepare("
    SELECT p.id, u.fullname, p.jumlah, p.tanggal_pembayaran, i.nama_iuran
    FROM pembayaran p
    JOIN users u ON p.user_id = u.id
    JOIN iuran i ON p.iuran_id = i.id
    WHERE p.status = 'pending' AND (p.bukti_file IS NULL OR p.bukti_file = '')
    ORDER BY p.id DESC
");
$query->execute();
$pembayaran_list = $query->fetchAll();

include '../includes/header.php';
?>
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <i class="fas fa-upload me-2"></i> Upload Bukti Pembayaran
                </div>
                <div class="card-body">
                    <?php if(isset($_SESSION['success'])): ?>
                        <div class="alert alert-success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
                    <?php endif; ?>
                    <?php if(isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
                    <?php endif; ?>

                    <?php if(count($pembayaran_list) > 0): ?>
                        <form method="POST" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label class="form-label">Pilih Pembayaran</label>
                                <select name="pembayaran_id" class="form-select" required>
                                    <option value="">-- Pilih Pembayaran --</option>
                                    <?php foreach($pembayaran_list as $p): ?>
                                        <option value="<?= $p['id'] ?>">
                                            <?= htmlspecialchars($p['fullname']) ?> - <?= htmlspecialchars($p['nama_iuran']) ?> 
                                            (Rp <?= number_format($p['jumlah'],0,',','.') ?> - <?= $p['tanggal_pembayaran'] ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">File Bukti (JPG, PNG, PDF)</label>
                                <input type="file" name="bukti" class="form-control" accept="image/*,application/pdf" required>
                            </div>
                            <button type="submit" name="upload" class="btn btn-primary">Upload Bukti</button>
                        </form>
                    <?php else: ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i> Tidak ada pembayaran pending yang memerlukan upload bukti.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include '../includes/footer.php'; ?>