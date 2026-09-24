<?php
/**
 * frontend/petugas/components/sidebar.php
 * Sudah tidak lagi menampilkan sidebar (diganti navbar atas di header.php).
 * File ini disimpan (dengan nama yang sama) supaya include di tiap halaman
 * tidak perlu diubah satu-satu. Isinya sekarang cuma modal konfirmasi logout.
 */
?>
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
