<?php
session_start();
require_once 'config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    $stmt = $pdo->prepare("SELECT u.*, r.nama_role FROM users u JOIN roles r ON u.role_id = r.id WHERE u.username = ? OR u.email = ?");
    $stmt->execute([$username, $username]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($password, $user['password'])) {
        if ($user['status_aktif'] == 'nonaktif') {
            $_SESSION['error'] = "Akun belum diverifikasi. Hubungi admin.";
            header('Location: index.php');
            exit;
        }
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['nama_role'];
        $_SESSION['fullname'] = $user['fullname'];
        
        // Redirect sesuai role
        if ($user['nama_role'] == 'superadmin') {
            header('Location: superadmin/index.php');
        } elseif ($user['nama_role'] == 'admin') {
            header('Location: admin/index.php');
        } elseif ($user['nama_role'] == 'bendahara') {
            header('Location: bendahara/index.php');
        } else { // warga
            header('Location: warga/index.php');
        }
        exit;
    } else {
        $_SESSION['error'] = "Username/email atau password salah!";
        header('Location: index.php');
        exit;
    }
}

// Jika akses langsung tanpa POST
header('Location: index.php');
exit;
?>