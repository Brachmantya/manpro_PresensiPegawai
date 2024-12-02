-- Tabel Pemilik
CREATE TABLE Pemilik (
    id_pemilik INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100),
    email VARCHAR(100),
    password VARCHAR(100)
);

-- Tabel Kecamatan
CREATE TABLE Kecamatan (
    id_kecamatan INT AUTO_INCREMENT PRIMARY KEY,
    nama_kecamatan VARCHAR(100)
);

-- Tabel Kelurahan
CREATE TABLE Kelurahan (
    id_kelurahan INT AUTO_INCREMENT PRIMARY KEY,
    nama_kelurahan VARCHAR(100),
    id_kecamatan INT,
    FOREIGN KEY (id_kecamatan) REFERENCES Kecamatan(id_kecamatan)
);

-- Tabel Pegawai
CREATE TABLE Pegawai (
    id_pegawai INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100),
    nomor_hp VARCHAR(15),
    email VARCHAR(100),
    alamat TEXT,
    posisi VARCHAR(50),
    gaji_perJam INT
);

-- Tabel Absen
CREATE TABLE Absen (
    id_absen INT AUTO_INCREMENT PRIMARY KEY,
    id_pegawai INT,
    waktu_masuk DATE,
    waktu_pulang DATE,
    FOREIGN KEY (id_pegawai) REFERENCES Pegawai(id_pegawai)
);

-- Tabel Laporan Kehadiran
CREATE TABLE Laporan_Kehadiran (
    id_laporan INT AUTO_INCREMENT PRIMARY KEY,
    tanggal DATE,
    id_pegawai INT,
    status_kehadiran VARCHAR(50),
    FOREIGN KEY (id_pegawai) REFERENCES Pegawai(id_pegawai)
);

-- Tabel Laporan Gaji
CREATE TABLE Laporan_Gaji (
    id_laporan_Gaji INT AUTO_INCREMENT PRIMARY KEY,
    id_pegawai INT,
    tanggal DATE,
    jml_hariKerja INT,
    jml_jamKerja INT,
    total_gaji DECIMAL(15, 2),
    FOREIGN KEY (id_pegawai) REFERENCES Pegawai(id_pegawai)
);
