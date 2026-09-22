<?php
session_start();
// Cek apakah sudah login dan levelnya petugas
if (!isset($_SESSION['level']) || $_SESSION['level'] != 'petugas') {
    echo "<script>alert('Silakan login terlebih dahulu!'); window.location='../../login.php';</script>";
    exit();
}
include '../../koneksi.php';

// Statistik ringkas, sama seperti dashboard admin
$total_siswa   = (int) (mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) AS jml FROM siswa"))['jml'] ?? 0);
$total_petugas = (int) (mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) AS jml FROM petugas"))['jml'] ?? 0);
$total_kelas   = (int) (mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) AS jml FROM kelas"))['jml'] ?? 0);

$pageTitle   = 'Dashboard Petugas - Aplikasi Pembayaran SPP';
$currentPage = 'index.php';
include __DIR__ . '/components/header.php';
include __DIR__ . '/components/sidebar.php';
?>
<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-4 border-bottom">
    <div>
        <h1 class="h3 fw-bold" style="color: #db2777;">Dashboard Petugas</h1>
        <p class="text-muted mb-0">Selamat datang, <b><?= htmlspecialchars($_SESSION['nama_petugas']); ?></b></p>
    </div>
    <div class="btn-toolbar mb-2 mb-md-0">
        <span class="badge p-2 fs-6" style="background-color: #db2777;"><i class="bi bi-calendar-event me-1"></i> <?= date('d M Y'); ?></span>
    </div>
</div>

<!-- Kartu Statistik, sama seperti dashboard admin -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-uppercase text-muted small fw-semibold">Total Siswa</div>
                    <div class="fs-3 fw-bold"><?= $total_siswa; ?></div>
                </div>
                <div class="d-flex align-items-center justify-content-center rounded-3" style="width:56px; height:56px; background-color:#fce7f3;">
                    <i class="bi bi-people" style="font-size:1.4rem; color:#db2777;"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-uppercase text-muted small fw-semibold">Petugas / Admin</div>
                    <div class="fs-3 fw-bold"><?= $total_petugas; ?></div>
                </div>
                <div class="d-flex align-items-center justify-content-center rounded-3" style="width:56px; height:56px; background-color:#ede9fe;">
                    <i class="bi bi-person-badge" style="font-size:1.4rem; color:#7c3aed;"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-uppercase text-muted small fw-semibold">Data Kelas</div>
                    <div class="fs-3 fw-bold"><?= $total_kelas; ?></div>
                </div>
                <div class="d-flex align-items-center justify-content-center rounded-3" style="width:56px; height:56px; background-color:#dbeafe;">
                    <i class="bi bi-building" style="font-size:1.4rem; color:#2563eb;"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-uppercase text-muted small fw-semibold">Status Sistem</div>
                    <div class="fs-4 fw-bold text-success">Aman</div>
                </div>
                <div class="d-flex align-items-center justify-content-center rounded-3" style="width:56px; height:56px; background-color:#dcfce7;">
                    <i class="bi bi-shield-check" style="font-size:1.4rem; color:#16a34a;"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Card Utama / Konten Petugas -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3">
        <h5 class="mb-0 fw-bold"><i class="bi bi-journal-check me-2"></i>Menu Utama Petugas Kasir</h5>
    </div>
    <div class="card-body">
        <p class="text-muted">Sebagai petugas, tugas utama kamu adalah melayani pembayaran SPP siswa dan melihat riwayat status pembayaran siswa.</p>
        <div class="alert mb-0" style="background-color: #fdf2f8; color: #9d174d;" role="alert">
            <i class="bi bi-shield-lock me-2"></i><b>Catatan:</b> Pastikan untuk selalu mengecek NISN siswa dengan teliti sebelum memproses transaksi pembayaran SPP.
        </div>
    </div>
</div>

<?php include __DIR__ . '/components/footer.php'; ?>