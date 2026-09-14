<?php
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
    <div class="pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h3 fw-bold mb-3" style="color: #db2777;">Kelola Data Kelas</h1>
        <a href="tambah_kelas.php" class="btn btn-primary text-white" style="background-color: #db2777; border-color: #db2777;">
            <i class="bi bi-plus-lg me-1"></i> Tambah Kelas
        </a>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Kelas</th>
                            <th>Kompetensi Keahlian</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $no = 1;
                        $data_kelas = mysqli_query($koneksi, "SELECT * FROM kelas ORDER BY id_kelas DESC");
                        while ($row = mysqli_fetch_assoc($data_kelas)) {
                        ?>
                        <tr>
                            <td><?php echo $no++; ?></td>
                            <td class="fw-semibold"><?php echo $row['nama_kelas']; ?></td>
                            <td><?php echo $row['kompetensi_keahlian']; ?></td>
                            <td class="text-center">
                                <a href="edit_kelas.php?id=<?php echo $row['id_kelas']; ?>" class="btn btn-sm btn-warning text-white px-2 py-1"><i class="bi bi-pencil-square"></i></a>
                                <a href="hapus_kelas.php?id=<?php echo $row['id_kelas']; ?>" class="btn btn-sm btn-danger px-2 py-1" onclick="return confirm('Yakin ingin menghapus kelas ini?')"><i class="bi bi-trash"></i></a>
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