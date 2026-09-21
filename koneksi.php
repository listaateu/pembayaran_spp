<?php
// Ambil pengaturan sesuai environment (development / staging / production)
require_once __DIR__ . '/config_env.php';

$koneksi = false;
$pesan_error = '';

try {
    $koneksi = mysqli_connect(
        $config['host'],
        $config['user'],
        $config['pass'],
        $config['db']
    );
    if (!$koneksi) {
        $pesan_error = mysqli_connect_error();
    }
} catch (mysqli_sql_exception $e) {
    // PHP 8.1+ melempar exception kalau koneksi gagal, kita tangkap di sini
    $pesan_error = $e->getMessage();
}

if (!$koneksi) {
    error_log("Koneksi database gagal: " . $pesan_error); // selalu dicatat ke error.log
    if ($config['tampilkan_error']) {
        // Development: tampilkan pesan lengkap supaya gampang dicari salahnya
        die("Koneksi database gagal: " . $pesan_error);
    }
    // Staging/Production: pengguna hanya lihat pesan umum
    die("Aplikasi sedang bermasalah. Silakan coba beberapa saat lagi.");
}

/**
 * Format tahun ajaran ala Indonesia: 2024 -> "2024/2025"
 * Dipakai di semua halaman biar tidak perlu didefinisikan ulang
 * di tiap file (kolom "tahun" di database TETAP disimpan sebagai
 * angka tunggal, ini cuma untuk TAMPILAN saja).
 */
if (!function_exists('formatTA')) {
    function formatTA($tahun)
    {
        $tahun = (int) $tahun;
        return $tahun . '/' . ($tahun + 1);
    }
}
?>