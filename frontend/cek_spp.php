<?php
/**
 * frontend/cek_spp.php  —  Verifikasi (NISN + NIS) & tampilan status SPP siswa
 * LETAKKAN DI: pembayaran_spp/frontend/cek_spp.php
 *
 * Alur:
 *  - POST dari index.php  -> cocokkan NISN + NIS -> simpan sesi -> redirect ke halaman ini (GET)
 *  - GET                  -> tampilkan status kalau sesi siswa masih aktif
 *  - ?keluar=1            -> hapus sesi siswa (sesi admin/petugas TIDAK ikut terhapus)
 *
 * Aturan status bulan meniru halaman pembayaran di backend:
 *   lunas      = sudah ada pembayaran bulan itu
 *   menunggak  = bulan sudah lewat & belum dibayar
 *   berjalan   = bulan ini
 *   belum tiba = bulan-bulan berikutnya ("bayar di muka" di sisi petugas)
 */
session_start();
include '../koneksi.php';

const SESI_MAKS_DETIK = 900;  // sesi siswa berakhir setelah 15 menit
const GAGAL_MAKS      = 5;    // salah input sebanyak ini -> dikunci sementara
const KUNCI_DETIK     = 300;  // lama dikunci: 5 menit

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

    // Gagal: pesan sengaja sama untuk "NISN tidak ada" dan "NIS salah"
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
header('Cache-Control: no-store'); // tombol Back setelah keluar tidak menampilkan data lama

$nisn = $_SESSION['siswa_nisn'];

