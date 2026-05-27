<?php
require_once '../../includes/auth.php';
redirectIfNotLoggedIn();
if(!hasRole('superadmin')) { echo "Akses ditolak"; exit; }
require_once '../../config/database.php';
global $pdo;

$message = '';
$error = '';

// Proses generate
if (isset($_POST['generate'])) {
    $periode_id = $_POST['periode_id'];
    $iuran_id = $_POST['iuran_id'];
    
    // Cek periode
    $periode = $pdo->prepare("SELECT * FROM periode WHERE id = ? AND status = 'aktif'");
    $periode->execute([$periode_id]);
    $p = $periode->fetch();
    if (!$p) {
        $error = "Periode tidak ditemukan atau tidak aktif.";
    } else {
        // Ambil semua warga
        $warga = $pdo->query("SELECT id FROM users WHERE role_id = 4")->fetchAll();
        $iuran = $pdo->prepare("SELECT nominal FROM iuran WHERE id = ?");
        $iuran->execute([$iuran_id]);
        $nominal = $iuran->fetchColumn();
        if (!$nominal) $nominal = 0;
        
        $inserted = 0;
        foreach ($warga as $w) {
            // Cek apakah sudah ada tagihan untuk periode dan warga ini
            $cek = $pdo->prepare("SELECT id FROM pembayaran WHERE user_id = ? AND periode_id = ? AND iuran_id = ?");
            $cek->execute([$w['id'], $periode_id, $iuran_id]);
            if (!$cek->fetch()) {
                $stmt = $pdo->prepare("INSERT INTO pembayaran (user_id, iuran_id, periode_id, jumlah, status, tanggal_pembayaran) VALUES (?, ?, ?, ?, 'pending', NOW())");
                $stmt->execute([$w['id'], $iuran_id, $periode_id, $nominal]);
                $inserted++;
            }
        }
        $message = "Berhasil membuat $inserted tagihan untuk periode {$p['bulan']}/{$p['tahun']} dengan iuran ID $iuran_id.";
    }
}

// Ambil daftar periode aktif
$periode_aktif = $pdo->query("SELECT * FROM periode WHERE status = 'aktif' ORDER BY tahun DESC, bulan DESC")->fetchAll();
$iuran_list = $pdo->query("SELECT id, nama_iuran, nominal FROM iuran")->fetchAll();
?>
<div class="container-fluid">
    <h2 class="mb-4">Generate Tagihan Otomatis</h2>
    <?php if($message): ?>
        <div class="alert alert-success"><?= $message ?></div>
    <?php endif; ?>
    <?php if($error): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>
    <div class="card">
        <div class="card-header bg-primary text-white">Buat Tagihan Massal</div>
        <div class="card-body">
            <form method="POST" action="index.php?view=generate_tagihan">
                <div class="mb-3">
                    <label>Pilih Periode Aktif</label>
                    <select name="periode_id" class="form-select" required>
                        <option value="">-- Pilih --</option>
                        <?php foreach($periode_aktif as $p): ?>
                            <option value="<?= $p['id'] ?>"><?= $p['bulan'] . '/' . $p['tahun'] ?> (<?= ucfirst($p['status']) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label>Pilih Jenis Iuran</label>
                    <select name="iuran_id" class="form-select" required>
                        <option value="">-- Pilih --</option>
                        <?php foreach($iuran_list as $i): ?>
                            <option value="<?= $i['id'] ?>"><?= htmlspecialchars($i['nama_iuran']) ?> - Rp <?= number_format($i['nominal'],0,',','.') ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" name="generate" class="btn btn-success">Generate Tagihan</button>
            </form>
        </div>
    </div>
    <div class="mt-3">
    </div>
</div>