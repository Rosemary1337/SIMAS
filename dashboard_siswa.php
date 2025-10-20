<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
date_default_timezone_set('Asia/Jakarta');

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'siswa') {
  header("Location: login.php");
  exit;
}

$conn = new mysqli("localhost", "root", "", "simas");
if ($conn->connect_error) die("Koneksi gagal: " . $conn->connect_error);

$id_siswa = $_SESSION['user_id'];

$kelas_id = $conn->query("SELECT kelas_id FROM users WHERE id=$id_siswa")->fetch_assoc()['kelas_id'] ?? null;

$kelas_nama = $conn->query("SELECT nama_kelas FROM kelas WHERE id=$kelas_id")->fetch_assoc()['nama_kelas'] ?? '-';

$jadwal  = $conn->query("SELECT * FROM jadwal WHERE kelas_id=$kelas_id ORDER BY FIELD(hari,'Senin','Selasa','Rabu','Kamis','Jumat','Sabtu')");
$tugas   = $conn->query("SELECT * FROM tugas WHERE kelas_id=$kelas_id ORDER BY deadline ASC");
$riwayat = $conn->query("SELECT * FROM absensi WHERE siswa_id=$id_siswa ORDER BY tanggal DESC LIMIT 10");
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard Siswa - SIMAS</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
  <style>
    body { margin:0; font-family:'Inter',sans-serif; background:#0f0f0f; color:#f1f1f1; }
    header { background:rgba(255,255,255,0.05); border-bottom:1px solid rgba(255,255,255,0.1); padding:1rem 2rem; text-align:center; font-weight:700; }
    .container { max-width:900px; margin:2rem auto; padding:1rem; }
    .section { background:rgba(255,255,255,0.03); border:1px solid rgba(255,255,255,0.08); border-radius:10px; padding:1.5rem; margin-bottom:2rem; }
    a.button { display:inline-block; background:linear-gradient(90deg,#fff,#bdbdbd); color:#000; padding:10px 20px; border-radius:6px; font-weight:600; text-decoration:none; }
    table { width:100%; border-collapse:collapse; margin-top:10px; }
    th,td { padding:8px; border-bottom:1px solid rgba(255,255,255,0.1); }
    input[type=file]{margin-top:10px;}
  </style>
</head>
<body>
  <header>Dashboard Siswa - SIMAS</header>
  <div class="container">
    <div class="section">
      <h2>Selamat datang, <?= htmlspecialchars($_SESSION['nama']) ?> 👋</h2>
      <p>Kelas: <strong><?= htmlspecialchars($kelas_nama) ?></strong></p>
    </div>

    <div class="section">
      <h2>Daftar Tugas</h2>
      <?php if ($tugas && $tugas->num_rows > 0): ?>
      <table>
        <tr><th>Judul</th><th>Deskripsi</th><th>Deadline</th><th>Aksi</th></tr>
        <?php while ($t = $tugas->fetch_assoc()): ?>
        <tr>
          <td><?= htmlspecialchars($t['judul']) ?></td>
          <td><?= htmlspecialchars($t['deskripsi']) ?></td>
          <td><?= htmlspecialchars($t['deadline']) ?></td>
          <td><a href="tugas_upload.php?id=<?= $t['id'] ?>" class="button">Kumpulkan</a></td>
        </tr>
        <?php endwhile; ?>
      </table>
      <?php else: ?>
        <p>Belum ada tugas.</p>
      <?php endif; ?>
    </div>

    <div class="section">
      <h2>Jadwal Pelajaran</h2>
      <?php if ($jadwal && $jadwal->num_rows > 0): ?>
      <table>
        <tr><th>Hari</th><th>Mata Pelajaran</th><th>Jam</th></tr>
        <?php while ($j = $jadwal->fetch_assoc()): ?>
        <tr>
          <td><?= htmlspecialchars($j['hari']) ?></td>
          <td><?= htmlspecialchars($j['mapel']) ?></td>
          <td><?= htmlspecialchars($j['jam_mulai']) ?> - <?= htmlspecialchars($j['jam_selesai']) ?></td>
        </tr>
        <?php endwhile; ?>
      </table>
      <?php else: ?>
        <p>Belum ada jadwal pelajaran.</p>
      <?php endif; ?>
    </div>

    <div class="section">
      <h2>Riwayat Absensi</h2>
      <?php if ($riwayat && $riwayat->num_rows > 0): ?>
      <table>
        <tr><th>Tanggal</th><th>Status</th><th>Keterangan</th></tr>
        <?php while ($r = $riwayat->fetch_assoc()): ?>
        <tr>
          <td><?= htmlspecialchars($r['tanggal']) ?></td>
          <td><?= htmlspecialchars($r['status']) ?></td>
          <td><?= htmlspecialchars($r['keterangan'] ?? '-') ?></td>
        </tr>
        <?php endwhile; ?>
      </table>
      <?php else: ?>
        <p>Belum ada riwayat absensi.</p>
      <?php endif; ?>
    </div>
  </div>
</body>
</html>
