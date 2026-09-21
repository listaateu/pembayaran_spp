<?php
/**
 * frontend/index.php  —  Halaman depan SPP DIGITAL (untuk siswa / wali)
 * LETAKKAN DI: pembayaran_spp/frontend/index.php
 *
 * Isinya: penjelasan singkat + form "Cek Status SPP" (NISN + NIS).
 * Verifikasi & tampilan hasilnya ada di cek_spp.php.
 */
session_start();
include '../koneksi.php';

// Token anti-CSRF untuk form cek status
if (empty($_SESSION['csrf_cek'])) {
    $_SESSION['csrf_cek'] = bin2hex(random_bytes(16));
}

// Pesan error (flash) dari cek_spp.php
$error = $_SESSION['cek_error'] ?? '';
unset($_SESSION['cek_error']);

// Angka ringkas (agregat saja, tidak membuka data pribadi siapa pun)
$total_siswa = 0;
$total_transaksi = 0;
$q1 = mysqli_query($koneksi, "SELECT COUNT(*) AS n FROM siswa");
if ($q1) { $total_siswa = (int) mysqli_fetch_assoc($q1)['n']; }
$q2 = mysqli_query($koneksi, "SELECT COUNT(*) AS n FROM pembayaran");
if ($q2) { $total_transaksi = (int) mysqli_fetch_assoc($q2)['n']; }

// Kalau admin/petugas sedang login, tombol atas diganti jadi "Dashboard"
$level_staf = $_SESSION['level'] ?? '';
$staf_login = in_array($level_staf, ['admin', 'petugas'], true);
$url_dashboard = ($level_staf === 'admin') ? '../backend/admin/index.php' : '../backend/petugas/index.php';

