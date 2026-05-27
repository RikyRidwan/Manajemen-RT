<?php
session_start();
require_once 'includes/auth.php';
logAktivitas('Logout');
session_destroy();
header('Location: index.php');
exit;
?>