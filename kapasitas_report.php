<?php
// kapasitas_report.php - Laporan Kapasitas Ruangan
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
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Database error: " . $e->getMessage());
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

// Query untuk statistik kapasitas
$stats_query = "
    SELECT 
        COUNT(*) as total_ruang,
        SUM(r.kapasitas) as total_kapasitas,
        AVG(r.kapasitas) as rata_kapasitas,
        MIN(r.kapasitas) as min_kapasitas,
        MAX(r.kapasitas) as max_kapasitas,
        COUNT(DISTINCT r.gedung) as total_gedung,
        COUNT(DISTINCT r.lantai) as total_lantai
    FROM ruang_kelas r
    $where_sql
";

$stmt = $conn->prepare($stats_query);
$stmt->execute($params);
$stats = $stmt->fetch(PDO::FETCH_ASSOC);

// Query untuk ruang dengan utilization
$util_query = "
    SELECT 
        r.nama_ruang,
        r.gedung,
        r.lantai,
        r.region,
        r.kapasitas,
        COUNT(j.id) as total_booking,
        GROUP_CONCAT(DISTINCT k.nama_kelas ORDER BY k.nama_kelas SEPARATOR ', ') as kelas_terbooking
    FROM ruang_kelas r
    LEFT JOIN jadwal_reservasi j ON r.nama_ruang = j.nama_ruang 
        AND j.tanggal >= CURDATE() 
        AND j.tanggal <= DATE_ADD(CURDATE(), INTERVAL 7 DAY)
    LEFT JOIN kelas k ON j.nama_kelas = k.nama_kelas
    $where_sql
    GROUP BY r.nama_ruang, r.gedung, r.lantai, r.region, r.kapasitas
    ORDER BY r.gedung, r.lantai, r.nama_ruang
";

$stmt = $conn->prepare($util_query);
$stmt->execute($params);
$ruang_utilization = $stmt->fetchAll();

// Query untuk rekomendasi penempatan
$rekomendasi_query = "
    SELECT 
        k.nama_kelas,
        k.jurusan,
        k.jumlah_mahasiswa,
        COUNT(r.nama_ruang) as ruang_cocok,
        GROUP_CONCAT(
            CONCAT(r.nama_ruang, ' (', r.kapasitas, ')') 
            ORDER BY r.kapasitas ASC 
            SEPARATOR ', '
        ) as ruang_rekomendasi
    FROM kelas k
    LEFT JOIN ruang_kelas r ON r.kapasitas >= k.jumlah_mahasiswa
    GROUP BY k.nama_kelas, k.jurusan, k.jumlah_mahasiswa
    ORDER BY k.jumlah_mahasiswa DESC
";

$rekomendasi = $conn->query($rekomendasi_query)->fetchAll();

// Get distinct values untuk filter
$gedung_list = $conn->query("SELECT DISTINCT gedung FROM ruang_kelas ORDER BY gedung")->fetchAll();
$lantai_list = $conn->query("SELECT DISTINCT lantai FROM ruang_kelas ORDER BY lantai")->fetchAll();

// Data untuk charts
$capacityData = [
    'kecil' => $conn->query("SELECT COUNT(*) FROM ruang_kelas WHERE kapasitas <= 35 $where_sql")->fetchColumn(),
    'sedang' => $conn->query("SELECT COUNT(*) FROM ruang_kelas WHERE kapasitas BETWEEN 36 AND 44 $where_sql")->fetchColumn(),
    'besar' => $conn->query("SELECT COUNT(*) FROM ruang_kelas WHERE kapasitas >= 45 $where_sql")->fetchColumn()
];

