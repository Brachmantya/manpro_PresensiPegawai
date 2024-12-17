<?php
// Mulai session
session_start();

// Konfigurasi Database
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "presensicafe";

// Koneksi ke Database
$conn = new mysqli($servername, $username, $password, $dbname);

// Periksa koneksi  
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Logika Login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'login') {
  $nama = $_POST['nama'];
  $nomor_hp = $_POST['nomor_hp'];

  // Periksa di tabel Pegawai berdasarkan nama dan nomor_hp
  $sqlPegawai = "SELECT id_pegawai, nama FROM Pegawai WHERE nomor_hp = ? AND nama = ?";
  $stmtPegawai = $conn->prepare($sqlPegawai);
  $stmtPegawai->bind_param("ss", $nomor_hp, $nama);
  $stmtPegawai->execute();
  $resultPegawai = $stmtPegawai->get_result();

  if ($resultPegawai->num_rows > 0) {
      // Login berhasil, set session dan redirect ke halaman pegawai
      $row = $resultPegawai->fetch_assoc();
      $_SESSION['id_pegawai'] = $row['id_pegawai'];
      $_SESSION['nama'] = $row['nama'];
      $_SESSION['nomor_hp'] = $nomor_hp;  // Menyimpan nomor HP dalam session
      header("Location: ../Pegawai/pegawaiDash.php");
      exit();
  }

  // Periksa di tabel Pemilik jika pegawai tidak ditemukan
  $sqlPemilik = "SELECT id_pemilik, nama FROM Pemilik WHERE nomor_hp = ? AND nama = ?";
  $stmtPemilik = $conn->prepare($sqlPemilik);
  $stmtPemilik->bind_param("ss", $nomor_hp, $nama);
  $stmtPemilik->execute();
  $resultPemilik = $stmtPemilik->get_result();

  if ($resultPemilik->num_rows > 0) {
      // Login berhasil, set session dan redirect ke halaman pemilik
      $row = $resultPemilik->fetch_assoc();
      $_SESSION['id_pemilik'] = $row['id_pemilik'];
      $_SESSION['nama'] = $row['nama'];
      header("Location: ../Pemilik/pemilikdash.php");
      exit();
  }

  // Jika login gagal
  $loginError = "Nama atau nomor HP salah!";
}
// Logika Reset Password
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'resetPassword') {
    $email = $_POST['email'];
    $newPassword = $_POST['newPassword'];

    // Update password di tabel Pemilik atau Pegawai
    $sqlUpdatePemilik = "UPDATE Pemilik SET password = ? WHERE email = ?";
    $stmtPemilik = $conn->prepare($sqlUpdatePemilik);
    $stmtPemilik->bind_param("ss", $newPassword, $email);
    $stmtPemilik->execute();

    $sqlUpdatePegawai = "UPDATE Pegawai SET email = ? WHERE email = ?";
    $stmtPegawai = $conn->prepare($sqlUpdatePegawai);
    $stmtPegawai->bind_param("ss", $newPassword, $email);
    $stmtPegawai->execute();

    $resetSuccess = "Password berhasil direset!";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login - Coffee Shop</title>
  <link rel="stylesheet" href="mainstyle.css">
  <style>
    body {
      font-family: 'Arial', sans-serif;
      background-color: #f7f7f7;
      margin: 0;
      padding: 0;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
    }

    .login-container {
      background-color: white;
      border-radius: 8px;
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
      width: 350px;
      padding: 20px;
      text-align: center;
    }

    h1 {
      font-size: 24px;
      color: #333;
    }

    .input-group {
      margin-bottom: 15px;
    }

    label {
      font-size: 14px;
      color: #666;
      display: block;
      margin-bottom: 5px;
    }

    input[type="text"], input[type="password"] {
      width: 100%;
      padding: 10px;
      border-radius: 5px;
      border: 1px solid #ddd;
      font-size: 14px;
    }

    button {
      width: 100%;
      padding: 10px;
      color: #fff;
      background: #d2691e;
      border: none;
      border-radius: 5px;
      font-size: 16px;
      cursor: pointer;
    }

    button:hover {
      background: #a0551c;
    }

    .error-message {
      color: red;
      font-size: 14px;
    }
    </style>
</head>
<body>
  <div class="login-container">
    <h1>Login to Coffee Shop</h1>
    <form method="POST">
      <input type="hidden" name="action" value="login">
      <div class="input-group">
        <label for="nama">Nama</label>
        <input type="text" id="nama" name="nama" placeholder="Masukkan nama" required>
      </div>
      <div class="input-group">
        <label for="nomor_hp">Nomor HP</label>
        <input type="text" id="nomor_hp" name="nomor_hp" placeholder="Masukkan nomor HP" required>
      </div>
      <button type="submit">Login</button>
      <?php if (isset($loginError)): ?>
        <p class="error-message"><?= $loginError ?></p>
      <?php endif; ?>
    </form>
  </div>
</body>
</html>