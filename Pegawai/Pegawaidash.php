<?php
// Koneksi ke database
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "presensicafe";

$conn = new mysqli($servername, $username, $password, $dbname);

// Periksa koneksi
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Proses absensi
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['absen'])) {
    $employee_id = $_POST['employee_id'];
    $date = date("Y-m-d");
    $time = date("H:i:s");

    $sql = "INSERT INTO attendance (employee_id, date, time) VALUES ('$employee_id', '$date', '$time')";
    if ($conn->query($sql) === TRUE) {
        $message = "Absensi berhasil dicatat!";
    } else {
        $message = "Gagal mencatat absensi: " . $conn->error;
    }
}

// Ambil data gaji
$gaji_query = "SELECT e.name, SUM(a.hours_worked) AS total_hours, (SUM(a.hours_worked) * e.hourly_rate) AS total_salary
               FROM employees e
               JOIN attendance a ON e.id = a.employee_id
               GROUP BY e.id";
$gaji_result = $conn->query($gaji_query);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Pegawai</title>
    <link rel="stylesheet" href="employeestyle.css">
</head>
<body>
<div class="employee-dashboard-container">
    <header>
        <h1>Dashboard Pegawai</h1>
        <button class="logout-btn" onclick="alert('Logout sukses!');">Logout</button>
    </header>

    <main>
        <!-- Form Absensi -->
        <section>
            <h2>Presensi</h2>
            <form method="POST" action="">
                <div class="input-group">
                    <label for="employee_id">ID Pegawai:</label>
                    <input type="text" id="employee_id" name="employee_id" required>
                </div>
                <button type="submit" name="absen">Absen</button>
            </form>
            <?php if (isset($message)) { echo "<p id='attendanceMessage'>$message</p>"; } ?>
        </section>

        <!-- Tabel Gaji -->
        <section>
            <h2>Rekap Gaji</h2>
            <table>
                <thead>
                    <tr>
                        <th>Nama Pegawai</th>
                        <th>Total Jam Kerja</th>
                        <th>Total Gaji</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($gaji_result->num_rows > 0) {
                        while ($row = $gaji_result->fetch_assoc()) {
                            echo "<tr>
                                    <td>{$row['name']}</td>
                                    <td>{$row['total_hours']}</td>
                                    <td>Rp. " . number_format($row['total_salary'], 2, ',', '.') . "</td>
                                  </tr>";
                        }
                    } else {
                        echo "<tr><td colspan='3'>Tidak ada data gaji.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </section>
    </main>
</div>
</body>
</html>
