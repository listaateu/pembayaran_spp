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
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-4 border-bottom">
        <h1 class="h3 fw-bold" style="color: #db2777;">
            Kelola Data Kelas 
            <?php 
            if(isset($_GET['tingkat'])) {
                echo "- Tingkat " . htmlspecialchars($_GET['tingkat']);
            }
            ?>
        </h1>
    </div>

    <div class="mb-3">
        <a href="tambah_kelas.php" class="btn btn-primary" style="background-color: #db2777; border: none;"><i class="bi bi-plus-lg me-1"></i> Tambah Kelas</a>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle">
                    <thead style="background-color: #fdf2f8; color: #db2777;">
                        <tr>
                            <th>No</th>
                            <th>Tingkat</th>
                            <th>Jurusan</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $no = 1;
                        
                        // Filter dari sidebar
                        if (isset($_GET['tingkat']) && $_GET['tingkat'] != '') {
                            $tingkat_filter = $_GET['tingkat'];
                            if ($tingkat_filter == '10') {
                                $query = mysqli_query($koneksi, "SELECT * FROM kelas WHERE tingkat LIKE '%PPLG 1%' OR tingkat LIKE '%AKL 1%' OR tingkat LIKE '%10%' OR tingkat LIKE '%1%'");
                            } elseif ($tingkat_filter == '11') {
                                $query = mysqli_query($koneksi, "SELECT * FROM kelas WHERE tingkat LIKE '%PPLG 2%' OR tingkat LIKE '%AKL 2%' OR tingkat LIKE '%11%' OR tingkat LIKE '%2%'");
                            } elseif ($tingkat_filter == '12') {
                                $query = mysqli_query($koneksi, "SELECT * FROM kelas WHERE tingkat LIKE '%PPLG 3%' OR tingkat LIKE '%AKL 3%' OR tingkat LIKE '%12%' OR tingkat LIKE '%3%'");
                            } else {
                                $query = mysqli_query($koneksi, "SELECT * FROM kelas WHERE tingkat LIKE '%$tingkat_filter%'");
                            }
                        } else {
                            $query = mysqli_query($koneksi, "SELECT * FROM kelas");
                        }
                        
                        if (mysqli_num_rows($query) > 0) {
                            while ($row = mysqli_fetch_assoc($query)) {
                                // Logika pembersih tampilan agar Tingkat menampilkan angka (10/11/12) dan Jurusan menampilkan singkatan
                                $raw_tingkat = $row['tingkat'];
                                $raw_jurusan = $row['jurusan'];

                                // Menentukan angka tingkat asli berdasarkan data di database
                                if (strpos($raw_tingkat, '1') !== false && (strpos($raw_tingkat, 'PPLG 1') !== false || strpos($raw_tingkat, 'AKL 1') !== false)) {
                                    $tampilkan_tingkat = '10';
                                } elseif (strpos($raw_tingkat, '2') !== false && (strpos($raw_tingkat, 'PPLG 2') !== false || strpos($raw_tingkat, 'AKL 2') !== false)) {
                                    $tampilkan_tingkat = '11';
                                } elseif (strpos($raw_tingkat, '3') !== false && (strpos($raw_tingkat, 'PPLG 3') !== false || strpos($raw_tingkat, 'AKL 3') !== false)) {
                                    $tampilkan_tingkat = '12';
                                } else {
                                    $tampilkan_tingkat = preg_replace('/[^0-9]/', '', $raw_tingkat);
                                    if(empty($tampilkan_tingkat)) $tampilkan_tingkat = '10';
                                }

                                // Menentukan singkatan jurusan yang rapi
                                if (stripos($raw_jurusan, 'Perangkat Lunak') !== false || stripos($raw_jurusan, 'PPLG') !== false || stripos($raw_tingkat, 'PPLG') !== false) {
                                    $tampilkan_jurusan = 'PPLG';
                                } elseif (stripos($raw_jurusan, 'Akuntansi') !== false || stripos($raw_jurusan, 'AKL') !== false || stripos($raw_tingkat, 'AKL') !== false) {
                                    $tampilkan_jurusan = 'AKL';
                                } elseif (stripos($raw_jurusan, 'APHP') !== false) {
                                    $tampilkan_jurusan = 'APHP';
                                } elseif (stripos($raw_jurusan, 'TSM') !== false) {
                                    $tampilkan_jurusan = 'TSM';
                                } elseif (stripos($raw_jurusan, 'TKR') !== false) {
                                    $tampilkan_jurusan = 'TKR';
                                } else {
                                    $tampilkan_jurusan = $raw_jurusan;
                                }
                                ?>
                                <tr>
                                    <td><?= $no++; ?></td>
                                    <td><span class="badge bg-secondary"><?= $tampilkan_tingkat; ?></span></td>
                                    <td><?= $tampilkan_jurusan; ?></td>
                                    <td>
                                        <a href="edit_kelas.php?id_kelas=<?= $row['id_kelas']; ?>" class="btn btn-warning btn-sm text-white"><i class="bi bi-pencil-square"></i></a>
                                        <a href="hapus_kelas.php?id_kelas=<?= $row['id_kelas']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin menghapus kelas ini?')"><i class="bi bi-trash"></i></a>
                                    </td>
                                </tr>
                                <?php
                            }
                        } else {
                            echo "<tr><td colspan='4' class='text-center py-3 text-muted'>Tidak ada data kelas untuk tingkat ini.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<?php include '../components/footer.php'; ?>