<?php
session_start();


// Check if the user is logged in
if (!isset($_SESSION['nama'])) {
  // If not logged in, redirect to login page
  header("Location: ../Login/index.php");
  exit();

}
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
//
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (isset($_POST['action'])) {
      $action = $_POST['action'];
      if ($action === 'addEmployee' || $action === 'editEmployee') {
          $id = $_POST['employee_id'] ?? null;
          $nama = $_POST['employee_name'];
          $nomor_hp = $_POST['employee_phone'];
          $email = $_POST['employee_email'];
          $alamat = $_POST['employee_address'];
          $posisi = $_POST['employee_position'];
          $gaji_perJam = $_POST['employee_salary'];

          if ($action === 'addEmployee') {
            // Tambah pegawai baru
            $sql = "INSERT INTO Pegawai (nama, nomor_hp, email, alamat, posisi, gaji_perJam) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssssi", $nama, $nomor_hp, $email, $alamat, $posisi, $gaji_perJam);
            $stmt->execute();
        } elseif ($action === 'editEmployee') {
            // Edit pegawai
            $sql = "UPDATE Pegawai SET nama=?, nomor_hp=?, email=?, alamat=?, posisi=?, gaji_perJam=? WHERE id_pegawai=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssssii", $nama, $nomor_hp, $email, $alamat, $posisi, $gaji_perJam, $id);
            $stmt->execute();
        }
    } 
    elseif ($action === 'deleteEmployee') {
      // Mulai transaksi untuk memastikan konsistensi data
      $conn->begin_transaction();

      try {
          // Hapus data dari tabel absen
          $sqlAbsen = "DELETE FROM absen WHERE id_pegawai = ?";
          $stmtAbsen = $conn->prepare($sqlAbsen);
          $stmtAbsen->bind_param("i", $id_pegawai);
          $stmtAbsen->execute();

          // Hapus data dari tabel gaji
          $sqlGaji = "DELETE FROM laporan_gaji WHERE id_pegawai = ?";
          $stmtGaji = $conn->prepare($sqlGaji);
          $stmtGaji->bind_param("i", $id_pegawai);
          $stmtGaji->execute();

          // Hapus data dari tabel laporan_kehadiran
          $sqlKehadiran = "DELETE FROM laporan_kehadiran WHERE id_pegawai = ?";
          $stmtKehadiran = $conn->prepare($sqlKehadiran);
          $stmtKehadiran->bind_param("i", $id_pegawai);
          $stmtKehadiran->execute();

          // Hapus data dari tabel pegawai
          $sqlPegawai = "DELETE FROM pegawai WHERE id_pegawai = ?";
          $stmtPegawai = $conn->prepare($sqlPegawai);
          $stmtPegawai->bind_param("i", $id_pegawai);
          $stmtPegawai->execute();

          // Commit transaksi jika semua berhasil
          $conn->commit();

          echo "Data pegawai dan data terkait berhasil dihapus.";
      } catch (Exception $e) {
          // Rollback jika terjadi kesalahan
          $conn->rollback();
          echo "Terjadi kesalahan saat menghapus data: " . $e->getMessage();
      }
    }
  }
}

// Proses Hapus Pegawai
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'deleteEmployee') {
    $employee_id = $_POST['employee_id'];

    $sql = "DELETE FROM Pegawai WHERE id_pegawai = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $employee_id);

    if ($stmt->execute()) {
        // Redirect to prevent form resubmission
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }
    $stmt->close();
}

