<?php
$currentPage = basename($_SERVER['PHP_SELF']);

// SESUAIKAN DENGAN KENYATAAN DATABASE KAMU:
$id_k10 = 2; // Ganti dengan ID asli Kelas 10 di database kamu
$id_k11 = 1; // Ganti dengan ID asli Kelas 11 di database kamu
$id_k12 = 3; // Ganti dengan ID asli Kelas 12 di database kamu
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

        <!-- MENU DATA SISWA DENGAN DROPDOWN SUBMENU KELAS -->
        <li class="nav-item mb-2">
            <a href="#submenuSiswa" data-bs-toggle="collapse" class="nav-link fw-semibold py-2 px-3 rounded text-dark d-flex justify-content-between align-items-center <?php echo (in_array($currentPage, ['siswa.php', 'tambah_siswa.php', 'edit_siswa.php'])) ? 'active-menu' : ''; ?>">
                <span><i class="bi bi-people me-2"></i> Data Siswa</span>
                <i class="bi bi-chevron-down small"></i>
            </a>

            <div class="collapse <?php echo (isset($_GET['id_kelas']) || isset($_GET['tingkat']) || $currentPage == 'siswa.php' || $currentPage == 'tambah_siswa.php' || $currentPage == 'edit_siswa.php') ? 'show' : ''; ?> ps-3 mt-1" id="submenuSiswa">
                <ul class="nav flex-column">
                    <li class="nav-item mb-1">
                        <a href="siswa.php" class="nav-link py-1 px-2 small rounded <?php echo (basename($_SERVER['PHP_SELF']) == 'siswa.php' && !isset($_GET['id_kelas']) && !isset($_GET['tingkat'])) ? 'fw-bold shadow-sm' : 'text-dark'; ?>" style="<?php echo (!isset($_GET['id_kelas']) && !isset($_GET['tingkat']) && basename($_SERVER['PHP_SELF']) == 'siswa.php') ? 'color: #9d174d !important; background-color: #fdf2f8 !important;' : ''; ?>">• Semua Siswa</a>
                    </li>
                    <li class="nav-item mb-1">
                        <a href="siswa.php?tingkat=10" class="nav-link py-1 px-2 small rounded <?php echo (isset($_GET['tingkat']) && $_GET['tingkat'] == '10') ? 'fw-bold shadow-sm' : 'text-dark'; ?>" style="<?php echo (isset($_GET['tingkat']) && $_GET['tingkat'] == '10') ? 'color: #9d174d !important; background-color: #fdf2f8 !important;' : ''; ?>">• Kelas 10</a>
                    </li>
                    <li class="nav-item mb-1">
                        <a href="siswa.php?tingkat=11" class="nav-link py-1 px-2 small rounded <?php echo (isset($_GET['tingkat']) && $_GET['tingkat'] == '11') ? 'fw-bold shadow-sm' : 'text-dark'; ?>" style="<?php echo (isset($_GET['tingkat']) && $_GET['tingkat'] == '11') ? 'color: #9d174d !important; background-color: #fdf2f8 !important;' : ''; ?>">• Kelas 11</a>
                    </li>
                    <li class="nav-item mb-1">
                        <a href="siswa.php?tingkat=12" class="nav-link py-1 px-2 small rounded <?php echo (isset($_GET['tingkat']) && $_GET['tingkat'] == '12') ? 'fw-bold shadow-sm' : 'text-dark'; ?>" style="<?php echo (isset($_GET['tingkat']) && $_GET['tingkat'] == '12') ? 'color: #9d174d !important; background-color: #fdf2f8 !important;' : ''; ?>">• Kelas 12</a>
                    </li>
                </ul>
            </div>
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

        <!-- MENU DATA KELAS -->
        <li class="nav-item mb-2">
            <a href="#submenuKelas" data-bs-toggle="collapse" class="nav-link fw-semibold py-2 px-3 rounded text-dark d-flex justify-content-between align-items-center <?php echo (in_array($currentPage, ['kelas.php', 'tambah_kelas.php', 'edit_kelas.php'])) ? 'active-menu' : ''; ?>">
                <span><i class="bi bi-building me-2"></i> Data Kelas</span>
                <i class="bi bi-chevron-down small"></i>
            </a>
            <div class="collapse <?php echo (isset($_GET['tingkat']) && $currentPage == 'kelas.php') ? 'show' : ''; ?> ps-3 mt-1" id="submenuKelas">
                <ul class="nav flex-column">
                    <li class="nav-item mb-1">
                        <a href="kelas.php" class="nav-link py-1 px-2 small rounded <?php echo (basename($_SERVER['PHP_SELF']) == 'kelas.php' && !isset($_GET['tingkat'])) ? 'fw-bold shadow-sm' : 'text-dark'; ?>" style="<?php echo (!isset($_GET['tingkat']) && basename($_SERVER['PHP_SELF']) == 'kelas.php') ? 'color: #9d174d !important; background-color: #fdf2f8 !important;' : ''; ?>">• Semua Kelas</a>
                    </li>
                    <li class="nav-item mb-1">
                        <a href="kelas.php?tingkat=10" class="nav-link py-1 px-2 small rounded <?php echo (isset($_GET['tingkat']) && $_GET['tingkat'] == '10') ? 'fw-bold shadow-sm' : 'text-dark'; ?>" style="<?php echo (isset($_GET['tingkat']) && $_GET['tingkat'] == '10') ? 'color: #9d174d !important; background-color: #fdf2f8 !important;' : ''; ?>">• Tingkat 10</a>
                    </li>
                    <li class="nav-item mb-1">
                        <a href="kelas.php?tingkat=11" class="nav-link py-1 px-2 small rounded <?php echo (isset($_GET['tingkat']) && $_GET['tingkat'] == '11') ? 'fw-bold shadow-sm' : 'text-dark'; ?>" style="<?php echo (isset($_GET['tingkat']) && $_GET['tingkat'] == '11') ? 'color: #9d174d !important; background-color: #fdf2f8 !important;' : ''; ?>">• Tingkat 11</a>
                    </li>
                    <li class="nav-item mb-1">
                        <a href="kelas.php?tingkat=12" class="nav-link py-1 px-2 small rounded <?php echo (isset($_GET['tingkat']) && $_GET['tingkat'] == '12') ? 'fw-bold shadow-sm' : 'text-dark'; ?>" style="<?php echo (isset($_GET['tingkat']) && $_GET['tingkat'] == '12') ? 'color: #9d174d !important; background-color: #fdf2f8 !important;' : ''; ?>">• Tingkat 12</a>
                    </li>
                </ul>
            </div>
        </li>

        <li class="nav-item mb-2">
            <a href="history_siswa.php" class="nav-link fw-semibold py-2 px-3 rounded text-dark <?php echo ($currentPage == 'history_siswa.php') ? 'active-menu' : ''; ?>">
                <i class="bi bi-clock-history me-2"></i> History Status Siswa
            </a>
        </li>
        <li class="nav-item mt-4 pt-3 border-top border-pink-subtle">
            <a href="../../logout.php" class="nav-link text-danger fw-semibold py-2 px-3 rounded"><i class="bi bi-box-arrow-right me-2"></i> Logout</a>
        </li>
    </ul>
</nav>

<style>
    .active-menu {
        background-color: #fbcfe8 !important;
        color: #9d174d !important;
        box-shadow: 0 2px 6px rgba(157, 23, 77, 0.1);
    }
    .sidebar .nav-link:hover:not(.active-menu) {
        background-color: #fce7f3 !important;
        color: #db2777 !important;
        transition: all 0.2s ease;
    }
</style>