// Kalau siswa masih punya sesi cek status yang aktif
$siswa_aktif = !empty($_SESSION['siswa_nisn']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>SPP Digital — Cek Status Pembayaran SPP</title>
<meta name="description" content="Cek status pembayaran SPP per bulan, tunggakan, dan riwayat kuitansi dengan NISN dan NIS.">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,400;9..144,500;9..144,600&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/frontend.css">
</head>
<body>
<div class="wrap">

  <!-- ========== TOPBAR ========== -->
  <header class="topbar">
    <a href="index.php" class="brand" aria-label="SPP Digital, beranda">
      <span class="brand-mark">
        <svg width="20" height="20" viewBox="0 0 26 26" fill="none" aria-hidden="true">
          <rect x="2" y="2" width="22" height="22" rx="6" stroke="#FBEAF4" stroke-width="1.8"/>
          <path d="M7 9.5H19M7 13H16M7 16.5H13" stroke="#FBEAF4" stroke-width="1.8" stroke-linecap="round"/>
        </svg>
      </span>
      SPP Digital
    </a>
    <div class="top-actions">
      <?php if ($staf_login): ?>
        <a href="<?= $url_dashboard; ?>" class="btn btn-pink">Buka Dashboard</a>
      <?php else: ?>
        <a href="../login.php" class="btn btn-ghost">Masuk Petugas</a>
      <?php endif; ?>
    </div>
  </header>

  <!-- ========== HERO ========== -->
  <main>
  <section class="hero">
    <div>
      <span class="eyebrow"><i></i> Untuk siswa &amp; orang tua</span>
      <h1>Status SPP-mu, <em>jelas</em> kapan saja.</h1>
      <p class="lead">Lihat bulan mana yang sudah lunas, cek tunggakan, dan buka riwayat kuitansi tanpa perlu antre bertanya ke loket.</p>

      <div class="cek-card" id="cek">
        <h2>Cek status SPP</h2>
        <p class="sub">Masukkan NISN dan NIS yang tertera di kartu pelajar.</p>

        <?php if ($error): ?>
          <div class="alert alert-err" role="alert"><?= htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if ($siswa_aktif): ?>
          <div class="alert alert-info">
            Kamu masih masuk di perangkat ini.
            <a href="cek_spp.php">Lanjut lihat status</a> &middot;
            <a href="cek_spp.php?keluar=1">Keluar</a>
          </div>
        <?php endif; ?>

        <form method="POST" action="cek_spp.php" autocomplete="off">
          <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf_cek']); ?>">
          <div class="field">
            <label for="nisn">NISN</label>
            <input type="text" id="nisn" name="nisn" inputmode="numeric" maxlength="20" placeholder="Contoh: 0051234567" required>
          </div>
          <div class="field">
            <label for="nis">NIS</label>
            <input type="text" id="nis" name="nis" inputmode="numeric" maxlength="20" placeholder="Nomor induk sekolah" required>
            <div class="hint">Dua-duanya harus cocok, demi menjaga privasi data pembayaran.</div>
          </div>
          <button type="submit" class="btn btn-pink btn-lg">Lihat status SPP</button>
        </form>

        <p class="privacy">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" aria-hidden="true" style="flex:none;margin-top:1px"><path d="M12 3l8 3v6c0 4.4-3.2 8.2-8 9-4.8-.8-8-4.6-8-9V6l8-3Z" stroke="#DB2777" stroke-width="1.8" stroke-linejoin="round"/><path d="M8.5 12l2.5 2.5 4.5-5" stroke="#DB2777" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
          Sesi otomatis berakhir setelah 15 menit. Jangan lupa tekan "Keluar" kalau memakai perangkat bersama.
        </p>
      </div>
    </div>

    <!-- kartu dekoratif -->
    <div class="kartu-wrap" aria-hidden="true">
      <div class="kartu">
        <div class="kartu-top"><span>SPP DIGITAL</span><span class="kartu-tag">KARTU SPP</span></div>
        <h3>Tahun Ajaran <?= formatTA(date('Y')); ?></h3>
        <small>Contoh tampilan status per bulan</small>
        <div class="kartu-grid">
          <?php
          $singk = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
          $bln_ini = (int) date('n');
          foreach ($singk as $i => $s) {
              $ke = $i + 1;
              $kelas = ($ke < $bln_ini) ? 'on' : (($ke === $bln_ini) ? 'now' : '');
              echo '<span class="' . $kelas . '">' . $s . '</span>';
          }
          ?>
        </div>
        <div class="kartu-foot"><span>Lunas &middot; bulan berjalan &middot; belum</span><b>SPP</b></div>
      </div>
    </div>
  </section>

  <!-- ========== FITUR ========== -->
  <section class="blok" id="fitur">
    <div class="sec-head">
      <h2>Semua yang perlu kamu tahu, di satu halaman</h2>
      <p>Data langsung dari catatan petugas sekolah, jadi selalu sama dengan yang ada di loket.</p>
    </div>
    <div class="fitur">
      <article>
        <div class="ikon"><svg width="22" height="22" viewBox="0 0 24 24" fill="none"><rect x="3" y="5" width="18" height="16" rx="3" stroke="currentColor" stroke-width="1.8"/><path d="M3 10h18M8 3v4M16 3v4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg></div>
        <h3>Status per bulan</h3>
        <p>Dua belas bulan ditampilkan sekaligus: lunas, bulan berjalan, atau menunggak, lengkap per tahun ajaran.</p>
      </article>
      <article>
        <div class="ikon"><svg width="22" height="22" viewBox="0 0 24 24" fill="none"><path d="M12 8v5m0 3.5v.01M10.3 3.9L2.5 17.5A2 2 0 0 0 4.2 20.5h15.6a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg></div>
        <h3>Tunggakan yang transparan</h3>
        <p>Jumlah bulan dan total rupiah yang belum dibayar dihitung otomatis, tidak ada yang tertinggal.</p>
      </article>
      <article>
        <div class="ikon"><svg width="22" height="22" viewBox="0 0 24 24" fill="none"><path d="M7 3h10a2 2 0 0 1 2 2v16l-3-2-2 2-2-2-2 2-2-2-3 2V5a2 2 0 0 1 2-2Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/><path d="M9 8h6M9 12h6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg></div>
        <h3>Riwayat &amp; nomor kuitansi</h3>
        <p>Setiap pembayaran punya nomor kuitansi dan tanggal. Halaman status juga bisa dicetak sebagai rekap.</p>
      </article>
    </div>

    <div class="stat-strip">
      <div><b><?= number_format($total_siswa, 0, ',', '.'); ?></b>siswa terdaftar</div>
      <div><b><?= number_format($total_transaksi, 0, ',', '.'); ?></b>pembayaran tercatat</div>
    </div>
  </section>

  <!-- ========== ALUR ========== -->
  <section class="blok" id="alur">
    <div class="sec-head">
      <h2>Bagaimana cara kerjanya?</h2>
    </div>
    <ol class="langkah">
      <li><h3>Bayar ke petugas</h3><p>Pembayaran SPP dilakukan langsung lewat petugas sekolah, per bulan.</p></li>
      <li><h3>Tercatat otomatis</h3><p>Petugas menginput pembayaran, status bulan itu langsung berubah jadi lunas.</p></li>
      <li><h3>Cek &amp; simpan bukti</h3><p>Buka halaman ini kapan pun untuk memastikan, atau cetak rekapnya.</p></li>
    </ol>
  </section>

  <!-- ========== FAQ ========== -->
  <section class="blok" id="faq">
    <div class="sec-head"><h2>Pertanyaan yang sering muncul</h2></div>
    <details class="faq">
      <summary>Di mana saya bisa menemukan NISN dan NIS?</summary>
      <p>Keduanya biasanya tercetak di kartu pelajar atau rapor. Kalau belum tahu, tanyakan ke wali kelas atau tata usaha.</p>
    </details>
    <details class="faq">
      <summary>Saya sudah bayar tapi statusnya belum lunas?</summary>
      <p>Status diperbarui setelah petugas mencatat pembayaran. Kalau sudah lewat satu hari kerja dan belum berubah, bawa bukti kuitansi ke petugas sekolah.</p>
    </details>
    <details class="faq">
      <summary>Apa arti "Menunggak" dan "Bulan berjalan"?</summary>
      <p>"Menunggak" berarti bulan tersebut sudah lewat dan belum tercatat dibayar. "Bulan berjalan" adalah bulan saat ini. Bulan setelahnya ditandai abu-abu sampai waktunya tiba.</p>
    </details>
    <details class="faq">
      <summary>Apakah data saya bisa dilihat orang lain?</summary>
      <p>Tidak bisa tanpa NISN dan NIS yang cocok. Setelah beberapa kali salah input, pengecekan dikunci sementara.</p>
    </details>
  </section>
  </main>

  <footer class="foot">
    <span>&copy; <?= date('Y'); ?> SPP Digital &middot; Aplikasi Pembayaran SPP Sekolah</span>
    <a href="../login.php">Login admin / petugas</a>
  </footer>

</div>
</body>
</html>
