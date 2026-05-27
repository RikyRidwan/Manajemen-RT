<?php
require_once '../includes/auth.php';
redirectIfNotLoggedIn();
if(!hasRole('warga')) { header('Location: ../dashboard.php'); exit; }
$user = getCurrentUser();
global $pdo;
$title = "Upload Bukti Pembayaran";

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) {
    $_SESSION['error'] = "Tagihan tidak dipilih.";
    header('Location: tagihan.php');
    exit;
}

// Ambil data tagihan milik user ini
$stmt = $pdo->prepare("SELECT p.*, i.nama_iuran, pr.tahun, pr.bulan 
                       FROM pembayaran p 
                       JOIN iuran i ON p.iuran_id = i.id 
                       JOIN periode pr ON p.periode_id = pr.id 
                       WHERE p.id = ? AND p.user_id = ?");
$stmt->execute([$id, $user['id']]);
$tagihan = $stmt->fetch();

if (!$tagihan) {
    $_SESSION['error'] = "Tagihan tidak valid atau bukan milik Anda.";
    header('Location: tagihan.php');
    exit;
}

if ($tagihan['status'] != 'pending') {
    $_SESSION['error'] = "Tagihan sudah diproses, tidak dapat upload bukti.";
    header('Location: tagihan.php');
    exit;
}

// Proses upload
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['bukti'])) {
    $file = $_FILES['bukti'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'pdf'];
    if (!in_array($ext, $allowed)) {
        $_SESSION['error'] = "Format file tidak didukung. Gunakan JPG, PNG, atau PDF.";
    } elseif ($file['size'] > 2 * 1024 * 1024) {
        $_SESSION['error'] = "Ukuran file maksimal 2MB.";
    } else {
        $filename = 'bukti_warga_' . $user['id'] . '_' . $id . '_' . time() . '.' . $ext;
        $target = '../assets/uploads/bukti/' . $filename;
        if (move_uploaded_file($file['tmp_name'], $target)) {
            $pdo->prepare("UPDATE pembayaran SET bukti_file = ? WHERE id = ?")->execute([$filename, $id]);
            $_SESSION['success'] = "Bukti berhasil diupload. Menunggu verifikasi bendahara.";
            header('Location: tagihan.php');
            exit;
        } else {
            $_SESSION['error'] = "Gagal menyimpan file. Coba lagi.";
        }
    }
}

include '../includes/header.php';
?>
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <i class="fas fa-upload me-2"></i> Upload Bukti Pembayaran
                </div>
                <div class="card-body">
                    <?php if(isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
                    <?php endif; ?>
                    <div class="alert alert-info">
                        <strong>Tagihan:</strong> <?= htmlspecialchars($tagihan['nama_iuran']) ?> - Periode <?= $tagihan['bulan'] . '/' . $tagihan['tahun'] ?><br>
                        <strong>Jumlah:</strong> Rp <?= number_format($tagihan['jumlah'], 0, ',', '.') ?>
                    </div>
                    <form method="POST" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label class="form-label">File Bukti (JPG, PNG, PDF) max 2MB</label>
                            <input type="file" name="bukti" class="form-control" accept="image/*,application/pdf" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Upload Bukti</button>
                        <a href="tagihan.php" class="btn btn-secondary">Batal</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include '../includes/footer.php'; ?>