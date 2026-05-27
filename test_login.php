<?php
require_once 'config/database.php';

$username = 'warga2'; // ganti dengan username warga yang ingin diuji
$password = '12345678';

$stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
$stmt->execute([$username]);
$user = $stmt->fetch();

if ($user) {
    echo "Username: " . $user['username'] . "<br>";
    echo "Status aktif: " . $user['status_aktif'] . "<br>";
    echo "Role ID: " . $user['role_id'] . "<br>";
    echo "Hash password: " . $user['password'] . "<br>";
    if (password_verify($password, $user['password'])) {
        echo "<span style='color:green'>✓ Password cocok! Warga bisa login.</span>";
    } else {
        echo "<span style='color:red'>✗ Password tidak cocok. Reset password diperlukan.</span>";
    }
} else {
    echo "User tidak ditemukan.";
}
?>