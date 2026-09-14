<?php
session_start();
// Cek apakah sudah login dan levelnya petugas
if (!isset($_SESSION['level']) || $_SESSION['level'] != 'petugas') {
    echo "<script>alert('Silakan login terlebih dahulu!'); window.location='../../login.php';</script>";
    exit();
}
include '../../koneksi.php';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Petugas - Aplikasi Pembayaran SPP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light">

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar Petugas -->
            <nav class="col-md-3 col-lg-2 d-md-block bg-dark navbar-dark sidebar collapse min-vh-100 p-3 shadow">
                <a href="index.php" class="d-flex align-items-center pb-3 mb-3 text-white text-decoration-none border-bottom">
                    <span class="fs-5 fw-bold"><i class="bi bi-wallet2 me-2"></i>PETUGAS SPP</span>
                </a>
                <ul class="nav flex-column">
                    <li class="nav-item mb-2">
                        <a href="index.php" class="nav-link active bg-success text-white rounded"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a>
                    </li>
                    <li class="nav-item mb-2">
                        <a href="transaksi.php" class="nav-link text-white-50"><i class="bi bi-cash-coin me-2"></i> Entri Transaksi</a>
                    </li>
                    <li class="nav-item mb-2">
                        <a href="history.php" class="nav-link text-white-50"><i class="bi bi-clock-history me-2"></i> History Pembayaran</a>
                    </li>
                    <li class="nav-item mt-4 pt-3 border-top">
                        <a href="../../logout.php" class="nav-link text-danger"><i class="bi bi-box-arrow-right me-2"></i> Logout</a>
                    </li>
                </ul>
            </nav>

            <!-- Main Content Petugas -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-4 border-bottom">
                    <div>
                        <h1 class="h3 fw-bold">Dashboard Petugas</h1>
                        <p class="text-muted mb-0">Selamat datang, <b><?php echo $_SESSION['nama_petugas']; ?></b> (Petugas / Kasir)</p>
                    </div>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <span class="badge bg-primary p-2 fs-6"><i class="bi bi-calendar-event me-1"></i> <?php echo date('d M Y'); ?></span>
                    </div>
                </div>

                <!-- Card Utama / Konten Petugas -->
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0 fw-bold"><i class="bi bi-journal-check me-2"></i>Menu Utama Petugas Kasir</h5>
                    </div>
                    <div class="card-body">
                        <p class="text-muted">Sebagai petugas, tugas utama kamu adalah melayani pembayaran SPP siswa dan melihat riwayat transaksi yang masuk.</p>
                        <div class="alert alert-success mb-0" role="alert">
                            <i class="bi bi-shield-lock me-2"></i><b>Catatan:</b> Pastikan untuk selalu mengecek NISN siswa dengan teliti sebelum memproses transaksi pembayaran SPP.
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>