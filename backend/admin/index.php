<?php
session_start();
if (!isset($_SESSION['level']) || $_SESSION['level'] != 'admin') {
    echo "<script>alert('Silakan login terlebih dahulu!'); window.location='../../login.php';</script>";
    exit();
}
include '../../koneksi.php';

// Menghitung jumlah data secara dinamis dari database
$query_siswa = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM siswa");
$data_siswa = mysqli_fetch_assoc($query_siswa);
$total_siswa = $data_siswa['total'];

$query_petugas = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM petugas");
$data_petugas = mysqli_fetch_assoc($query_petugas);
$total_petugas = $data_petugas['total'];

$query_kelas = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM kelas");
$data_kelas = mysqli_fetch_assoc($query_kelas);
$total_kelas = $data_kelas['total'];

include '../components/header.php';
include '../components/sidebar.php';
?>

<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
    <div class="d-flex justify-content-between align-items-center pt-3 pb-2 mb-3 border-bottom">
        <div>
            <h1 class="h3 fw-bold" style="color: #db2777;">Dashboard</h1>
            <p class="text-muted mb-0">Selamat datang kembali, <strong><?php echo $_SESSION['nama_petugas'] ?? 'Administrator'; ?></strong> </p>
        </div>
        <div>
            <span class="badge px-3 py-2 text-white shadow-sm" style="background-color: #db2777;">
                <i class="bi bi-calendar-event me-1"></i> <?php echo date('d M Y'); ?>
            </span>
        </div>
    </div>

    <!-- Cards Statistik Dinamis dengan Ikon dan Warna Soft Berbeda -->
    <div class="row g-3 mb-4">
        <!-- Total Siswa (Nuansa Pink Soft) -->
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm border-start border-4" style="border-left-color: #db2777 !important;">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small fw-bold text-uppercase mb-1" style="color: #db2777;">Total Siswa</div>
                        <div class="h2 fw-bold mb-0 text-dark"><?php echo $total_siswa; ?></div>
                    </div>
                    <div class="fs-1 p-3 rounded-3" style="color: #db2777; background-color: #ffe6f0;">
                        <i class="bi bi-people-fill"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Petugas / Admin (Nuansa Ungu Soft) -->
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm border-start border-4" style="border-left-color: #8b5cf6 !important;">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small fw-bold text-uppercase mb-1" style="color: #8b5cf6;">Petugas / Admin</div>
                        <div class="h2 fw-bold mb-0 text-dark"><?php echo $total_petugas; ?></div>
                    </div>
                    <div class="fs-1 p-3 rounded-3" style="color: #8b5cf6; background-color: #f3e8ff;">
                        <i class="bi bi-person-badge-fill"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Data Kelas (Nuansa Biru/Cyan Soft) -->
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm border-start border-4" style="border-left-color: #0ea5e9 !important;">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small fw-bold text-uppercase mb-1" style="color: #0ea5e9;">Data Kelas</div>
                        <div class="h2 fw-bold mb-0 text-dark"><?php echo $total_kelas; ?></div>
                    </div>
                    <div class="fs-1 p-3 rounded-3" style="color: #0ea5e9; background-color: #e0f2fe;">
                        <i class="bi bi-building-fill"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Status Sistem (Nuansa Hijau Soft) -->
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm border-start border-4 border-success">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small fw-bold text-uppercase mb-1 text-success">Status Sistem</div>
                        <div class="h2 fw-bold mb-0 text-success">Aman</div>
                    </div>
                    <div class="fs-1 p-3 rounded-3 text-success bg-success bg-opacity-10">
                        <i class="bi bi-shield-check"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Informasi Aplikasi -->
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <h5 class="fw-bold mb-3" style="color: #db2777;"><i class="bi bi-info-circle me-2"></i>Informasi Aplikasi Pembayaran SPP</h5>
            <p class="text-secondary">
                Aplikasi ini dikembangkan untuk mempermudah pengelolaan data pembayaran SPP siswa secara terkomputerisasi. Sistem ini mendukung pembagian hak akses untuk <strong>Administrator</strong>, <strong>Petugas</strong>, dan <strong>Siswa</strong>.
            </p>
            <div class="alert border-0 text-dark" style="background-color: #ffe6f0;" role="alert">
                <i class="bi bi-lightbulb me-2 fw-bold" style="color: #db2777;"></i> <strong>Tips:</strong> Pastikan untuk selalu melakukan pengecekan data kelas dan nominal SPP sebelum memasukkan data siswa baru.
            </div>
        </div>
    </div>
</main>

<?php include '../components/footer.php'; ?>