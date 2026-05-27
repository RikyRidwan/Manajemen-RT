<?php
require_once '../includes/auth.php';
redirectIfNotLoggedIn();
if (!hasRole('superadmin')) { header('Location: ../dashboard.php'); exit; }
$title = "Hak Akses";
$placeholder_title = "Hak Akses";
$placeholder_desc = "Kelola hak akses pengguna sistem";
$placeholder_icon = "fas fa-shield-alt";
include '../includes/header.php';
include '../includes/placeholder.php';
include '../includes/footer.php';
?>