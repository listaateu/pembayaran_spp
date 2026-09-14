<?php
session_start();
include '../../koneksi.php';

if (!isset($_GET['nisn'])) {
    header("location:history_siswa.php");
    exit();
}

$nisn = $_GET['nisn'];

// Ambil data siswa beserta tahun SPP
$q_siswa = mysqli_query($koneksi, "SELECT siswa.*, kelas.nama_kelas, spp.tahun, spp.nominal FROM siswa JOIN kelas ON siswa.id_kelas = kelas.id_kelas JOIN spp ON siswa.id_spp = spp.id_spp WHERE siswa.nisn = '$nisn'");
$s = mysqli_fetch_assoc($q_siswa);

if (!$s) {
    echo "<script>alert('Data siswa tidak ditemukan!'); window.location='history_siswa.php';</script>";
    exit();
}

include '../components/header.php';
include '../components/sidebar.php';
?>

<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
    <div class="pt-3 pb-2 mb-3 border-bottom d-flex justify-content-between align-items-center">
        <div>
            <h1 class="h3 fw-bold mb-1" style="color: #db2777;">Detail Status Pembayaran SPP</h1>
            <p class="text-muted mb-0">Siswa: <strong><?php echo $s['nama']; ?></strong> (NISN: <?php echo $s['nisn']; ?> | Kelas: <?php echo $s['nama_kelas']; ?> | SPP Tahun: <span class="badge bg-secondary"><?php echo $s['tahun']; ?></span>)</p>
        </div>
        <a href="history_siswa.php" class="btn btn-secondary btn-sm">Kembali</a>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered text-center align-middle">
                    <thead style="background-color: #fdf2f8; color: #db2777;">
                        <tr>
                            <th>Bulan</th>
                            <th>Total Tagihan (Tahun <?php echo $s['tahun']; ?>)</th>
                            <th>Sudah Dibayar</th>
                            <th>Status Pelunasan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $bulan_arr = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                        $nominal_tagihan = $s['nominal'];

                        foreach ($bulan_arr as $bln) {
                            $q_cek = mysqli_query($koneksi, "SELECT SUM(jumlah_bayar) as total_bayar FROM pembayaran WHERE nisn = '$nisn' AND bulan_dibayar = '$bln'");
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
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<?php include '../components/footer.php'; ?>