// --- Data siswa + kelas ---
$stmt = mysqli_prepare($koneksi, "
    SELECT siswa.nisn, siswa.nis, siswa.nama, siswa.tahun_masuk, kelas.tingkat, kelas.jurusan
    FROM siswa JOIN kelas ON siswa.id_kelas = kelas.id_kelas
    WHERE siswa.nisn = ? LIMIT 1");
mysqli_stmt_bind_param($stmt, "s", $nisn);
mysqli_stmt_execute($stmt);
$siswa = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

if (!$siswa) { // siswa sudah dihapus admin saat sesi berjalan
    unset($_SESSION['siswa_nisn'], $_SESSION['siswa_masuk_pada']);
    balikKeDepan('Data siswa tidak ditemukan.');
}

// --- Tarif SPP per tahun: [tahun => nominal] ---
$tarif = [];
$q = mysqli_query($koneksi, "SELECT tahun, nominal FROM spp ORDER BY tahun ASC");
while ($r = mysqli_fetch_assoc($q)) {
    $tarif[(int) $r['tahun']] = (int) $r['nominal'];
}

// --- Semua pembayaran siswa ---
$bayar = [];      // [tahun][nama_bulan] = ['jumlah'=>, 'tgl'=>]
$riwayat = [];    // daftar transaksi, terbaru di atas
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

// --- Tahun ajaran yang ditampilkan: rentang valid siswa (masuk .. masuk+2) + tahun yang sudah ada pembayarannya ---
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

// Tab yang terbuka duluan: tahun berjalan kalau ada, kalau tidak yang paling dekat
$TAHUN_INI = (int) date('Y');
$BULAN_INI = (int) date('n');
$tab_awal = null;
$jarak = PHP_INT_MAX;
foreach ($daftar_tahun as $t) {
    if (abs($t - $TAHUN_INI) < $jarak) { $jarak = abs($t - $TAHUN_INI); $tab_awal = $t; }
}

// --- Hitung status setiap bulan + ringkasan ---
$data_tahun = [];   // [tahun => ['nominal', 'bulan' => [...], 'lunas' => n]]
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
            $st = 'sebagian';           // sisa data lama (cicilan) — tetap ditampilkan apa adanya
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
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,400;9..144,500;9..144,600&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/frontend.css">
<style>
  /* Riwayat dilipat: tampil 5 terbaru dulu, sisanya lewat tombol */
  .riwayat.lipat .baris-lebih{display:none}
</style>
</head>
<body>
<div class="wrap" style="max-width:860px">

  <header class="topbar">
    <a href="index.php" class="brand">
      <span class="brand-mark">
        <svg width="20" height="20" viewBox="0 0 26 26" fill="none" aria-hidden="true">
          <rect x="2" y="2" width="22" height="22" rx="6" stroke="#FBEAF4" stroke-width="1.8"/>
          <path d="M7 9.5H19M7 13H16M7 16.5H13" stroke="#FBEAF4" stroke-width="1.8" stroke-linecap="round"/>
        </svg>
      </span>
      SPP Digital
    </a>
    <div class="top-actions">
      <button type="button" class="btn btn-ghost" onclick="window.print()">Cetak rekap</button>
      <a href="cek_spp.php?keluar=1" class="btn btn-pink">Keluar</a>
    </div>
  </header>

  <main>
    <!-- ===== PROFIL ===== -->
    <section class="profil">
      <div class="avatar" aria-hidden="true"><?= htmlspecialchars($inisial); ?></div>
      <div style="position:relative;z-index:1">
        <h1><?= htmlspecialchars($siswa['nama']); ?></h1>
        <div class="chips">
          <span class="chip">Kelas <?= htmlspecialchars($siswa['tingkat'] . ' ' . $siswa['jurusan']); ?></span>
          <span class="chip">NISN <?= htmlspecialchars($siswa['nisn']); ?></span>
          <span class="chip">NIS <?= htmlspecialchars($siswa['nis']); ?></span>
          <span class="chip">Masuk TA <?= formatTA($siswa['tahun_masuk']); ?></span>
        </div>
      </div>
    </section>

    <!-- ===== BANNER STATUS ===== -->
    <?php if (!$daftar_tahun): ?>
      <div class="banner banner-bad" style="background:var(--mute-bg);color:var(--mute)">
        Tarif SPP untuk tahun ajaranmu belum diisi oleh admin, jadi status belum bisa ditampilkan.
      </div>
    <?php elseif ($jml_tunggak === 0): ?>
      <div class="banner banner-ok">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="1.8"/><path d="M8 12.5l2.7 2.7L16 9.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
        Mantap! Tidak ada tunggakan SPP.
      </div>
    <?php else: ?>
      <div class="banner banner-bad">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none"><path d="M12 8v5m0 3.5v.01M10.3 3.9L2.5 17.5A2 2 0 0 0 4.2 20.5h15.6a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
        Ada <?= $jml_tunggak; ?> bulan yang belum dibayar (<?= rp($rp_tunggak); ?>). Silakan selesaikan lewat petugas sekolah.
      </div>
    <?php endif; ?>

    <!-- ===== RINGKASAN ===== -->
    <div class="ringkas">
      <div><small>Bulan lunas</small><b><?= $total_lunas; ?></b><span>di semua tahun ajaran</span></div>
      <div><small>Total sudah dibayar</small><b><?= rp($total_dibayar); ?></b><span><?= count($riwayat); ?> transaksi</span></div>
      <div><small>Menunggak</small><b><?= $jml_tunggak; ?> bulan</b><span><?= $jml_tunggak ? rp($rp_tunggak) : 'Tidak ada'; ?></span></div>
    </div>

    <!-- ===== STATUS PER TAHUN AJARAN ===== -->
    <?php if ($daftar_tahun): ?>
    <section class="panel">
      <h2>Status per bulan</h2>

      <div class="tabs no-print" role="tablist" aria-label="Tahun ajaran">
        <?php foreach ($daftar_tahun as $th): $aktif = ($th === $tab_awal); ?>
          <button type="button" class="tab" role="tab" id="tab-<?= $th; ?>" data-target="panel-<?= $th; ?>"
                  aria-selected="<?= $aktif ? 'true' : 'false'; ?>" aria-controls="panel-<?= $th; ?>">
            TA <?= formatTA($th); ?>
          </button>
        <?php endforeach; ?>
      </div>

      <?php foreach ($data_tahun as $th => $d): $persen = (int) round($d['lunas'] / 12 * 100); ?>
        <div class="tabpanel" id="panel-<?= $th; ?>" role="tabpanel" aria-labelledby="tab-<?= $th; ?>">
          <div class="ta-head">
            <h3>Tahun Ajaran <?= formatTA($th); ?></h3>
            <span><?= $d['lunas']; ?> dari 12 bulan lunas &middot; <?= rp($d['nominal']); ?> / bulan</span>
          </div>
          <div class="bar" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="<?= $persen; ?>"><i style="width:<?= $persen; ?>%"></i></div>

          <div class="bulan">
            <?php foreach ($d['bulan'] as $b): [$teks, $kls] = $label_status[$b['status']]; ?>
              <div class="bln <?= $kls; ?>">
                <span class="n"><?= $b['nama']; ?></span>
                <span class="s"><?= $teks; ?></span>
                <span class="k">
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

      <div class="legend">
        <span><i style="background:var(--ok-bg);border:1px solid var(--ok)"></i>Lunas</span>
        <span><i style="background:var(--bad-bg);border:1px solid var(--bad)"></i>Menunggak</span>
        <span><i style="background:#fff;border:1.5px solid var(--pink)"></i>Bulan berjalan</span>
        <span><i style="background:var(--mute-bg);border:1px solid var(--mute)"></i>Belum tiba</span>
      </div>
    </section>
    <?php endif; ?>

    <!-- ===== RIWAYAT ===== -->
    <section class="panel">
      <h2>Riwayat pembayaran</h2>
      <?php if ($riwayat): ?>
        <table class="riwayat" id="tabel-riwayat">
          <thead>
            <tr><th>Tanggal</th><th>Untuk</th><th>No. Kuitansi</th><th>Jumlah</th></tr>
          </thead>
          <tbody>
            <?php foreach ($riwayat as $i => $r): ?>
              <tr class="<?= ($i >= 5) ? 'baris-lebih' : ''; ?>">
                <td><?= tglIndo($r['tgl_bayar']); ?></td>
                <td>SPP <?= htmlspecialchars($r['bulan_dibayar']); ?> <?= (int) $r['tahun_dibayar']; ?></td>
                <td class="kw"><?= str_pad($r['id_pembayaran'], 5, '0', STR_PAD_LEFT); ?></td>
                <td class="num"><?= rp($r['jumlah_bayar']); ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
        <?php if (count($riwayat) > 5): ?>
          <button type="button" class="btn btn-ghost no-print" id="btn-riwayat"
                  data-total="<?= count($riwayat); ?>" style="display:none;width:100%;margin-top:14px">
            Tampilkan semua (<?= count($riwayat); ?>)
          </button>
        <?php endif; ?>
      <?php else: ?>
        <div class="kosong">Belum ada pembayaran yang tercatat.</div>
      <?php endif; ?>
    </section>

    <p class="catatan no-print">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" style="flex:none;margin-top:2px"><circle cx="12" cy="12" r="9" stroke="#DB2777" stroke-width="1.8"/><path d="M12 11v5m0-8.5v.01" stroke="#DB2777" stroke-width="1.8" stroke-linecap="round"/></svg>
      Data ini diambil langsung dari catatan petugas. Kalau ada yang tidak sesuai dengan kuitansi yang kamu pegang, tunjukkan kuitansinya ke petugas sekolah. Rekap dicetak pada <?= tglIndo(date('Y-m-d')); ?>.
    </p>
  </main>

  <footer class="foot">
    <span>&copy; <?= date('Y'); ?> SPP Digital</span>
    <a href="cek_spp.php?keluar=1">Keluar</a>
  </footer>
</div>

<script>
  // Tanpa JS: semua tahun ajaran tampil bertumpuk. Dengan JS: jadi tab.
  document.documentElement.classList.add('js');
  (function () {
    var tabs = document.querySelectorAll('.tab');
    var panels = document.querySelectorAll('.tabpanel');
    function tampil(idPanel) {
      tabs.forEach(function (t) { t.setAttribute('aria-selected', t.dataset.target === idPanel ? 'true' : 'false'); });
      panels.forEach(function (p) { p.hidden = (p.id !== idPanel); });
    }
    tabs.forEach(function (tab) {
      tab.addEventListener('click', function () { tampil(tab.dataset.target); });
    });
    var awal = document.querySelector('.tab[aria-selected="true"]');
    if (awal) { tampil(awal.dataset.target); }

    // Riwayat: lipat ke 5 terbaru, tombol untuk buka semua
    var tabel = document.getElementById('tabel-riwayat');
    var tombol = document.getElementById('btn-riwayat');
    if (tabel && tombol) {
      tabel.classList.add('lipat');
      tombol.style.display = 'flex';
      tombol.addEventListener('click', function () {
        var terlipat = tabel.classList.toggle('lipat');
        tombol.textContent = terlipat ? 'Tampilkan semua (' + tombol.dataset.total + ')' : 'Ringkas, tampilkan 5 terbaru saja';
      });
      // Saat cetak, semua baris ikut dicetak
      var dilipatSebelumCetak = false;
      window.addEventListener('beforeprint', function () {
        dilipatSebelumCetak = tabel.classList.contains('lipat');
        tabel.classList.remove('lipat');
      });
      window.addEventListener('afterprint', function () {
        if (dilipatSebelumCetak) { tabel.classList.add('lipat'); }
      });
    }
  })();
</script>
</body>
</html>
