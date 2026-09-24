<?php
$currentPage = basename($_SERVER['PHP_SELF']);
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

        <!-- ===== SECTION: TRANSAKSI ===== -->
        <li class="nav-section-label mt-3 mb-1">
            <i class="bi bi-lightning-charge me-1"></i> Transaksi
        </li>

        <li class="nav-item mb-2">
            <a href="transaksi.php" class="nav-link fw-semibold py-2 px-3 rounded <?= in_array($currentPage, ['transaksi.php', 'detail_pembayaran.php', 'cetak_pembayaran.php']) ? 'active-menu' : 'text-dark'; ?>">
                <i class="bi bi-wallet-fill me-2"></i> Transaksi Pembayaran
            </a>
        </li>
        <li class="nav-item mb-2">
            <a href="history.php" class="nav-link fw-semibold py-2 px-3 rounded <?= in_array($currentPage, ['history.php', 'detail_history.php']) ? 'active-menu' : 'text-dark'; ?>">
                <i class="bi bi-clock-history me-2"></i> History Pembayaran
            </a>
        </li>

        <li class="nav-item mb-2">
            <a href="setting.php" class="nav-link fw-semibold py-2 px-3 rounded <?= ($currentPage == 'setting.php') ? 'active-menu' : 'text-dark'; ?>">
                <i class="bi bi-gear me-2"></i> Pengaturan
            </a>
        </li>

        <li class="nav-item mt-4 pt-3 border-top border-pink-subtle">
            <a href="../../frontend/petugas/index.php" class="nav-link fw-semibold py-2 px-3 rounded text-dark">
                <i class="bi bi-layout-text-window-reverse me-2"></i> Tampilan Frontend
            </a>
        </li>
        <li class="nav-item mb-2">
            <a href="#" data-bs-toggle="modal" data-bs-target="#modalLogout" class="nav-link text-danger fw-semibold py-2 px-3 rounded"><i class="bi bi-box-arrow-right me-2"></i> Logout</a>
        </li>
    </ul>
</nav>

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

    /* Label kecil pemisah antar kelompok menu */
    .nav-section-label {
        font-size: 0.7rem;
        font-weight: 800;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: #c2418a;
        padding: 0.25rem 0.6rem;
        opacity: 0.75;
    }

    /* Jarak & padding antar menu, lebih lega */
    .sidebar .nav-item {
        margin-bottom: 0.5rem;
    }
    .sidebar .nav-link {
        padding-top: 0.65rem;
        padding-bottom: 0.65rem;
        font-size: 0.95rem;
        line-height: 1.4;
        transition: transform 0.15s ease, background-color 0.2s ease;
    }
    .sidebar .nav-link:hover {
        transform: translateX(3px);
    }

    @media (max-width: 767px) {
        .sidebar .nav-link {
            padding-top: 0.75rem;
            padding-bottom: 0.75rem;
            font-size: 1rem;
        }
        .sidebar .nav-item {
            margin-bottom: 0.65rem;
        }
    }
</style>