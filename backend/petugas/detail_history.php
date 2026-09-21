<?php
session_start();
if (!isset($_SESSION['level']) || $_SESSION['level'] != 'petugas') {
    echo "<script>alert('Silakan login terlebih dahulu!'); window.location='../../login.php';</script>";
    exit();
}
include '../../koneksi.php';

$nisn = mysqli_real_escape_string($koneksi, $_GET['nisn']);

// Ambil data siswa + tingkat kelasnya (BUKAN lewat id_spp lagi, karena sekarang bisa multi-tahun)
$q_siswa = mysqli_query($koneksi, "
    SELECT siswa.*, kelas.tingkat, kelas.jurusan
    FROM siswa
    JOIN kelas ON siswa.id_kelas = kelas.id_kelas
    WHERE siswa.nisn = '$nisn'
");
$s = mysqli_fetch_assoc($q_siswa);

if (!$s) {
    echo "<script>alert('Data siswa tidak ditemukan!'); window.location='history.php';</script>";
    exit();
}

$tingkat = (int) $s['tingkat'];

// Ambil semua tahun SPP yang ada, urut dari yang paling baru
$tahun_urut = []; // [tahun => nominal], besar ke kecil
$q_tahun = mysqli_query($koneksi, "SELECT tahun, nominal FROM spp ORDER BY tahun DESC");
while ($t = mysqli_fetch_assoc($q_tahun)) {
    $tahun_urut[(int) $t['tahun']] = (int) $t['nominal'];
}

// Batasi jumlah tahun yang ditampilkan sesuai tingkat:
// Kelas 10 -> 1 tahun (tahun berjalan), Kelas 11 -> 2 tahun, Kelas 12 -> 3 tahun
$jumlah_tahun_ditampilkan = $tingkat - 9; // 10->1, 11->2, 12->3
$tahun_untuk_siswa = array_slice($tahun_urut, 0, max($jumlah_tahun_ditampilkan, 1), true);
krsort($tahun_untuk_siswa); // tampilkan dari tahun terbaru ke lama

$pageTitle   = 'Detail Status Pembayaran - Petugas';
$currentPage = 'history.php';
include __DIR__ . '/components/header.php';
?>

<div class="pt-3 pb-2 mb-3 border-bottom d-flex justify-content-between align-items-center">
    <div>
        <h1 class="h3 fw-bold mb-1" style="color: #db2777;">Detail Status Pembayaran SPP</h1>
        <p class="text-muted mb-0">Siswa: <strong><?php echo htmlspecialchars($s['nama']); ?></strong>
            (NISN: <?php echo htmlspecialchars($s['nisn']); ?> |
            Kelas: <?php echo htmlspecialchars($tingkat . ' ' . $s['jurusan']); ?>)
        </p>
    </div>
    <a href="history.php" class="btn btn-secondary btn-sm">Kembali</a>
</div>

<?php
$bulan_arr = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

foreach ($tahun_untuk_siswa as $tahun => $nominal_tagihan):
?>
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body">
        <h6 class="fw-bold mb-3" style="color:#9d174d;">Tahun Ajaran <?= formatTA($tahun); ?></h6>
        <div class="table-responsive">
            <table class="table table-bordered text-center align-middle mb-0">
                <thead style="background-color: #fdf2f8; color: #db2777;">
                    <tr>
                        <th>Bulan</th>
                        <th>Total Tagihan</th>
                        <th>Sudah Dibayar</th>
                        <th>Status Pelunasan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($bulan_arr as $bln):
                        $q_cek = mysqli_query($koneksi, "SELECT SUM(jumlah_bayar) as total_bayar FROM pembayaran
                                                          WHERE nisn = '$nisn' AND bulan_dibayar = '$bln' AND tahun_dibayar = '$tahun'");
                        $d_cek = mysqli_fetch_assoc($q_cek);
                        $sudah_bayar = $d_cek['total_bayar'] ?? 0;

                        if ($sudah_bayar >= $nominal_tagihan) {
                            $badge = '<span class="badge bg-success px-3 py-2">Lunas</span>';
                        } elseif ($sudah_bayar > 0) {
                            $badge = '<span class="badge bg-warning text-dark px-3 py-2">Cicilan (Rp ' . number_format($sudah_bayar, 0, ',', '.') . ')</span>';
                        } else {
                            $badge = '<span class="badge bg-secondary px-3 py-2">Belum Bayar</span>';
                        }
                    ?>
                    <tr>
                        <td class="fw-semibold text-start ps-4"><?php echo $bln; ?></td>
                        <td>Rp <?php echo number_format($nominal_tagihan, 0, ',', '.'); ?></td>
                        <td class="fw-semibold text-success">Rp <?php echo number_format($sudah_bayar, 0, ',', '.'); ?></td>
                        <td><?php echo $badge; ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endforeach; ?>

<?php include __DIR__ . '/components/footer.php'; ?>