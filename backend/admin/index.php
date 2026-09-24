<?php
session_start();
if (!isset($_SESSION['level']) || $_SESSION['level'] != 'admin') {
    echo "<script>alert('Silakan login terlebih dahulu!'); window.location='../../login.php';</script>";
    exit();
}
include '../../koneksi.php';

// Menghitung jumlah data secara dinamis dari database
$query_siswa = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM siswa");
$data_siswa = mysqli_fetch_assoc($query_siswa);
$total_siswa = $data_siswa['total'];

$query_petugas = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM petugas");
$data_petugas = mysqli_fetch_assoc($query_petugas);
$total_petugas = $data_petugas['total'];

$query_kelas = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM kelas");
$data_kelas = mysqli_fetch_assoc($query_kelas);
$total_kelas = $data_kelas['total'];

// ================== TAMBAHAN: Siswa Belum Lunas (bulan berjalan) + search + pagination ==================
$DAFTAR_BULAN = [
    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
    5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
    9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
];
$tahun_sekarang    = (int) date('Y');
$bulan_angka       = (int) date('n');
// Tahun ajaran berjalan dianggap mulai bulan Juli (umum di sekolah Indonesia)
$tahun_ajaran_aktif = ($bulan_angka >= 7) ? $tahun_sekarang : $tahun_sekarang - 1;
$nama_bulan_ini     = $DAFTAR_BULAN[$bulan_angka];
$nama_bulan_esc     = mysqli_real_escape_string($koneksi, $nama_bulan_ini);

$cari_lunas = isset($_GET['cari_lunas']) ? trim($_GET['cari_lunas']) : '';
$cari_esc   = mysqli_real_escape_string($koneksi, $cari_lunas);
$kondisi_cari = '';
if ($cari_lunas !== '') {
    $kondisi_cari = "AND (siswa.nis LIKE '%$cari_esc%' OR siswa.nisn LIKE '%$cari_esc%' OR siswa.nama LIKE '%$cari_esc%' OR kelas.tingkat LIKE '%$cari_esc%' OR kelas.jurusan LIKE '%$cari_esc%')";
}

$query_dasar_belum_lunas = "FROM siswa
    JOIN kelas ON siswa.id_kelas = kelas.id_kelas
    LEFT JOIN pembayaran ON pembayaran.nisn = siswa.nisn
        AND pembayaran.tahun_dibayar = '$tahun_ajaran_aktif'
        AND pembayaran.bulan_dibayar = '$nama_bulan_esc'
    WHERE siswa.tahun_masuk <= $tahun_ajaran_aktif
        AND (siswa.tahun_masuk + 2) >= $tahun_ajaran_aktif
        AND pembayaran.id_pembayaran IS NULL
        $kondisi_cari";

$q_total_belum_lunas = mysqli_query($koneksi, "SELECT COUNT(DISTINCT siswa.nisn) AS total $query_dasar_belum_lunas");
$total_belum_lunas   = (int) (mysqli_fetch_assoc($q_total_belum_lunas)['total'] ?? 0);

$per_halaman_lunas = 10;
$total_halaman_lunas = max(1, (int) ceil($total_belum_lunas / $per_halaman_lunas));
$halaman_lunas = isset($_GET['hal_lunas']) ? (int) $_GET['hal_lunas'] : 1;
if ($halaman_lunas < 1) $halaman_lunas = 1;
if ($halaman_lunas > $total_halaman_lunas) $halaman_lunas = $total_halaman_lunas;
$offset_lunas = ($halaman_lunas - 1) * $per_halaman_lunas;

