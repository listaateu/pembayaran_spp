<?php
session_start();
if (!isset($_SESSION['level']) || $_SESSION['level'] != 'petugas') {
    echo "<script>alert('Silakan login terlebih dahulu!'); window.location='../../login.php';</script>";
    exit();
}
include '../../koneksi.php';

/* ============================================================
   HISTORY STATUS PEMBAYARAN SISWA (versi petugas)
   Sama seperti punya admin: 1 baris per siswa, ringkasan jumlah
   bulan yang sudah dibayar + tahun ajaran yang sudah ada
   transaksinya (format "2024/2025"), plus kotak pencarian.
   ============================================================ */

function formatTA($tahun)
{
    $tahun = (int) $tahun;
    return $tahun . '/' . ($tahun + 1);
}

$query = mysqli_query($koneksi, "
    SELECT
        siswa.nisn,
        siswa.nama,
        kelas.tingkat,
        kelas.jurusan,
        COUNT(pembayaran.id_pembayaran) AS jumlah_transaksi,
        GROUP_CONCAT(DISTINCT pembayaran.tahun_dibayar ORDER BY pembayaran.tahun_dibayar SEPARATOR ',') AS tahun_list,
        MAX(pembayaran.tgl_bayar) AS terakhir_bayar
    FROM pembayaran
    JOIN siswa ON pembayaran.nisn = siswa.nisn
    JOIN kelas ON siswa.id_kelas = kelas.id_kelas
    GROUP BY siswa.nisn, siswa.nama, kelas.tingkat, kelas.jurusan
    ORDER BY siswa.nama ASC
");

$pageTitle   = 'History Status Siswa - Petugas';
$currentPage = 'history.php';
include __DIR__ . '/components/header.php';
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-4 border-bottom">
    <div>
        <h1 class="h3 fw-bold mb-1" style="color: #db2777;">History Status Pembayaran Siswa</h1>
        <p class="text-muted mb-0 small">Cari siswa, lalu klik "Lihat Detail" untuk melihat rincian pembayaran per bulan per tahun ajaran.</p>
    </div>
</div>

<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-3">
        <div class="input-group" style="max-width: 380px;">
            <span class="input-group-text bg-white border-end-0" style="color:#db2777;">
                <i class="bi bi-search"></i>
            </span>
            <input type="text" id="cari-siswa" class="form-control border-start-0"
                   placeholder="Cari nama atau NISN..." autocomplete="off">
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="tabel-history">
                <thead style="background-color: #fdf2f8; color: #db2777;">
                    <tr>
                        <th>No</th>
                        <th>NISN</th>
                        <th>Nama Siswa</th>
                        <th>Kelas</th>
                        <th>Bulan Tercatat Bayar</th>
                        <th>Tahun Ajaran</th>
                        <th>Terakhir Bayar</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $no = 1;
                    if ($query && mysqli_num_rows($query) > 0):
                        while ($row = mysqli_fetch_assoc($query)):
                            $nama_cari = strtolower($row['nama'] . ' ' . $row['nisn']);

                            $tahun_ajaran_list = '-';
                            if (!empty($row['tahun_list'])) {
                                $tahun_pecah = array_filter(array_map('trim', explode(',', $row['tahun_list'])));
                                $tahun_ajaran_list = implode(', ', array_map('formatTA', $tahun_pecah));
                            }
                    ?>
                        <tr data-cari="<?= htmlspecialchars($nama_cari); ?>">
                            <td><?= $no++; ?></td>
                            <td class="text-muted"><?= htmlspecialchars($row['nisn']); ?></td>
                            <td class="fw-semibold"><?= htmlspecialchars($row['nama']); ?></td>
                            <td><?= htmlspecialchars($row['tingkat'] . ' ' . $row['jurusan']); ?></td>
                            <td>
                                <span class="badge bg-success px-3 py-2">
                                    <?= (int) $row['jumlah_transaksi']; ?> bulan
                                </span>
                            </td>
                            <td class="text-muted small"><?= htmlspecialchars($tahun_ajaran_list); ?></td>
                            <td class="text-muted small"><?= htmlspecialchars($row['terakhir_bayar']); ?></td>
                            <td class="text-end">
                                <a href="detail_history.php?nisn=<?= urlencode($row['nisn']); ?>"
                                   class="btn btn-sm text-white" style="background-color:#db2777;">
                                    <i class="bi bi-eye me-1"></i> Lihat Detail
                                </a>
                            </td>
                        </tr>
                    <?php
                        endwhile;
                    else:
                    ?>
                        <tr><td colspan="8" class="text-center py-3 text-muted">Belum ada history atau riwayat pembayaran siswa.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <div id="tidak-ditemukan" class="text-center text-muted py-4" style="display:none;">
            Tidak ada siswa yang cocok dengan pencarian.
        </div>
    </div>
</div>

<script>
    (function () {
        var input = document.getElementById('cari-siswa');
        var baris = document.querySelectorAll('#tabel-history tbody tr[data-cari]');
        var pesanKosong = document.getElementById('tidak-ditemukan');

        input.addEventListener('input', function () {
            var kata = this.value.trim().toLowerCase();
            var adaYangCocok = false;

            baris.forEach(function (tr) {
                var cocok = tr.dataset.cari.indexOf(kata) !== -1;
                tr.style.display = cocok ? '' : 'none';
                if (cocok) adaYangCocok = true;
            });

            pesanKosong.style.display = (kata !== '' && !adaYangCocok) ? 'block' : 'none';
        });
    })();
</script>

<?php include __DIR__ . '/components/footer.php'; ?>