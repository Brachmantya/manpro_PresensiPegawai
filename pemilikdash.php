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

// Handle Tambah/Edit Data Pegawai
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'saveEmployee') {
    $employee_id = $_POST['employee_id'];
    $employee_name = $_POST['employee_name'];
    $employee_phone = $_POST['employee_phone'];
    $employee_email = $_POST['employee_email'];
    $employee_address = $_POST['employee_address'];
    $employee_position = $_POST['employee_position'];
    $employee_salary = $_POST['employee_salary'];

    if ($employee_id) {
        // Update data pegawai
        $sql = "UPDATE Pegawai 
                SET nama = ?, nomor_hp = ?, email = ?, alamat = ?, posisi = ?, gaji_perJam = ? 
                WHERE id_pegawai = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            "sssssii",
            $employee_name,
            $employee_phone,
            $employee_email,
            $employee_address,
            $employee_position,
            $employee_salary,
            $employee_id
        );
    } else {
        // Tambah data pegawai
        $sql = "INSERT INTO Pegawai (nama, nomor_hp, email, alamat, posisi, gaji_perJam) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            "sssssi",
            $employee_name,
            $employee_phone,
            $employee_email,
            $employee_address,
            $employee_position,
            $employee_salary
        );
    }
    $stmt->execute();
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Handle Hapus Data Pegawai
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'deleteEmployee') {
    $employee_id = $_POST['employee_id'];
    $sql = "DELETE FROM Pegawai WHERE id_pegawai = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $employee_id);
    $stmt->execute();
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard Pemilik - Presensi Cafe</title>
  <link rel="stylesheet" href="ownerstyle.css">
</head>
<body>
  <div class="owner-dashboard-container">
    <header>
      <h1>Dashboard Pemilik</h1>
    </header>
    <main>
      <!-- Informasi Pegawai -->
      <section>
        <h2>Informasi Pegawai</h2>
        <div class="button-container">
          <button class="primary-btn" onclick="openModal('employeeInfoModal')">Tambah Data Pegawai</button>
        </div>
        <table>
          <thead>
            <tr>
              <th>Nama</th>
              <th>Nomor HP</th>
              <th>Email</th>
              <th>Alamat</th>
              <th>Posisi</th>
              <th>Gaji Per Jam (Rp)</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody id="employeeInfoTable">
            <?php
            $sql = "SELECT id_pegawai, nama, nomor_hp, email, alamat, posisi, gaji_perJam FROM Pegawai";
            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>
                            <td>{$row['nama']}</td>
                            <td>{$row['nomor_hp']}</td>
                            <td>{$row['email']}</td>
                            <td>{$row['alamat']}</td>
                            <td>{$row['posisi']}</td>
                            <td>" . number_format($row['gaji_perJam'], 0, ',', '.') . "</td>
                            <td>
                              <button class='secondary-btn' onclick='editRow({$row['id_pegawai']})'>Edit</button>
                              <form method='POST' style='display:inline-block;'>
                                  <input type='hidden' name='action' value='deleteEmployee'>
                                  <input type='hidden' name='employee_id' value='{$row['id_pegawai']}'>
                                  <button type='submit' class='danger-btn'>Hapus</button>
                              </form>
                            </td>
                          </tr>";
                }
            } else {
                echo "<tr><td colspan='7'>Tidak ada data</td></tr>";
            }
            ?>
          </tbody>
        </table>
      </section>

      <!-- Laporan Gaji -->
    <section>
      <h2>Laporan Gaji</h2>
      <table>
        <thead>
          <tr>
            <th>Nama Pegawai</th>
            <th>Tanggal</th>
            <th>Jumlah Hari Kerja</th>
            <th>Jumlah Jam Kerja</th>
            <th>Total Gaji (Rp)</th>
          </tr>
        </thead>
        <tbody>
          <?php
          $sql = "SELECT 
                      Pegawai.nama, 
                      Laporan_Gaji.tanggal, 
                      Laporan_Gaji.jml_hariKerja, 
                      Laporan_Gaji.jml_jamKerja, 
                      Laporan_Gaji.total_gaji
                  FROM Laporan_Gaji
                  JOIN Pegawai ON Laporan_Gaji.id_pegawai = Pegawai.id_pegawai";
          $result = $conn->query($sql);

          if ($result->num_rows > 0) {
              while ($row = $result->fetch_assoc()) {
                  echo "<tr>
                          <td>{$row['nama']}</td>
                          <td>{$row['tanggal']}</td>
                          <td>{$row['jml_hariKerja']}</td>
                          <td>{$row['jml_jamKerja']}</td>
                          <td>" . number_format($row['total_gaji'], 0, ',', '.') . "</td>
                        </tr>";
              }
          } else {
              echo "<tr><td colspan='6'>Tidak ada data</td></tr>";
          }
          ?>
        </tbody>
      </table>
    </section>

    </main>
  </div>

  <!-- Modal: Tambah Data Pegawai -->
  <div id="employeeInfoModal" class="modal" style="display: none;">
    <div class="modal-content">
      <span class="close" onclick="closeModal('employeeInfoModal')">&times;</span>
      <h3>Tambah/Edit Data Pegawai</h3>
      <form method="POST">
        <input type="hidden" name="action" value="saveEmployee">
        <input type="hidden" id="employee_id" name="employee_id">
        <label for="employee_name">Nama:</label>
        <input type="text" id="employee_name" name="employee_name" required>
        <label for="employee_phone">Nomor HP:</label>
        <input type="text" id="employee_phone" name="employee_phone" required>
        <label for="employee_email">Email:</label>
        <input type="text" id="employee_email" name="employee_email" required>
        <label for="employee_address">Alamat:</label>
        <input type="text" id="employee_address" name="employee_address" required>
        <label for="employee_position">Posisi:</label>
        <input type="text" id="employee_position" name="employee_position" required>
        <label for="employee_salary">Gaji Per Jam (Rp):</label>
        <input type="text" id="employee_salary" name="employee_salary" required>
        <button type="submit" class="primary-btn">Simpan</button>
      </form>
    </div>
  </div>

  <script>
    function openModal(modalId) {
      document.getElementById(modalId).style.display = 'block';
    }

    function closeModal(modalId) {
      document.getElementById(modalId).style.display = 'none';
    }
  </script>
</body>
</html>
