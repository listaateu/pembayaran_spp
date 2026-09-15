<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
if (!isset($_SESSION['level']) || $_SESSION['level'] != 'admin') {
    echo "<script>alert('Silakan login terlebih dahulu!'); window.location='../../login.php';</script>";
    exit();
}

include '../../koneksi.php';
include '../components/header.php';
include '../components/sidebar.php';
?>

<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-4 border-bottom">
        <h1 class="h3 fw-bold" style="color: #db2777;">History Status Pembayaran Siswa</h1>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle">
                    <thead style="background-color: #fdf2f8; color: #db2777;">
                        <tr>
                            <th>No</th>
                            <th>Nama Petugas</th>
                            <th>NISN</th>
                            <th>Nama Siswa</th>
                            <th>Kelas</th>
                            <th>Tgl Bayar</th>
                            <th>Bulan & Tahun Dibayar</th>
                            <th>Nominal Terbayar</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $no = 1;
                        $query = mysqli_query($koneksi, "
                            SELECT pembayaran.*, petugas.nama_petugas, siswa.nama, kelas.tingkat, kelas.jurusan, spp.nominal 
                            FROM pembayaran 
                            JOIN petugas ON pembayaran.id_petugas = petugas.id_petugas 
                            JOIN siswa ON pembayaran.nisn = siswa.nisn 
                            JOIN kelas ON siswa.id_kelas = kelas.id_kelas 
                            JOIN spp ON siswa.id_spp = spp.id_spp 
                            ORDER BY pembayaran.tgl_bayar DESC
                        ");
                        
                        if ($query && mysqli_num_rows($query) > 0) {
                            while ($row = mysqli_fetch_assoc($query)) {
                                ?>
                                <tr>
                                    <td><?= $no++; ?></td>
                                    <td><?= $row['nama_petugas']; ?></td>
                                    <td><?= $row['nisn']; ?></td>
                                    <td><?= $row['nama']; ?></td>
                                    <td><?= $row['tingkat'] . ' ' . $row['jurusan']; ?></td>
                                    <td><?= $row['tgl_bayar']; ?></td>
                                    <td><?= $row['bulan_dibayar'] . ' ' . $row['tahun_dibayar']; ?></td>
                                    <td>Rp <?= number_format($row['jumlah_bayar'], 0, ',', '.'); ?></td>
                                </tr>
                                <?php
                            }
                        } else {
                            echo "<tr><td colspan='8' class='text-center py-3 text-muted'>Belum ada history atau riwayat pembayaran siswa.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<?php include '../components/footer.php'; ?>