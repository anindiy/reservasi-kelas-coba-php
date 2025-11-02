<?php
// update_kelas.php - Fixed for older MySQL
echo "<h2>🔄 Update Tabel Kelas</h2>";

$host = "localhost";
$dbname = "reservasi_kelas";
$username = "root";
$password = "anin";

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Cek apakah kolom jumlah_mahasiswa sudah ada
    $stmt = $conn->query("SHOW COLUMNS FROM kelas LIKE 'jumlah_mahasiswa'");
    $column_exists = $stmt->rowCount() > 0;
    
    if (!$column_exists) {
        // Tambah kolom jumlah_mahasiswa
        $conn->exec("ALTER TABLE kelas ADD COLUMN jumlah_mahasiswa INT DEFAULT 40");
        echo "✅ Kolom 'jumlah_mahasiswa' ditambahkan<br>";
    } else {
        echo "✅ Kolom 'jumlah_mahasiswa' sudah ada<br>";
    }
    
    // Update jumlah mahasiswa untuk setiap kelas
    $kelas_jumlah = [
        'TI-A' => 42,
        'TI-B' => 45,
        'TI-C' => 38,
        'MT-A' => 35,
        'FS-A' => 30
    ];
    
    foreach ($kelas_jumlah as $kelas => $jumlah) {
        $stmt = $conn->prepare("UPDATE kelas SET jumlah_mahasiswa = ? WHERE nama_kelas = ?");
        $stmt->execute([$jumlah, $kelas]);
        echo "✅ Kelas $kelas: $jumlah mahasiswa<br>";
    }
    
    echo "<hr>";
    echo "<h3 style='color: green;'>✅ Update berhasil!</h3>";
    echo "<a href='dashboard_with_capacity.php' style='
        background: #28a745; 
        color: white; 
        padding: 15px 30px; 
        text-decoration: none; 
        border-radius: 5px;
    '>🚀 Lihat Dashboard dengan Kapasitas</a>";
    
} catch(PDOException $e) {
    echo "<div style='color: red;'>❌ Error: " . $e->getMessage() . "</div>";
}
?>