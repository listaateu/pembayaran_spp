<?php
session_start();
if (!isset($_SESSION['level']) || $_SESSION['level'] != 'admin') {
    echo "<script>alert('Silakan login terlebih dahulu!'); window.location='../../login.php';</script>";
    exit();
}
include '../../koneksi.php';

// Ambil NISN dari URL
$nisn = $_GET['nisn'] ?? '';
$data = mysqli_query($koneksi, "SELECT siswa.*, kelas.tingkat, kelas.jurusan as nama_jurusan FROM siswa JOIN kelas ON siswa.id_kelas = kelas.id_kelas WHERE siswa.nisn = '$nisn'");
$siswa = mysqli_fetch_assoc($data);

if (!$siswa) {
    echo "<script>alert('Data siswa tidak ditemukan!'); window.location='siswa.php';</script>";
    exit();
}

if (isset($_POST['update'])) {
    $nis = $_POST['nis'];
    $nama = $_POST['nama'];
    $tingkat = $_POST['tingkat'];
    $jurusan = $_POST['jurusan'];
    $alamat = $_POST['alamat'];
    $no_telp = $_POST['no_telp'];
    $id_spp = $_POST['id_spp'];

    // Cari id_kelas berdasarkan tingkat dan jurusan yang dipilih
    $cari_kelas = mysqli_query($koneksi, "SELECT id_kelas FROM kelas WHERE tingkat = '$tingkat' AND (jurusan LIKE '%$jurusan%' OR jurusan = '$jurusan') LIMIT 1");

    if ($cari_kelas && mysqli_num_rows($cari_kelas) > 0) {
        $data_kelas = mysqli_fetch_assoc($cari_kelas);
        $id_kelas = $data_kelas['id_kelas'];
    } else {
        $fallback = mysqli_query($koneksi, "SELECT id_kelas FROM kelas LIMIT 1");
        $data_fb = mysqli_fetch_assoc($fallback);
        $id_kelas = $data_fb['id_kelas'] ?? 1;
    }

    $query = "UPDATE siswa SET nis = '$nis', nama = '$nama', id_kelas = '$id_kelas', alamat = '$alamat', no_telp = '$no_telp', id_spp = '$id_spp' WHERE nisn = '$nisn'";

    if (mysqli_query($koneksi, $query)) {
        echo "<script>alert('Data siswa berhasil diubah!'); window.location='siswa.php';</script>";
    } else {
        echo "<script>alert('Gagal mengubah data: " . mysqli_error($koneksi) . "'); window.history.back();</script>";
    }
}

include '../components/header.php';
include '../components/sidebar.php';
?>

<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-4 border-bottom">
        <h1 class="h3 fw-bold" style="color: #db2777;">Edit Data Siswa</h1>
    </div>

    <div class="card border-0 shadow-sm col-md-12">
        <div class="card-body">
            <form method="POST" action="">
                <div class="mb-3">
                    <label class="form-label">NISN (Tidak dapat diubah)</label>
                    <input type="text" class="form-control" value="<?= $siswa['nisn']; ?>" disabled>
                </div>
                <div class="mb-3">
                    <label class="form-label">NIS</label>
                    <input type="text" name="nis" class="form-control" value="<?= $siswa['nis']; ?>" required maxlength="8">
                </div>
                <div class="mb-3">
                    <label class="form-label">Nama Siswa</label>
                    <input type="text" name="nama" class="form-control" value="<?= $siswa['nama']; ?>" required>
                </div>

                <!-- Pilihan Tingkat dan Jurusan yang Langsung Terpilih Sesuai Data Asli -->
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Tingkat</label>
                        <select name="tingkat" class="form-select" required>
                            <option value="">-- Pilih Tingkat --</option>
                            <option value="10" <?= (trim($siswa['tingkat']) == '10') ? 'selected' : ''; ?>>10</option>
                            <option value="11" <?= (trim($siswa['tingkat']) == '11') ? 'selected' : ''; ?>>11</option>
                            <option value="12" <?= (trim($siswa['tingkat']) == '12') ? 'selected' : ''; ?>>12</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Jurusan</label>
                        <select name="jurusan" class="form-select" required>
                            <option value="">-- Pilih Jurusan --</option>
                            <option value="PPLG" <?= (stripos($siswa['nama_jurusan'], 'PPLG') !== false || stripos($siswa['nama_jurusan'], 'Perangkat Lunak') !== false) ? 'selected' : ''; ?>>PPLG</option>
                            <option value="AKL" <?= (stripos($siswa['nama_jurusan'], 'AKL') !== false || stripos($siswa['nama_jurusan'], 'Akuntansi') !== false) ? 'selected' : ''; ?>>AKL</option>
                            <option value="APHP" <?= (stripos($siswa['nama_jurusan'], 'APHP') !== false) ? 'selected' : ''; ?>>APHP</option>
                            <option value="TSM" <?= (stripos($siswa['nama_jurusan'], 'TSM') !== false) ? 'selected' : ''; ?>>TSM</option>
                            <option value="TKR" <?= (stripos($siswa['nama_jurusan'], 'TKR') !== false) ? 'selected' : ''; ?>>TKR</option>
                            <option value="APAT" <?= (stripos($siswa['nama_jurusan'], 'APAT') !== false) ? 'selected' : ''; ?>>APAT</option>
                        </select>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Alamat</label>
                    <textarea name="alamat" class="form-control" rows="3" required><?= str_replace('<', '', $siswa['alamat']); ?></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">No. Telepon</label>
                    <input type="text" name="no_telp" class="form-control" value="<?= $siswa['no_telp']; ?>" required maxlength="13">
                </div>
                <div class="mb-3">
                    <label class="form-label">Nominal / Tahun SPP</label>
                    <select name="id_spp" class="form-select" required>
                        <option value="">-- Pilih SPP --</option>
                        <?php
                        $spp = mysqli_query($koneksi, "SELECT * FROM spp");
                        while ($s = mysqli_fetch_assoc($spp)) {
                            $selected = ($s['id_spp'] == $siswa['id_spp']) ? 'selected' : '';
                            echo "<option value='{$s['id_spp']}' $selected>Tahun: {$s['tahun']} - Rp " . number_format($s['nominal'], 0, ',', '.') . "</option>";
                        }
                        ?>
                    </select>
                </div>
                <button type="submit" name="update" value="1" class="btn btn-primary" style="background-color: #d63384; border: none;"><i class="bi bi-save me-1"></i> Update Data</button>
                <a href="siswa.php" class="btn btn-secondary">Kembali</a>
            </form>
        </div>
    </div>
</main>

<?php include '../components/footer.php'; ?>