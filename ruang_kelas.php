<?php
// update_ruang_kelas.php - Fixed for older MySQL
echo "<h2>🔄 Update Tabel Ruang Kelas</h2>";

$host = "localhost";
$dbname = "reservasi_kelas";
$username = "root";
$password = "anin";

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Cek apakah kolom kapasitas sudah ada
    $stmt = $conn->query("SHOW COLUMNS FROM ruang_kelas LIKE 'kapasitas'");
    $column_exists = $stmt->rowCount() > 0;
    
    if (!$column_exists) {
        // Tambah kolom kapasitas
        $conn->exec("ALTER TABLE ruang_kelas ADD COLUMN kapasitas INT DEFAULT 40");
        echo "✅ Kolom 'kapasitas' ditambahkan<br>";
    } else {
        echo "✅ Kolom 'kapasitas' sudah ada<br>";
    }
    
    // Update kapasitas untuk setiap ruang
    $ruang_kapasitas = [
        'R101' => 45,
        'R102' => 45,
        'R201' => 40,
        'R202' => 40,
        'R301' => 35,
        'R302' => 35,
        'LAB-KOMP1' => 30,
        'LAB-KOMP2' => 30
    ];
    
    foreach ($ruang_kapasitas as $ruang => $kapasitas) {
        $stmt = $conn->prepare("UPDATE ruang_kelas SET kapasitas = ? WHERE nama_ruang = ?");
        $stmt->execute([$kapasitas, $ruang]);
        echo "✅ Ruang $ruang: kapasitas $kapasitas mahasiswa<br>";
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