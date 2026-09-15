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
              <table class="table table-striped table-hover align-middle">
    <thead style="background-color: #fdf2f8; color: #db2777;">
        <tr>
            <th>No</th>
            <th>NISN</th>
            <th>Nama Siswa</th>
            <th>Tanggal Bayar</th>
            <th>Bulan / Tahun</th>
            <th>Nominal Bayar</th>
            <th>Aksi</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $no = 1;
        $query = mysqli_query($koneksi, "
            SELECT pembayaran.*, siswa.nama 
            FROM pembayaran 
            JOIN siswa ON pembayaran.nisn = siswa.nisn 
            ORDER BY pembayaran.tgl_bayar DESC
        ");
        
        if ($query && mysqli_num_rows($query) > 0) {
            while ($row = mysqli_fetch_assoc($query)) {
                ?>
                <tr>
                    <td><?= $no++; ?></td>
                    <td><strong><?= $row['nisn']; ?></strong></td>
                    <td><?= $row['nama']; ?></td>
                    <td><?= date('d-m-Y', strtotime($row['tgl_bayar'])); ?></td>
                    <td><?= $row['bulan_dibayar'] . ' ' . $row['tahun_dibayar']; ?></td>
                    <td>Rp <?= number_format($row['jumlah_bayar'], 0, ',', '.'); ?></td>
                    <td>
                        <a href="hapus_pembayaran.php?id_pembayaran=<?= $row['id_pembayaran']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin menghapus riwayat pembayaran ini?')"><i class="bi bi-trash"></i></a>
                    </td>
                </tr>
                <?php
            }
        } else {
            echo "<tr><td colspan='7' class='text-center py-3 text-muted'>Belum ada riwayat pembayaran.</td></tr>";
        }
        ?>
    </tbody>
</table>
            </div>
        </div>
    </div>
</main>

<?php include '../components/footer.php'; ?>