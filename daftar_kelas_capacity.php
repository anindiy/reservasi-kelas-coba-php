<?php
// daftar_kelas_capacity.php - Daftar kelas dan ruang dengan kapasitas
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
    die("Database error: " . $e->getMessage());
}

// Get data
$kelas_list = $conn->query("SELECT * FROM kelas ORDER BY nama_kelas")->fetchAll();
$ruang_list = $conn->query("SELECT * FROM ruang_kelas ORDER BY gedung, lantai, nama_ruang")->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Daftar Kelas & Ruang - Kapasitas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .sidebar { width: 250px; min-height: 100vh; position: fixed; }
        .main-content { margin-left: 250px; padding: 20px; }
        .capacity-bar { height: 20px; }
        .table th { background: #f8f9fa; }
    </style>
</head>
<body>
    <div class="d-flex">
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
                        <a href="daftar_kelas_capacity.php" class="nav-link text-white active">
                            <i class="fas fa-list me-2"></i>Daftar Kelas & Ruang
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="kapasitas_report.php" class="nav-link text-white">
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

        <div class="main-content" style="flex: 1;">
            <nav class="navbar navbar-light bg-light mb-4">
                <div class="container-fluid">
                    <span class="navbar-brand">
                        <i class="fas fa-user me-2"></i>
                        <?php echo $_SESSION['user']['nama']; ?>
                    </span>
                </div>
            </nav>

            <div class="container-fluid">
                <h2 class="mb-4">
                    <i class="fas fa-list me-2"></i>Daftar Kelas & Ruangan
                </h2>

                <div class="row">
                    <!-- Daftar Kelas -->
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0">
                                    <i class="fas fa-users me-2"></i>Daftar Kelas
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
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach($kelas_list as $k): ?>
                                                <tr>
                                                    <td><strong><?php echo $k['nama_kelas']; ?></strong></td>
                                                    <td><?php echo $k['jurusan']; ?></td>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <span class="me-2"><?php echo $k['jumlah_mahasiswa']; ?></span>
                                                            <div class="progress capacity-bar" style="width: 100px;">
                                                                <div class="progress-bar 
                                                                    <?php echo $k['jumlah_mahasiswa'] > 40 ? 'bg-warning' : 'bg-success'; ?>" 
                                                                    style="width: <?php echo min(($k['jumlah_mahasiswa']/50)*100, 100); ?>%">
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <?php if ($k['jumlah_mahasiswa'] <= 35): ?>
                                                            <span class="badge bg-success">Kecil</span>
                                                        <?php elseif ($k['jumlah_mahasiswa'] <= 45): ?>
                                                            <span class="badge bg-warning">Sedang</span>
                                                        <?php else: ?>
                                                            <span class="badge bg-danger">Besar</span>
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

                    <!-- Daftar Ruang -->
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header bg-success text-white">
                                <h5 class="mb-0">
                                    <i class="fas fa-door-open me-2"></i>Daftar Ruangan
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Ruang</th>
                                                <th>Gedung</th>
                                                <th>Lantai</th>
                                                <th>Kapasitas</th>
                                                <th>Tipe</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach($ruang_list as $r): ?>
                                                <tr>
                                                    <td><strong><?php echo $r['nama_ruang']; ?></strong></td>
                                                    <td><?php echo $r['gedung']; ?></td>
                                                    <td>Lantai <?php echo $r['lantai']; ?></td>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <span class="me-2"><?php echo $r['kapasitas']; ?></span>
                                                            <div class="progress capacity-bar" style="width: 100px;">
                                                                <div class="progress-bar 
                                                                    <?php echo $r['kapasitas'] >= 45 ? 'bg-success' : ($r['kapasitas'] >= 35 ? 'bg-warning' : 'bg-info'); ?>" 
                                                                    style="width: <?php echo ($r['kapasitas']/50)*100; ?>%">
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <?php if ($r['kapasitas'] >= 45): ?>
                                                            <span class="badge bg-success">Besar</span>
                                                        <?php elseif ($r['kapasitas'] >= 35): ?>
                                                            <span class="badge bg-warning">Sedang</span>
                                                        <?php else: ?>
                                                            <span class="badge bg-info">Kecil</span>
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

                <!-- Rekomendasi -->
                <div class="card mt-4">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-lightbulb me-2"></i>Rekomendasi Penempatan Kelas
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Kelas</th>
                                        <th>Jumlah Mahasiswa</th>
                                        <th>Ruang yang Cocok</th>
                                        <th>Keterangan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($kelas_list as $k): ?>
                                        <tr>
                                            <td><strong><?php echo $k['nama_kelas']; ?></strong></td>
                                            <td><?php echo $k['jumlah_mahasiswa']; ?> mhs</td>
                                            <td>
                                                <?php 
                                                $cocok = [];
                                                foreach($ruang_list as $r) {
                                                    if ($r['kapasitas'] >= $k['jumlah_mahasiswa']) {
                                                        $cocok[] = $r['nama_ruang'] . ' (' . $r['kapasitas'] . ')';
                                                    }
                                                }
                                                echo implode(', ', $cocok);
                                                ?>
                                            </td>
                                            <td>
                                                <?php if (empty($cocok)): ?>
                                                    <span class="badge bg-danger">Tidak ada ruang yang cocok</span>
                                                <?php else: ?>
                                                    <span class="badge bg-success"><?php echo count($cocok); ?> ruang tersedia</span>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>