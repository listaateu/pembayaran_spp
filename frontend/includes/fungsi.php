<?php
/**
 * frontend/includes/fungsi.php — Konstanta & fungsi bantu yang dipakai banyak halaman frontend
 * LETAKKAN DI: pembayaran_spp/frontend/includes/fungsi.php
 *
 * Nanti halaman petugas di frontend juga bisa memakai file ini.
 */

// ---- Pengaturan keamanan ----
const SESI_MAKS_DETIK = 900;   // sesi siswa habis setelah 15 menit
const GAGAL_MAKS      = 5;     // maksimal salah input sebelum dikunci
const KUNCI_DETIK     = 300;   // lama dikunci: 5 menit

// ---- Nama bulan (kunci 1 = Januari, dst.) ----
const DAFTAR_BULAN = [1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
                      'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

// Aman-kan teks sebelum ditampilkan di HTML (mencegah XSS)
if (!function_exists('e')) {
    function e($teks) { return htmlspecialchars((string) $teks, ENT_QUOTES, 'UTF-8'); }
}

// 50000 -> "Rp 50.000"
if (!function_exists('rp')) {
    function rp($n) { return 'Rp ' . number_format((int) $n, 0, ',', '.'); }
}

// "2026-09-24" -> "24 Sep 2026"
if (!function_exists('tglIndo')) {
    function tglIndo($tgl) {
        $t = strtotime((string) $tgl);
        if (!$t) { return '-'; }
        return date('j', $t) . ' ' . substr(DAFTAR_BULAN[(int) date('n', $t)], 0, 3) . ' ' . date('Y', $t);
    }
}

// 2025 -> "2025/2026"
if (!function_exists('formatTA')) {
    function formatTA($tahun) { $t = (int) $tahun; return $t . '/' . ($t + 1); }
}

// Simpan pesan error di sesi lalu kembali ke form cek di index.php
if (!function_exists('balikKeDepan')) {
    function balikKeDepan($pesan)
    {
        $_SESSION['cek_error'] = $pesan;
        header('Location: index.php#cek');
        exit();
    }
}
