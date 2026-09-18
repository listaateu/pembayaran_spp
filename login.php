<?php
session_start();
include 'koneksi.php';

if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Menggunakan Prepared Statement untuk mitigasi SQL Injection
    $stmt = mysqli_prepare($koneksi, "SELECT * FROM petugas WHERE username = ? AND password = ?");
    mysqli_stmt_bind_param($stmt, "ss", $username, $password);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($row = mysqli_fetch_assoc($result)) {
        $_SESSION['username'] = $row['username'];
        $_SESSION['nama_petugas'] = $row['nama_petugas'];
        $_SESSION['level'] = $row['level'];

        if ($row['level'] == 'admin') {
            header("location: backend/admin/index.php");
            exit();
        } else if ($row['level'] == 'petugas') {
            header("location: backend/petugas/index.php");
            exit();
        }
    } else {
        echo "<script>alert('Login gagal! Username atau password salah.'); window.location='login.php';</script>";
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
<style>
  :root{
    --ink:        #2A1424;
    --ink-soft:   #8A6E80;
    --plum:       #6E1345;
    --plum-deep:  #4A0E30;
    --pink:       #DB2777;
    --pink-dark:  #B21D60;
    --blush:      #FDF2F8;
    --paper:      #FFFFFF;
    --line:       #F1D9E6;
    --gold:       #F0B94E;
    --shadow:     0 30px 60px -20px rgba(110,19,69,.35);
  }

  *{ box-sizing:border-box; }

  html,body{
    margin:0;
    min-height:100vh;
  }

  body{
    font-family:"Plus Jakarta Sans", sans-serif;
    color:var(--ink);
    background:
      radial-gradient(1100px 600px at 85% -10%, #FCE4F1 0%, transparent 60%),
      radial-gradient(900px 500px at -10% 110%, #FBE9DD 0%, transparent 55%),
      var(--blush);
    display:flex;
    align-items:center;
    justify-content:center;
    padding:32px 20px;
  }

  .stage{
    width:100%;
    max-width:900px;
  }

  .card{
    display:grid;
    grid-template-columns: 1.05fr 1fr;
    background:var(--paper);
    border-radius:28px;
    overflow:hidden;
    box-shadow:var(--shadow);
    opacity:0;
    transform:translateY(14px);
    animation:rise .6s cubic-bezier(.2,.7,.3,1) forwards;
  }

  @keyframes rise{
    to{ opacity:1; transform:translateY(0); }
  }

  @media (prefers-reduced-motion: reduce){
    .card{ animation:none; opacity:1; transform:none; }
  }

  /* ---------- Left: brand panel ---------- */
  .brand{
    position:relative;
    padding:44px 40px 0;
    background:
      radial-gradient(480px 320px at 15% 0%, rgba(255,255,255,.10), transparent 60%),
      linear-gradient(165deg, var(--plum) 0%, var(--plum-deep) 100%);
    color:#FBEAF4;
    display:flex;
    flex-direction:column;
    overflow:hidden;
  }

  .mark{
    display:flex;
    align-items:center;
    gap:10px;
    font-family:"Fraunces", serif;
    font-size:19px;
    font-weight:500;
    letter-spacing:.2px;
  }

  .mark svg{ flex-shrink:0; }

  .brand h1{
    font-family:"Fraunces", serif;
    font-weight:500;
    font-size:clamp(28px, 3.4vw, 34px);
    line-height:1.18;
    margin:34px 0 12px;
    max-width:15ch;
  }

  .brand p{
    margin:0;
    font-size:14.5px;
    line-height:1.6;
    color:#EACBDD;
    max-width:30ch;
  }

  /* receipt stub illustration */
  .stub-wrap{
    margin-top:auto;
    padding-top:34px;
    display:flex;
    justify-content:center;
  }

  .stub{
    width:230px;
    background:#fff;
    border-radius:14px 14px 4px 4px;
    padding:16px 18px 18px;
    color:var(--ink);
    transform:rotate(-6deg) translateY(14px);
    box-shadow:0 18px 34px -14px rgba(0,0,0,.35);
    position:relative;
  }

  .stub::after{
    /* perforated tear edge */
    content:"";
    position:absolute;
    left:0; right:0; bottom:-1px;
    height:14px;
    background:
      radial-gradient(circle at 8px 0, transparent 6px, #fff 6.5px) top left/16px 14px repeat-x;
  }

  .stub .stub-top{
    display:flex;
    justify-content:space-between;
    align-items:flex-start;
    margin-bottom:10px;
  }

  .stub .stub-brand{
    font-family:"Fraunces", serif;
    font-weight:600;
    font-size:13px;
    color:var(--pink-dark);
  }

  .stub .stub-tag{
    font-size:9.5px;
    font-weight:700;
    color:#1B8A5A;
    background:#E6F6EE;
    padding:2px 7px;
    border-radius:20px;
  }

  .stub .stub-row{
    display:flex;
    justify-content:space-between;
    font-size:11px;
    color:var(--ink-soft);
    padding:5px 0;
    border-top:1px dashed var(--line);
  }

  .stub .stub-row:first-of-type{ border-top:none; }

  .stub .stub-total{
    margin-top:8px;
    padding-top:10px;
    border-top:1px dashed var(--line);
    font-family:"Fraunces", serif;
    font-size:18px;
    font-weight:600;
    color:var(--pink-dark);
  }

  /* perforated divider between panels */
  .card::before{
    content:"";
    position:absolute;
    top:0; bottom:0;
    left:calc(1.05fr / 2.05 * 0 + 0px); /* unused fallback */
  }

  /* ---------- Right: form panel ---------- */
  .form-panel{
    padding:48px 44px;
    display:flex;
    flex-direction:column;
    justify-content:center;
  }

  .form-panel h2{
    margin:0 0 6px;
    font-size:22px;
    font-weight:700;
    letter-spacing:-.2px;
  }

  .form-panel .sub{
    margin:0 0 30px;
    font-size:14px;
    color:var(--ink-soft);
  }

  .field{
    margin-bottom:18px;
  }

  .field label{
    display:block;
    font-size:13px;
    font-weight:600;
    margin-bottom:7px;
    color:var(--ink);
  }

  .field .control{
    position:relative;
  }

  .field input{
    width:100%;
    font-family:inherit;
    font-size:15px;
    padding:12px 14px;
    border-radius:12px;
    border:1.5px solid var(--line);
    background:#FFFBFD;
    color:var(--ink);
    outline:none;
    transition:border-color .15s ease, background .15s ease;
  }

  .field input::placeholder{ color:#C9AFC0; }

  .field input:focus{
    border-color:var(--pink);
    background:#fff;
  }

  .field input:focus-visible{
    outline:2px solid var(--pink);
    outline-offset:1px;
  }

  .toggle-pass{
    position:absolute;
    right:6px;
    top:50%;
    transform:translateY(-50%);
    background:none;
    border:none;
    padding:6px;
    cursor:pointer;
    color:var(--ink-soft);
    display:flex;
    border-radius:8px;
  }

  .toggle-pass:hover{ color:var(--pink-dark); }
  .toggle-pass:focus-visible{ outline:2px solid var(--pink); outline-offset:1px; }

  button.submit{
    width:100%;
    margin-top:8px;
    padding:13px 16px;
    border:none;
    border-radius:12px;
    background:var(--pink);
    color:#fff;
    font-family:inherit;
    font-size:15px;
    font-weight:700;
    cursor:pointer;
    transition:background .15s ease, transform .05s ease;
  }

  button.submit:hover{ background:var(--pink-dark); }
  button.submit:active{ transform:scale(.99); }
  button.submit:focus-visible{ outline:2px solid var(--plum); outline-offset:2px; }

  .hint{
    margin-top:22px;
    font-size:12.5px;
    color:var(--ink-soft);
    text-align:center;
  }

  /* ---------- Responsive ---------- */
  @media (max-width: 780px){
    .card{
      grid-template-columns:1fr;
    }
    .brand{
      padding:32px 28px 26px;
    }
    .brand h1{ margin:20px 0 8px; font-size:24px; }
    .brand p{ display:none; }
    .stub-wrap{
      margin-top:22px;
      padding-top:0;
      justify-content:flex-start;
    }
    .stub{
      transform:rotate(-3deg);
      width:180px;
    }
    .form-panel{ padding:34px 28px 40px; }
  }
</style>
</head>
<body>

  <div class="stage">
    <div class="card">

      <!-- Brand / left panel -->
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

      <!-- Form / right panel -->
      <div class="form-panel">
        <h2>Masuk ke akun</h2>
        <p class="sub">Khusus untuk admin dan petugas sekolah.</p>

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