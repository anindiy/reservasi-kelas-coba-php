<?php
// final_init.php - Inisialisasi final dengan password 'anin'
echo "<h2>🗃️ Membuat Database Reservasi Kelas</h2>";

$host = "localhost";
$username = "root";
$password = "anin";  // Password yang berhasil
$dbname = "reservasi_kelas";

try {
    // Step 1: Connect dengan password 'anin'
    echo "1. Menghubungkan ke MySQL dengan password 'anin'...<br>";
    $conn = new PDO("mysql:host=$host", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✅ <strong>Koneksi BERHASIL!</strong><br><br>";
    
    // Step 2: Create database
    echo "2. Membuat database '$dbname'...<br>";
    $conn->exec("CREATE DATABASE IF NOT EXISTS $dbname");
    $conn->exec("USE $dbname");
    echo "✅ Database berhasil dibuat<br><br>";
    
    // Step 3: Create semua tabel
    echo "3. Membuat tabel-tabel...<br>";
    
    // Table dosen
    $conn->exec("CREATE TABLE IF NOT EXISTS dosen (
        id_dosen VARCHAR(20) PRIMARY KEY,
        nama VARCHAR(100) NOT NULL,
        password VARCHAR(255) NOT NULL
    )");
    echo "✅ Tabel 'dosen' dibuat<br>";
    
    // Table mata_kuliah
    $conn->exec("CREATE TABLE IF NOT EXISTS mata_kuliah (
        kode_mk VARCHAR(10) PRIMARY KEY,
        nama_mk VARCHAR(100) NOT NULL,
        jurusan VARCHAR(50) NOT NULL
    )");
    echo "✅ Tabel 'mata_kuliah' dibuat<br>";
    
    // Table jam_kuliah  
    $conn->exec("CREATE TABLE IF NOT EXISTS jam_kuliah (
        jam_ke INT PRIMARY KEY,
        waktu_mulai TIME NOT NULL,
        waktu_selesai TIME NOT NULL
    )");
    echo "✅ Tabel 'jam_kuliah' dibuat<br>";
    
    // Table kelas
    $conn->exec("CREATE TABLE IF NOT EXISTS kelas (
        nama_kelas VARCHAR(20) PRIMARY KEY,
        jurusan VARCHAR(50) NOT NULL
    )");
    echo "✅ Tabel 'kelas' dibuat<br>";
    
    // Table ruang_kelas
    $conn->exec("CREATE TABLE IF NOT EXISTS ruang_kelas (
        nama_ruang VARCHAR(20) PRIMARY KEY,
        gedung VARCHAR(50) NOT NULL,
        lantai INT NOT NULL,
        region VARCHAR(50) NOT NULL
    )");
    echo "✅ Tabel 'ruang_kelas' dibuat<br>";
    
    // Table jadwal_reservasi
    $conn->exec("CREATE TABLE IF NOT EXISTS jadwal_reservasi (
        id INT AUTO_INCREMENT PRIMARY KEY,
        id_dosen VARCHAR(20) NOT NULL,
        kode_mk VARCHAR(10) NOT NULL,
        nama_kelas VARCHAR(20) NOT NULL,
        jam_mulai INT NOT NULL,
        jam_akhir INT NOT NULL,
        tanggal DATE NOT NULL,
        nama_ruang VARCHAR(20) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY unique_booking (tanggal, jam_mulai, jam_akhir, nama_ruang)
    )");
    echo "✅ Tabel 'jadwal_reservasi' dibuat<br><br>";
    
    // Step 4: Insert sample data
    echo "4. Memasukkan data sample...<br>";
    
    // Insert dosen dengan password hash
    $password_hash = password_hash('password123', PASSWORD_DEFAULT);
    $conn->exec("INSERT IGNORE INTO dosen (id_dosen, nama, password) VALUES 
        ('D001', 'Dr. Ahmad Santoso', '$password_hash'),
        ('D002', 'Prof. Siti Rahayu', '$password_hash'),
        ('D003', 'Dr. Budi Prasetyo', '$password_hash')");
    echo "✅ 3 data dosen dimasukkan<br>";
    
    // Insert mata kuliah
    $conn->exec("INSERT IGNORE INTO mata_kuliah (kode_mk, nama_mk, jurusan) VALUES 
        ('MK001', 'Pemrograman Web', 'Teknik Informatika'),
        ('MK002', 'Basis Data', 'Teknik Informatika'),
        ('MK003', 'Algoritma dan Struktur Data', 'Teknik Informatika'),
        ('MK004', 'Jaringan Komputer', 'Teknik Informatika'),
        ('MK005', 'Kalkulus', 'Matematika'),
        ('MK006', 'Fisika Dasar', 'Fisika')");
    echo "✅ 6 data mata kuliah dimasukkan<br>";
    
    // Insert jam kuliah
    $conn->exec("INSERT IGNORE INTO jam_kuliah (jam_ke, waktu_mulai, waktu_selesai) VALUES 
        (1, '07:30:00', '09:00:00'),
        (2, '09:00:00', '10:30:00'),
        (3, '10:30:00', '12:00:00'),
        (4, '13:00:00', '14:30:00'),
        (5, '14:30:00', '16:00:00'),
        (6, '16:00:00', '17:30:00'),
        (7, '18:30:00', '20:00:00'),
        (8, '20:00:00', '21:30:00')");
    echo "✅ 8 data jam kuliah dimasukkan<br>";
    
    // Insert kelas
    $conn->exec("INSERT IGNORE INTO kelas (nama_kelas, jurusan) VALUES 
        ('TI-A', 'Teknik Informatika'),
        ('TI-B', 'Teknik Informatika'),
        ('TI-C', 'Teknik Informatika'),
        ('MT-A', 'Matematika'),
        ('FS-A', 'Fisika')");
    echo "✅ 5 data kelas dimasukkan<br>";
    
    // Insert ruang kelas
    $conn->exec("INSERT IGNORE INTO ruang_kelas (nama_ruang, gedung, lantai, region) VALUES 
        ('R101', 'Gedung A', 1, 'Kampus Pusat'),
        ('R102', 'Gedung A', 1, 'Kampus Pusat'),
        ('R201', 'Gedung A', 2, 'Kampus Pusat'),
        ('R202', 'Gedung A', 2, 'Kampus Pusat'),
        ('R301', 'Gedung B', 3, 'Kampus Pusat'),
        ('R302', 'Gedung B', 3, 'Kampus Pusat'),
        ('LAB-KOMP1', 'Gedung C', 1, 'Kampus Pusat'),
        ('LAB-KOMP2', 'Gedung C', 1, 'Kampus Pusat')");
    echo "✅ 8 data ruang kelas dimasukkan<br><br>";
    
    echo "<hr>";
    echo "<div style='background: #d4edda; padding: 25px; border-radius: 10px; text-align: center;'>";
    echo "<h2 style='color: #155724; margin-bottom: 20px;'>🎉 SUKSES! APLIKASI SIAP DIGUNAKAN</h2>";
    echo "<p style='font-size: 18px;'><strong>Database:</strong> reservasi_kelas</p>";
    echo "<p style='font-size: 18px;'><strong>Password MySQL:</strong> anin</p>";
    echo "</div>";
    
    echo "<div style='background: #fff3cd; padding: 20px; margin: 20px 0; border-radius: 10px;'>";
    echo "<h4>📋 INFORMASI LOGIN APLIKASI:</h4>";
    echo "<table style='width: 100%; border-collapse: collapse;'>";
    echo "<tr style='background: #e2e3e5;'>";
    echo "<th style='padding: 12px; border: 1px solid #ddd;'>Username</th>";
    echo "<th style='padding: 12px; border: 1px solid #ddd;'>Nama</th>";
    echo "<th style='padding: 12px; border: 1px solid #ddd;'>Password</th>";
    echo "</tr>";
    echo "<tr>";
    echo "<td style='padding: 12px; border: 1px solid #ddd;'>D001</td>";
    echo "<td style='padding: 12px; border: 1px solid #ddd;'>Dr. Ahmad Santoso</td>";
    echo "<td style='padding: 12px; border: 1px solid #ddd;'>password123</td>";
    echo "</tr>";
    echo "<tr>";
    echo "<td style='padding: 12px; border: 1px solid #ddd;'>D002</td>";
    echo "<td style='padding: 12px; border: 1px solid #ddd;'>Prof. Siti Rahayu</td>";
    echo "<td style='padding: 12px; border: 1px solid #ddd;'>password123</td>";
    echo "</tr>";
    echo "<tr>";
    echo "<td style='padding: 12px; border: 1px solid #ddd;'>D003</td>";
    echo "<td style='padding: 12px; border: 1px solid #ddd;'>Dr. Budi Prasetyo</td>";
    echo "<td style='padding: 12px; border: 1px solid #ddd;'>password123</td>";
    echo "</tr>";
    echo "</table>";
    echo "</div>";
    
    echo "<div style='text-align: center; margin: 30px 0;'>";
    echo "<a href='app.php' style='
        background: #28a745; 
        color: white; 
        padding: 18px 35px; 
        text-decoration: none; 
        border-radius: 8px;
        font-size: 20px;
        font-weight: bold;
        display: inline-block;
    '>🚀 MASUK KE APLIKASI RESERVASI</a>";
    echo "</div>";
    
    echo "<div style='background: #d1ecf1; padding: 15px; border-radius: 5px;'>";
    echo "<strong>💡 Simpan informasi ini:</strong> Password MySQL: <strong>anin</strong> | Password Aplikasi: <strong>password123</strong>";
    echo "</div>";
    
} catch(PDOException $e) {
    echo "<div style='color: red; background: #f8d7da; padding: 20px; border-radius: 10px;'>";
    echo "<h3>❌ ERROR: " . $e->getMessage() . "</h3>";
    echo "<p>Pastikan MySQL running dan password 'anin' benar.</p>";
    echo "</div>";
}
?>