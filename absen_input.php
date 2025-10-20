<?php
session_start();
date_default_timezone_set('Asia/Jakarta');
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'siswa') {
  header("Location: login.php");
  exit;
}

$host = "localhost";
$user = "root";
$pass = "";
$dbname = "simas";
$conn = new mysqli($host, $user, $pass, $dbname);

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $kode = strtoupper(trim($_POST['kode']));
  $tanggal = date("Y-m-d");
  $id_siswa = $_SESSION['user_id'];


  $cek = $conn->query("SELECT * FROM absensi_kode WHERE kode='$kode' AND tanggal='$tanggal' AND expired_at > NOW()");
  if ($cek->num_rows === 1) {

    $sudah = $conn->query("SELECT * FROM absensi WHERE user_id=$id_siswa AND tanggal='$tanggal'");
    if ($sudah->num_rows === 0) {
      $conn->query("INSERT INTO absensi (user_id, tanggal, status) VALUES ($id_siswa, '$tanggal', 'Hadir')");
      $message = "Absensi berhasil dicatat!";
    } else {
      $message = "Kamu sudah absen hari ini.";
    }
  } else {
    $message = "Kode tidak valid atau sudah kedaluwarsa.";
  }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Input Absensi - SIMAS</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
  <style>
    body {
      background: #0f0f0f;
      color: #e5e5e5;
      font-family: 'Inter', sans-serif;
      display: flex;
      align-items: center;
      justify-content: center;
      height: 100vh;
    }
    .box {
      background: rgba(255,255,255,0.03);
      border: 1px solid rgba(255,255,255,0.08);
      padding: 2rem;
      border-radius: 10px;
      width: 100%;
      max-width: 360px;
      text-align: center;
    }
    input {
      width: 100%;
      padding: 10px;
      margin: 10px 0;
      background: rgba(255,255,255,0.05);
      border: 1px solid rgba(255,255,255,0.08);
      border-radius: 8px;
      color: #fff;
    }
    button {
      width: 100%;
      padding: 10px;
      background: linear-gradient(90deg, #fff, #bdbdbd);
      color: #000;
      border: none;
      border-radius: 8px;
      font-weight: 600;
      cursor: pointer;
    }
    p.msg { margin-top: 10px; font-size: 0.95rem; }
  </style>
</head>
<body>
  <div class="box">
    <h2>Input Kode Absensi</h2>
    <form method="POST">
      <input type="text" name="kode" placeholder="Masukkan kode (contoh: SIMAS-7FQ2B9)" required>
      <button type="submit">Kirim</button>
    </form>
    <?php if ($message): ?>
      <p class="msg"><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>
  </div>
</body>
</html>
