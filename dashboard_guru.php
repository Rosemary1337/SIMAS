<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
date_default_timezone_set('Asia/Jakarta');

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'guru') {
  header("Location: login.php");
  exit;
}

$conn = new mysqli("localhost", "root", "", "simas");
if ($conn->connect_error) die("Koneksi gagal: " . $conn->connect_error);

$guru_id = $_SESSION['user_id'];
$nama_guru = $_SESSION['nama'] ?? 'Guru';

// Tambah tugas
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_tugas'])) {
  $judul = $_POST['judul'];
  $deskripsi = $_POST['deskripsi'];
  $deadline = $_POST['deadline'];
  $kelas_id = $_POST['kelas_id'];
  $conn->query("INSERT INTO tugas (judul, deskripsi, deadline, guru_id, kelas_id) 
                VALUES ('$judul','$deskripsi','$deadline',$guru_id,$kelas_id)");
  header("Location: dashboard_guru.php");
  exit;
}

// Tambah jadwal
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_jadwal'])) {
  $hari = $_POST['hari'];
  $jam_mulai = $_POST['jam_mulai'];
  $jam_selesai = $_POST['jam_selesai'];
  $mapel = $_POST['mapel'];
  $kelas_id = $_POST['kelas_id'];
  $conn->query("INSERT INTO jadwal (hari, jam_mulai, jam_selesai, mapel, guru_id, kelas_id)
                VALUES ('$hari','$jam_mulai','$jam_selesai','$mapel',$guru_id,$kelas_id)");
  header("Location: dashboard_guru.php");
  exit;
}

// Input absensi
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['input_absensi'])) {
  $siswa_id = $_POST['siswa_id'];
  $status = $_POST['status'];
  $tanggal = date('Y-m-d');
  $cek = $conn->query("SELECT id FROM absensi WHERE siswa_id=$siswa_id AND tanggal='$tanggal'");
  if ($cek->num_rows === 0) {
    $conn->query("INSERT INTO absensi (tanggal, siswa_id, guru_id, status) 
                  VALUES ('$tanggal',$siswa_id,$guru_id,'$status')");
  } else {
    $conn->query("UPDATE absensi SET status='$status' WHERE siswa_id=$siswa_id AND tanggal='$tanggal'");
  }
  header("Location: dashboard_guru.php");
  exit;
}

$kelas = $conn->query("SELECT * FROM kelas");
$tugas = $conn->query("SELECT t.*, k.nama_kelas FROM tugas t JOIN kelas k ON t.kelas_id=k.id ORDER BY t.deadline ASC");
$jadwal = $conn->query("SELECT j.*, k.nama_kelas FROM jadwal j JOIN kelas k ON j.kelas_id=k.id ORDER BY FIELD(hari,'Senin','Selasa','Rabu','Kamis','Jumat','Sabtu')");
$absensi_terbaru = $conn->query("SELECT a.*, u.nama FROM absensi a JOIN users u ON a.siswa_id=u.id ORDER BY a.tanggal DESC LIMIT 10");
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard Guru - SIMAS</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
<style>
body {margin:0;font-family:'Inter',sans-serif;background:#0f0f0f;color:#f1f1f1;}
header {background:rgba(255,255,255,0.05);border-bottom:1px solid rgba(255,255,255,0.1);padding:1rem;text-align:center;font-weight:700;}
.container{max-width:1000px;margin:2rem auto;padding:1rem;}
.section{background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.08);border-radius:10px;padding:1.5rem;margin-bottom:2rem;}
table{width:100%;border-collapse:collapse;margin-top:10px;}
th,td{padding:8px;border-bottom:1px solid rgba(255,255,255,0.1);}
input,select,textarea{margin-top:5px;padding:8px;width:100%;border-radius:6px;border:none;}
button{margin-top:10px;padding:8px 14px;background:linear-gradient(90deg,#fff,#bdbdbd);color:#000;border:none;border-radius:6px;font-weight:600;cursor:pointer;}
</style>
</head>
<body>
<header>Dashboard Guru - SIMAS</header>
<div class="container">

<div class="section">
<h2>Selamat datang, <?= htmlspecialchars($nama_guru) ?> 👋</h2>
</div>

<div class="section">
<h2>Tambah Tugas</h2>
<form method="POST">
  <input type="hidden" name="add_tugas" value="1">
  <label>Judul</label>
  <input type="text" name="judul" required>
  <label>Deskripsi</label>
  <textarea name="deskripsi"></textarea>
  <label>Deadline</label>
  <input type="date" name="deadline" required>
  <label>Kelas</label>
  <select name="kelas_id" required>
    <?php while($k=$kelas->fetch_assoc()): ?>
      <option value="<?= $k['id'] ?>"><?= htmlspecialchars($k['nama_kelas']) ?></option>
    <?php endwhile; ?>
  </select>
  <button type="submit">Tambah</button>
</form>
</div>

<div class="section">
<h2>Daftar Tugas</h2>
<table>
<tr><th>Judul</th><th>Kelas</th><th>Deadline</th></tr>
<?php while($t=$tugas->fetch_assoc()): ?>
<tr>
<td><?= htmlspecialchars($t['judul']) ?></td>
<td><?= htmlspecialchars($t['nama_kelas']) ?></td>
<td><?= htmlspecialchars($t['deadline']) ?></td>
</tr>
<?php endwhile; ?>
</table>
</div>

<div class="section">
<h2>Tambah Jadwal</h2>
<form method="POST">
  <input type="hidden" name="add_jadwal" value="1">
  <label>Hari</label>
  <select name="hari" required>
    <option>Senin</option><option>Selasa</option><option>Rabu</option>
    <option>Kamis</option><option>Jumat</option><option>Sabtu</option>
  </select>
  <label>Jam Mulai</label>
  <input type="time" name="jam_mulai" required>
  <label>Jam Selesai</label>
  <input type="time" name="jam_selesai" required>
  <label>Mata Pelajaran</label>
  <input type="text" name="mapel" required>
  <label>Kelas</label>
  <select name="kelas_id" required>
    <?php $kelas2=$conn->query("SELECT * FROM kelas"); while($k=$kelas2->fetch_assoc()): ?>
      <option value="<?= $k['id'] ?>"><?= htmlspecialchars($k['nama_kelas']) ?></option>
    <?php endwhile; ?>
  </select>
  <button type="submit">Tambah</button>
</form>
</div>

<div class="section">
<h2>Input Absensi</h2>
<form method="POST">
  <input type="hidden" name="input_absensi" value="1">
  <label>Siswa</label>
  <select name="siswa_id" required>
    <?php
    $siswa = $conn->query("SELECT id,nama FROM users WHERE role='siswa'");
    while($s=$siswa->fetch_assoc()): ?>
      <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['nama']) ?></option>
    <?php endwhile; ?>
  </select>
  <label>Status</label>
  <select name="status">
    <option>Hadir</option><option>Izin</option><option>Sakit</option><option>Alpha</option>
  </select>
  <button type="submit">Simpan</button>
</form>
</div>

<div class="section">
<h2>Riwayat Absensi Terbaru</h2>
<table>
<tr><th>Tanggal</th><th>Nama Siswa</th><th>Status</th></tr>
<?php while($a=$absensi_terbaru->fetch_assoc()): ?>
<tr>
<td><?= htmlspecialchars($a['tanggal']) ?></td>
<td><?= htmlspecialchars($a['nama']) ?></td>
<td><?= htmlspecialchars($a['status']) ?></td>
</tr>
<?php endwhile; ?>
</table>
</div>

</div>
</body>
</html>
