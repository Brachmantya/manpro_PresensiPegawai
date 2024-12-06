<?php
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
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Periksa di tabel Pegawai
    $sqlPegawai = "SELECT id_pegawai FROM Pegawai WHERE nama = ? AND email = ?";
    $stmtPegawai = $conn->prepare($sqlPegawai);
    $stmtPegawai->bind_param("ss", $username, $password);
    $stmtPegawai->execute();
    $resultPegawai = $stmtPegawai->get_result();

    if ($resultPegawai->num_rows > 0) {
        // Redirect ke halaman pegawai
        header("Location: ../Pegawai/Pegawaidash.php");
        exit();
    }

    // Periksa di tabel Pemilik
    $sqlPemilik = "SELECT id_pemilik FROM Pemilik WHERE nama = ? AND password = ?";
    $stmtPemilik = $conn->prepare($sqlPemilik);
    $stmtPemilik->bind_param("ss", $username, $password);
    $stmtPemilik->execute();
    $resultPemilik = $stmtPemilik->get_result();

    if ($resultPemilik->num_rows > 0) {
        // Redirect ke halaman owner
        header("Location:../Pemilik/pemilikdash.php");
        exit();
    }

    // Jika username tidak ditemukan
    $loginError = "Username atau password salah!";
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
    /* Tambahan style untuk pesan error */
    .username-error {
      color: green;
      font-size: 12px;
      margin-top: 5px;
      display: none;
    }
  </style>
</head>
<body>
  <div class="login-container">
  <h1><i class="coffee-icon">☕</i> Login Coffee Shop</h1>
    <form method="POST" id="loginForm">
      <input type="hidden" name="action" value="login">
      <div class="input-group">
        <label for="username">Username or phone number</label>
        <input type="text" id="username" name="username" placeholder="Masukkan username" required>
        <div id="usernameError" class="username-error">Login untuk Pegawai</div>
      </div>
      <div class="input-group">
        <label for="password">Password</label>
        <input type="password" id="password" name="password" placeholder="Masukkan password" required>
      </div>
      <button type="submit">Login</button>
      <?php if (isset($loginError)): ?>
        <p style="color: red;"><?= $loginError ?></p>
      <?php endif; ?>
    </form>
    <div class="forgot-password">
      <a href="#" onclick="openForgotPasswordModal()">Forgot Password?</a>
    </div>
  </div>

  <!-- Modal Forgot Password -->
  <div id="forgotPasswordModal" class="modal" style="display: none;">
    <div class="modal-content">
      <span class="close" onclick="closeForgotPasswordModal()">&times;</span>
      <h3>Reset Password</h3>
      <form method="POST">
        <input type="hidden" name="action" value="resetPassword">
        <div class="input-group">
          <label for="email">Email Anda</label>
          <input type="email" id="email" name="email" placeholder="Masukkan email terdaftar" required>
        </div>
        <div class="input-group">
          <label for="newPassword">Password Baru</label>
          <input type="password" id="newPassword" name="newPassword" placeholder="Masukkan password baru" required>
        </div>
        <button type="submit">Reset Password</button>
        <?php if (isset($resetSuccess)): ?>
          <p style="color: green;"><?= $resetSuccess ?></p>
        <?php endif; ?>
      </form>
    </div>
  </div>

  <script>
    function openForgotPasswordModal() {
      document.getElementById('forgotPasswordModal').style.display = 'block';
    }

    function closeForgotPasswordModal() {
      document.getElementById('forgotPasswordModal').style.display = 'none';
    }

    // Fungsi validasi username secara real-time
    document.getElementById('username').addEventListener('input', function() {
        const usernameInput = this;
        const passwordInput = document.getElementById('password');
        const submitButton = document.querySelector('button[type="submit"]');
        const errorMessage = document.getElementById('usernameError');

        // Cek apakah username hanya berisi angka
        const isNumericOnly = /^[0-9]+$/.test(usernameInput.value);

        if (isNumericOnly) {
            // Jika username hanya angka, sembunyikan password
            passwordInput.style.display = 'none';
            passwordInput.disabled = true;
            submitButton.disabled = true;
            errorMessage.style.display = 'block';
        } else {
            // Jika username valid, tampilkan kembali password
            passwordInput.style.display = 'block';
            passwordInput.disabled = false;
            submitButton.disabled = false;
            errorMessage.style.display = 'none';
        }
    });
  </script>
</body>
</html>