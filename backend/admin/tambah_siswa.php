<?php
session_start();
if (!isset($_SESSION['level']) || $_SESSION['level'] != 'admin') {
    echo "<script>alert('Silakan login terlebih dahulu!'); window.location='../../login.php';</script>";
    exit();
}
include '../../koneksi.php';

if (isset($_POST['simpan'])) {
    $nisn = $_POST['nisn'];
    $nis = $_POST['nis'];
    $nama = $_POST['nama'];
    $id_kelas = $_POST['id_kelas'];
    $alamat = $_POST['alamat'];
    $no_telp = $_POST['no_telp'];
    $id_spp = $_POST['id_spp'];

    $query = "INSERT INTO siswa (nisn, nis, nama, id_kelas, alamat, no_telp, id_spp) 
              VALUES ('$nisn', '$nis', '$nama', '$id_kelas', '$alamat', '$no_telp', '$id_spp')";
    
    if (mysqli_query($koneksi, $query)) {
        echo "<script>alert('Data siswa berhasil ditambahkan!'); window.location='siswa.php';</script>";
    } else {
        echo "<script>alert('Gagal menambah data: " . mysqli_error($koneksi) . "');</script>";
    }
}

include '../components/header.php';
include '../components/sidebar.php';
?>

<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-4 border-bottom">
        <h1 class="h3 fw-bold">Tambah Data Siswa</h1>
    </div>

    <div class="card border-0 shadow-sm col-md-12">
        <div class="card-body">
            <form method="POST" action="">
                <div class="mb-3">
                    <label class="form-label">NISN</label>
                    <input type="text" name="nisn" class="form-control" required maxlength="20">
                </div>
                <div class="mb-3">
                    <label class="form-label">NIS</label>
                    <input type="text" name="nis" class="form-control" required maxlength="8">
                </div>
                <div class="mb-3">
                    <label class="form-label">Nama Siswa</label>
                    <input type="text" name="nama" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Kelas</label>
                    <select name="id_kelas" class="form-select" required>
                        <option value="">-- Pilih Kelas --</option>
                        <?php
                        $kelas = mysqli_query($koneksi, "SELECT * FROM kelas");
                        while ($k = mysqli_fetch_assoc($kelas)) {
                            echo "<option value='{$k['id_kelas']}'>{$k['nama_kelas']}</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Alamat</label>
                    <textarea name="alamat" class="form-control" rows="3" required></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">No. Telepon</label>
                    <input type="text" name="no_telp" class="form-control" required maxlength="13">
                </div>
                <div class="mb-3">
                    <label class="form-label">Nominal / Tahun SPP</label>
                    <select name="id_spp" class="form-select" required>
                        <option value="">-- Pilih SPP --</option>
                        <?php
                        $spp = mysqli_query($koneksi, "SELECT * FROM spp");
                        while ($s = mysqli_fetch_assoc($spp)) {
                            echo "<option value='{$s['id_spp']}'>Tahun: {$s['tahun']} - Rp " . number_format($s['nominal'], 0, ',', '.') . "</option>";
                        }
                        ?>
                    </select>
                </div>
                <button type="submit" name="simpan" class="btn btn-primary"><i class="bi bi-save me-1"></i> Simpan Data</button>
                <a href="siswa.php" class="btn btn-secondary">Kembali</a>
            </form>
        </div>
    </div>
</main>

<?php include '../components/footer.php'; ?>