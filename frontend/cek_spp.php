<?php
/**
 * frontend/cek_spp.php  —  Verifikasi (NISN + NIS) & tampilan status SPP siswa
 * LETAKKAN DI: pembayaran_spp/frontend/cek_spp.php
 */
session_start();
include '../koneksi.php';

const SESI_MAKS_DETIK = 900;
const GAGAL_MAKS      = 5;
const KUNCI_DETIK     = 300;

$BULAN = [1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
          'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

if (!function_exists('rp')) {
    function rp($n) { return 'Rp ' . number_format((int) $n, 0, ',', '.'); }
}
if (!function_exists('tglIndo')) {
    function tglIndo($tgl) {
        global $BULAN;
        $t = strtotime($tgl);
        if (!$t) { return '-'; }
        return date('j', $t) . ' ' . substr($BULAN[(int) date('n', $t)], 0, 3) . ' ' . date('Y', $t);
    }
}
function formatTA($tahun) { $t = (int) $tahun; return $t . '/' . ($t + 1); }
function balikKeDepan($pesan)
{
    $_SESSION['cek_error'] = $pesan;
    header('Location: index.php#cek');
    exit();
}

// ------------------------------------------------------------
// 1) KELUAR
// ------------------------------------------------------------
if (isset($_GET['keluar'])) {
    unset($_SESSION['siswa_nisn'], $_SESSION['siswa_masuk_pada']);
    header('Location: index.php');
    exit();
}

// ------------------------------------------------------------
// 2) PROSES FORM (POST dari index.php)
// ------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sekarang = time();

    if (!isset($_POST['csrf'], $_SESSION['csrf_cek']) || !hash_equals($_SESSION['csrf_cek'], (string) $_POST['csrf'])) {
        balikKeDepan('Formulir kedaluwarsa. Silakan coba lagi.');
    }

    $kunci_sampai = (int) ($_SESSION['cek_kunci_sampai'] ?? 0);
    if ($kunci_sampai > $sekarang) {
        $sisa_menit = (int) ceil(($kunci_sampai - $sekarang) / 60);
        balikKeDepan("Terlalu banyak percobaan. Coba lagi sekitar $sisa_menit menit lagi.");
    }

    $nisn = trim($_POST['nisn'] ?? '');
    $nis  = trim($_POST['nis'] ?? '');
    if ($nisn === '' || $nis === '') {
        balikKeDepan('NISN dan NIS wajib diisi.');
    }

    $stmt = mysqli_prepare($koneksi, "SELECT nisn FROM siswa WHERE nisn = ? AND nis = ? LIMIT 1");
    mysqli_stmt_bind_param($stmt, "ss", $nisn, $nis);
    mysqli_stmt_execute($stmt);
    $hasil = mysqli_stmt_get_result($stmt);
    $cocok = mysqli_fetch_assoc($hasil);
    mysqli_stmt_close($stmt);

    if ($cocok) {
        session_regenerate_id(true);
        $_SESSION['siswa_nisn']       = $cocok['nisn'];
        $_SESSION['siswa_masuk_pada'] = $sekarang;
        unset($_SESSION['cek_gagal'], $_SESSION['cek_kunci_sampai']);
        header('Location: cek_spp.php');
        exit();
    }

    $_SESSION['cek_gagal'] = (int) ($_SESSION['cek_gagal'] ?? 0) + 1;
    if ($_SESSION['cek_gagal'] >= GAGAL_MAKS) {
        $_SESSION['cek_kunci_sampai'] = $sekarang + KUNCI_DETIK;
        $_SESSION['cek_gagal'] = 0;
        balikKeDepan('Terlalu banyak percobaan. Pengecekan dikunci 5 menit.');
    }
    balikKeDepan('NISN dan NIS tidak cocok. Periksa lagi ya.');
}

