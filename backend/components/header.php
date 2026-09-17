<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aplikasi Pembayaran SPP</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    
    <style>
        :root {
            --pastel-pink: #ffe6f0;
            --pastel-blue: #e3f2fd;
            --pastel-yellow: #fffde7;
            --soft-purple: #f3e5f5;
        }

        body {
            background-color: #f8fafc;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        /* Kartu dengan sudut melengkung halus dan bayangan estetik */
        .card {
            border-radius: 16px;
            border: none;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.03) !important;
            transition: transform 0.2s ease;
        }

        /* Tombol kustom warna cerah lembut */
        .btn-primary {
            background-color: #89cff0;
            border: none;
            color: #1e293b;
            font-weight: 600;
            border-radius: 10px;
            padding: 8px 16px;
            transition: all 0.2s;
        }

        .btn-primary:hover {
            background-color: #6fb3d2;
            color: #fff;
        }

        /* Tabel yang lebih bersih dan manis */
        .table {
            vertical-align: middle;
        }
        
        .table thead th {
            background-color: #fdf2f8 !important;
            color: #db2777;
            border-bottom: 2px solid #fbcfe8;
        }

        /* Matikan kursor teks & seleksi teks di SELURUH halaman (permintaan guru) */
        * {
            user-select: none !important;
            -webkit-user-select: none !important;
            -moz-user-select: none !important;
            -ms-user-select: none !important;
            cursor: default !important;
        }

        /* Elemen yang memang harus bisa diklik tetap pakai cursor pointer,
           jangan ikut ke-override jadi cursor default */
        a, button, .btn, label, select, .form-select, .cek-bulan {
            cursor: pointer !important;
        }

        /* Input teks tetap boleh diketik & diseleksi (kalau ada form isian) */
        input[type="text"], input[type="number"], input[type="password"],
        input[type="email"], input[type="search"], textarea {
            user-select: text !important;
            cursor: text !important;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container-fluid">
        <div class="row">