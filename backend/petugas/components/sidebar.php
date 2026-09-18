<?php $currentPage = $currentPage ?? ''; ?>
<nav class="col-md-3 col-lg-2 d-md-block sidebar collapse min-vh-100 p-3 shadow" style="background-color: #ffe6f0;">
    <a href="index.php" class="d-flex align-items-center pb-3 mb-3 text-dark text-decoration-none border-bottom border-pink-subtle">
        <span class="fs-5 fw-bold" style="color: #db2777;"><i class="bi bi-wallet2 me-2"></i>SPP DIGITAL</span>
    </a>
    <ul class="nav flex-column">
        <li class="nav-item mb-2">
            <a href="index.php" class="nav-link fw-semibold py-2 px-3 rounded <?= $currentPage === 'index.php' ? 'active-menu' : 'text-dark'; ?>">
                <i class="bi bi-speedometer2 me-2"></i> Dashboard
            </a>
        </li>
        <li class="nav-item mb-2">
            <a href="transaksi.php" class="nav-link fw-semibold py-2 px-3 rounded <?= $currentPage === 'transaksi.php' ? 'active-menu' : 'text-dark'; ?>">
                <i class="bi bi-cash-coin me-2"></i> Entri Transaksi Pembayaran
            </a>
        </li>
        <li class="nav-item mb-2">
            <a href="history.php" class="nav-link fw-semibold py-2 px-3 rounded <?= $currentPage === 'history.php' ? 'active-menu' : 'text-dark'; ?>">
                <i class="bi bi-clock-history me-2"></i> History Status Siswa
            </a>
        </li>
        <li class="nav-item mt-4 pt-3 border-top border-pink-subtle">
            <a href="#" data-bs-toggle="modal" data-bs-target="#modalLogout" class="nav-link text-danger fw-semibold py-2 px-3 rounded">
                <i class="bi bi-box-arrow-right me-2"></i> Logout
            </a>
        </li>
    </ul>
</nav>