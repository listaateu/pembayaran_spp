<?php
/**
 * kirim_wa.php
 * -----------------------------------------------------------
 * LETAKKAN FILE INI DI: pembayaran_spp/backend/kirim_wa.php
 * (sejajar/satu folder dengan folder "petugas" dan "admin")
 *
 * File ini dipanggil otomatis oleh tombol "Kirim ke WhatsApp"
 * di halaman cetak_pembayaran.php (baik punya petugas maupun admin).
 * Tugasnya: ambil data pembayaran + nomor HP siswa dari database,
 * lalu kirim pesan WA langsung lewat Fonnte (tanpa perlu buka WA lagi).
 * -----------------------------------------------------------
 */

session_start();
header('Content-Type: application/json');

// Hanya boleh diakses kalau sudah login sebagai admin atau petugas
if (!isset($_SESSION['level']) || !in_array($_SESSION['level'], ['admin', 'petugas'])) {
    echo json_encode(['success' => false, 'message' => 'Silakan login terlebih dahulu.']);
    exit();
}

include '../koneksi.php'; // sesuaikan jika lokasi koneksi.php beda

// =====================================================
// 1) TOKEN FONNTE KAMU
// =====================================================
define('FONNTE_TOKEN', 'EXRcw4EjZjZdjwrco7od');
define('FONNTE_API_URL', 'https://api.fonnte.com/send');

// =====================================================
// 2) AMBIL ID PEMBAYARAN YANG DIKIRIM DARI HALAMAN KUITANSI
// =====================================================
$ids_mentah = $_POST['ids'] ?? '';
$daftar_id  = array_filter(array_map('intval', explode(',', $ids_mentah)));

if (count($daftar_id) === 0) {
    echo json_encode(['success' => false, 'message' => 'ID pembayaran tidak valid.']);
    exit();
}
$ids_sql = implode(',', $daftar_id);

// =====================================================
// 3) AMBIL DATA PEMBAYARAN + NOMOR HP SISWA
//    (query ini sama persis dengan yang dipakai di cetak_pembayaran.php,
//     ditambah kolom siswa.no_telp)
// =====================================================
$q = mysqli_query($koneksi, "
    SELECT pembayaran.*, siswa.nama, siswa.nis, siswa.no_telp, kelas.tingkat, kelas.jurusan
    FROM pembayaran
    JOIN siswa ON pembayaran.nisn = siswa.nisn
    JOIN kelas ON siswa.id_kelas = kelas.id_kelas
    WHERE pembayaran.id_pembayaran IN ($ids_sql)
    ORDER BY pembayaran.tahun_dibayar ASC, pembayaran.id_pembayaran ASC
");

$daftar      = [];
$total_bayar = 0;
while ($row = mysqli_fetch_assoc($q)) {
    $daftar[] = $row;
    $total_bayar += (int) $row['jumlah_bayar'];
}

if (count($daftar) === 0) {
    echo json_encode(['success' => false, 'message' => 'Data pembayaran tidak ditemukan.']);
    exit();
}

$d = $daftar[0];

// =====================================================
// 4) VALIDASI NOMOR HP
// =====================================================
if (empty($d['no_telp'])) {
    echo json_encode(['success' => false, 'message' => 'Nomor HP siswa ini belum diisi di database, jadi tidak bisa dikirim otomatis.']);
    exit();
}

// Rapikan format nomor: hilangkan spasi/strip, pastikan mulai dengan 62 (bukan 0)
$nomor_wa = preg_replace('/[^0-9]/', '', $d['no_telp']);
if (substr($nomor_wa, 0, 1) === '0') {
    $nomor_wa = '62' . substr($nomor_wa, 1);
}

// =====================================================
// 5) SUSUN ISI PESAN
// =====================================================
$satu_transaksi = (count($daftar) === 1);

if ($satu_transaksi) {
    $keterangan_bulan = "SPP Bulan " . $d['bulan_dibayar'] . " " . $d['tahun_dibayar'];
} else {
    $list_bulan = array_map(function ($t) {
        return $t['bulan_dibayar'] . ' (Rp ' . number_format($t['jumlah_bayar'], 0, ',', '.') . ')';
    }, $daftar);
    $keterangan_bulan = "SPP " . count($daftar) . " bulan sekaligus: " . implode(', ', $list_bulan);
}

$pesan  = "Assalamu'alaikum,\n\n";
$pesan .= "Berikut kami sampaikan bukti pembayaran SPP:\n\n";
$pesan .= "Nama   : " . $d['nama'] . "\n";
$pesan .= "Kelas  : " . $d['tingkat'] . " " . $d['jurusan'] . "\n";
$pesan .= "Untuk  : " . $keterangan_bulan . "\n";
$pesan .= "Total  : Rp " . number_format($total_bayar, 0, ',', '.') . "\n";
$pesan .= "Status : LUNAS\n\n";
$pesan .= "Terima kasih.\n- SPP Digital -";

// =====================================================
// 6) KIRIM KE FONNTE
// =====================================================
$ch = curl_init(FONNTE_API_URL);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, [
    'target'      => $nomor_wa,
    'message'     => $pesan,
    'countryCode' => '62',
]);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: ' . FONNTE_TOKEN]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlErr  = curl_error($ch);
curl_close($ch);

if ($curlErr) {
    echo json_encode(['success' => false, 'message' => 'Koneksi ke Fonnte gagal: ' . $curlErr]);
    exit();
}

$result = json_decode($response, true);

if ($httpCode === 200 && isset($result['status']) && $result['status'] === true) {
    echo json_encode(['success' => true, 'message' => 'Kuitansi berhasil dikirim ke WhatsApp (' . $nomor_wa . ').']);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Gagal mengirim pesan ke Fonnte.',
        'detail'  => $result,
    ]);
}