<?php
session_start();
if (!isset($_SESSION['level']) || $_SESSION['level'] != 'admin') {
    echo "<script>alert('Silakan login terlebih dahulu!'); window.location='../../login.php';</script>";
    exit();
}
include '../../koneksi.php';

if (isset($_GET['id_pembayaran'])) {
    $id_pembayaran = $_GET['id_pembayaran'];

    $query = mysqli_query($koneksi, "DELETE FROM pembayaran WHERE id_pembayaran = '$id_pembayaran'");

    if ($query) {
        echo "<script>alert('Data transaksi berhasil dihapus!'); window.location='pembayaran.php';</script>";
    } else {
        echo "<script>alert('Gagal menghapus data transaksi.'); window.location='pembayaran.php';</script>";
    }
} else {
    header("location:pembayaran.php");
}
?>