// Handle Logout (Menghapus session PHP)
if (isset($_POST['logout'])) {
  // Mulai session jika belum dimulai
  if (session_status() == PHP_SESSION_NONE) {
      session_start();
  }
  
  // Hapus semua variabel session
  $_SESSION = array();
  
  // Hapus cookie session jika ada
  if (ini_get("session.use_cookies")) {
      $params = session_get_cookie_params();
      setcookie(session_name(), '', time() - 42000,
          $params["path"], $params["domain"],
          $params["secure"], $params["httponly"]
      );
  }
  
  // Hancurkan session
  session_destroy();
  
  // Redirect ke halaman login
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
        <p>Username: <strong><?= $_SESSION['nama']; ?></strong></p>
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
                              
                              <form method='POST' style='display:inline-block;' onsubmit='return confirmDelete()'>
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

      <!-- Laporan Absen -->
      <section>
        <h2>Laporan Absen</h2>
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
              <th>Jam Masuk</th>
              <th>Jam Pulang</th>
            </tr>
          </thead>
          <tbody>
            <?php
            // Ambil nilai filter dari GET request
            $startDate = isset($_GET['start_date']) ? $_GET['start_date'] : '';
            $endDate = isset($_GET['end_date']) ? $_GET['end_date'] : '';
            $searchName = isset($_GET['search_name']) ? $_GET['search_name'] : '';

            // Query untuk laporan absen dengan filter
            $sql = "SELECT 
                        Pegawai.nama,
                        Absen.tanggal,
                        Absen.waktu_masuk, 
                        Absen.waktu_pulang
                    FROM Absen
                    JOIN Pegawai ON Absen.id_pegawai = Pegawai.id_pegawai
                    WHERE 1=1";

            // Filter berdasarkan rentang tanggal
            if ($startDate && $endDate) {
                $sql .= " AND Absen.tanggal BETWEEN '$startDate' AND '$endDate'";
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
                            <td>{$row['waktu_masuk']}</td>
                            <td>{$row['waktu_pulang']}</td>
                          </tr>";
                }
            } else {
                echo "<tr><td colspan='4'>Tidak ada data</td></tr>";
            }
            ?>
          </tbody>
        </table>
      </section>
      <section>
            <h2>Rekap Gaji</h2>
            <form method="GET" action="">
            <label for="start">Dari Tanggal:</label>
            <input type="date" id="start" name="start" value="<?php echo isset($_GET['start']) ? $_GET['start'] : ''; ?>">
          
            <label for="end">Sampai Tanggal:</label>
            <input type="date" id="end" name="end" value="<?php echo isset($_GET['end']) ? $_GET['end'] : ''; ?>">
              <br>
              <br>
            <label for="search">Cari Nama Pegawai:</label>

            <input type="text" id="search" name="search" value="<?php echo isset($_GET['search']) ? $_GET['search'] : ''; ?>" style="width: 600px;">
      
            <button type="submit" class="primary-btn">Filter</button>
          <br><br>
        </form>
            <table>
                <thead>
                    <tr>
                        <th>id laporan gaji</th>
                        <th>Nama Pegawai</th>
                        <th>Tanggal</th>
                        <th>Total Jam Kerja</th>
                        <th>Total Gaji</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $start = isset($_GET['start']) ? $_GET['start'] : '';
                    $end = isset($_GET['end']) ? $_GET['end'] : '';
                    $search = isset($_GET['search']) ? $_GET['search'] : '';

                    $sql = "SELECT
                         laporan_gaji.id_laporan_Gaji,
                         Pegawai.nama,
                         laporan_gaji.tanggal,
                         laporan_gaji.jml_jamKerja,
                         laporan_gaji.total_gaji
                    FROM laporan_gaji
                    JOIN Pegawai ON laporan_gaji.id_pegawai = Pegawai.id_pegawai
                    WHERE 1=1";

                    // Filter berdasarkan rentang tanggal
                    if ($start && $end) {
                        $sql .= " AND laporan_gaji.tanggal BETWEEN '$start' AND '$end'";
                    }

                    // Filter berdasarkan nama pegawai
                    if ($search) {
                        $sql .= " AND Pegawai.nama LIKE '%$search%'";
                    } 

                    $result = $conn->query($sql);

                    //$queryGaji = "SELECT id_laporan_Gaji, id_pegawai, tanggal, jml_jamKerja, total_gaji FROM laporan_gaji";
                    //$gaji_result = $conn->query($queryGaji);
                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            echo "<tr>
                                    <td>{$row['id_laporan_Gaji']}</td>
                                    <td>{$row['nama']}</td>
                                    <td>{$row['tanggal']}</td>
                                    <td>{$row['jml_jamKerja']}</td>
                                    <td>Rp. " . number_format($row['total_gaji'], 2, ',', '.') . "</td>
                                  </tr>";
                        }
                    } else {
                        echo "<tr><td colspan='5'>Tidak ada data gaji.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </section>
    </main>
  </div>

  <!-- Modal: Tambah Data Pegawai -->
  <!-- Modal: Tambah Data Pegawai -->
  <div id="employeeInfoModal" class="modal" style="display: none;">
    <div class="modal-content">
      <span class="close" onclick="closeModal('employeeInfoModal')">&times;</span>
      <h3>Tambah/Edit Data Pegawai</h3>
      <form method="POST">
        <!--<input type="hidden" name="action" value="saveEmployee">-->
        <input type="hidden" name="action" id="formAction" value="addEmployee">
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
    document.getElementById('formAction').value = 'addEmployee';
    document.getElementById('employee_id').value = ''; // Reset ID saat menambah data baru
  }

    function closeModal(modalId) {
    document.getElementById(modalId).style.display = 'none';
  }

  function editEmployee(employeeId) {
    const row = document.getElementById(`employee-${employeeId}`);
    const cells = row.getElementsByTagName('td');

    document.getElementById('formAction').value = 'editEmployee';
    document.getElementById('employee_id').value = employeeId;
    document.getElementById('employee_name').value = cells[0].textContent;
    document.getElementById('employee_phone').value = cells[1].textContent;
    document.getElementById('employee_email').value = cells[2].textContent;
    document.getElementById('employee_address').value = cells[3].textContent;
    document.getElementById('employee_position').value = cells[4].textContent;
    
    // Perbaikan untuk mengambil gaji dan menghilangkan titik dan format mata uang
    const salaryText = cells[5].textContent;
    const salaryNumeric = salaryText.replace(/[^0-9]/g, '');
    document.getElementById('employee_salary').value = salaryNumeric;

    openModal('employeeInfoModal');
}

