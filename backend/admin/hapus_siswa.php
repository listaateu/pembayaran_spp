<?php
session_start();
if (!isset($_SESSION['level']) || $_SESSION['level'] != 'admin') {
    echo "<script>alert('Silakan login terlebih dahulu!'); window.location='../../login.php';</script>";
    exit();
}
include '../../koneksi.php';

$nisn = $_GET['nisn'];
$hapus = mysqli_query($koneksi, "DELETE FROM siswa WHERE nisn = '$nisn'");

if ($hapus) {
    echo "<script>alert('Data siswa berhasil dihapus!'); window.location='siswa.php';</script>";
} else {
    echo "<script>alert('Gagal menghapus data: " . mysqli_error($koneksi) . "'); window.location='siswa.php';</script>";
}
?>