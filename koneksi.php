<?php
$host = "localhost";
$user = "root";
$pass = "";
// Pastikan nama database ini sama persis dengan yang kamu buat di phpMyAdmin tadi
$db   = "pembayaran_spp"; 

$koneksi = mysqli_connect($host, $user, $pass, $db);

if (!$koneksi) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}
?>