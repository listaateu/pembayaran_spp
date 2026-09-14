<?php
session_start();
if (!isset($_SESSION['level']) || $_SESSION['level'] != 'admin') {
    echo "<script>alert('Silakan login terlebih dahulu!'); window.location='../../login.php';</script>";
    exit();
}
include '../../koneksi.php';
include '../components/header.php';
include '../components/sidebar.php';

// Logika Pencarian
$keyword = "";
if (isset($_GET['cari'])) {
    $keyword = mysqli_real_escape_string($koneksi, $_GET['keyword']);
    $query_siswa = mysqli_query($koneksi, "SELECT siswa.*, kelas.nama_kelas, spp.nominal FROM siswa JOIN kelas ON siswa.id_kelas = kelas.id_kelas JOIN spp ON siswa.id_spp = spp.id_spp WHERE siswa.nama LIKE '%$keyword%' OR siswa.nisn LIKE '%$keyword%' OR siswa.nis LIKE '%$keyword%' ORDER BY siswa.nisn DESC");
} else {
    $query_siswa = mysqli_query($koneksi, "SELECT siswa.*, kelas.nama_kelas, spp.nominal FROM siswa JOIN kelas ON siswa.id_kelas = kelas.id_kelas JOIN spp ON siswa.id_spp = spp.id_spp ORDER BY siswa.nisn DESC");
}
?>

<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
    <div class="pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h3 fw-bold mb-3" style="color: #db2777;">Kelola Data Siswa</h1>
        
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <!-- Tombol Tambah di Kiri -->
            <a href="tambah_siswa.php" class="btn btn-primary text-white" style="background-color: #db2777; border-color: #db2777;">
                <i class="bi bi-plus-lg me-1"></i> Tambah Siswa
            </a>

            <!-- Form Pencarian di Kanan -->
            <form action="" method="GET" class="d-flex" style="width: 300px;">
                <input type="text" name="keyword" class="form-control form-control-sm me-2" placeholder="Cari nama, NISN, NIS..." value="<?php echo htmlspecialchars($keyword); ?>">
                <button type="submit" name="cari" class="btn btn-sm btn-outline-secondary" style="border-color: #db2777; color: #db2777;"><i class="bi bi-search"></i></button>
                <?php if (!empty($keyword)) { ?>
                    <a href="siswa.php" class="btn btn-sm btn-secondary ms-1">Reset</a>
                <?php } ?>
            </form>
        </div>
    </div>

    <!-- Tabel Daftar Siswa -->
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>NISN</th>
                            <th>NIS</th>
                            <th>Nama Siswa</th>
                            <th>Kelas</th>
                            <th>No. Telp</th>
                            <th>Nominal SPP</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $no = 1;
                        if (mysqli_num_rows($query_siswa) > 0) {
                            while ($row = mysqli_fetch_assoc($query_siswa)) {
                        ?>
                        <tr>
                            <td><?php echo $no++; ?></td>
                            <td><?php echo $row['nisn']; ?></td>
                            <td><?php echo $row['nis']; ?></td>
                            <td class="fw-semibold"><?php echo $row['nama']; ?></td>
                            <td><?php echo $row['nama_kelas']; ?></td>
                            <td><?php echo $row['no_telp']; ?></td>
                            <td>Rp <?php echo number_format($row['nominal'], 0, ',', '.'); ?></td>
                            <td class="text-center">
                                <a href="edit_siswa.php?nisn=<?php echo $row['nisn']; ?>" class="btn btn-sm btn-warning text-white px-2 py-1"><i class="bi bi-pencil-square"></i></a>
                                <a href="hapus_siswa.php?nisn=<?php echo $row['nisn']; ?>" class="btn btn-sm btn-danger px-2 py-1" onclick="return confirm('Yakin ingin menghapus siswa ini?')"><i class="bi bi-trash"></i></a>
                            </td>
                        </tr>
                        <?php 
                            }
                        } else {
                            echo "<tr><td colspan='8' class='text-center text-muted py-3'>Data siswa tidak ditemukan.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<?php include '../components/footer.php'; ?>