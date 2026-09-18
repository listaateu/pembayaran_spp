<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'SPP Digital - Petugas'; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .active-menu { background-color: #fbcfe8 !important; color: #9d174d !important; box-shadow: 0 2px 6px rgba(157, 23, 77, 0.1); }
        .sidebar .nav-link:hover:not(.active-menu) { background-color: #fce7f3 !important; color: #db2777 !important; transition: all 0.2s ease; }
        <?= $extraHeadStyle ?? ''; ?>
    </style>
</head>
<body class="bg-light">
<div class="container-fluid">
    <div class="row">
        <?php include __DIR__ . '/sidebar.php'; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">