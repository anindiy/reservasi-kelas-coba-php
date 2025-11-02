<?php
// test_connection.php
echo "<h3>Testing Koneksi Database</h3>";

$host = "localhost";
$username = "root";
$password = "anin";

try {
    // Test koneksi tanpa database
    $conn = new PDO("mysql:host=$host", $username, $password);
    echo "✅ Koneksi ke MySQL BERHASIL<br>";
    
    // Cek database
    $stmt = $conn->query("SHOW DATABASES LIKE 'reservasi_kelas'");
    if ($stmt->rowCount() > 0) {
        echo "✅ Database 'reservasi_kelas' ADA<br>";
    } else {
        echo "❌ Database 'reservasi_kelas' TIDAK ADA<br>";
        echo "Jalankan init_database.php terlebih dahulu<br>";
    }
    
} catch(PDOException $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
    echo "Periksa:<br>";
    echo "1. Apakah MySQL running?<br>";
    echo "2. Password MySQL benar?<br>";
}

echo "<br><a href='init_database.php'>Jalankan Init Database</a>";
?>