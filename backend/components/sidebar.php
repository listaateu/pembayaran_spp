<?php
$currentPage = basename($_SERVER['PHP_SELF']);
$tingkatAktif = isset($_GET['tingkat']) ? $_GET['tingkat'] : '';

$halamanSiswa = ['siswa.php', 'tambah_siswa.php', 'edit_siswa.php'];
$halamanBayar = ['pembayaran.php', 'detail_pembayaran.php'];
$halamanKelas = ['kelas.php', 'tambah_kelas.php', 'edit_kelas.php'];

// Gaya submenu aktif dipakai berulang, jadi dibikin fungsi kecil biar tidak copy-paste
function gayaSub($aktif)
{
    return $aktif ? 'color: #9d174d !important; background-color: #fdf2f8 !important;' : '';
}
?>

<nav class="col-md-3 col-lg-2 d-md-block sidebar collapse min-vh-100 p-3 shadow" style="background-color: #ffe6f0;">
    <a href="index.php" class="d-flex align-items-center pb-3 mb-3 text-dark text-decoration-none border-bottom border-pink-subtle">
        <span class="fs-5 fw-bold" style="color: #db2777;"><i class="bi bi-wallet2 me-2"></i>SPP DIGITAL</span>
    </a>
    <ul class="nav flex-column">
        <li class="nav-item mb-2">
            <a href="index.php" class="nav-link fw-semibold py-2 px-3 rounded <?= ($currentPage == 'index.php') ? 'active-menu' : 'text-dark'; ?>">
                <i class="bi bi-speedometer2 me-2"></i> Dashboard
            </a>
        </li>

        <!-- DATA SISWA -->
        <li class="nav-item mb-2">
            <a href="#submenuSiswa" data-bs-toggle="collapse" class="nav-link fw-semibold py-2 px-3 rounded text-dark d-flex justify-content-between align-items-center <?= in_array($currentPage, $halamanSiswa) ? 'active-menu' : ''; ?>">
                <span><i class="bi bi-people me-2"></i> Data Siswa</span>
                <i class="bi bi-chevron-down small"></i>
            </a>
            <div class="collapse <?= in_array($currentPage, $halamanSiswa) ? 'show' : ''; ?> ps-3 mt-1" id="submenuSiswa">
                <ul class="nav flex-column">
                    <li class="nav-item mb-1">
                        <?php $aktif = ($currentPage == 'siswa.php' && $tingkatAktif === '' && !isset($_GET['id_kelas'])); ?>
                        <a href="siswa.php" class="nav-link py-1 px-2 small rounded <?= $aktif ? 'fw-bold shadow-sm' : 'text-dark'; ?>" style="<?= gayaSub($aktif); ?>">• Semua Siswa</a>
                    </li>
                    <?php foreach (['10', '11', '12'] as $t): $aktif = ($currentPage == 'siswa.php' && $tingkatAktif === $t); ?>
                        <li class="nav-item mb-1">
                            <a href="siswa.php?tingkat=<?= $t; ?>" class="nav-link py-1 px-2 small rounded <?= $aktif ? 'fw-bold shadow-sm' : 'text-dark'; ?>" style="<?= gayaSub($aktif); ?>">• Kelas <?= $t; ?></a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </li>

        <li class="nav-item mb-2">
            <a href="petugas.php" class="nav-link fw-semibold py-2 px-3 rounded <?= ($currentPage == 'petugas.php') ? 'active-menu' : 'text-dark'; ?>">
                <i class="bi bi-person-badge me-2"></i> Data Petugas
            </a>
        </li>
        <li class="nav-item mb-2">
            <a href="spp.php" class="nav-link fw-semibold py-2 px-3 rounded <?= ($currentPage == 'spp.php') ? 'active-menu' : 'text-dark'; ?>">
                <i class="bi bi-cash-stack me-2"></i> Data SPP
            </a>
        </li>

        <!-- TRANSAKSI PEMBAYARAN, dipisah per kelas seperti Data Siswa.
             Menu "Semua Transaksi" & "Entri Pembayaran" sudah dihapus:
             Kelas 10/11/12 sudah cukup nutup semua siswa, dan alur bayar
             sudah menyatu lewat tombol "Bayar" di tabel belum-lunas. -->
        <li class="nav-item mb-2">
            <a href="#submenuBayar" data-bs-toggle="collapse" class="nav-link fw-semibold py-2 px-3 rounded text-dark d-flex justify-content-between align-items-center <?= in_array($currentPage, $halamanBayar) ? 'active-menu' : ''; ?>">
                <span><i class="bi bi-wallet-fill me-2"></i> Transaksi Pembayaran</span>
                <i class="bi bi-chevron-down small"></i>
            </a>
            <div class="collapse <?= in_array($currentPage, $halamanBayar) ? 'show' : ''; ?> ps-3 mt-1" id="submenuBayar">
                <ul class="nav flex-column">
                    <?php foreach (['10', '11', '12'] as $t): $aktif = ($currentPage == 'pembayaran.php' && $tingkatAktif === $t); ?>
                        <li class="nav-item mb-1">
                            <a href="pembayaran.php?tingkat=<?= $t; ?>" class="nav-link py-1 px-2 small rounded <?= $aktif ? 'fw-bold shadow-sm' : 'text-dark'; ?>" style="<?= gayaSub($aktif); ?>">• Kelas <?= $t; ?></a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </li>

        <!-- DATA KELAS -->
        <li class="nav-item mb-2">
            <a href="#submenuKelas" data-bs-toggle="collapse" class="nav-link fw-semibold py-2 px-3 rounded text-dark d-flex justify-content-between align-items-center <?= in_array($currentPage, $halamanKelas) ? 'active-menu' : ''; ?>">
                <span><i class="bi bi-building me-2"></i> Data Kelas</span>
                <i class="bi bi-chevron-down small"></i>
            </a>
            <div class="collapse <?= in_array($currentPage, $halamanKelas) ? 'show' : ''; ?> ps-3 mt-1" id="submenuKelas">
                <ul class="nav flex-column">
                    <li class="nav-item mb-1">
                        <?php $aktif = ($currentPage == 'kelas.php' && $tingkatAktif === ''); ?>
                        <a href="kelas.php" class="nav-link py-1 px-2 small rounded <?= $aktif ? 'fw-bold shadow-sm' : 'text-dark'; ?>" style="<?= gayaSub($aktif); ?>">• Semua Kelas</a>
                    </li>
                    <?php foreach (['10', '11', '12'] as $t): $aktif = ($currentPage == 'kelas.php' && $tingkatAktif === $t); ?>
                        <li class="nav-item mb-1">
                            <a href="kelas.php?tingkat=<?= $t; ?>" class="nav-link py-1 px-2 small rounded <?= $aktif ? 'fw-bold shadow-sm' : 'text-dark'; ?>" style="<?= gayaSub($aktif); ?>">• Tingkat <?= $t; ?></a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </li>

        <li class="nav-item mb-2">
            <a href="history_siswa.php" class="nav-link fw-semibold py-2 px-3 rounded <?= ($currentPage == 'history_siswa.php') ? 'active-menu' : 'text-dark'; ?>">
                <i class="bi bi-clock-history me-2"></i> History Status Siswa
            </a>
        </li>
        <li class="nav-item mb-2">
            <a href="laporan.php" class="nav-link fw-semibold py-2 px-3 rounded <?= ($currentPage == 'laporan.php') ? 'active-menu' : 'text-dark'; ?>">
                <i class="bi bi-file-earmark-bar-graph me-2"></i> Generate Laporan
            </a>
        </li>
        <li class="nav-item mt-4 pt-3 border-top border-pink-subtle">
            <a href="#" data-bs-toggle="modal" data-bs-target="#modalLogout" class="nav-link text-danger fw-semibold py-2 px-3 rounded"><i class="bi bi-box-arrow-right me-2"></i> Logout</a>
        </li>
    </ul>
</nav>

<!-- Modal Konfirmasi Logout -->
<div class="modal fade" id="modalLogout" tabindex="-1" aria-labelledby="modalLogoutLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-body text-center p-4 p-md-5">
                <div class="mx-auto mb-3 d-flex align-items-center justify-content-center rounded-circle" style="width: 64px; height: 64px; background-color: #fce7f3; color: #db2777; font-size: 1.75rem;">
                    <i class="bi bi-box-arrow-right"></i>
                </div>
                <h5 class="fw-bold mb-2" style="color: #9d174d;">Yakin mau logout?</h5>
                <p class="text-muted mb-4">Kamu akan keluar dari sesi ini dan perlu login ulang untuk mengakses dashboard.</p>
                <div class="d-flex justify-content-center gap-2">
                    <button type="button" class="btn btn-light border px-4 rounded-pill" data-bs-dismiss="modal">Batal</button>
                    <a href="../../logout.php" class="btn text-white px-4 rounded-pill" style="background-color: #db2777;">Ya, Logout</a>
                </div>
            </div>
        </div>
    </div>
</div>

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