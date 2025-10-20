<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'siswa') {
  header("Location: login.php");
  exit;
}

$conn = new mysqli("localhost", "root", "", "simas");
if ($conn->connect_error) die("Koneksi gagal: " . $conn->connect_error);

$tugas_id = intval($_GET['id']);
$siswa_id = $_SESSION['user_id'];

$tugas = $conn->query("SELECT * FROM tugas WHERE id=$tugas_id")->fetch_assoc();
if (!$tugas) die("Tugas tidak ditemukan.");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $dir = "uploads/";
  if (!is_dir($dir)) mkdir($dir);
  $file = $_FILES['file']['name'];
  $tmp = $_FILES['file']['tmp_name'];
  $path = $dir . time() . "_" . basename($file);
  if (move_uploaded_file($tmp, $path)) {
    $conn->query("INSERT INTO pengumpulan_tugas (tugas_id, siswa_id, file_path) VALUES ($tugas_id,$siswa_id,'$path')");
    $msg = "Tugas berhasil dikumpulkan!";
  } else {
    $msg = "Gagal mengupload file!";
  }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Kumpulkan Tugas</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
<style>
body{margin:0;font-family:'Inter',sans-serif;background:#0f0f0f;color:#f1f1f1;}
.container{max-width:600px;margin:3rem auto;padding:2rem;background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.08);border-radius:10px;}
input[type=file]{margin:10px 0;}
button{padding:8px 14px;background:linear-gradient(90deg,#fff,#bdbdbd);color:#000;border:none;border-radius:6px;font-weight:600;cursor:pointer;}
a{color:#bdbdbd;text-decoration:none;}
</style>
</head>
<body>
<div class="container">
  <h2>Kumpulkan Tugas: <?= htmlspecialchars($tugas['judul']) ?></h2>
  <p><?= htmlspecialchars($tugas['deskripsi']) ?></p>
  <form method="POST" enctype="multipart/form-data">
    <label>Pilih File:</label>
    <input type="file" name="file" required>
    <button type="submit">Upload</button>
  </form>
  <?php if (!empty($msg)) echo "<p>$msg</p>"; ?>
  <p><a href="dashboard_siswa.php">⬅ Kembali ke Dashboard</a></p>
</div>
</body>
</html>
