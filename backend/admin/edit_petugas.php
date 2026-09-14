<?php
session_start();
if (!isset($_SESSION['level']) || $_SESSION['level'] != 'admin') {
    echo "<script>alert('Silakan login terlebih dahulu!'); window.location='../../login.php';</script>";
    exit();
}
include '../../koneksi.php';

$id = $_GET['id'];
$query = mysqli_query($koneksi, "SELECT * FROM petugas WHERE id_petugas = '$id'");
$petugas = mysqli_fetch_assoc($query);

if (isset($_POST['update_petugas'])) {
    $username = $_POST['username'];
    $nama_petugas = $_POST['nama_petugas'];
    $level = $_POST['level'];

    // Jika password diisi, update password juga. Jika kosong, biarkan password lama.
    if (!empty($_POST['password'])) {
        $password = md5($_POST['password']);
        $update = mysqli_query($koneksi, "UPDATE petugas SET username='$username', password='$password', nama_petugas='$nama_petugas', level='$level' WHERE id_petugas='$id'");
    } else {
        $update = mysqli_query($koneksi, "UPDATE petugas SET username='$username', nama_petugas='$nama_petugas', level='$level' WHERE id_petugas='$id'");
    }

    if ($update) {
        echo "<script>alert('Data petugas berhasil diubah!'); window.location='petugas.php';</script>";
    } else {
        echo "<script>alert('Gagal mengubah petugas: " . mysqli_error($koneksi) . "');</script>";
    }
}

include '../components/header.php';
include '../components/sidebar.php';
?>

<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-4 border-bottom">
        <h1 class="h3 fw-bold" style="color: #db2777;">Edit Data Petugas</h1>
    </div>

    <div class="card border-0 shadow-sm col-md-6 p-4">
        <form method="POST" action="">
            <div class="mb-3">
                <label class="form-label">Username</label>
                <input type="text" name="username" class="form-control" value="<?php echo $petugas['username']; ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Password Baru <small class="text-muted">(Kosongkan jika tidak ingin mengubah password)</small></label>
                <input type="password" name="password" class="form-control">
            </div>
            <div class="mb-3">
                <label class="form-label">Nama Petugas</label>
                <input type="text" name="nama_petugas" class="form-control" value="<?php echo $petugas['nama_petugas']; ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Level</label>
                <select name="level" class="form-select" required>
                    <option value="admin" <?php echo ($petugas['level'] == 'admin') ? 'selected' : ''; ?>>Admin</option>
                    <option value="petugas" <?php echo ($petugas['level'] == 'petugas') ? 'selected' : ''; ?>>Petugas</option>
                </select>
            </div>
            <button type="submit" name="update_petugas" class="btn btn-success"><i class="bi bi-check-circle me-1"></i> Update Petugas</button>
            <a href="petugas.php" class="btn btn-secondary">Kembali</a>
        </form>
    </div>
</main>

<?php include '../components/footer.php'; ?>