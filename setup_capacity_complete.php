<?php
// setup_capacity_complete.php - Complete setup for capacity feature
echo "<h2>🎯 Setup Lengkap Fitur Kapasitas</h2>";

$host = "localhost";
$dbname = "reservasi_kelas";
$username = "root";
$password = "anin";

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h3>1. Setup Tabel Ruang Kelas</h3>";
    
    // Cek dan tambah kolom kapasitas di ruang_kelas
    $stmt = $conn->query("SHOW COLUMNS FROM ruang_kelas LIKE 'kapasitas'");
    if ($stmt->rowCount() == 0) {
        $conn->exec("ALTER TABLE ruang_kelas ADD COLUMN kapasitas INT DEFAULT 40");
        echo "✅ Kolom 'kapasitas' ditambahkan ke ruang_kelas<br>";
    } else {
        echo "✅ Kolom 'kapasitas' sudah ada di ruang_kelas<br>";
    }
    
    // Update data kapasitas ruang
    $ruang_data = [
        ['R101', 45],
        ['R102', 45],
        ['R201', 40],
        ['R202', 40],
        ['R301', 35],
        ['R302', 35],
        ['LAB-KOMP1', 30],
        ['LAB-KOMP2', 30]
    ];
    
    foreach ($ruang_data as $data) {
        $stmt = $conn->prepare("UPDATE ruang_kelas SET kapasitas = ? WHERE nama_ruang = ?");
        $stmt->execute($data);
        echo "✅ Ruang {$data[0]}: kapasitas {$data[1]}<br>";
    }
    
    echo "<h3>2. Setup Tabel Kelas</h3>";
    
    // Cek dan tambah kolom jumlah_mahasiswa di kelas
    $stmt = $conn->query("SHOW COLUMNS FROM kelas LIKE 'jumlah_mahasiswa'");
    if ($stmt->rowCount() == 0) {
        $conn->exec("ALTER TABLE kelas ADD COLUMN jumlah_mahasiswa INT DEFAULT 40");
        echo "✅ Kolom 'jumlah_mahasiswa' ditambahkan ke kelas<br>";
    } else {
        echo "✅ Kolom 'jumlah_mahasiswa' sudah ada di kelas<br>";
    }
    
    // Update data jumlah mahasiswa
    $kelas_data = [
        ['TI-A', 42],
        ['TI-B', 45],
        ['TI-C', 38],
        ['MT-A', 35],
        ['FS-A', 30]
    ];
    
    foreach ($kelas_data as $data) {
        $stmt = $conn->prepare("UPDATE kelas SET jumlah_mahasiswa = ? WHERE nama_kelas = ?");
        $stmt->execute($data);
        echo "✅ Kelas {$data[0]}: {$data[1]} mahasiswa<br>";
    }
    
    echo "<h3>3. Verifikasi Data</h3>";
    
    // Verifikasi ruang_kelas
    echo "<strong>Data Ruang Kelas:</strong><br>";
    $ruang = $conn->query("SELECT nama_ruang, gedung, kapasitas FROM ruang_kelas ORDER BY nama_ruang")->fetchAll();
    foreach ($ruang as $r) {
        echo "• {$r['nama_ruang']} - {$r['gedung']}: {$r['kapasitas']} kursi<br>";
    }
    
    echo "<br><strong>Data Kelas:</strong><br>";
    $kelas = $conn->query("SELECT nama_kelas, jurusan, jumlah_mahasiswa FROM kelas ORDER BY nama_kelas")->fetchAll();
    foreach ($kelas as $k) {
        echo "• {$k['nama_kelas']} - {$k['jurusan']}: {$k['jumlah_mahasiswa']} mahasiswa<br>";
    }
    
    echo "<hr>";
    echo "<div style='background: #d4edda; padding: 20px; border-radius: 10px; text-align: center;'>";
    echo "<h3 style='color: #155724;'>🎉 Setup Fitur Kapasitas Berhasil!</h3>";
    echo "<p>Semua tabel dan data kapasitas telah diupdate.</p>";
    echo "</div>";
    
    echo "<div style='text-align: center; margin: 20px 0;'>";
    echo "<a href='dashboard_with_capacity.php' style='
        background: #28a745; 
        color: white; 
        padding: 15px 30px; 
        text-decoration: none; 
        border-radius: 5px;
        margin: 10px;
        display: inline-block;
    '>🚀 Dashboard dengan Kapasitas</a>";
    
    echo "<a href='daftar_kelas_capacity.php' style='
        background: #17a2b8; 
        color: white; 
        padding: 15px 30px; 
        text-decoration: none; 
        border-radius: 5px;
        margin: 10px;
        display: inline-block;
    '>📊 Lihat Daftar Kapasitas</a>";
    echo "</div>";
    
} catch(PDOException $e) {
    echo "<div style='color: red; background: #f8d7da; padding: 20px; border-radius: 10px;'>";
    echo "<h3>❌ Error: " . $e->getMessage() . "</h3>";
    echo "<p>Pastikan database 'reservasi_kelas' sudah dibuat.</p>";
    echo "</div>";
}
?>