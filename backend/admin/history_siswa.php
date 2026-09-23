<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
if (!isset($_SESSION['level']) || $_SESSION['level'] != 'admin') {
    echo "<script>alert('Silakan login terlebih dahulu!'); window.location='../../login.php';</script>";
    exit();
}

include '../../koneksi.php';
include '../components/header.php';
include '../components/sidebar.php';

/* ============================================================
   HISTORY STATUS PEMBAYARAN SISWA (versi ringkas per siswa)

   1 baris per siswa, dengan ringkasan:
   - Berapa bulan yang sudah tercatat dibayar (progress bar dari 36 bulan)
   - Tahun ajaran apa saja yang sudah ada transaksinya (badge ditumpuk)
   - Tombol "Lihat Detail" -> ke detail_history.php

   Ditambahkan juga kotak pencarian nama/NISN (filter di sisi
   browser, tanpa reload halaman) + kartu ringkasan di atas tabel.

   Catatan: fungsi formatTA() sekarang ada di koneksi.php, jadi
   tidak perlu didefinisikan lagi di sini.
   ============================================================ */

const TOTAL_BULAN_WAJIB = 36; // 12 bulan x 3 tahun ajaran

// Palet warna pastel buat avatar inisial, dipilih gantian per siswa
$PALET_AVATAR = [
    ['bg' => '#fce7f3', 'fg' => '#be185d'], // pink
    ['bg' => '#ede9fe', 'fg' => '#7c3aed'], // ungu
    ['bg' => '#fef3c7', 'fg' => '#b45309'], // peach/kuning
    ['bg' => '#dcfce7', 'fg' => '#15803d'], // hijau
    ['bg' => '#dbeafe', 'fg' => '#1d4ed8'], // biru
    ['bg' => '#ffe4e6', 'fg' => '#be123c'], // rose
];

function inisialNama($nama)
{
    $kata = preg_split('/\s+/', trim($nama));
    $inisial = strtoupper(substr($kata[0], 0, 1));
    if (count($kata) > 1) {
        $inisial .= strtoupper(substr(end($kata), 0, 1));
    }
    return $inisial;
}

function warnaAvatar($nama, $palet)
{
    $indeks = array_sum(array_map('ord', str_split($nama))) % count($palet);
    return $palet[$indeks];
}

