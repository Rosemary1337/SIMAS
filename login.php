<?php
session_start();

$host = "localhost";
$user = "root";
$pass = "";
$dbname = "simas";

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
  die("Koneksi gagal: " . $conn->connect_error);
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $username = trim($_POST['username']);
  $password = trim($_POST['password']);

  $stmt = $conn->prepare("SELECT * FROM users WHERE username=? AND password=MD5(?)");
  $stmt->bind_param("ss", $username, $password);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['role'] = $user['role'];
    $_SESSION['nama'] = $user['nama'];

    if ($user['role'] == 'guru') {
      header("Location: dashboard_guru.php");
    } else {
      header("Location: dashboard_siswa.php");
    }
    exit;
  } else {
    $message = "Username atau password salah!";
  }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Login - SIMAS</title>
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
    .login-container {
      background: rgba(255,255,255,0.03);
      border: 1px solid rgba(255,255,255,0.08);
      padding: 2rem 3rem;
      border-radius: 12px;
      width: 100%;
      max-width: 380px;
      text-align: center;
      box-shadow: 0 0 20px rgba(255,255,255,0.05);
    }
    h2 {
      background: linear-gradient(90deg, #ffffff, #bdbdbd);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      margin-bottom: 1.5rem;
    }
    input {
      width: 100%;
      padding: 10px;
      margin: 10px 0;
      background: rgba(255,255,255,0.05);
      border: 1px solid rgba(255,255,255,0.08);
      border-radius: 8px;
      color: #ffffffff;
    }
    button {
      width: 100%;
      padding: 10px;
      background: linear-gradient(90deg, #ffffff, #bdbdbd);
      color: #000;
      border: none;
      border-radius: 8px;
      font-weight: 600;
      cursor: pointer;
      transition: 0.3s;
    }
    button:hover {
      opacity: 0.85;
    }
    p.error {
      color: #ff6b6b;
      margin-top: 0.5rem;
      font-size: 0.9rem;
    }
  </style>
</head>
<body>
  <div class="login-container">
    <h2>Login SIMAS</h2>
    <form method="POST">
      <input type="text" name="username" placeholder="Username" required />
      <input type="password" name="password" placeholder="Password" required />
      <button type="submit">Masuk</button>
      <?php if ($message): ?>
        <p class="error"><?= htmlspecialchars($message) ?></p>
      <?php endif; ?>
    </form>
  </div>
</body>
</html>