$gedungStats = $conn->query("SELECT gedung, COUNT(*) as count, AVG(kapasitas) as avg_cap FROM ruang_kelas $where_sql GROUP BY gedung ORDER BY gedung")->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Laporan Kapasitas - Reservasi Kelas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .sidebar { width: 250px; min-height: 100vh; position: fixed; }
        .main-content { margin-left: 250px; padding: 20px; }
        .stat-card { border-left: 4px solid #007bff; }
        .util-high { background: #f8d7da !important; }
        .util-medium { background: #fff3cd !important; }
        .util-low { background: #d1ecf1 !important; }
        .progress { height: 20px; }
        .chart-container { background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); margin-bottom: 20px; }
        .filter-box { background: #f8f9fa; padding: 20px; border-radius: 10px; margin-bottom: 20px; }
        @media print {
            .sidebar, .navbar, .filter-box, .btn { display: none !important; }
            .main-content { margin-left: 0 !important; }
            .chart-container { break-inside: avoid; }
        }
    </style>
</head>
<body>
    <div class="d-flex">
        <!-- Sidebar -->
        <div class="bg-dark text-white sidebar">
            <div class="p-3">
                <h4 class="text-center">Reservasi Kelas</h4>
                <hr>
                <ul class="nav nav-pills flex-column">
                    <li class="nav-item">
                        <a href="dashboard_with_capacity.php" class="nav-link text-white">
                            <i class="fas fa-home me-2"></i>Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="daftar_kelas_capacity.php" class="nav-link text-white">
                            <i class="fas fa-list me-2"></i>Daftar Kelas & Ruang
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="kapasitas_report.php" class="nav-link text-white active">
                            <i class="fas fa-chart-bar me-2"></i>Laporan Kapasitas
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="logout.php" class="nav-link text-white">
                            <i class="fas fa-sign-out-alt me-2"></i>Logout
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Main content -->
        <div class="main-content" style="flex: 1;">
            <nav class="navbar navbar-light bg-light mb-4">
                <div class="container-fluid">
                    <span class="navbar-brand">
                        <i class="fas fa-user me-2"></i>
                        <?php echo $_SESSION['user']['nama']; ?>
                    </span>
                    <div class="d-flex">
                        <button onclick="window.print()" class="btn btn-outline-primary me-2">
                            <i class="fas fa-print me-1"></i>Print
                        </button>
                        <div class="dropdown me-2">
                            <button class="btn btn-outline-success dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                <i class="fas fa-download me-1"></i>Export
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="kapasitas_export.php?type=pdf&gedung=<?php echo $filter_gedung; ?>&lantai=<?php echo $filter_lantai; ?>&kapasitas=<?php echo $filter_kapasitas; ?>" target="_blank">
                                    <i class="fas fa-file-pdf me-2"></i>Export PDF
                                </a></li>
                                <li><a class="dropdown-item" href="kapasitas_export.php?type=excel&gedung=<?php echo $filter_gedung; ?>&lantai=<?php echo $filter_lantai; ?>&kapasitas=<?php echo $filter_kapasitas; ?>">
                                    <i class="fas fa-file-excel me-2"></i>Export Excel
                                </a></li>
                            </ul>
                        </div>
                        <span class="navbar-text">
                            <i class="fas fa-calendar me-1"></i>
                            <?php echo date('d/m/Y'); ?>
                        </span>
                    </div>
                </div>
            </nav>

            <div class="container-fluid">
                <h2 class="mb-4">
                    <i class="fas fa-chart-bar me-2"></i>Laporan Kapasitas Ruangan
                </h2>

                <!-- Filter Section -->
                <div class="filter-box">
                    <h5><i class="fas fa-filter me-2"></i>Filter Laporan</h5>
                    <form method="GET" class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Gedung</label>
                            <select name="gedung" class="form-select">
                                <option value="">Semua Gedung</option>
                                <?php foreach($gedung_list as $g): ?>
                                    <option value="<?php echo $g['gedung']; ?>" <?php echo $filter_gedung == $g['gedung'] ? 'selected' : ''; ?>>
                                        <?php echo $g['gedung']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Lantai</label>
                            <select name="lantai" class="form-select">
                                <option value="">Semua Lantai</option>
                                <?php foreach($lantai_list as $l): ?>
                                    <option value="<?php echo $l['lantai']; ?>" <?php echo $filter_lantai == $l['lantai'] ? 'selected' : ''; ?>>
                                        Lantai <?php echo $l['lantai']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Tipe Kapasitas</label>
                            <select name="kapasitas" class="form-select">
                                <option value="">Semua Tipe</option>
                                <option value="kecil" <?php echo $filter_kapasitas == 'kecil' ? 'selected' : ''; ?>>Kecil (≤35)</option>
                                <option value="sedang" <?php echo $filter_kapasitas == 'sedang' ? 'selected' : ''; ?>>Sedang (36-44)</option>
                                <option value="besar" <?php echo $filter_kapasitas == 'besar' ? 'selected' : ''; ?>>Besar (≥45)</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">&nbsp;</label>
                            <div>
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-filter me-1"></i>Terapkan Filter
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Statistik Cards -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card stat-card">
                            <div class="card-body">
                                <h5 class="card-title">Total Ruang</h5>
                                <h2 class="text-primary"><?php echo $stats['total_ruang']; ?></h2>
                                <p class="card-text">Ruang tersedia</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card stat-card" style="border-left-color: #28a745;">
                            <div class="card-body">
                                <h5 class="card-title">Total Kapasitas</h5>
                                <h2 class="text-success"><?php echo $stats['total_kapasitas']; ?></h2>
                                <p class="card-text">Kursi tersedia</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card stat-card" style="border-left-color: #ffc107;">
                            <div class="card-body">
                                <h5 class="card-title">Rata-rata Kapasitas</h5>
                                <h2 class="text-warning"><?php echo number_format($stats['rata_kapasitas'], 1); ?></h2>
                                <p class="card-text">Kursi per ruang</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card stat-card" style="border-left-color: #17a2b8;">
                            <div class="card-body">
                                <h5 class="card-title">Rentang Kapasitas</h5>
                                <h2 class="text-info"><?php echo $stats['min_kapasitas']; ?>-<?php echo $stats['max_kapasitas']; ?></h2>
                                <p class="card-text">Kursi terkecil-terbesar</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Charts -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="chart-container">
                            <h5><i class="fas fa-chart-pie me-2"></i>Distribusi Kapasitas</h5>
                            <canvas id="capacityChart" width="400" height="200"></canvas>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="chart-container">
                            <h5><i class="fas fa-chart-bar me-2"></i>Kapasitas per Gedung</h5>
                            <canvas id="buildingChart" width="400" height="200"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Utilization Table -->
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-table me-2"></i>Utilisasi Ruang (7 Hari ke Depan)
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Ruang</th>
                                        <th>Gedung</th>
                                        <th>Lantai</th>
                                        <th>Kapasitas</th>
                                        <th>Booking (7 hari)</th>
                                        <th>Kelas yang Booking</th>
                                        <th>Status Utilisasi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($ruang_utilization as $ruang): 
                                        $utilization_class = '';
                                        if ($ruang['total_booking'] >= 5) {
                                            $utilization_class = 'util-high';
                                        } elseif ($ruang['total_booking'] >= 3) {
                                            $utilization_class = 'util-medium';
                                        } elseif ($ruang['total_booking'] > 0) {
                                            $utilization_class = 'util-low';
                                        }
                                    ?>
                                        <tr class="<?php echo $utilization_class; ?>">
                                            <td><strong><?php echo $ruang['nama_ruang']; ?></strong></td>
                                            <td><?php echo $ruang['gedung']; ?></td>
                                            <td>Lantai <?php echo $ruang['lantai']; ?></td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <span class="me-2"><?php echo $ruang['kapasitas']; ?></span>
                                                    <div class="progress" style="width: 100px;">
                                                        <div class="progress-bar 
                                                            <?php echo $ruang['kapasitas'] >= 45 ? 'bg-success' : ($ruang['kapasitas'] >= 35 ? 'bg-warning' : 'bg-info'); ?>" 
                                                            style="width: <?php echo ($ruang['kapasitas']/50)*100; ?>%">
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge <?php echo $ruang['total_booking'] > 0 ? 'bg-primary' : 'bg-secondary'; ?>">
                                                    <?php echo $ruang['total_booking']; ?> booking
                                                </span>
                                            </td>
                                            <td>
                                                <small><?php echo $ruang['kelas_terbooking'] ?: 'Tidak ada booking'; ?></small>
                                            </td>
                                            <td>
                                                <?php if ($ruang['total_booking'] == 0): ?>
                                                    <span class="badge bg-secondary">Tidak aktif</span>
                                                <?php elseif ($ruang['total_booking'] <= 2): ?>
                                                    <span class="badge bg-success">Rendah</span>
                                                <?php elseif ($ruang['total_booking'] <= 4): ?>
                                                    <span class="badge bg-warning">Sedang</span>
                                                <?php else: ?>
                                                    <span class="badge bg-danger">Tinggi</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Rekomendasi Penempatan -->
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-lightbulb me-2"></i>Rekomendasi Penempatan Kelas
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Kelas</th>
                                        <th>Jurusan</th>
                                        <th>Jumlah Mahasiswa</th>
                                        <th>Ruang yang Cocok</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($rekomendasi as $rek): ?>
                                        <tr>
                                            <td><strong><?php echo $rek['nama_kelas']; ?></strong></td>
                                            <td><?php echo $rek['jurusan']; ?></td>
                                            <td>
                                                <span class="badge 
                                                    <?php echo $rek['jumlah_mahasiswa'] > 40 ? 'bg-danger' : ($rek['jumlah_mahasiswa'] > 35 ? 'bg-warning' : 'bg-success'); ?>">
                                                    <?php echo $rek['jumlah_mahasiswa']; ?> mhs
                                                </span>
                                            </td>
                                            <td>
                                                <small><?php echo $rek['ruang_rekomendasi'] ?: 'Tidak ada ruang yang cocok'; ?></small>
                                            </td>
                                            <td>
                                                <?php if ($rek['ruang_cocok'] == 0): ?>
                                                    <span class="badge bg-danger">Tidak ada ruang</span>
                                                <?php elseif ($rek['ruang_cocok'] == 1): ?>
                                                    <span class="badge bg-warning">1 ruang cocok</span>
                                                <?php else: ?>
                                                    <span class="badge bg-success"><?php echo $rek['ruang_cocok']; ?> ruang cocok</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Data untuk charts
        const capacityData = {
            kecil: <?php echo $capacityData['kecil']; ?>,
            sedang: <?php echo $capacityData['sedang']; ?>,
            besar: <?php echo $capacityData['besar']; ?>
        };

        const buildingData = {
            labels: [<?php 
                echo '"' . implode('","', array_column($gedungStats, 'gedung')) . '"';
            ?>],
            counts: [<?php echo implode(',', array_column($gedungStats, 'count')); ?>],
            averages: [<?php 
                $averages = [];
                foreach($gedungStats as $g) {
                    $averages[] = number_format($g['avg_cap'], 1);
                }
                echo implode(',', $averages);
            ?>]
        };

        // Pie Chart - Distribusi Kapasitas
        const capacityCtx = document.getElementById('capacityChart').getContext('2d');
        new Chart(capacityCtx, {
            type: 'pie',
            data: {
                labels: ['Kecil (≤35)', 'Sedang (36-44)', 'Besar (≥45)'],
                datasets: [{
                    data: [capacityData.kecil, capacityData.sedang, capacityData.besar],
                    backgroundColor: ['#17a2b8', '#ffc107', '#28a745']
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.raw || 0;
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = Math.round((value / total) * 100);
                                return `${label}: ${value} ruang (${percentage}%)`;
                            }
                        }
                    }
                }
            }
        });

        // Bar Chart - Kapasitas per Gedung
        const buildingCtx = document.getElementById('buildingChart').getContext('2d');
        new Chart(buildingCtx, {
            type: 'bar',
            data: {
                labels: buildingData.labels,
                datasets: [{
                    label: 'Jumlah Ruang',
                    data: buildingData.counts,
                    backgroundColor: '#007bff',
                    yAxisID: 'y'
                }, {
                    label: 'Rata-rata Kapasitas',
                    data: buildingData.averages,
                    type: 'line',
                    borderColor: '#dc3545',
                    backgroundColor: 'transparent',
                    borderWidth: 2,
                    yAxisID: 'y1'
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Jumlah Ruang'
                        }
                    },
                    y1: {
                        beginAtZero: true,
                        position: 'right',
                        title: {
                            display: true,
                            text: 'Rata-rata Kapasitas'
                        },
                        grid: {
                            drawOnChartArea: false
                        }
                    }
                }
            }
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>