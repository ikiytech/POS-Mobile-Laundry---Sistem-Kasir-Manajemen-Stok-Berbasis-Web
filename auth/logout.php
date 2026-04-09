<?php
session_start();

// Kosongkan semua variabel sesi
$_SESSION = array();

// Hancurkan sesi sepenuhnya
session_destroy();

// Arahkan kembali ke halaman login
header("Location: login.php");
exit(); // Selalu gunakan exit setelah header
?>