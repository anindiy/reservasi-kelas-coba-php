<?php
// test_passwords.php - Test semua password MySQL yang umum
echo "<h2>🔐 Testing Koneksi MySQL dengan Berbagai Password</h2>";

$passwords_to_try = [
    '',           // Kosong (XAMPP default lama)
    'root',       // Password: root
    'password',   // Password: password  
    '123456',     // Password: 123456
    'mysql',      // Password: mysql
    'anin',      // Password: anin
];

$host = "localhost";
$username = "root";
$success = false;

echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
echo "<tr><th>Password</th><th>Status</th><th>Pesan</th></tr>";

foreach ($passwords_to_try as $password) {
    echo "<tr>";
    echo "<td style='padding: 10px;'><strong>" . ($password ?: '(kosong)') . "</strong></td>";
    
    try {
        $conn = new PDO("mysql:host=$host", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        echo "<td style='padding: 10px; background: #d4edda;'>✅ BERHASIL</td>";
        echo "<td style='padding: 10px;'>Koneksi sukses!</td>";
        
        // Simpan password yang berhasil
        $success = true;
        $correct_password = $password;
        break;
        
    } catch(PDOException $e) {
        echo "<td style='padding: 10px; background: #f8d7da;'>❌ GAGAL</td>";
        echo "<td style='padding: 10px;'>" . $e->getMessage() . "</td>";
    }
    
    echo "</tr>";
}

echo "</table>";

if ($success) {
    echo "<div style='background: #d4edda; padding: 20px; margin: 20px 0; border-radius: 10px;'>";
    echo "<h3>🎉 Password yang berhasil: <strong>" . ($correct_password ?: '(kosong)') . "</strong></h3>";
    
    // Buat file config dengan password yang benar
    $config_content = "<?php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');  
define('DB_PASS', '" . addslashes($correct_password) . "');
define('DB_NAME', 'reservasi_kelas');
?>";
    
    file_put_contents('db_config.php', $config_content);
    echo "<p>File konfigurasi disimpan: <strong>db_config.php</strong></p>";
    
    echo "<br><a href='create_database.php' style='
        background: #28a745; 
        color: white; 
        padding: 15px 30px; 
        text-decoration: none; 
        border-radius: 5px;
        font-size: 18px;
    '>🚀 BUAT DATABASE SEKARANG</a>";
    echo "</div>";
} else {
    echo "<div style='background: #f8d7da; padding: 20px; margin: 20px 0; border-radius: 10px;'>";
    echo "<h3>❌ Semua password gagal. Solusi:</h3>";
    echo "<ol>";
    echo "<li><strong>Reset password MySQL:</strong> Buka XAMPP → MySQL → Config → my.ini</li>";
    echo "<li><strong>Atau install ulang XAMPP</strong> dan pastikan MySQL tanpa password</li>";
    echo "<li><strong>Cek di phpMyAdmin:</strong> Buka http://localhost/phpmyadmin</li>";
    echo "</ol>";
    
    echo "<br><a href='http://localhost/phpmyadmin' target='_blank' style='
        background: #007bff; 
        color: white; 
        padding: 10px 20px; 
        text-decoration: none; 
        border-radius: 5px;
    '>🔗 Buka phpMyAdmin</a>";
    echo "</div>";
}
?>