// ------------------------------------------------------------
// 3) TAMPILAN (GET) — wajib punya sesi siswa yang masih aktif
// ------------------------------------------------------------
if (empty($_SESSION['siswa_nisn'])) {
    header('Location: index.php');
    exit();
}
if (time() - (int) ($_SESSION['siswa_masuk_pada'] ?? 0) > SESI_MAKS_DETIK) {
    unset($_SESSION['siswa_nisn'], $_SESSION['siswa_masuk_pada']);
    balikKeDepan('Sesi berakhir demi keamanan. Silakan masukkan NISN dan NIS lagi.');
}
header('Cache-Control: no-store');

$nisn = $_SESSION['siswa_nisn'];

$stmt = mysqli_prepare($koneksi, "
    SELECT siswa.nisn, siswa.nis, siswa.nama, siswa.tahun_masuk, kelas.tingkat, kelas.jurusan
    FROM siswa JOIN kelas ON siswa.id_kelas = kelas.id_kelas
    WHERE siswa.nisn = ? LIMIT 1");
mysqli_stmt_bind_param($stmt, "s", $nisn);
mysqli_stmt_execute($stmt);
$siswa = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

if (!$siswa) {
    unset($_SESSION['siswa_nisn'], $_SESSION['siswa_masuk_pada']);
    balikKeDepan('Data siswa tidak ditemukan.');
}

$tarif = [];
$q = mysqli_query($koneksi, "SELECT tahun, nominal FROM spp ORDER BY tahun ASC");
while ($r = mysqli_fetch_assoc($q)) {
    $tarif[(int) $r['tahun']] = (int) $r['nominal'];
}

$bayar = [];
$riwayat = [];
$stmt = mysqli_prepare($koneksi, "
    SELECT id_pembayaran, tgl_bayar, bulan_dibayar, tahun_dibayar, jumlah_bayar
    FROM pembayaran WHERE nisn = ?
    ORDER BY tgl_bayar DESC, id_pembayaran DESC");
mysqli_stmt_bind_param($stmt, "s", $nisn);
mysqli_stmt_execute($stmt);
$q = mysqli_stmt_get_result($stmt);
$total_dibayar = 0;
while ($r = mysqli_fetch_assoc($q)) {
    $th = (int) $r['tahun_dibayar'];
    $bl = $r['bulan_dibayar'];
    if (!isset($bayar[$th][$bl])) { $bayar[$th][$bl] = ['jumlah' => 0, 'tgl' => $r['tgl_bayar']]; }
    $bayar[$th][$bl]['jumlah'] += (int) $r['jumlah_bayar'];
    $riwayat[] = $r;
    $total_dibayar += (int) $r['jumlah_bayar'];
}
mysqli_stmt_close($stmt);

$masuk = (int) $siswa['tahun_masuk'];
$daftar_tahun = [];
for ($t = $masuk; $t <= $masuk + 2; $t++) {
    if (isset($tarif[$t])) { $daftar_tahun[$t] = true; }
}
foreach (array_keys($bayar) as $t) {
    if (isset($tarif[$t])) { $daftar_tahun[$t] = true; }
}
$daftar_tahun = array_keys($daftar_tahun);
sort($daftar_tahun);

$TAHUN_INI = (int) date('Y');
$BULAN_INI = (int) date('n');
$tab_awal = null;
$jarak = PHP_INT_MAX;
foreach ($daftar_tahun as $t) {
    if (abs($t - $TAHUN_INI) < $jarak) { $jarak = abs($t - $TAHUN_INI); $tab_awal = $t; }
}

$data_tahun = [];
$total_lunas = 0;
$jml_tunggak = 0;
$rp_tunggak  = 0;

foreach ($daftar_tahun as $th) {
    $nominal = $tarif[$th];
    $rows = [];
    $lunas = 0;
    foreach ($BULAN as $ke => $nama) {
        $sudah = $bayar[$th][$nama]['jumlah'] ?? 0;
        $tgl   = $bayar[$th][$nama]['tgl'] ?? null;
        $lewat = ($th < $TAHUN_INI) || ($th == $TAHUN_INI && $ke < $BULAN_INI);
        $jalan = ($th == $TAHUN_INI && $ke == $BULAN_INI);

        if ($sudah > 0 && $sudah >= $nominal) {
            $st = 'lunas'; $lunas++;
        } elseif ($sudah > 0) {
            $st = 'sebagian';
        } elseif ($lewat) {
            $st = 'tunggak'; $jml_tunggak++; $rp_tunggak += $nominal;
        } elseif ($jalan) {
            $st = 'jalan';
        } else {
            $st = 'depan';
        }
        $rows[] = ['nama' => $nama, 'status' => $st, 'tgl' => $tgl, 'sudah' => $sudah];
    }
    $total_lunas += $lunas;
    $data_tahun[$th] = ['nominal' => $nominal, 'bulan' => $rows, 'lunas' => $lunas];
}

$label_status = [
    'lunas'    => ['Lunas', 'bln-lunas'],
    'tunggak'  => ['Menunggak', 'bln-tunggak'],
    'jalan'    => ['Bulan berjalan', 'bln-jalan'],
    'depan'    => ['Belum tiba', 'bln-depan'],
    'sebagian' => ['Terbayar sebagian', 'bln-sebagian'],
];

$inisial = strtoupper(mb_substr(trim($siswa['nama']), 0, 1));
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="robots" content="noindex">
<title>Status SPP — <?= htmlspecialchars($siswa['nama']); ?></title>
<link href="https://fonts.googleapis.com" rel="preconnect">
<link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700;900&family=Lato:wght@400;700&display=swap" rel="stylesheet">
<link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
<link href="assets/vendor/bootstrap-icons/bootstrap-icons.min.css" rel="stylesheet">
<link href="assets/css/main.css" rel="stylesheet">
<link href="assets/css/custom.css" rel="stylesheet">
<style>.riwayat.lipat .baris-lebih{display:none}</style>
</head>
<body>
<div class="container" style="max-width:900px;padding-top:30px;padding-bottom:40px">

  <!-- ===== TOPBAR ===== -->
  <header class="d-flex justify-content-between align-items-center pb-4 mb-4 border-bottom">
    <a href="index.php" class="text-decoration-none d-flex align-items-center gap-2">
      <i class="bi bi-file-earmark-text-fill fs-3" style="color:#DB2777"></i>
      <span class="h4 mb-0" style="color:#3b0a2b">SPP Digital</span>
    </a>
    <div class="d-flex gap-2">
      <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill no-print" onclick="window.print()">
        <i class="bi bi-printer"></i> Cetak rekap
      </button>
      <a href="cek_spp.php?keluar=1" class="btn btn-pink btn-sm rounded-pill">Keluar</a>
    </div>
  </header>

  <main>
    <!-- ===== PROFIL ===== -->
    <section class="d-flex align-items-center gap-3 mb-4">
      <div class="avatar-circle"><?= htmlspecialchars($inisial); ?></div>
      <div>
        <h1 class="h3 mb-2"><?= htmlspecialchars($siswa['nama']); ?></h1>
        <div class="d-flex flex-wrap gap-2">
          <span class="badge rounded-pill text-bg-light border">Kelas <?= htmlspecialchars($siswa['tingkat'] . ' ' . $siswa['jurusan']); ?></span>
          <span class="badge rounded-pill text-bg-light border">NISN <?= htmlspecialchars($siswa['nisn']); ?></span>
          <span class="badge rounded-pill text-bg-light border">NIS <?= htmlspecialchars($siswa['nis']); ?></span>
          <span class="badge rounded-pill text-bg-light border">Masuk TA <?= formatTA($siswa['tahun_masuk']); ?></span>
        </div>
      </div>
    </section>

    <!-- ===== BANNER STATUS ===== -->
    <?php if (!$daftar_tahun): ?>
      <div class="alert alert-secondary">Tarif SPP untuk tahun ajaranmu belum diisi oleh admin, jadi status belum bisa ditampilkan.</div>
    <?php elseif ($jml_tunggak === 0): ?>
      <div class="alert alert-success d-flex align-items-center gap-2">
        <i class="bi bi-check-circle-fill"></i> Mantap! Tidak ada tunggakan SPP.
      </div>
    <?php else: ?>
      <div class="alert alert-danger d-flex align-items-center gap-2">
        <i class="bi bi-exclamation-triangle-fill"></i>
        Ada <?= $jml_tunggak; ?> bulan yang belum dibayar (<?= rp($rp_tunggak); ?>). Silakan selesaikan lewat petugas sekolah.
      </div>
    <?php endif; ?>

    <!-- ===== RINGKASAN ===== -->
    <div class="row g-3 mb-4">
      <div class="col-md-4">
        <div class="card border-0 shadow-sm p-3 h-100">
          <small class="text-muted">Bulan lunas</small>
          <div class="h3 mb-0" style="color:#DB2777"><?= $total_lunas; ?></div>
          <small class="text-muted">di semua tahun ajaran</small>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card border-0 shadow-sm p-3 h-100">
          <small class="text-muted">Total sudah dibayar</small>
          <div class="h3 mb-0" style="color:#DB2777"><?= rp($total_dibayar); ?></div>
          <small class="text-muted"><?= count($riwayat); ?> transaksi</small>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card border-0 shadow-sm p-3 h-100">
          <small class="text-muted">Menunggak</small>
          <div class="h3 mb-0" style="color:#DB2777"><?= $jml_tunggak; ?> bulan</div>
          <small class="text-muted"><?= $jml_tunggak ? rp($rp_tunggak) : 'Tidak ada'; ?></small>
        </div>
      </div>
    </div>

    <!-- ===== STATUS PER TAHUN AJARAN ===== -->
    <?php if ($daftar_tahun): ?>
    <section class="card border-0 shadow-sm p-4 mb-4">
      <h2 class="h5 mb-3">Status per bulan</h2>

      <ul class="nav nav-pills mb-3 no-print" role="tablist">
        <?php foreach ($daftar_tahun as $th): $aktif = ($th === $tab_awal); ?>
          <li class="nav-item" role="presentation">
            <button class="nav-link <?= $aktif ? 'active' : ''; ?>" style="<?= $aktif ? 'background:#DB2777' : ''; ?>"
                    data-bs-toggle="pill" data-bs-target="#panel-<?= $th; ?>" type="button">
              TA <?= formatTA($th); ?>
            </button>
          </li>
        <?php endforeach; ?>
      </ul>

      <div class="tab-content">
        <?php foreach ($data_tahun as $th => $d): $persen = (int) round($d['lunas'] / 12 * 100); $aktif = ($th === $tab_awal); ?>
          <div class="tab-pane fade <?= $aktif ? 'show active' : ''; ?>" id="panel-<?= $th; ?>">
            <div class="d-flex justify-content-between align-items-center mb-2">
              <h3 class="h6 mb-0">Tahun Ajaran <?= formatTA($th); ?></h3>
              <small class="text-muted"><?= $d['lunas']; ?> dari 12 bulan lunas &middot; <?= rp($d['nominal']); ?> / bulan</small>
            </div>
            <div class="progress mb-3" style="height:8px">
              <div class="progress-bar" style="width:<?= $persen; ?>%;background:#DB2777"></div>
            </div>

            <div class="bulan-grid">
              <?php foreach ($d['bulan'] as $b): [$teks, $kls] = $label_status[$b['status']]; ?>
                <div class="bln <?= $kls; ?>">
                  <span class="n"><?= $b['nama']; ?></span>
                  <span class="s"><?= $teks; ?></span>
                  <span class="k small">
                    <?php if ($b['status'] === 'lunas'): ?>
                      Dibayar <?= tglIndo($b['tgl']); ?>
                    <?php elseif ($b['status'] === 'sebagian'): ?>
                      <?= rp($b['sudah']); ?> dari <?= rp($d['nominal']); ?>
                    <?php elseif ($b['status'] === 'tunggak'): ?>
                      <?= rp($d['nominal']); ?>
                    <?php else: ?>
                      &nbsp;
                    <?php endif; ?>
                  </span>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>

      <div class="legend mt-3 pt-3 border-top">
        <span><i style="background:#e9f9ee;border:1px solid #bfe9cd"></i>Lunas</span>
        <span><i style="background:#fdecec;border:1px solid #f6c2c2"></i>Menunggak</span>
        <span><i style="background:#fff;border:1.5px solid #DB2777"></i>Bulan berjalan</span>
        <span><i style="background:#f4f4f5;border:1px solid #e4e4e7"></i>Belum tiba</span>
      </div>
    </section>
    <?php endif; ?>

    <!-- ===== RIWAYAT ===== -->
    <section class="card border-0 shadow-sm p-4">
      <h2 class="h5 mb-3">Riwayat pembayaran</h2>
      <?php if ($riwayat): ?>
        <div class="table-responsive">
          <table class="table riwayat align-middle" id="tabel-riwayat">
            <thead>
              <tr><th>Tanggal</th><th>Untuk</th><th>No. Kuitansi</th><th class="text-end">Jumlah</th></tr>
            </thead>
            <tbody>
              <?php foreach ($riwayat as $i => $r): ?>
                <tr class="<?= ($i >= 5) ? 'baris-lebih' : ''; ?>">
                  <td><?= tglIndo($r['tgl_bayar']); ?></td>
                  <td>SPP <?= htmlspecialchars($r['bulan_dibayar']); ?> <?= (int) $r['tahun_dibayar']; ?></td>
                  <td class="font-monospace"><?= str_pad($r['id_pembayaran'], 5, '0', STR_PAD_LEFT); ?></td>
                  <td class="text-end fw-semibold"><?= rp($r['jumlah_bayar']); ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        <?php if (count($riwayat) > 5): ?>
          <button type="button" class="btn btn-outline-secondary no-print w-100 mt-2" id="btn-riwayat" data-total="<?= count($riwayat); ?>" style="display:none">
            Tampilkan semua (<?= count($riwayat); ?>)
          </button>
        <?php endif; ?>
      <?php else: ?>
        <p class="text-muted mb-0">Belum ada pembayaran yang tercatat.</p>
      <?php endif; ?>
    </section>

    <p class="small text-muted mt-4 no-print">
      <i class="bi bi-info-circle" style="color:#DB2777"></i>
      Data ini diambil langsung dari catatan petugas. Kalau ada yang tidak sesuai dengan kuitansi yang kamu pegang, tunjukkan kuitansinya ke petugas sekolah. Rekap dicetak pada <?= tglIndo(date('Y-m-d')); ?>.
    </p>
  </main>

  <footer class="text-center text-muted small pt-4 mt-4 border-top">
    <span>&copy; <?= date('Y'); ?> SPP Digital</span> &middot;
  </footer>
</div>

<script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script>
  var tabel = document.getElementById('tabel-riwayat');
  var tombol = document.getElementById('btn-riwayat');
  if (tabel && tombol) {
    tabel.classList.add('lipat');
    tombol.style.display = 'block';
    tombol.addEventListener('click', function () {
      var terlipat = tabel.classList.toggle('lipat');
      tombol.textContent = terlipat ? 'Tampilkan semua (' + tombol.dataset.total + ')' : 'Tutup';
    });
    var dilipatSebelumCetak = false;
    window.addEventListener('beforeprint', function () {
      dilipatSebelumCetak = tabel.classList.contains('lipat');
      tabel.classList.remove('lipat');
    });
    window.addEventListener('afterprint', function () {
      if (dilipatSebelumCetak) { tabel.classList.add('lipat'); }
    });
  }
</script>
</body>
</html>
