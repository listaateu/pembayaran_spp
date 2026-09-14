<?php
session_start();
include '../../koneksi.php';

// Pastikan ada id_petugas, jika session belum ada, ambil petugas pertama dari database sebagai default
if (!isset($_SESSION['id_petugas'])) {
    $q_petugas = mysqli_query($koneksi, "SELECT id_petugas FROM petugas LIMIT 1");
    $d_petugas = mysqli_fetch_assoc($q_petugas);
    $_SESSION['id_petugas'] = $d_petugas['id_petugas'] ?? 1;
}

if (isset($_POST['simpan'])) {
    $id_petugas = $_SESSION['id_petugas'];
    $nisn = $_POST['nisn'];
    $tgl_bayar = $_POST['tgl_bayar'];
    $bulan_dibayar = $_POST['bulan_dibayar'];
    $tahun_dibayar = $_POST['tahun_dibayar'];
    $id_spp = $_POST['id_spp'];
    $jumlah_bayar = $_POST['jumlah_bayar'];

    // Ambil nominal SPP untuk validasi cicilan agar tidak melebihi total tagihan
    $cek_spp = mysqli_query($koneksi, "SELECT nominal FROM spp WHERE id_spp = '$id_spp'");
    $data_spp = mysqli_fetch_assoc($cek_spp);
    $nominal_spp = $data_spp['nominal'];

    if ($jumlah_bayar > $nominal_spp) {
        echo "<script>alert('Gagal! Jumlah bayar tidak boleh melebihi nominal total SPP (Rp " . number_format($nominal_spp, 0, ',', '.') . ").'); window.location='tambah_pembayaran.php';</script>";
    } else {
        $query = mysqli_query($koneksi, "INSERT INTO pembayaran (id_petugas, nisn, tgl_bayar, bulan_dibayar, tahun_dibayar, id_spp, jumlah_bayar) VALUES ('$id_petugas', '$nisn', '$tgl_bayar', '$bulan_dibayar', '$tahun_dibayar', '$id_spp', '$jumlah_bayar')");

        if ($query) {
            echo "<script>alert('Transaksi pembayaran/cicilan berhasil disimpan!'); window.location='pembayaran.php';</script>";
        } else {
            echo "<script>alert('Gagal menyimpan transaksi.');</script>";
        }
    }
}

include '../components/header.php';
include '../components/sidebar.php';
?>

<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
    <div class="pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h3 fw-bold mb-3" style="color: #db2777;">Entri Pembayaran & Cicilan SPP</h1>
    </div>

    <div class="card border-0 shadow-sm col-md-12">
        <div class="card-body">
            <form action="" method="POST">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Siswa</label>
                    <select name="nisn" class="form-select" required>
                        <option value="">-- Pilih Siswa --</option>
                        <?php
                        $siswa = mysqli_query($koneksi, "SELECT siswa.*, kelas.nama_kelas FROM siswa JOIN kelas ON siswa.id_kelas = kelas.id_kelas");
                        while ($s = mysqli_fetch_assoc($siswa)) {
                            echo "<option value='{$s['nisn']}'>{$s['nisn']} - {$s['nama']} ({$s['nama_kelas']})</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Tanggal Bayar</label>
                    <input type="date" name="tgl_bayar" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Bulan Yang Dibayar</label>
                    <select name="bulan_dibayar" class="form-select" required>
                        <option value="">-- Pilih Bulan --</option>
                        <option value="Januari">Januari</option>
                        <option value="Februari">Februari</option>
                        <option value="Maret">Maret</option>
                        <option value="April">April</option>
                        <option value="Mei">Mei</option>
                        <option value="Juni">Juni</option>
                        <option value="Juli">Juli</option>
                        <option value="Agustus">Agustus</option>
                        <option value="September">September</option>
                        <option value="Oktober">Oktober</option>
                        <option value="November">November</option>
                        <option value="Desember">Desember</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Tahun Yang Dibayar</label>
                    <input type="text" name="tahun_dibayar" class="form-control" value="<?php echo date('Y'); ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Pilih Tahun SPP & Nominal Total</label>
                    <select name="id_spp" class="form-select" required>
                        <option value="">-- Pilih SPP / Nominal --</option>
                        <?php
                        $spp = mysqli_query($koneksi, "SELECT * FROM spp");
                        while ($sp = mysqli_fetch_assoc($spp)) {
                            echo "<option value='{$sp['id_spp']}'>Tahun {$sp['tahun']} - Rp " . number_format($sp['nominal'], 0, ',', '.') . "</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Jumlah Bayar / Cicilan (Rp)</label>
                    <input type="number" name="jumlah_bayar" class="form-control" placeholder="Masukkan nominal uang yang dibayarkan siswa (bisa dicicil)" required>
                    <div class="form-text text-muted">Petugas dapat memasukkan nominal cicilan sesuai uang yang dibayarkan siswa (tidak boleh melebihi total nominal SPP).</div>
                </div>

                <div class="d-flex justify-content-between">
                    <a href="pembayaran.php" class="btn btn-secondary">Kembali</a>
                    <button type="submit" name="simpan" class="btn text-white" style="background-color: #db2777;">Simpan Pembayaran</button>
                </div>
            </form>
        </div>
    </div>
</main>

<?php include '../components/footer.php'; ?>