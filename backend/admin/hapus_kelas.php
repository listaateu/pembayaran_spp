<?php
session_start();
if (!isset($_SESSION['level']) || $_SESSION['level'] != 'admin') {
    echo "<script>alert('Silakan login terlebih dahulu!'); window.location='../../login.php';</script>";
    exit();
}
include '../../koneksi.php';

$id = $_GET['id'];
$query = mysqli_query($koneksi, "DELETE FROM kelas WHERE id_kelas = '$id'");

if ($query) {
    echo "<script>alert('Data kelas berhasil dihapus!'); window.location='kelas.php';</script>";
} else {
    echo "<script>alert('Gagal menghapus data: " . mysqli_error($koneksi) . "'); window.location='kelas.php';</script>";
}
?>