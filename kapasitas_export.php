<?php
// kapasitas_export.php - Export laporan kapasitas
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user'])) {
    header("Location: index_fixed.php");
    exit();
}

$host = "localhost";
$dbname = "reservasi_kelas";
$username = "root";
$password = "anin";

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
} catch(PDOException $e) {
    die("Database error");
}

// Get filter parameters
$filter_gedung = $_GET['gedung'] ?? '';
$filter_lantai = $_GET['lantai'] ?? '';
$filter_kapasitas = $_GET['kapasitas'] ?? '';

// Build query dengan filter
$where_conditions = [];
$params = [];

if ($filter_gedung) {
    $where_conditions[] = "r.gedung = ?";
    $params[] = $filter_gedung;
}

if ($filter_lantai) {
    $where_conditions[] = "r.lantai = ?";
    $params[] = $filter_lantai;
}

if ($filter_kapasitas) {
    switch ($filter_kapasitas) {
        case 'kecil':
            $where_conditions[] = "r.kapasitas <= 35";
            break;
        case 'sedang':
            $where_conditions[] = "r.kapasitas BETWEEN 36 AND 44";
            break;
        case 'besar':
            $where_conditions[] = "r.kapasitas >= 45";
            break;
    }
}

$where_sql = "";
if (!empty($where_conditions)) {
    $where_sql = "WHERE " . implode(" AND ", $where_conditions);
}

$export_type = $_GET['type'] ?? 'pdf';

