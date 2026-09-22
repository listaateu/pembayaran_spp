<?php
session_start();
if (!isset($_SESSION['level']) || $_SESSION['level'] != 'petugas') {
    echo "<script>alert('Silakan login terlebih dahulu!'); window.location='../../login.php';</script>";
    exit();
}
include '../../koneksi.php';

$error = '';
$sukses = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ganti_password'])) {
    $password_lama = $_POST['password_lama'] ?? '';
    $password_baru = $_POST['password_baru'] ?? '';
    $konfirmasi    = $_POST['konfirmasi_password'] ?? '';
    $username      = $_SESSION['username'];

    // Ambil password saat ini dari database
    $stmt = mysqli_prepare($koneksi, "SELECT password FROM petugas WHERE username = ?");
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    $hasil = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($hasil);
    mysqli_stmt_close($stmt);

    if (!$row || !password_verify($password_lama, $row['password'])) {
        $error = 'Password lama salah.';
    } elseif (strlen($password_baru) < 6) {
        $error = 'Password baru minimal 6 karakter.';
    } elseif ($password_baru !== $konfirmasi) {
        $error = 'Konfirmasi password baru tidak cocok.';
    } else {
        $hash_baru = password_hash($password_baru, PASSWORD_DEFAULT);
        $stmt = mysqli_prepare($koneksi, "UPDATE petugas SET password = ?, password_updated_at = NOW() WHERE username = ?");
        mysqli_stmt_bind_param($stmt, "ss", $hash_baru, $username);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        $sukses = 'Password berhasil diubah. Gunakan password baru saat login berikutnya.';
    }
}

$pageTitle   = 'Pengaturan - Aplikasi Pembayaran SPP';
$currentPage = 'setting.php';
include __DIR__ . '/components/header.php';
include __DIR__ . '/components/sidebar.php';
?>
<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">

    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-4 border-bottom">
        <div>
            <h1 class="h3 fw-bold" style="color: #db2777;">Pengaturan Akun</h1>
            <p class="text-muted mb-0">Ganti kata sandi akun kamu di sini.</p>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="fw-bold mb-3"><i class="bi bi-key me-2"></i>Ganti Password</h5>

                    <?php if ($error): ?>
                        <div class="alert alert-danger py-2"><?= htmlspecialchars($error); ?></div>
                    <?php endif; ?>
                    <?php if ($sukses): ?>
                        <div class="alert alert-success py-2"><?= htmlspecialchars($sukses); ?></div>
                    <?php endif; ?>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Username</label>
                        <input type="text" class="form-control" value="<?= htmlspecialchars($_SESSION['username']); ?>" disabled readonly>
                        <div class="form-text">Username tidak bisa diubah.</div>
                    </div>

                    <form method="POST" action="setting.php">
                        <div class="mb-3">
                            <label for="password_lama" class="form-label fw-semibold">Password Lama</label>
                            <input type="password" class="form-control" id="password_lama" name="password_lama" required>
                        </div>
                        <div class="mb-3">
                            <label for="password_baru" class="form-label fw-semibold">Password Baru</label>
                            <input type="password" class="form-control" id="password_baru" name="password_baru" minlength="6" required>
                        </div>
                        <div class="mb-3">
                            <label for="konfirmasi_password" class="form-label fw-semibold">Konfirmasi Password Baru</label>
                            <input type="password" class="form-control" id="konfirmasi_password" name="konfirmasi_password" minlength="6" required>
                        </div>
                        <button type="submit" name="ganti_password" class="btn text-white" style="background-color: #db2777;">Simpan Perubahan</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <?php include __DIR__ . '/components/footer.php'; ?>