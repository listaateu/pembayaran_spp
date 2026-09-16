<?php
session_start();
if (!isset($_SESSION['level']) || $_SESSION['level'] != 'admin') {
    echo "<script>alert('Silakan login terlebih dahulu!'); window.location='../../login.php';</script>";
    exit();
}
include '../../koneksi.php';
include '../components/header.php';
include '../components/sidebar.php';

// 1. Hitung total yang sudah dibayar per siswa per SPP (akumulasi semua cicilan),
//    disimpan ke array supaya cepat dicek tanpa query berulang di dalam loop.
$total_dibayar_per_spp = [];
$q_total = mysqli_query($koneksi, "SELECT nisn, id_spp, SUM(jumlah_bayar) AS total FROM pembayaran GROUP BY nisn, id_spp");
while ($t = mysqli_fetch_assoc($q_total)) {
    $kunci = $t['nisn'] . '_' . $t['id_spp'];
    $total_dibayar_per_spp[$kunci] = (int) $t['total'];
}

// 2. Ambil data riwayat pembayaran, digabung dengan data siswa & spp (buat tahu nominal totalnya)
$query = mysqli_query($koneksi, "
    SELECT pembayaran.*, siswa.nama, spp.nominal AS nominal_spp
    FROM pembayaran 
    JOIN siswa ON pembayaran.nisn = siswa.nisn 
    JOIN spp ON pembayaran.id_spp = spp.id_spp
    ORDER BY pembayaran.tgl_bayar DESC
");
?>

<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
    <div class="pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h3 fw-bold mb-3" style="color: #db2777;">Riwayat Pembayaran SPP</h1>
        <a href="tambah_pembayaran.php" class="btn text-white" style="background-color: #db2777;">
            <i class="bi bi-plus-lg me-1"></i> Entri Pembayaran
        </a>
    </div>

    <!-- Tabel Riwayat Pembayaran -->
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
              <table class="table table-striped table-hover align-middle">
    <thead style="background-color: #fdf2f8; color: #db2777;">
        <tr>
            <th>No</th>
            <th>NISN</th>
            <th>Nama Siswa</th>
            <th>Tanggal Bayar</th>
            <th>Bulan / Tahun</th>
            <th>Nominal Bayar</th>
            <th>Status</th>
            <th>Aksi</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $no = 1;
        
        if ($query && mysqli_num_rows($query) > 0) {
            while ($row = mysqli_fetch_assoc($query)) {
                // Ambil total yang sudah dibayar siswa ini untuk SPP ini (akumulasi semua cicilan)
                $kunci = $row['nisn'] . '_' . $row['id_spp'];
                $sudah_dibayar = $total_dibayar_per_spp[$kunci] ?? 0;
                $nominal_spp = (int) $row['nominal_spp'];
                $sisa = $nominal_spp - $sudah_dibayar;

                if ($sisa <= 0) {
                    $status_badge = '<span class="badge bg-success">Lunas</span>';
                } else {
                    $status_badge = '<span class="badge bg-warning text-dark">Cicilan (Sisa Rp ' . number_format($sisa, 0, ',', '.') . ')</span>';
                }
                ?>
                <tr>
                    <td><?= $no++; ?></td>
                    <td><strong><?= $row['nisn']; ?></strong></td>
                    <td><?= $row['nama']; ?></td>
                    <td><?= date('d-m-Y', strtotime($row['tgl_bayar'])); ?></td>
                    <td><?= $row['bulan_dibayar'] . ' ' . $row['tahun_dibayar']; ?></td>
                    <td>Rp <?= number_format($row['jumlah_bayar'], 0, ',', '.'); ?></td>
                    <td><?= $status_badge; ?></td>
                    <td>
                        <a href="hapus_pembayaran.php?id_pembayaran=<?= $row['id_pembayaran']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin menghapus riwayat pembayaran ini?')"><i class="bi bi-trash"></i></a>
                    </td>
                </tr>
                <?php
            }
        } else {
            echo "<tr><td colspan='8' class='text-center py-3 text-muted'>Belum ada riwayat pembayaran.</td></tr>";
        }
        ?>
    </tbody>
</table>
            </div>
        </div>
    </div>
</main>

<?php include '../components/footer.php'; ?>