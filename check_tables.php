<?php
// check_tables.php - Check table structure
echo "<h2>🔍 Struktur Tabel Database</h2>";

$host = "localhost";
$dbname = "reservasi_kelas";
$username = "root";
$password = "anin";

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Cek struktur tabel ruang_kelas
    echo "<h3>Struktur Tabel: ruang_kelas</h3>";
    $stmt = $conn->query("DESCRIBE ruang_kelas");
    $columns = $stmt->fetchAll();
    
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    foreach ($columns as $col) {
        echo "<tr>";
        echo "<td>{$col['Field']}</td>";
        echo "<td>{$col['Type']}</td>";
        echo "<td>{$col['Null']}</td>";
        echo "<td>{$col['Key']}</td>";
        echo "<td>{$col['Default']}</td>";
        echo "<td>{$col['Extra']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Cek data ruang_kelas
    echo "<h3>Data Ruang Kelas:</h3>";
    $stmt = $conn->query("SELECT * FROM ruang_kelas");
    $ruang = $stmt->fetchAll();
    
    echo "<table border='1' style='border-collapse: collapse; width: 100%; margin-bottom: 20px;'>";
    echo "<tr><th>Nama Ruang</th><th>Gedung</th><th>Lantai</th><th>Region</th><th>Kapasitas</th></tr>";
    foreach ($ruang as $r) {
        echo "<tr>";
        echo "<td>{$r['nama_ruang']}</td>";
        echo "<td>{$r['gedung']}</td>";
        echo "<td>{$r['lantai']}</td>";
        echo "<td>{$r['region']}</td>";
        echo "<td>{$r['kapasitas'] ?? 'NULL'}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Cek struktur tabel kelas
    echo "<h3>Struktur Tabel: kelas</h3>";
    $stmt = $conn->query("DESCRIBE kelas");
    $columns = $stmt->fetchAll();
    
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    foreach ($columns as $col) {
        echo "<tr>";
        echo "<td>{$col['Field']}</td>";
        echo "<td>{$col['Type']}</td>";
        echo "<td>{$col['Null']}</td>";
        echo "<td>{$col['Key']}</td>";
        echo "<td>{$col['Default']}</td>";
        echo "<td>{$col['Extra']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Cek data kelas
    echo "<h3>Data Kelas:</h3>";
    $stmt = $conn->query("SELECT * FROM kelas");
    $kelas = $stmt->fetchAll();
    
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>Nama Kelas</th><th>Jurusan</th><th>Jumlah Mahasiswa</th></tr>";
    foreach ($kelas as $k) {
        echo "<tr>";
        echo "<td>{$k['nama_kelas']}</td>";
        echo "<td>{$k['jurusan']}</td>";
        echo "<td>{$k['jumlah_mahasiswa'] ?? 'NULL'}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
} catch(PDOException $e) {
    echo "<div style='color: red;'>❌ Error: " . $e->getMessage() . "</div>";
}
?>