// Get data untuk export
$ruang_data = $conn->prepare("
    SELECT r.*, COUNT(j.id) as total_booking 
    FROM ruang_kelas r 
    LEFT JOIN jadwal_reservasi j ON r.nama_ruang = j.nama_ruang AND j.tanggal >= CURDATE()
    $where_sql
    GROUP BY r.nama_ruang 
    ORDER BY r.gedung, r.lantai, r.nama_ruang
");
$ruang_data->execute($params);
$ruang_data = $ruang_data->fetchAll();

$kelas_data = $conn->query("SELECT * FROM kelas ORDER BY nama_kelas")->fetchAll();

// Statistik
$stats = $conn->prepare("
    SELECT 
        COUNT(*) as total_ruang,
        SUM(r.kapasitas) as total_kapasitas,
        AVG(r.kapasitas) as rata_kapasitas
    FROM ruang_kelas r
    $where_sql
");
$stats->execute($params);
$stats = $stats->fetch(PDO::FETCH_ASSOC);

if ($export_type === 'excel') {
    // Export ke Excel
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment; filename="laporan_kapasitas_' . date('Y-m-d') . '.xls"');
    
    echo "<table border='1'>";
    echo "<tr><th colspan='6' style='background: #007bff; color: white;'>LAPORAN KAPASITAS RUANGAN</th></tr>";
    echo "<tr><th colspan='6'>Tanggal: " . date('d/m/Y') . " | Filter: " . 
         ($filter_gedung ? "Gedung $filter_gedung" : "") . 
         ($filter_lantai ? " | Lantai $filter_lantai" : "") . 
         ($filter_kapasitas ? " | Tipe $filter_kapasitas" : "") . 
         "</th></tr>";
    
    // Statistik
    echo "<tr><td colspan='6'><strong>STATISTIK:</strong> " . 
         "Total Ruang: {$stats['total_ruang']} | " .
         "Total Kapasitas: {$stats['total_kapasitas']} | " .
         "Rata-rata: " . number_format($stats['rata_kapasitas'], 1) . 
         "</td></tr>";
    
    echo "<tr><th>Ruang</th><th>Gedung</th><th>Lantai</th><th>Kapasitas</th><th>Booking Aktif</th><th>Status</th></tr>";
    
    foreach($ruang_data as $r) {
        $status = $r['total_booking'] > 5 ? 'Tinggi' : ($r['total_booking'] > 2 ? 'Sedang' : 'Rendah');
        echo "<tr>";
        echo "<td>{$r['nama_ruang']}</td>";
        echo "<td>{$r['gedung']}</td>";
        echo "<td>{$r['lantai']}</td>";
        echo "<td>{$r['kapasitas']}</td>";
        echo "<td>{$r['total_booking']}</td>";
        echo "<td>{$status}</td>";
        echo "</tr>";
    }
    
    echo "</table>";
    
} else {
    // Export ke PDF (HTML sederhana)
    header('Content-Type: text/html');
    echo "<!DOCTYPE html>
    <html>
    <head>
        <title>Laporan Kapasitas</title>
        <style>
            body { font-family: Arial, sans-serif; margin: 20px; }
            .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #333; padding-bottom: 10px; }
            .table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
            .table th, .table td { border: 1px solid #ddd; padding: 8px; text-align: left; }
            .table th { background-color: #f2f2f2; }
            .summary { background: #f8f9fa; padding: 15px; border-radius: 5px; margin-bottom: 20px; }
            .filter-info { background: #e9ecef; padding: 10px; border-radius: 5px; margin-bottom: 15px; }
        </style>
    </head>
    <body>
        <div class='header'>
            <h1>LAPORAN KAPASITAS RUANGAN</h1>
            <p>Sistem Reservasi Kelas - " . date('d/m/Y') . "</p>
        </div>
        
        <div class='filter-info'>
            <strong>Filter Terapkan:</strong><br>
            Gedung: " . ($filter_gedung ?: 'Semua') . " | 
            Lantai: " . ($filter_lantai ?: 'Semua') . " | 
            Tipe Kapasitas: " . ($filter_kapasitas ?: 'Semua') . "
        </div>
        
        <div class='summary'>
            <h3>Ringkasan</h3>
            <p><strong>Total Ruang:</strong> {$stats['total_ruang']}</p>
            <p><strong>Total Kapasitas:</strong> {$stats['total_kapasitas']} kursi</p>
            <p><strong>Rata-rata Kapasitas:</strong> " . number_format($stats['rata_kapasitas'], 1) . " kursi/ruang</p>
        </div>
        
        <h3>Data Ruangan</h3>
        <table class='table'>
            <tr>
                <th>Ruang</th>
                <th>Gedung</th>
                <th>Lantai</th>
                <th>Kapasitas</th>
                <th>Booking Aktif</th>
                <th>Utilisasi</th>
            </tr>";
            
            foreach($ruang_data as $r) {
                $utilization = $r['total_booking'] > 5 ? 'Tinggi' : ($r['total_booking'] > 2 ? 'Sedang' : 'Rendah');
                echo "<tr>
                    <td>{$r['nama_ruang']}</td>
                    <td>{$r['gedung']}</td>
                    <td>{$r['lantai']}</td>
                    <td>{$r['kapasitas']} kursi</td>
                    <td>{$r['total_booking']} booking</td>
                    <td>{$utilization}</td>
                </tr>";
            }
            
        echo "</table>
        
        <h3>Data Kelas</h3>
        <table class='table'>
            <tr>
                <th>Kelas</th>
                <th>Jurusan</th>
                <th>Jumlah Mahasiswa</th>
                <th>Kebutuhan Ruang</th>
            </tr>";
            
            foreach($kelas_data as $k) {
                $kebutuhan = $k['jumlah_mahasiswa'] > 40 ? 'Ruang Besar' : ($k['jumlah_mahasiswa'] > 35 ? 'Ruang Sedang' : 'Ruang Kecil');
                echo "<tr>
                    <td>{$k['nama_kelas']}</td>
                    <td>{$k['jurusan']}</td>
                    <td>{$k['jumlah_mahasiswa']} mahasiswa</td>
                    <td>{$kebutuhan}</td>
                </tr>";
            }
            
        echo "</table>
        
        <div style='margin-top: 30px; text-align: center; font-size: 12px; color: #666;'>
            <p>Dicetak pada: " . date('d/m/Y H:i:s') . " oleh {$_SESSION['user']['nama']}</p>
        </div>
    </body>
    </html>";
}
?>