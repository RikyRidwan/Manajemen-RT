<?php
require_once '../includes/auth.php';
redirectIfNotLoggedIn();
if(!hasRole('warga')) { header('Location: ../dashboard.php'); exit; }
$title = "Hubungi Admin";
include '../includes/header.php';
?>
<div class="container-fluid"><div class="card"><div class="card-header">Chat dengan Admin RT</div><div class="card-body"><p>Untuk pertanyaan atau bantuan, silakan hubungi admin melalui WhatsApp:</p><a href="https://wa.me/628123456789?text=Halo%20Admin%20RT,%20saya%20warga%20mau%20bertanya" class="btn btn-success">Chat via WhatsApp</a></div></div></div>
<?php include '../includes/footer.php'; ?>