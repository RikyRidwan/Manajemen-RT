<?php
require_once 'config/database.php';

// Hash baru untuk password '12345678' menggunakan server ini
$new_hash = password_hash('12345678', PASSWORD_DEFAULT);
echo "Hash baru untuk password '12345678':<br>";
echo "<code>" . $new_hash . "</code><br><br>";

// Update semua warga (role_id = 4)
$stmt = $pdo->prepare("UPDATE users SET password = ? WHERE role_id = 4");
$stmt->execute([$new_hash]);
$count = $stmt->rowCount();

echo "Berhasil memperbarui password untuk $count warga.<br>";
echo "Sekarang coba login dengan username warga (contoh: warga2) dan password <strong>12345678</strong>.<br>";
echo "<a href='index.php'>Kembali ke halaman login</a>";
?>