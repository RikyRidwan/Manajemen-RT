<?php
session_start();
require_once __DIR__ . '/../config/database.php';  // ← path absolut dari folder ini

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function getCurrentUser() {
    global $pdo;
    if(!isLoggedIn()) return null;
    $stmt = $pdo->prepare("SELECT u.*, r.nama_role FROM users u JOIN roles r ON u.role_id = r.id WHERE u.id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function hasRole($role_names) {
    $user = getCurrentUser();
    if(!$user) return false;
    if(is_array($role_names)) {
        return in_array($user['nama_role'], $role_names);
    }
    return $user['nama_role'] == $role_names;
}

function redirectIfNotLoggedIn() {
    if(!isLoggedIn()) {
        header('Location: ../index.php');
        exit;
    }
}

function logAktivitas($aksi) {
    global $pdo;
    if(isLoggedIn()) {
        $ip = $_SERVER['REMOTE_ADDR'];
        $stmt = $pdo->prepare("INSERT INTO log_aktivitas (user_id, aksi, ip) VALUES (?, ?, ?)");
        $stmt->execute([$_SESSION['user_id'], $aksi, $ip]);
    }
}
?>