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

    // Ambil nominal total SPP
    $cek_spp = mysqli_query($koneksi, "SELECT nominal FROM spp WHERE id_spp = '$id_spp'");
    $data_spp = mysqli_fetch_assoc($cek_spp);
    $nominal_spp = $data_spp['nominal'] ?? 0;

    // Hitung TOTAL yang sudah pernah dibayar siswa ini untuk SPP ini sebelumnya (akumulasi semua cicilan)
    $cek_sudah_bayar = mysqli_query($koneksi, "SELECT SUM(jumlah_bayar) AS total FROM pembayaran WHERE nisn = '$nisn' AND id_spp = '$id_spp'");
    $data_sudah_bayar = mysqli_fetch_assoc($cek_sudah_bayar);
    $sudah_dibayar = $data_sudah_bayar['total'] ?? 0;

    // Sisa tagihan = total SPP - yang sudah dibayar sebelumnya
    $sisa_tagihan = $nominal_spp - $sudah_dibayar;

    if ($jumlah_bayar > $sisa_tagihan) {
        echo "<script>alert('Gagal! Jumlah bayar (Rp " . number_format($jumlah_bayar, 0, ',', '.') . ") melebihi sisa tagihan siswa ini (Rp " . number_format($sisa_tagihan, 0, ',', '.') . "). Siswa sudah membayar Rp " . number_format($sudah_dibayar, 0, ',', '.') . " dari total Rp " . number_format($nominal_spp, 0, ',', '.') . ".'); window.location='tambah_pembayaran.php';</script>";
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

// Ambil semua siswa + tingkat + jurusan (kode pendek, tanpa nomor rombel)
// buat dijadikan sumber data dropdown bertingkat (Tingkat -> Jurusan -> Siswa) di sisi JavaScript.
$daftar_siswa_js = [];
$q_siswa_js = mysqli_query($koneksi, "SELECT siswa.nisn, siswa.nama, kelas.tingkat, kelas.jurusan 
                                       FROM siswa 
                                       JOIN kelas ON siswa.id_kelas = kelas.id_kelas 
                                       ORDER BY siswa.nama ASC");
while ($sj = mysqli_fetch_assoc($q_siswa_js)) {
    $kode_jurusan = trim(preg_replace('/\s*\d+$/', '', $sj['jurusan']));
    $daftar_siswa_js[] = [
        'nisn'    => $sj['nisn'],
        'nama'    => $sj['nama'],
        'tingkat' => $sj['tingkat'],
        'jurusan' => $kode_jurusan,
    ];
}
// Ambil riwayat semua pembayaran (nisn, id_spp, jumlah_bayar) buat hitung akumulasi cicilan di sisi JavaScript
$daftar_pembayaran_js = [];
$q_bayar_js = mysqli_query($koneksi, "SELECT nisn, id_spp, jumlah_bayar FROM pembayaran");
while ($pj = mysqli_fetch_assoc($q_bayar_js)) {
    $daftar_pembayaran_js[] = [
        'nisn'         => $pj['nisn'],
        'id_spp'       => $pj['id_spp'],
        'jumlah_bayar' => (int) $pj['jumlah_bayar'],
    ];
}

// Ambil daftar SPP (id_spp, tahun, nominal) buat ditampilkan di kotak info sisa tagihan
$daftar_spp_js = [];
$q_spp_js = mysqli_query($koneksi, "SELECT id_spp, tahun, nominal FROM spp");
while ($spj = mysqli_fetch_assoc($q_spp_js)) {
    $daftar_spp_js[] = [
        'id_spp'  => $spj['id_spp'],
        'tahun'   => $spj['tahun'],
        'nominal' => (int) $spj['nominal'],
    ];
}
?>

<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
    <div class="pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h3 fw-bold mb-3" style="color: #db2777;">Entri Pembayaran & Cicilan SPP</h1>
    </div>

    <div class="card border-0 shadow-sm col-md-12">
        <div class="card-body">
            <form action="" method="POST">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Tingkat</label>
                    <select id="filter_tingkat" class="form-select">
                        <option value="">-- Pilih Tingkat --</option>
                        <option value="10">10</option>
                        <option value="11">11</option>
                        <option value="12">12</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Jurusan</label>
                    <select id="filter_jurusan" class="form-select" disabled>
                        <option value="">-- Pilih Tingkat dulu --</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Siswa</label>
                    <select name="nisn" id="filter_siswa" class="form-select" required disabled>
                        <option value="">-- Pilih Jurusan dulu --</option>
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
                    <select name="id_spp" id="filter_spp" class="form-select" required>
                        <option value="">-- Pilih SPP / Nominal --</option>
                        <?php
                        $spp = mysqli_query($koneksi, "SELECT * FROM spp");
                        while ($sp = mysqli_fetch_assoc($spp)) {
                            echo "<option value='{$sp['id_spp']}'>Tahun {$sp['tahun']} - Rp " . number_format($sp['nominal'], 0, ',', '.') . "</option>";
                        }
                        ?>
                    </select>
                </div>

                <div id="info_tagihan" class="alert alert-info d-none mb-3">
                    <div class="d-flex justify-content-between"><span>Total SPP</span><strong id="info_total">Rp 0</strong></div>
                    <div class="d-flex justify-content-between"><span>Sudah Dibayar</span><strong id="info_sudah">Rp 0</strong></div>
                    <div class="d-flex justify-content-between"><span>Sisa Tagihan</span><strong id="info_sisa" class="text-danger">Rp 0</strong></div>
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

<script>
// Data semua siswa (nisn, nama, tingkat, jurusan) dikirim dari PHP.
// Dipakai buat mengisi dropdown Siswa secara dinamis sesuai Tingkat & Jurusan yang dipilih.
const daftarSiswa = <?php echo json_encode($daftar_siswa_js, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;

const daftarPembayaran = <?php echo json_encode($daftar_pembayaran_js, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
const daftarSpp = <?php echo json_encode($daftar_spp_js, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;

const daftarJurusan = ['PPLG', 'AKL', 'APHP', 'APAT', 'TKR', 'TSM'];

const selTingkat = document.getElementById('filter_tingkat');
const selJurusan = document.getElementById('filter_jurusan');
const selSiswa   = document.getElementById('filter_siswa');
const selSpp     = document.getElementById('filter_spp');
const inputBayar = document.querySelector('input[name="jumlah_bayar"]');
const boxInfo    = document.getElementById('info_tagihan');
const infoTotal  = document.getElementById('info_total');
const infoSudah  = document.getElementById('info_sudah');
const infoSisa   = document.getElementById('info_sisa');

function formatRupiah(angka) {
    return 'Rp ' + angka.toLocaleString('id-ID');
}

// Hitung & tampilkan sisa tagihan setiap kali Siswa atau SPP berubah
function updateInfoTagihan() {
    const nisn = selSiswa.value;
    const idSpp = selSpp.value;

    if (nisn === '' || idSpp === '') {
        boxInfo.classList.add('d-none');
        return;
    }

    const spp = daftarSpp.find(function (s) { return String(s.id_spp) === String(idSpp); });
    if (!spp) {
        boxInfo.classList.add('d-none');
        return;
    }

    const sudahDibayar = daftarPembayaran
        .filter(function (p) { return p.nisn === nisn && String(p.id_spp) === String(idSpp); })
        .reduce(function (total, p) { return total + p.jumlah_bayar; }, 0);

    const sisa = spp.nominal - sudahDibayar;

    infoTotal.textContent = formatRupiah(spp.nominal);
    infoSudah.textContent = formatRupiah(sudahDibayar);
    infoSisa.textContent = formatRupiah(sisa);
    boxInfo.classList.remove('d-none');

    // Batasi maksimal input jumlah bayar sesuai sisa tagihan
    inputBayar.setAttribute('max', sisa);
}

selSiswa.addEventListener('change', updateInfoTagihan);
selSpp.addEventListener('change', updateInfoTagihan);

// Waktu Tingkat dipilih -> aktifkan & isi dropdown Jurusan
selTingkat.addEventListener('change', function () {
    const tingkat = this.value;

    // Reset Jurusan & Siswa dulu
    selJurusan.innerHTML = '';
    selSiswa.innerHTML = '<option value="">-- Pilih Jurusan dulu --</option>';
    selSiswa.setAttribute('disabled', true);
    boxInfo.classList.add('d-none');

    if (tingkat === '') {
        selJurusan.innerHTML = '<option value="">-- Pilih Tingkat dulu --</option>';
        selJurusan.setAttribute('disabled', true);
        return;
    }

    selJurusan.removeAttribute('disabled');
    selJurusan.innerHTML = '<option value="">-- Pilih Jurusan --</option>';
    daftarJurusan.forEach(function (j) {
        const opt = document.createElement('option');
        opt.value = j;
        opt.textContent = j;
        selJurusan.appendChild(opt);
    });
});

// Waktu Jurusan dipilih -> isi dropdown Siswa sesuai Tingkat + Jurusan yang aktif
selJurusan.addEventListener('change', function () {
    const tingkat = selTingkat.value;
    const jurusan = this.value;

    selSiswa.innerHTML = '';

    if (jurusan === '') {
        selSiswa.innerHTML = '<option value="">-- Pilih Jurusan dulu --</option>';
        selSiswa.setAttribute('disabled', true);
        boxInfo.classList.add('d-none');
        return;
    }

    const hasil = daftarSiswa.filter(function (s) {
        return s.tingkat === tingkat && s.jurusan === jurusan;
    });

    if (hasil.length === 0) {
        selSiswa.innerHTML = '<option value="">-- Tidak ada siswa di kelas ini --</option>';
        selSiswa.setAttribute('disabled', true);
        return;
    }

    selSiswa.removeAttribute('disabled');
    selSiswa.innerHTML = '<option value="">-- Pilih Siswa --</option>';
    hasil.forEach(function (s) {
        const opt = document.createElement('option');
        opt.value = s.nisn;
        opt.textContent = s.nisn + ' - ' + s.nama;
        selSiswa.appendChild(opt);
    });
});
</script>

<?php include '../components/footer.php'; ?>