<?php
session_start();
if (!isset($_SESSION['level']) || $_SESSION['level'] != 'admin') {
    echo "<script>alert('Silakan login terlebih dahulu!'); window.location='../../login.php';</script>";
    exit();
}

// Panggil koneksi database
include '../../koneksi.php'; 

include '../components/header.php';
include '../components/sidebar.php';
?>
<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-4 border-bottom">
        <h1 class="h3 fw-bold" style="color: #db2777;">Kelola Data Siswa</h1>
    </div>

    <div class="mb-3 d-flex justify-content-between align-items-center">
        <a href="tambah_siswa.php" class="btn btn-primary" style="background-color: #db2777; border: none;"><i class="bi bi-plus-lg me-1"></i> Tambah Siswa</a>
        
        <!-- Filter Kelas -->
        <form method="GET" class="d-flex gap-2">
            <select name="id_kelas" class="form-select form-select-sm" style="width: 200px;">
                <option value="">-- Pilih Berdasarkan Kelas --</option>
                <?php
                $q_kls = mysqli_query($koneksi, "SELECT * FROM kelas");
                while ($kls = mysqli_fetch_assoc($q_kls)) {
                    $selected = (isset($_GET['id_kelas']) && $_GET['id_kelas'] == $kls['id_kelas']) ? 'selected' : '';
                    echo "<option value='{$kls['id_kelas']}' $selected>{$kls['tingkat']} - {$kls['jurusan']}</option>";
                }
                ?>
            </select>
            <button type="submit" class="btn btn-sm btn-primary" style="background-color: #db2777; border: none;">Filter</button>
        </form>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle">
                    <thead style="background-color: #fdf2f8; color: #db2777;">
                        <tr>
                            <th>No</th>
                            <th>NISN</th>
                            <th>NIS</th>
                            <th>Nama Siswa</th>
                            <th>Tingkat</th>
                            <th>Jurusan</th>
                            <th>Alamat</th>
                            <th>No. Telp</th>
                            <th>Nominal SPP</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $no = 1;
                        $where = "";
                        
                        // PERBAIKAN: Menangkap id_kelas maupun tingkat secara akurat
                        if (isset($_GET['id_kelas']) && $_GET['id_kelas'] != '') {
                            $id_k = mysqli_real_escape_string($koneksi, $_GET['id_kelas']);
                            $where = "WHERE siswa.id_kelas = '$id_k'";
                        } elseif (isset($_GET['tingkat']) && $_GET['tingkat'] != '') {
                            $tkt = mysqli_real_escape_string($koneksi, $_GET['tingkat']);
                            $where = "WHERE kelas.tingkat LIKE '%$tkt%'";
                        }

                        // Query utama dengan ORDER BY nisn DESC agar data terbaru muncul di paling atas
                        $query = mysqli_query($koneksi, "SELECT siswa.*, kelas.tingkat, kelas.jurusan, spp.nominal 
                                                 FROM siswa 
                                                 JOIN kelas ON siswa.id_kelas = kelas.id_kelas 
                                                 JOIN spp ON siswa.id_spp = spp.id_spp 
                                                 $where 
                                                 ORDER BY siswa.nisn DESC");
                        
                        if (mysqli_num_rows($query) > 0) {
                            while ($row = mysqli_fetch_assoc($query)) {
                                $raw_tingkat = $row['tingkat'];
                                $raw_jurusan = $row['jurusan'];

                                // Penyesuaian tampilan tingkat angka bersih
                                if (strpos($raw_tingkat, '1') !== false) { $tampilkan_tingkat = '10'; }
                                elseif (strpos($raw_tingkat, '2') !== false) { $tampilkan_tingkat = '11'; }
                                elseif (strpos($raw_tingkat, '3') !== false) { $tampilkan_tingkat = '12'; }
                                else { $tampilkan_tingkat = preg_replace('/[^0-9]/', '', $raw_tingkat); if(empty($tampilkan_tingkat)) $tampilkan_tingkat = '10'; }

                                // Penyesuaian singkatan jurusan
                                if (stripos($raw_jurusan, 'Perangkat Lunak') !== false || stripos($raw_jurusan, 'PPLG') !== false || stripos($raw_tingkat, 'PPLG') !== false) {
                                    $tampilkan_jurusan = 'PPLG';
                                } elseif (stripos($raw_jurusan, 'Akuntansi') !== false || stripos($raw_jurusan, 'AKL') !== false || stripos($raw_tingkat, 'AKL') !== false) {
                                    $tampilkan_jurusan = 'AKL';
                                } else {
                                    $tampilkan_jurusan = $raw_jurusan;
                                }
                                ?>
                                <tr>
                                    <td><?= $no++; ?></td>
                                    <td><?= $row['nisn']; ?></td>
                                    <td><?= $row['nis']; ?></td>
                                    <td><?= $row['nama']; ?></td>
                                    <td><span class="badge bg-secondary"><?= $tampilkan_tingkat; ?></span></td>
                                    <td><?= $tampilkan_jurusan; ?></td>
                                    <td><?= $row['alamat']; ?></td>
                                    <td><?= $row['no_telp']; ?></td>
                                    <td>Rp <?= number_format($row['nominal'], 0, ',', '.'); ?></td>
                                    <td>
                                        <a href="edit_siswa.php?nisn=<?= $row['nisn']; ?>" class="btn btn-warning btn-sm text-white"><i class="bi bi-pencil-square"></i></a>
                                        <a href="hapus_siswa.php?nisn=<?= $row['nisn']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin menghapus data ini?')"><i class="bi bi-trash"></i></a>
                                    </td>
                                </tr>
                                <?php
                            }
                        } else {
                            echo "<tr><td colspan='10' class='text-center py-3 text-muted'>Tidak ada data siswa ditemukan untuk kelas ini.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<?php include '../components/footer.php'; ?>