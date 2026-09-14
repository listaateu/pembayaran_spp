<?php
session_start();
if (!isset($_SESSION['level']) || $_SESSION['level'] != 'admin') {
    echo "<script>alert('Silakan login terlebih dahulu!'); window.location='../../login.php';</script>";
    exit();
}
include '../../koneksi.php';

$id = $_GET['id'];
$query = mysqli_query($koneksi, "DELETE FROM spp WHERE id_spp = '$id'");

if ($query) {
    echo "<script>alert('Data SPP berhasil dihapus!'); window.location='spp.php';</script>";
} else {
    echo "<script>alert('Gagal menghapus data: " . mysqli_error($koneksi) . "'); window.location='spp.php';</script>";
}
?>