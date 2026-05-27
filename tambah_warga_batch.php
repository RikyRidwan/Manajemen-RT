<?php
// File: tambah_warga_batch.php
// Jalankan sekali melalui browser untuk menambah 100 warga dummy
// Setelah selesai, HAPUS file ini!

require_once 'config/database.php';

// Pastikan hanya superadmin yang bisa menjalankan (optional, tapi aman)
// Atau cukup jalankan secara manual

echo "<pre>Memulai penambahan 100 warga...\n";

for ($i = 1; $i <= 100; $i++) {
    $username = "warga$i";
    $email = "warga$i@example.com";
    $password = password_hash('12345678', PASSWORD_DEFAULT);
    $fullname = "Warga Contoh $i";
    $no_hp = "0812" . str_pad($i, 8, '0', STR_PAD_LEFT);
    $alamat = "Jl. Contoh No. $i, RT 01";
    $no_kk = "KK" . str_pad($i, 10, '0', STR_PAD_LEFT);
    $blok_rumah = "Blok " . chr(65 + ($i % 26)) . $i;
    $status_warga = 'aktif';
    
    try {
        $pdo->beginTransaction();
        
        // Insert ke users
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role_id, fullname, no_hp, alamat, status_aktif) VALUES (?, ?, ?, 4, ?, ?, ?, 'aktif')");
        $stmt->execute([$username, $email, $password, $fullname, $no_hp, $alamat]);
        $user_id = $pdo->lastInsertId();
        
        // Insert ke warga_detail
        $stmt2 = $pdo->prepare("INSERT INTO warga_detail (user_id, no_kk, blok_rumah, status_warga) VALUES (?, ?, ?, ?)");
        $stmt2->execute([$user_id, $no_kk, $blok_rumah, $status_warga]);
        
        $pdo->commit();
        echo "Berhasil tambah: $username ($fullname)\n";
    } catch (PDOException $e) {
        $pdo->rollBack();
        echo "Gagal tambah $username: " . $e->getMessage() . "\n";
    }
}

echo "\nSelesai! Hapus file ini untuk keamanan.\n";
echo "</pre>";
?>