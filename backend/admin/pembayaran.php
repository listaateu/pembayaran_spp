<?php
session_start();
if (!isset($_SESSION['level']) || $_SESSION['level'] != 'admin') {
    echo "<script>alert('Silakan login terlebih dahulu!'); window.location='../../login.php';</script>";
    exit();
}
include '../../koneksi.php';
include '../components/header.php';
include '../components/sidebar.php';

// Mengambil data riwayat pembayaran dari database, digabung dengan data siswa, petugas, dan spp
$query_pembayaran = mysqli_query($koneksi, "SELECT pembayaran.*, siswa.nama, petugas.nama_petugas, spp.tahun, spp.nominal FROM pembayaran JOIN siswa ON pembayaran.nisn = siswa.nisn JOIN petugas ON pembayaran.id_petugas = petugas.id_petugas JOIN spp ON pembayaran.id_spp = spp.id_spp ORDER BY pembayaran.tgl_bayar DESC");
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
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Petugas</th>
                            <th>NISN / Nama Siswa</th>
                            <th>Tanggal Bayar</th>
                            <th>Bulan / Tahun</th>
                            <th>Nominal Bayar</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $no = 1;
                        if (mysqli_num_rows($query_pembayaran) > 0) {
                            while ($row = mysqli_fetch_assoc($query_pembayaran)) {
                        ?>
                        <tr>
                            <td><?php echo $no++; ?></td>
                            <td><?php echo $row['nama_petugas']; ?></td>
                            <td>
                                <strong><?php echo $row['nisn']; ?></strong><br>
                                <span class="text-muted small"><?php echo $row['nama']; ?></span>
                            </td>
                            <td><?php echo date('d-m-Y', strtotime($row['tgl_bayar'])); ?></td>
                            <td><?php echo $row['bulan_dibayar'] . ' ' . $row['tahun_dibayar']; ?></td>
                            <td class="fw-semibold text-success">Rp <?php echo number_format($row['jumlah_bayar'], 0, ',', '.'); ?></td>
                            <td class="text-center">
                                <a href="hapus_pembayaran.php?id_pembayaran=<?php echo $row['id_pembayaran']; ?>" class="btn btn-sm btn-danger px-2 py-1" onclick="return confirm('Yakin ingin menghapus data transaksi ini?')"><i class="bi bi-trash"></i></a>
                            </td>
                        </tr>
                        <?php 
                            }
                        } else {
                            echo "<tr><td colspan='7' class='text-center text-muted py-3'>Belum ada riwayat transaksi pembayaran.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<?php include '../components/footer.php'; ?>