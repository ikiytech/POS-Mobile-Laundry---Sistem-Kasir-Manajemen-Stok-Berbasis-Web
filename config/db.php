<?php
$conn = new mysqli(
    "sql111.infinityfree.com",
    "if0_41596821",
    "posmobile",
    "if0_41596821_litpos"
);

if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}
?>