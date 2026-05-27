<?php
session_start();
require_once 'config/database.php';

$username = 'warga2';
$password = '12345678';

$stmt = $pdo->prepare("SELECT u.*, r.nama_role FROM users u JOIN roles r ON u.role_id = r.id WHERE u.username = ?");
$stmt->execute([$username]);
$user = $stmt->fetch();

if ($user) {
    echo "User ditemukan: " . $user['username'] . "<br>";
    echo "Status aktif: " . $user['status_aktif'] . "<br>";
    echo "Role: " . $user['nama_role'] . "<br>";
    echo "Hash password: " . $user['password'] . "<br>";
    if (password_verify($password, $user['password'])) {
        echo "<span style='color:green'>✅ Password cocok! Bisa login.</span><br>";
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['nama_role'];
        $_SESSION['fullname'] = $user['fullname'];
        echo "<a href='warga/index.php'>Klik untuk ke dashboard warga</a>";
    } else {
        echo "<span style='color:red'>❌ Password tidak cocok. Reset password belum berhasil.</span>";
    }
} else {
    echo "User tidak ditemukan.";
}
?>