document.querySelector('form').addEventListener('submit', function (event) {
    event.preventDefault();
    const formAction = document.getElementById('formAction').value;
    const employeeId = document.getElementById('employee_id').value;

    const name = document.getElementById('employee_name').value;
    const phone = document.getElementById('employee_phone').value;
    const email = document.getElementById('employee_email').value;
    const address = document.getElementById('employee_address').value;
    const position = document.getElementById('employee_position').value;
    const salary = document.getElementById('employee_salary').value;

    if (formAction === 'editEmployee' && employeeId) {
        // Perbarui baris yang sudah ada
        const row = document.getElementById(`employee-${employeeId}`);
        const cells = row.getElementsByTagName('td');
        cells[0].textContent = name;
        cells[1].textContent = phone;
        cells[2].textContent = email;
        cells[3].textContent = address;
        cells[4].textContent = position;
        cells[5].textContent = parseInt(salary).toLocaleString('id-ID'); // Format dengan titik untuk ribuan
    } else if (formAction === 'addEmployee') {
        // Tambah baris baru
        const table = document.getElementById('employeeInfoTable');
        const newRow = table.insertRow();
        const newId = new Date().getTime(); // ID unik sementara untuk tampilan

        newRow.id = `employee-${newId}`;
        newRow.innerHTML = `
            <td>${name}</td>
            <td>${phone}</td>
            <td>${email}</td>
            <td>${address}</td>
            <td>${position}</td>
            <td>${parseInt(salary).toLocaleString('id-ID')}</td>
            <td>
                <button class="secondary-btn" onclick="editEmployee(${newId})">Edit</button>
                <form method="POST" style="display:inline-block;">
                    <input type="hidden" name="action" value="deleteEmployee">
                    <input type="hidden" name="employee_id" value="${newId}">
                    <button type="submit" class="danger-btn">Hapus</button>
                </form>
            </td>
        `;
    }

    // Kirim data ke server
    this.submit();
});

  </script>
</body>
</html>
