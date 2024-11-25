-- Tabel Pemilik
CREATE TABLE Pemilik (
    ID_Pemilik INT PRIMARY KEY,
    Nama VARCHAR(100),
    Email VARCHAR(100),
    Password VARCHAR(100)
);

-- Tabel Kecamatan
CREATE TABLE Kecamatan (
    ID_Kecamatan INT PRIMARY KEY,
    Nama_Kecamatan VARCHAR(100)
);

-- Tabel Kelurahan
CREATE TABLE Kelurahan (
    ID_Kelurahan INT PRIMARY KEY,
    Nama_Kelurahan VARCHAR(100),
    ID_Kecamatan INT,
    FOREIGN KEY (ID_Kecamatan) REFERENCES Kecamatan(ID_Kecamatan)
);

-- Tabel Posisi/Jabatan
CREATE TABLE Posisi (
    ID_Posisi INT PRIMARY KEY,
    Nama_Posisi VARCHAR(100),
    Satuan_Gaji_Per_Jam DECIMAL(10, 2)
);

-- Tabel Gaji
CREATE TABLE Gaji (
    ID_Gaji INT PRIMARY KEY,
    ID_Posisi INT,
    Satuan_Gaji_Per_Jam DECIMAL(10, 2),
    FOREIGN KEY (ID_Posisi) REFERENCES Posisi(ID_Posisi)
);

-- Tabel Pegawai
CREATE TABLE Pegawai (
    ID_Pegawai INT PRIMARY KEY,
    Nama VARCHAR(100),
    Nomor_HP VARCHAR(15),
    Email VARCHAR(100),
    Alamat TEXT,
    ID_Posisi INT,
    ID_Gaji INT,
    FOREIGN KEY (ID_Posisi) REFERENCES Posisi(ID_Posisi),
    FOREIGN KEY (ID_Gaji) REFERENCES Gaji(ID_Gaji)
);

-- Tabel Absen
CREATE TABLE Absen (
    ID_Absen INT PRIMARY KEY,
    ID_Pegawai INT,
    Waktu_Masuk DATE,
    Waktu_Pulang DATE,
    FOREIGN KEY (ID_Pegawai) REFERENCES Pegawai(ID_Pegawai)
);

-- Tabel Laporan Kehadiran
CREATE TABLE Laporan_Kehadiran (
    ID_Laporan INT PRIMARY KEY,
    Tanggal DATE,
    ID_Pegawai INT,
    Status_Kehadiran VARCHAR(50),
    FOREIGN KEY (ID_Pegawai) REFERENCES Pegawai(ID_Pegawai)
);

-- Tabel Laporan Gaji
CREATE TABLE Laporan_Gaji (
    ID_Laporan_Gaji INT PRIMARY KEY,
    ID_Pegawai INT,
    Tanggal_Minggu DATE,
    Total_Gaji DECIMAL(15, 2),
    FOREIGN KEY (ID_Pegawai) REFERENCES Pegawai(ID_Pegawai)
);
