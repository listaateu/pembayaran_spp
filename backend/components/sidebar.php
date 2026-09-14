<?php
// Mengambil nama file yang sedang aktif di browser
$currentPage = basename($_SERVER['PHP_SELF']);
?>

<!-- Sidebar Modular Active State -->
<nav class="col-md-3 col-lg-2 d-md-block sidebar collapse min-vh-100 p-3 shadow" style="background-color: #ffe6f0;">
    <a href="index.php" class="d-flex align-items-center pb-3 mb-3 text-dark text-decoration-none border-bottom border-pink-subtle">
        <span class="fs-5 fw-bold" style="color: #db2777;"><i class="bi bi-wallet2 me-2"></i>SPP DIGITAL</span>
    </a>
    <ul class="nav flex-column">
        <li class="nav-item mb-2">
            <a href="index.php" class="nav-link fw-semibold py-2 px-3 rounded <?php echo ($currentPage == 'index.php') ? 'active-menu' : 'text-dark'; ?>">
                <i class="bi bi-speedometer2 me-2"></i> Dashboard
            </a>
        </li>
        <li class="nav-item mb-2">
            <a href="siswa.php" class="nav-link fw-semibold py-2 px-3 rounded <?php echo ($currentPage == 'siswa.php' || $currentPage == 'tambah_siswa.php' || $currentPage == 'edit_siswa.php') ? 'active-menu' : 'text-dark'; ?>">
                <i class="bi bi-people me-2"></i> Data Siswa
            </a>
        </li>
        <li class="nav-item mb-2">
            <a href="petugas.php" class="nav-link fw-semibold py-2 px-3 rounded <?php echo ($currentPage == 'petugas.php') ? 'active-menu' : 'text-dark'; ?>">
                <i class="bi bi-person-badge me-2"></i> Data Petugas
            </a>
        </li>
        <li class="nav-item mb-2">
            <a href="spp.php" class="nav-link fw-semibold py-2 px-3 rounded <?php echo ($currentPage == 'spp.php') ? 'active-menu' : 'text-dark'; ?>">
                <i class="bi bi-cash-stack me-2"></i> Data SPP
            </a>
        </li>
        <li class="nav-item mb-2">
            <a href="pembayaran.php" class="nav-link fw-semibold py-2 px-3 rounded <?php echo ($currentPage == 'pembayaran.php' || $currentPage == 'tambah_pembayaran.php') ? 'active-menu' : 'text-dark'; ?>">
                <i class="bi bi-wallet-fill me-2"></i> Transaksi Pembayaran
            </a>
        </li>
        <li class="nav-item mb-2">
            <a href="kelas.php" class="nav-link fw-semibold py-2 px-3 rounded <?php echo ($currentPage == 'kelas.php' || $currentPage == 'tambah_kelas.php' || $currentPage == 'edit_kelas.php') ? 'active-menu' : 'text-dark'; ?>">
                <i class="bi bi-building me-2"></i> Data Kelas
            </a>
        </li>
        <li class="nav-item mb-2">
    <a href="history_siswa.php" class="nav-link fw-semibold py-2 px-3 rounded text-dark">
        <i class="bi bi-clock-history me-2"></i> History Status Siswa
    </a>
</li>
        <li class="nav-item mt-4 pt-3 border-top border-pink-subtle">
            <a href="../../logout.php" class="nav-link text-danger fw-semibold py-2 px-3 rounded"><i class="bi bi-box-arrow-right me-2"></i> Logout</a>
        </li>
    </ul>
</nav>

<style>
    /* Styling khusus untuk menu yang sedang aktif */
    .active-menu {
        background-color: #fbcfe8 !important;
        color: #9d174d !important;
        box-shadow: 0 2px 6px rgba(157, 23, 77, 0.1);
    }

    /* Efek hover lembut pada menu lainnya */
    .sidebar .nav-link:hover:not(.active-menu) {
        background-color: #fce7f3 !important;
        color: #db2777 !important;
        transition: all 0.2s ease;
    }
</style>