$query = mysqli_query($koneksi, "
    SELECT
        siswa.nisn,
        siswa.nama,
        kelas.tingkat,
        kelas.jurusan,
        COUNT(pembayaran.id_pembayaran) AS jumlah_transaksi,
        GROUP_CONCAT(DISTINCT pembayaran.tahun_dibayar ORDER BY pembayaran.tahun_dibayar SEPARATOR ',') AS tahun_list,
        MAX(pembayaran.tgl_bayar) AS terakhir_bayar
    FROM pembayaran
    JOIN siswa ON pembayaran.nisn = siswa.nisn
    JOIN kelas ON siswa.id_kelas = kelas.id_kelas
    GROUP BY siswa.nisn, siswa.nama, kelas.tingkat, kelas.jurusan
    ORDER BY siswa.nama ASC
");

// Data buat kartu ringkasan
$semua_baris = [];
if ($query && mysqli_num_rows($query) > 0) {
    while ($r = mysqli_fetch_assoc($query)) {
        $semua_baris[] = $r;
    }
}
$total_siswa = count($semua_baris);
$total_bulan_semua = array_sum(array_column($semua_baris, 'jumlah_transaksi'));
$rata_rata_bulan = $total_siswa > 0 ? round($total_bulan_semua / $total_siswa, 1) : 0;
$jumlah_lunas_penuh = count(array_filter($semua_baris, function ($r) {
    return (int) $r['jumlah_transaksi'] >= TOTAL_BULAN_WAJIB;
}));
?>

<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-4 border-bottom">
        <div>
            <h1 class="h3 fw-bold mb-1" style="color: #db2777;">📖 History Status Pembayaran Siswa</h1>
            <p class="text-muted mb-0 small">Cari siswa, lalu klik "Lihat Detail" untuk melihat rincian pembayaran per bulan per tahun ajaran.</p>
        </div>
    </div>

    <!-- KARTU RINGKASAN -->
    <div class="row g-3 mb-3">
        <div class="col-6 col-md-4">
            <div class="card border-0 shadow-sm h-100 kartu-ringkasan" style="background: linear-gradient(135deg, #fdf2f8, #fce7f3);">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="ikon-ringkasan" style="background:#fbcfe8;">👥</div>
                    <div>
                        <div class="small text-muted">Total Siswa Bayar</div>
                        <div class="h4 fw-bold mb-0" style="color:#db2777;"><?= $total_siswa; ?></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4">
            <div class="card border-0 shadow-sm h-100 kartu-ringkasan" style="background: linear-gradient(135deg, #eef2ff, #e0e7ff);">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="ikon-ringkasan" style="background:#c7d2fe;">📊</div>
                    <div>
                        <div class="small text-muted">Rata-rata Bulan Lunas</div>
                        <div class="h4 fw-bold mb-0" style="color:#4338ca;"><?= $rata_rata_bulan; ?> <span class="fs-6 fw-normal text-muted">/ <?= TOTAL_BULAN_WAJIB; ?></span></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="card border-0 shadow-sm h-100 kartu-ringkasan" style="background: linear-gradient(135deg, #f0fdf4, #dcfce7);">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="ikon-ringkasan" style="background:#bbf7d0;">🎉</div>
                    <div>
                        <div class="small text-muted">Sudah Lunas Penuh</div>
                        <div class="h4 fw-bold mb-0" style="color:#15803d;"><?= $jumlah_lunas_penuh; ?> siswa</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body py-3">
            <div class="input-group input-group-pencarian" style="max-width: 420px;">
                <span class="input-group-text bg-white border-end-0" style="color:#db2777;">
                    <i class="bi bi-search"></i>
                </span>
                <input type="text" id="cari-siswa" class="form-control border-start-0"
                       placeholder="Cari nama atau NISN siswa..." autocomplete="off">
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table align-middle mb-0" id="tabel-history">
                    <thead style="background-color: #fdf2f8; color: #db2777;">
                        <tr>
                            <th>No</th>
                            <th>Siswa</th>
                            <th>Kelas</th>
                            <th style="min-width:180px;">Bulan Tercatat Bayar</th>
                            <th>Tahun Ajaran</th>
                            <th>Terakhir Bayar</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $no = 1;
                        if (count($semua_baris) > 0):
                            foreach ($semua_baris as $row):
                                $nama_cari = strtolower($row['nama'] . ' ' . $row['nisn']);
                                $jml = (int) $row['jumlah_transaksi'];
                                $persen = min(100, round($jml / TOTAL_BULAN_WAJIB * 100));
                                $lunas_penuh = $jml >= TOTAL_BULAN_WAJIB;

                                if ($persen >= 100) { $warna_progress = '#16a34a'; }
                                elseif ($persen >= 60) { $warna_progress = '#65a30d'; }
                                elseif ($persen >= 30) { $warna_progress = '#f59e0b'; }
                                else { $warna_progress = '#f43f5e'; }

                                $avatar = warnaAvatar($row['nama'], $PALET_AVATAR);

                                // "2024,2025" -> badge terpisah "2024/2025" "2025/2026"
                                $tahun_badges = '<span class="text-muted small">-</span>';
                                if (!empty($row['tahun_list'])) {
                                    $tahun_pecah = array_filter(array_map('trim', explode(',', $row['tahun_list'])));
                                    $tahun_badges = '';
                                    foreach ($tahun_pecah as $th) {
                                        $tahun_badges .= '<span class="badge-tahun">' . htmlspecialchars(formatTA($th)) . '</span> ';
                                    }
                                }
                        ?>
                            <tr data-cari="<?= htmlspecialchars($nama_cari); ?>">
                                <td class="text-muted"><?= $no++; ?></td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="avatar-inisial" style="background:<?= $avatar['bg']; ?>; color:<?= $avatar['fg']; ?>;">
                                            <?= htmlspecialchars(inisialNama($row['nama'])); ?>
                                        </div>
                                        <div>
                                            <div class="fw-semibold"><?= htmlspecialchars($row['nama']); ?></div>
                                            <div class="text-muted small"><?= htmlspecialchars($row['nisn']); ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge-kelas">🎓 <?= htmlspecialchars($row['tingkat'] . ' ' . $row['jurusan']); ?></span>
                                </td>
                                <td>
                                    <div class="d-flex justify-content-between small mb-1">
                                        <span class="fw-semibold" style="color:<?= $warna_progress; ?>;">
                                            <?= $jml; ?> bulan<?= $lunas_penuh ? ' 🎉' : ''; ?>
                                        </span>
                                        <span class="text-muted"><?= $persen; ?>%</span>
                                    </div>
                                    <div class="progress-mini">
                                        <div class="progress-mini-isi" style="width:<?= $persen; ?>%; background:<?= $warna_progress; ?>;"></div>
                                    </div>
                                </td>
                                <td><?= $tahun_badges; ?></td>
                                <td class="text-muted small"><?= htmlspecialchars($row['terakhir_bayar']); ?></td>
                                <td class="text-end">
                                    <a href="detail_history.php?nisn=<?= urlencode($row['nisn']); ?>" class="btn-lihat-detail">
                                        <i class="bi bi-eye"></i> Lihat Detail
                                    </a>
                                </td>
                            </tr>
                        <?php
                            endforeach;
                        else:
                        ?>
                            <tr><td colspan="7" class="text-center py-4 text-muted">Belum ada history atau riwayat pembayaran siswa.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div id="tidak-ditemukan" class="text-center text-muted py-5" style="display:none;">
                <div style="font-size:2rem;">🔍</div>
                Tidak ada siswa yang cocok dengan pencarian.
            </div>
        </div>
    </div>
</main>

<style>
    .kartu-ringkasan { border-radius: 16px; transition: transform .15s ease; }
    .kartu-ringkasan:hover { transform: translateY(-2px); }
    .ikon-ringkasan {
        width: 44px; height: 44px; border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.3rem; flex-shrink: 0;
    }

    .input-group-pencarian { border-radius: 999px; overflow: hidden; }
    .input-group-pencarian .form-control,
    .input-group-pencarian .input-group-text {
        border-color: #f3d4e4;
    }
    .input-group-pencarian .form-control:focus {
        box-shadow: 0 0 0 .2rem rgba(219, 39, 119, .15);
        border-color: #db2777;
    }

    #tabel-history { border-collapse: separate; border-spacing: 0 8px; }
    #tabel-history thead th { border: none; font-size: .82rem; padding: 10px 12px; }
    #tabel-history thead tr { border-radius: 12px; }
    #tabel-history tbody tr {
        background: #fff;
        box-shadow: 0 1px 3px rgba(0,0,0,.05);
        transition: background .15s ease, transform .15s ease;
    }
    #tabel-history tbody tr:hover { background: #fdf2f8; transform: translateY(-1px); }
    #tabel-history tbody td { border: none; padding: 12px; }
    #tabel-history tbody td:first-child { border-top-left-radius: 12px; border-bottom-left-radius: 12px; }
    #tabel-history tbody td:last-child { border-top-right-radius: 12px; border-bottom-right-radius: 12px; }

    .avatar-inisial {
        width: 38px; height: 38px; border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        font-weight: 700; font-size: .8rem; flex-shrink: 0;
    }

    .badge-kelas {
        background: #f3f4f6; color: #374151;
        padding: 4px 10px; border-radius: 999px;
        font-size: .78rem; font-weight: 600; white-space: nowrap;
    }

    .badge-tahun {
        display: inline-block; background: #fdf2f8; color: #9d174d;
        padding: 2px 8px; border-radius: 999px; font-size: .72rem;
        font-weight: 600; margin: 1px 0;
    }

    .progress-mini {
        width: 100%; height: 8px; border-radius: 999px;
        background: #f1f1f4; overflow: hidden;
    }
    .progress-mini-isi {
        height: 100%; border-radius: 999px;
        transition: width .3s ease;
    }

    .btn-lihat-detail {
        display: inline-flex; align-items: center; gap: 6px;
        background: #db2777; color: #fff; text-decoration: none;
        padding: 7px 14px; border-radius: 999px;
        font-size: .82rem; font-weight: 600;
        transition: transform .15s ease, background .15s ease;
    }
    .btn-lihat-detail:hover {
        background: #9d174d; color: #fff; transform: scale(1.04);
    }
</style>

<script>
    (function () {
        var input = document.getElementById('cari-siswa');
        var baris = document.querySelectorAll('#tabel-history tbody tr[data-cari]');
        var pesanKosong = document.getElementById('tidak-ditemukan');

        input.addEventListener('input', function () {
            var kata = this.value.trim().toLowerCase();
            var adaYangCocok = false;

            baris.forEach(function (tr) {
                var cocok = tr.dataset.cari.indexOf(kata) !== -1;
                tr.style.display = cocok ? '' : 'none';
                if (cocok) adaYangCocok = true;
            });

            pesanKosong.style.display = (kata !== '' && !adaYangCocok) ? 'block' : 'none';
        });
    })();
</script>

<?php include '../components/footer.php'; ?>