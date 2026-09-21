<?php
// ============================================================
// PENGATURAN ENVIRONMENT
// Ganti satu baris APP_ENV di bawah untuk pindah environment:
//   'development' = lagi ngoding (error ditampilkan lengkap di layar)
//   'staging'     = uji coba sebelum dirilis (error disembunyikan, dicatat ke log)
//   'production'  = dipakai sungguhan (error disembunyikan, dicatat ke log)
// ============================================================
define('APP_ENV', 'development');

$semua_config = [
    'development' => [
        'host'            => 'localhost',
        'user'            => 'root',
        'pass'            => '',
        'db'              => 'pembayaran_spp',
        'tampilkan_error' => true,
    ],
    'staging' => [
        'host'            => 'localhost',
        'user'            => 'root',
        'pass'            => '',
        'db'              => 'pembayaran_spp', // nanti bisa diganti ke database salinan khusus uji coba
        'tampilkan_error' => false,
    ],
    'production' => [
        'host'            => 'localhost',
        'user'            => 'root',
        'pass'            => '',
        'db'              => 'pembayaran_spp',
        'tampilkan_error' => false,
    ],
];

// Kalau APP_ENV salah ketik, otomatis pakai production (paling aman)
$config = $semua_config[APP_ENV] ?? $semua_config['production'];

// Atur tampilan error sesuai environment
error_reporting(E_ALL);
ini_set('display_errors', $config['tampilkan_error'] ? '1' : '0');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/error.log');