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
        <?php
        // Kalau lagi di halaman kelas tertentu (misal ?tingkat=11), bawa info itu
        // (termasuk jurusan kalau sedang difilter) ke halaman tambah siswa,
        // supaya dropdown Tingkat & Jurusan otomatis kepilih.
        $link_tambah = 'tambah_siswa.php';
        $param_tambah = [];
        if (isset($_GET['tingkat']) && $_GET['tingkat'] != '') {
            $param_tambah['tingkat'] = $_GET['tingkat'];
        }
        if (isset($_GET['jurusan']) && $_GET['jurusan'] != '') {
            $param_tambah['jurusan'] = $_GET['jurusan'];
        }
        if (isset($_GET['rombel']) && $_GET['rombel'] != '') {
            $param_tambah['rombel'] = $_GET['rombel'];
        }
        if (count($param_tambah) > 0) {
            $link_tambah .= '?' . http_build_query($param_tambah);
        }
        ?>
        <a href="<?= $link_tambah; ?>" class="btn btn-primary" style="background-color: #db2777; border: none;"><i class="bi bi-plus-lg me-1"></i> Tambah Siswa</a>
        
        <!-- Filter Kelas -->
        <form method="GET" class="d-flex gap-2">
            <select name="id_kelas" class="form-select form-select-sm" style="width: 200px;">
                <option value="">-- Pilih Berdasarkan Kelas --</option>
                <?php
                $q_kls = mysqli_query($koneksi, "SELECT * FROM kelas ORDER BY tingkat, jurusan");
                while ($kls = mysqli_fetch_assoc($q_kls)) {
                    $selected = (isset($_GET['id_kelas']) && $_GET['id_kelas'] == $kls['id_kelas']) ? 'selected' : '';
                    echo "<option value='{$kls['id_kelas']}' $selected>{$kls['tingkat']} - {$kls['jurusan']}</option>";
                }
                ?>
            </select>
            <button type="submit" class="btn btn-sm btn-primary" style="background-color: #db2777; border: none;">Filter</button>
        </form>
    </div>

    <?php
    // Daftar jurusan yang tersedia (sinkron dengan pilihan di form Tambah Siswa)
    $daftar_jurusan = ['PPLG', 'AKL', 'APHP', 'APAT', 'TKR', 'TSM'];
    $tingkat_aktif = isset($_GET['tingkat']) ? $_GET['tingkat'] : '';
    $jurusan_aktif = isset($_GET['jurusan']) ? $_GET['jurusan'] : '';
    $rombel_aktif = isset($_GET['rombel']) ? $_GET['rombel'] : '';
    ?>

    <?php if ($tingkat_aktif != ''): ?>
    <!-- Sub-filter jurusan: cuma muncul kalau lagi buka halaman kelas tertentu -->
    <div class="mb-3 d-flex flex-wrap gap-2">
        <a href="siswa.php?tingkat=<?= urlencode($tingkat_aktif); ?>"
           class="btn btn-sm <?= ($jurusan_aktif == '') ? 'btn-primary' : 'btn-outline-secondary'; ?>"
           style="<?= ($jurusan_aktif == '') ? 'background-color:#db2777;border:none;' : ''; ?>">
           Semua Jurusan
        </a>
        <?php foreach ($daftar_jurusan as $j): ?>
            <a href="siswa.php?tingkat=<?= urlencode($tingkat_aktif); ?>&jurusan=<?= urlencode($j); ?>"
               class="btn btn-sm <?= ($jurusan_aktif == $j) ? 'btn-primary' : 'btn-outline-secondary'; ?>"
               style="<?= ($jurusan_aktif == $j) ? 'background-color:#db2777;border:none;' : ''; ?>">
               <?= $j; ?>
            </a>
        <?php endforeach; ?>
    </div>

    <?php if ($jurusan_aktif != ''): ?>
    <!-- Sub-filter rombel: cuma muncul kalau jurusan sudah dipilih -->
    <div class="mb-3 d-flex flex-wrap gap-2">
        <a href="siswa.php?tingkat=<?= urlencode($tingkat_aktif); ?>&jurusan=<?= urlencode($jurusan_aktif); ?>"
           class="btn btn-sm <?= ($rombel_aktif == '') ? 'btn-primary' : 'btn-outline-secondary'; ?>"
           style="<?= ($rombel_aktif == '') ? 'background-color:#9d174d;border:none;' : ''; ?>">
           Semua Rombel
        </a>
        <?php foreach (['1', '2', '3'] as $r): ?>
            <a href="siswa.php?tingkat=<?= urlencode($tingkat_aktif); ?>&jurusan=<?= urlencode($jurusan_aktif); ?>&rombel=<?= urlencode($r); ?>"
               class="btn btn-sm <?= ($rombel_aktif == $r) ? 'btn-primary' : 'btn-outline-secondary'; ?>"
               style="<?= ($rombel_aktif == $r) ? 'background-color:#9d174d;border:none;' : ''; ?>">
               Rombel <?= $r; ?>
            </a>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
    <?php endif; ?>

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
                            <th>Rombel</th>
                            <th>Alamat</th>
                            <th>No. Telp</th>
                            <th>Nominal SPP</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $no = 1;
                        $kondisi = [];

                        // Filter: id_kelas paling akurat (langsung FK), dipakai kalau ada (dari dropdown "Pilih Berdasarkan Kelas").
                        if (isset($_GET['id_kelas']) && $_GET['id_kelas'] != '') {
                            $id_k = mysqli_real_escape_string($koneksi, $_GET['id_kelas']);
                            $kondisi[] = "siswa.id_kelas = '$id_k'";
                        } else {
                            // tingkat pakai EXACT MATCH ('=') karena data di DB sudah rapi (persis "10"/"11"/"12").
                            if ($tingkat_aktif != '') {
                                $tkt = mysqli_real_escape_string($koneksi, $tingkat_aktif);
                                $kondisi[] = "kelas.tingkat = '$tkt'";
                            }
                            // jurusan dicek di AWAL teks (misal "PPLG%" cocok ke "PPLG 1", "PPLG 2", dst).
                            // Kalau rombel juga dipilih, nyari EXACT ke "PPLG 1" (jurusan + rombel spesifik).
                            if ($jurusan_aktif != '') {
                                $jrs = mysqli_real_escape_string($koneksi, $jurusan_aktif);
                                if ($rombel_aktif != '') {
                                    $rmb = mysqli_real_escape_string($koneksi, $rombel_aktif);
                                    $kondisi[] = "kelas.jurusan = '$jrs $rmb'";
                                } else {
                                    $kondisi[] = "kelas.jurusan LIKE '$jrs%'";
                                }
                            }
                        }

                        $where = count($kondisi) > 0 ? "WHERE " . implode(" AND ", $kondisi) : "";

                        // Query utama dengan ORDER BY nisn DESC agar data terbaru muncul di paling atas
                        $query = mysqli_query($koneksi, "SELECT siswa.*, kelas.tingkat, kelas.jurusan, spp.nominal 
                                 FROM siswa 
                                 JOIN kelas ON siswa.id_kelas = kelas.id_kelas 
                                 JOIN spp ON siswa.id_spp = spp.id_spp 
                                 $where 
                                 ORDER BY (siswa.nisn + 0) DESC");
                        
                        if ($query && mysqli_num_rows($query) > 0) {
                            while ($row = mysqli_fetch_assoc($query)) {
                                // Kolom tingkat di DB sekarang sudah bersih (persis "10"/"11"/"12"),
                                // jadi tampilkan apa adanya, TIDAK perlu ditebak-tebak lagi.
                                $tampilkan_tingkat = $row['tingkat'];

                                // Kolom jurusan formatnya "PPLG 1", "AKL 2", "APHP 3", dst.
                                // Ambil kode jurusannya saja (buang angka rombel di belakang) untuk ditampilkan.
                                $tampilkan_jurusan = trim(preg_replace('/\s*\d+$/', '', $row['jurusan']));

                                // Ambil nomor rombel-nya saja (angka di belakang, misal "1" dari "PPLG 1")
                                $tampilkan_rombel = '';
                                if (preg_match('/(\d+)\s*$/', $row['jurusan'], $m)) {
                                    $tampilkan_rombel = $m[1];
                                }
                                ?>
                                <tr>
                                    <td><?= $no++; ?></td>
                                    <td><?= $row['nisn']; ?></td>
                                    <td><?= $row['nis']; ?></td>
                                    <td><?= $row['nama']; ?></td>
                                    <td><span class="badge bg-secondary"><?= htmlspecialchars($tampilkan_tingkat); ?></span></td>
                                    <td><?= htmlspecialchars($tampilkan_jurusan); ?></td>
                                    <td><?= htmlspecialchars($tampilkan_rombel); ?></td>
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
                            echo "<tr><td colspan='11' class='text-center py-3 text-muted'>Tidak ada data siswa ditemukan untuk kelas ini.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<?php include '../components/footer.php'; ?>