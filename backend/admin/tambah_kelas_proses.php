<?php
session_start();
if (!isset($_SESSION['level']) || $_SESSION['level'] != 'admin') {
    echo "<script>alert('Silakan login terlebih dahulu!'); window.location='../../login.php';</script>";
    exit();
}
include '../../koneksi.php';

if (isset($_POST['simpan'])) {
    $nama_kelas = $_POST['nama_kelas'];
    $kompetensi_keahlian = $_POST['kompetensi_keahlian'];

    $query = mysqli_query($koneksi, "INSERT INTO kelas (nama_kelas, kompetensi_keahlian) VALUES ('$nama_kelas', '$kompetensi_keahlian')");

    if ($query) {
        echo "<script>alert('Data kelas berhasil ditambahkan!'); window.location='kelas.php';</script>";
    } else {
        echo "<script>alert('Gagal menambah data!'); window.location='kelas.php';</script>";
    }
}
?>