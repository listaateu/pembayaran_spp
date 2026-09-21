<?php
session_start();
include 'koneksi.php';

$error = "";

if (isset($_POST['login'])) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    // Prepared statement -> aman dari SQL Injection
    $stmt = mysqli_prepare($koneksi, "SELECT * FROM petugas WHERE username = ?");
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($row = mysqli_fetch_assoc($result)) {
        // Cocokkan password dengan hash yang tersimpan di DB
        if (password_verify($password, $row['password'])) {
            // Regenerate session ID -> mencegah session fixation
            session_regenerate_id(true);

            $_SESSION['username']     = $row['username'];
            $_SESSION['nama_petugas'] = $row['nama_petugas'];
            $_SESSION['level']        = $row['level'];

            if ($row['level'] == 'admin') {
                header("location: backend/admin/index.php");
                exit();
            } else if ($row['level'] == 'petugas') {
                header("location: backend/petugas/index.php");
                exit();
            }
        } else {
            $error = "Username atau password salah.";
        }
    } else {
        $error = "Username atau password salah.";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login - Aplikasi Pembayaran SPP</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,400;9..144,500;9..144,600&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="style.css">
</head>
<body>

  <div class="stage">
    <div class="card">

      <div class="brand">
        <div class="mark">
          <svg width="26" height="26" viewBox="0 0 26 26" fill="none">
            <rect x="2" y="2" width="22" height="22" rx="6" stroke="#FBEAF4" stroke-width="1.6"/>
            <path d="M7 9.5H19M7 13H16M7 16.5H13" stroke="#FBEAF4" stroke-width="1.6" stroke-linecap="round"/>
          </svg>
          SPP Digital
        </div>

        <h1>Pembayaran SPP,<br>tercatat rapi.</h1>
        <p>Masuk untuk mengelola transaksi, mencetak kuitansi, dan memantau status pembayaran siswa.</p>

        <div class="stub-wrap">
          <div class="stub">
            <div class="stub-top">
              <span class="stub-brand">SPP DIGITAL</span>
              <span class="stub-tag">LUNAS</span>
            </div>
            <div class="stub-row"><span>Bulan</span><span>September</span></div>
            <div class="stub-row"><span>Kelas</span><span>12 PPLG 1</span></div>
            <div class="stub-total">Rp 900.000</div>
          </div>
        </div>
      </div>

      <div class="form-panel">
        <h2>Masuk ke akun</h2>
        <p class="sub">Khusus untuk admin dan petugas sekolah.</p>

        <?php if ($error): ?>
          <div class="error-box"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="">
          <div class="field">
            <label for="username">Username</label>
            <div class="control">
              <input type="text" id="username" name="username" placeholder="Masukkan username" required autofocus>
            </div>
          </div>

          <div class="field">
            <label for="password">Kata sandi</label>
            <div class="control">
              <input type="password" id="password" name="password" placeholder="Masukkan kata sandi" required>
              <button type="button" class="toggle-pass" id="togglePass" aria-label="Tampilkan kata sandi" aria-pressed="false">
                <svg id="eyeIcon" width="19" height="19" viewBox="0 0 24 24" fill="none">
                  <path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7-10-7-10-7Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/>
                  <circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="1.7"/>
                </svg>
              </button>
            </div>
          </div>

          <button type="submit" name="login" class="submit">Masuk</button>
        </form>

        <p class="hint">Lupa kata sandi? Hubungi admin sekolah.</p>
      </div>

    </div>
  </div>

  <script>
    const toggle = document.getElementById('togglePass');
    const passInput = document.getElementById('password');
    const eyeIcon = document.getElementById('eyeIcon');

    toggle.addEventListener('click', () => {
      const showing = passInput.type === 'text';
      passInput.type = showing ? 'password' : 'text';
      toggle.setAttribute('aria-pressed', String(!showing));
      toggle.setAttribute('aria-label', showing ? 'Tampilkan kata sandi' : 'Sembunyikan kata sandi');
      eyeIcon.innerHTML = showing
        ? '<path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7-10-7-10-7Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/><circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="1.7"/>'
        : '<path d="M3 3l18 18M10.6 10.6a3 3 0 0 0 4.2 4.2M9.9 5.2A10.4 10.4 0 0 1 12 5c6.4 0 10 7 10 7a17.9 17.9 0 0 1-3.2 4.1M6.2 6.2A17.7 17.7 0 0 0 2 12s3.6 7 10 7c1.2 0 2.3-.2 3.3-.6" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>';
    });
  </script>

</body>
</html>