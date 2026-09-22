<?php
session_start();
if (!isset($_SESSION['level']) || $_SESSION['level'] != 'admin') {
    echo "<script>alert('Silakan login terlebih dahulu!'); window.location='../../login.php';</script>";
    exit();
}
include '../../koneksi.php';

$id = (int) ($_GET['id'] ?? 0);
$stmt = mysqli_prepare($koneksi, "SELECT * FROM petugas WHERE id_petugas = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$petugas = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (!$petugas) {
    echo "<script>alert('Data petugas tidak ditemukan!'); window.location='petugas.php';</script>";
    exit();
}

if (isset($_POST['update_petugas'])) {
    $username = trim($_POST['username']);
    $nama_petugas = trim($_POST['nama_petugas']);
    $level = $_POST['level'];

    if (!in_array($level, ['admin', 'petugas'], true)) {
        echo "<script>alert('Level tidak valid!'); window.location='petugas.php';</script>";
        exit();
    }

    // Jika password diisi, update password juga (di-hash). Jika kosong, biarkan password lama.
    if (!empty($_POST['password'])) {
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $upd = mysqli_prepare($koneksi, "UPDATE petugas SET username=?, password=?, nama_petugas=?, level=?, password_updated_at=NOW() WHERE id_petugas=?");
        mysqli_stmt_bind_param($upd, "ssssi", $username, $password, $nama_petugas, $level, $id);
    } else {
        $upd = mysqli_prepare($koneksi, "UPDATE petugas SET username=?, nama_petugas=?, level=? WHERE id_petugas=?");
        mysqli_stmt_bind_param($upd, "sssi", $username, $nama_petugas, $level, $id);
    }
    $update = mysqli_stmt_execute($upd);

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
                <input type="text" name="username" class="form-control" value="<?php echo htmlspecialchars($petugas['username']); ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Password Baru <small class="text-muted">(Kosongkan jika tidak ingin mengubah password)</small></label>
                <input type="password" name="password" class="form-control" autocomplete="new-password" value="">
            </div>
            <div class="mb-3">
                <label class="form-label">Nama Petugas</label>
                <input type="text" name="nama_petugas" class="form-control" value="<?php echo htmlspecialchars($petugas['nama_petugas']); ?>" required>
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