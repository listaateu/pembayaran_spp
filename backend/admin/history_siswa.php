<?php
session_start();
include '../../koneksi.php';
include '../components/header.php';
include '../components/sidebar.php';

// Ambil data siswa beserta tahun dan nominal dari tabel spp
$query_siswa = mysqli_query($koneksi, "SELECT siswa.*, kelas.nama_kelas, spp.tahun, spp.nominal FROM siswa JOIN kelas ON siswa.id_kelas = kelas.id_kelas JOIN spp ON siswa.id_spp = spp.id_spp");
?>

<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
    <div class="pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h3 fw-bold mb-3" style="color: #db2777;">History & Status Pembayaran Per Siswa</h1>
        <p class="text-muted">Pilih siswa di bawah untuk melihat rincian status pelunasan SPP selama 12 bulan.</p>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>NISN</th>
                            <th>Nama Siswa</th>
                            <th>Kelas</th>
                            <th>Tahun & Tagihan SPP/Bulan</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $no = 1;
                        while ($s = mysqli_fetch_assoc($query_siswa)) {
                        ?>
                        <tr>
                            <td><?php echo $no++; ?></td>
                            <td><strong><?php echo $s['nisn']; ?></strong></td>
                            <td><?php echo $s['nama']; ?></td>
                            <td><?php echo $s['nama_kelas']; ?></td>
                            <td><span class="badge bg-light text-dark border px-2 py-1">Tahun <?php echo $s['tahun']; ?></span> - Rp <?php echo number_format($s['nominal'], 0, ',', '.'); ?></td>
                            <td class="text-center">
                                <a href="detail_history.php?nisn=<?php echo $s['nisn']; ?>" class="btn btn-sm text-white" style="background-color: #db2777;">
                                    <i class="bi bi-eye me-1"></i> Cek Status 12 Bulan
                                </a>
                            </td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<?php include '../components/footer.php'; ?>