$q_list_belum_lunas = mysqli_query($koneksi, "SELECT DISTINCT siswa.nisn, siswa.nis, siswa.nama, kelas.tingkat, kelas.jurusan
    $query_dasar_belum_lunas
    ORDER BY siswa.nama ASC
    LIMIT $per_halaman_lunas OFFSET $offset_lunas");

$daftar_belum_lunas = [];
while ($r = mysqli_fetch_assoc($q_list_belum_lunas)) {
    $daftar_belum_lunas[] = $r;
}

// Bikin link "Bayar Sekarang" yang otomatis mengarah & memfilter ke siswa terkait di halaman Pembayaran
function link_bayar_admin($row, $tahun_ajaran_aktif) {
    $jurusan_kode = trim(preg_replace('/\s*\d+$/', '', $row['jurusan']));
    $rombel_no = '';
    if (preg_match('/(\d+)\s*$/', $row['jurusan'], $m)) $rombel_no = $m[1];
    return 'pembayaran.php?tingkat=' . urlencode($row['tingkat'])
        . '&jurusan=' . urlencode($jurusan_kode)
        . '&rombel=' . urlencode($rombel_no)
        . '&nisn=' . urlencode($row['nisn'])
        . '&tahun=' . $tahun_ajaran_aktif
        . '&f=1';
}

include '../components/header.php';
include '../components/sidebar.php';
?>

<style>
    /* ================== TAMBAHAN: widget jam & awan ================== */
    .widget-jam {
        position: relative;
        overflow: hidden;
        min-height: 220px;
        background: linear-gradient(160deg, #38bdf8 0%, #0ea5e9 55%, #0284c7 100%);
    }
    .widget-jam .awan-area {
        position: absolute;
        inset: 0;
        overflow: hidden;
        pointer-events: none;
    }
    .widget-jam .awan {
        position: absolute;
        color: rgba(255, 255, 255, 0.3);
    }
    .widget-jam .awan-1 { top: 14px;  left: -10px; font-size: 3.2rem; animation: mengambang 9s ease-in-out infinite; }
    .widget-jam .awan-2 { top: 60px;  right: -6px; font-size: 2.2rem; animation: mengambang 7s ease-in-out infinite reverse; }
    .widget-jam .awan-3 { bottom: 18px; left: 20%;  font-size: 1.6rem; animation: mengambang 11s ease-in-out infinite; }
    .widget-jam .awan-4 { top: 100px; left: 55%;    font-size: 1.2rem; animation: mengambang 8s ease-in-out infinite reverse; }

    @keyframes mengambang {
        0%, 100% { transform: translate(0, 0); }
        50%      { transform: translate(10px, -6px); }
    }

    .widget-jam .jam-besar {
        font-size: 2.6rem;
        font-weight: 800;
        letter-spacing: 1px;
        color: #fff;
        line-height: 1.1;
    }
    .widget-jam .tanggal-kecil {
        color: rgba(255, 255, 255, 0.85);
        font-size: 0.9rem;
    }
</style>

<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
    <div class="d-flex justify-content-between align-items-center pt-3 pb-2 mb-3 border-bottom">
        <div>
            <h1 class="h3 fw-bold" style="color: #db2777;">Dashboard</h1>
            <p class="text-muted mb-0">Selamat datang kembali, <strong><?php echo $_SESSION['nama_petugas'] ?? 'Administrator'; ?></strong> </p>
        </div>
        <div>
            <span class="badge px-3 py-2 text-white shadow-sm" style="background-color: #db2777;">
                <i class="bi bi-calendar-event me-1"></i> <?php echo date('d M Y'); ?>
            </span>
        </div>
    </div>

    <!-- Cards Statistik Dinamis (3 kolom) -->
    <div class="row g-3 mb-3">
        <!-- Total Siswa (Nuansa Pink Soft) -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm border-start border-4 h-100" style="border-left-color: #db2777 !important;">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small fw-bold text-uppercase mb-1" style="color: #db2777;">Total Siswa</div>
                        <div class="h2 fw-bold mb-0 text-dark"><?php echo $total_siswa; ?></div>
                    </div>
                    <div class="fs-1 p-3 rounded-3" style="color: #db2777; background-color: #ffe6f0;">
                        <i class="bi bi-people-fill"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Petugas / Admin (Nuansa Ungu Soft) -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm border-start border-4 h-100" style="border-left-color: #8b5cf6 !important;">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small fw-bold text-uppercase mb-1" style="color: #8b5cf6;">Petugas / Admin</div>
                        <div class="h2 fw-bold mb-0 text-dark"><?php echo $total_petugas; ?></div>
                    </div>
                    <div class="fs-1 p-3 rounded-3" style="color: #8b5cf6; background-color: #f3e8ff;">
                        <i class="bi bi-person-badge-fill"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Data Kelas (Nuansa Biru/Cyan Soft) -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm border-start border-4 h-100" style="border-left-color: #0ea5e9 !important;">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted small fw-bold text-uppercase mb-1" style="color: #0ea5e9;">Data Kelas</div>
                        <div class="h2 fw-bold mb-0 text-dark"><?php echo $total_kelas; ?></div>
                    </div>
                    <div class="fs-1 p-3 rounded-3" style="color: #0ea5e9; background-color: #e0f2fe;">
                        <i class="bi bi-building-fill"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Informasi Aplikasi + Widget Jam & Awan berdampingan -->
    <div class="row g-3">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h5 class="fw-bold mb-3" style="color: #db2777;"><i class="bi bi-info-circle me-2"></i>Informasi Aplikasi Pembayaran SPP</h5>
                    <p class="text-secondary">
                        Aplikasi ini dikembangkan untuk mempermudah pengelolaan data pembayaran SPP siswa secara terkomputerisasi. Sistem ini mendukung pembagian hak akses untuk <strong>Administrator</strong>, <strong>Petugas</strong>, dan <strong>Siswa</strong>.
                    </p>
                    <div class="alert border-0 text-dark mb-0" style="background-color: #ffe6f0;" role="alert">
                        <i class="bi bi-lightbulb me-2 fw-bold" style="color: #db2777;"></i> <strong>Tips:</strong> Pastikan untuk selalu melakukan pengecekan data kelas dan nominal SPP sebelum memasukkan data siswa baru.
                    </div>
                </div>
            </div>
        </div>

        <!-- ================== TAMBAHAN: Widget Jam & Awan ================== -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100 widget-jam">
                <div class="awan-area">
                    <i class="bi bi-cloud-fill awan awan-1"></i>
                    <i class="bi bi-cloud-fill awan awan-2"></i>
                    <i class="bi bi-cloud-fill awan awan-3"></i>
                    <i class="bi bi-cloud-fill awan awan-4"></i>
                </div>
                <div class="card-body d-flex flex-column justify-content-center" style="position: relative; z-index: 2;">
                    <div class="small fw-bold text-uppercase mb-2" style="color: rgba(255,255,255,0.85);">
                        <i class="bi bi-clock-history me-1"></i> Waktu Sekarang
                    </div>
                    <div class="jam-besar" id="jamSekarangAdmin">--:--:--</div>
                    <div class="tanggal-kecil mt-1" id="tanggalSekarangAdmin">-</div>
                </div>
            </div>
        </div>
    </div>

    <!-- ================== TAMBAHAN: Siswa Belum Lunas Bulan Ini ================== -->
    <div class="card border-0 shadow-sm mt-3">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                <h5 class="fw-bold mb-0" style="color: #db2777;">
                    <i class="bi bi-exclamation-circle me-2"></i>Siswa Belum Lunas &middot; <?= $nama_bulan_ini; ?> <?= formatTA($tahun_ajaran_aktif); ?>
                    <span class="badge rounded-pill ms-1" style="background-color:#fee2e2; color:#dc2626;"><?= $total_belum_lunas; ?></span>
                </h5>
                <form method="GET" class="d-flex gap-2">
                    <input type="text" name="cari_lunas" class="form-control form-control-sm" style="width:220px;" placeholder="Cari NIS / NISN / Nama / Kelas..." value="<?= htmlspecialchars($cari_lunas); ?>">
                    <button type="submit" class="btn btn-sm text-white" style="background-color:#db2777;"><i class="bi bi-search"></i></button>
                    <?php if ($cari_lunas !== ''): ?>
                        <a href="index.php" class="btn btn-sm btn-outline-secondary" title="Reset pencarian"><i class="bi bi-x-circle"></i></a>
                    <?php endif; ?>
                </form>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead style="background-color: #fdf2f8; color: #db2777;">
                        <tr>
                            <th>No</th>
                            <th>NIS</th>
                            <th>Nama Siswa</th>
                            <th>Kelas</th>
                            <th>Status</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($daftar_belum_lunas) > 0): ?>
                            <?php $no = $offset_lunas + 1; foreach ($daftar_belum_lunas as $row): ?>
                                <tr>
                                    <td><?= $no++; ?></td>
                                    <td><?= htmlspecialchars($row['nis']); ?></td>
                                    <td class="fw-semibold"><?= htmlspecialchars($row['nama']); ?></td>
                                    <td><?= htmlspecialchars($row['tingkat'] . ' ' . $row['jurusan']); ?></td>
                                    <td><span class="badge bg-danger">Belum Bayar</span></td>
                                    <td class="text-center">
                                        <a href="<?= link_bayar_admin($row, $tahun_ajaran_aktif); ?>" class="btn btn-sm text-white" style="background-color:#db2777;">
                                            <i class="bi bi-cash-coin me-1"></i>Bayar
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="6" class="text-center py-3 text-muted">🎉 Semua siswa sudah lunas bulan ini!</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <?php if ($total_halaman_lunas > 1): ?>
                <nav class="mt-3">
                    <ul class="pagination pagination-sm justify-content-center mb-0">
                        <li class="page-item <?= $halaman_lunas <= 1 ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?hal_lunas=<?= $halaman_lunas - 1; ?>&cari_lunas=<?= urlencode($cari_lunas); ?>">&laquo;</a>
                        </li>
                        <?php for ($i = 1; $i <= $total_halaman_lunas; $i++): ?>
                            <li class="page-item <?= $i == $halaman_lunas ? 'active' : ''; ?>">
                                <a class="page-link" href="?hal_lunas=<?= $i; ?>&cari_lunas=<?= urlencode($cari_lunas); ?>" <?= $i == $halaman_lunas ? 'style="background-color:#db2777;border-color:#db2777;"' : ''; ?>><?= $i; ?></a>
                            </li>
                        <?php endfor; ?>
                        <li class="page-item <?= $halaman_lunas >= $total_halaman_lunas ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?hal_lunas=<?= $halaman_lunas + 1; ?>&cari_lunas=<?= urlencode($cari_lunas); ?>">&raquo;</a>
                        </li>
                    </ul>
                </nav>
            <?php endif; ?>
        </div>
    </div>
</main>

<script>
    // ================== TAMBAHAN: jam berjalan real-time ==================
    const hariIndo = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', "Jum'at", 'Sabtu'];
    const bulanIndo = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

    function perbaruiJamAdmin() {
        const sekarang = new Date();
        const jam   = String(sekarang.getHours()).padStart(2, '0');
        const menit = String(sekarang.getMinutes()).padStart(2, '0');
        const detik = String(sekarang.getSeconds()).padStart(2, '0');

        const elJam = document.getElementById('jamSekarangAdmin');
        if (elJam) elJam.textContent = jam + ':' + menit + ':' + detik;

        const elTanggal = document.getElementById('tanggalSekarangAdmin');
        if (elTanggal) {
            elTanggal.textContent = hariIndo[sekarang.getDay()] + ', ' + sekarang.getDate() + ' ' + bulanIndo[sekarang.getMonth()] + ' ' + sekarang.getFullYear();
        }
    }
    perbaruiJamAdmin();
    setInterval(perbaruiJamAdmin, 1000);
</script>

<?php include '../components/footer.php'; ?>