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

// Handle Logout (Menghapus session PHP)
if (isset($_POST['logout'])) {
    session_start();
    session_unset();
    session_destroy();
    header("Location: ../Login/index.php");
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
      <h1>Dashboard Pemilik ☕</h1>
      <!-- Form Logout dengan PHP -->
      <form method="POST" style="display:inline">
        <input type="hidden" name="logout" value="true">
        <button type="submit" class="logout-btn" id="logoutButton">Logout</button>
      </form>
    </header>
    
    <main>
      <div class="user-info">
        <p>Username: <strong id="displayUsername"></strong></p>
        <p>Posisi: Pemilik <strong id="displayRole"></strong></p>
      </div>

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
                    echo "<tr id='employee-{$row['id_pegawai']}'>
                            <td>{$row['nama']}</td>
                            <td>{$row['nomor_hp']}</td>
                            <td>{$row['email']}</td>
                            <td>{$row['alamat']}</td>
                            <td>{$row['posisi']}</td>
                            <td>" . number_format($row['gaji_perJam'], 0, ',', '.') . "</td>
                            <td>
                              <button class='secondary-btn' onclick='editEmployee({$row['id_pegawai']})'>Edit</button>
                              
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
        <br> 

        <!-- Form Filter -->
        <form method="GET" action="">
          <label for="start_date">Dari Tanggal:</label>
          <input type="date" id="start_date" name="start_date" value="<?php echo isset($_GET['start_date']) ? $_GET['start_date'] : ''; ?>">
          
          <label for="end_date">Sampai Tanggal:</label>
          <input type="date" id="end_date" name="end_date" value="<?php echo isset($_GET['end_date']) ? $_GET['end_date'] : ''; ?>">
            <br>
            <br>
          <label for="search_name">Cari Nama Pegawai:</label>

          <input type="text" id="search_name" name="search_name" value="<?php echo isset($_GET['search_name']) ? $_GET['search_name'] : ''; ?>" style="width: 600px;">
      
          <button type="submit" class="primary-btn">Filter</button>
          <br><br>
        </form>

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
            // Ambil nilai filter dari GET request
            $startDate = isset($_GET['start_date']) ? $_GET['start_date'] : '';
            $endDate = isset($_GET['end_date']) ? $_GET['end_date'] : '';
            $searchName = isset($_GET['search_name']) ? $_GET['search_name'] : '';

            // Query untuk laporan gaji dengan filter
            $sql = "SELECT 
                        Pegawai.nama, 
                        Laporan_Gaji.tanggal, 
                        Laporan_Gaji.jml_hariKerja, 
                        Laporan_Gaji.jml_jamKerja, 
                        Laporan_Gaji.total_gaji
                    FROM Laporan_Gaji
                    JOIN Pegawai ON Laporan_Gaji.id_pegawai = Pegawai.id_pegawai
                    WHERE 1=1";

            // Filter berdasarkan rentang tanggal
            if ($startDate && $endDate) {
                $sql .= " AND Laporan_Gaji.tanggal BETWEEN '$startDate' AND '$endDate'";
            }

            // Filter berdasarkan nama pegawai
            if ($searchName) {
                $sql .= " AND Pegawai.nama LIKE '%$searchName%'";
            }

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
                echo "<tr><td colspan='5'>Tidak ada data</td></tr>";
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
    // Fungsi Logout
    function logout() {
      document.forms[0].submit(); // Submit form logout
    }

    function openModal(modalId) {
      document.getElementById(modalId).style.display = 'block';
    }

    function closeModal(modalId) {
      document.getElementById(modalId).style.display = 'none';
    }

    function editEmployee(employeeId) {
      var row = document.getElementById('employee-' + employeeId);
      var cells = row.getElementsByTagName('td');
      document.getElementById('employee_id').value = employeeId;
      document.getElementById('employee_name').value = cells[0].innerText;
      document.getElementById('employee_phone').value = cells[1].innerText;
      document.getElementById('employee_email').value = cells[2].innerText;
      document.getElementById('employee_address').value = cells[3].innerText;
      document.getElementById('employee_position').value = cells[4].innerText;
      var salaryText = cells[5].innerText;
      var salary = salaryText.replace(/\./g, '');
      document.getElementById('employee_salary').value = salary;
      openModal('employeeInfoModal');
    }
  </script>
</body>
</html>
