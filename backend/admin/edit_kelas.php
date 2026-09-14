<?php
session_start();
if (!isset($_SESSION['level']) || $_SESSION['level'] != 'admin') {
    echo "<script>alert('Silakan login terlebih dahulu!'); window.location='../../login.php';</script>";
    exit();
}
include '../../koneksi.php';

$id = $_GET['id'];
$query = mysqli_query($koneksi, "SELECT * FROM kelas WHERE id_kelas = '$id'");
$kelas = mysqli_fetch_assoc($query);

if (isset($_POST['update_kelas'])) {
    $nama_kelas = $_POST['nama_kelas'];
    $kompetensi_keahlian = $_POST['kompetensi_keahlian'];

    $update = mysqli_query($koneksi, "UPDATE kelas SET nama_kelas='$nama_kelas', kompetensi_keahlian='$kompetensi_keahlian' WHERE id_kelas='$id'");
    if ($update) {
        echo "<script>alert('Data kelas berhasil diubah!'); window.location='kelas.php';</script>";
    } else {
        echo "<script>alert('Gagal mengubah kelas: " . mysqli_error($koneksi) . "');</script>";
    }
}

include '../components/header.php';
include '../components/sidebar.php';
?>

<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-4 border-bottom">
        <h1 class="h3 fw-bold" style="color: #db2777;">Edit Data Kelas</h1>
    </div>

    <div class="card border-0 shadow-sm col-md-6 p-4">
        <form method="POST" action="">
            <div class="mb-3">
                <label class="form-label">Nama Kelas</label>
                <input type="text" name="nama_kelas" class="form-control" value="<?php echo $kelas['nama_kelas']; ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Kompetensi Keahlian</label>
                <input type="text" name="kompetensi_keahlian" class="form-control" value="<?php echo $kelas['kompetensi_keahlian']; ?>" required>
            </div>
            <button type="submit" name="update_kelas" class="btn btn-success"><i class="bi bi-check-circle me-1"></i> Update Kelas</button>
            <a href="kelas.php" class="btn btn-secondary">Kembali</a>
        </form>
    </div>
</main>

<?php include '../components/footer.php'; ?>