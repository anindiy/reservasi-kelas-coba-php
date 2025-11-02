<?php
// app.php - Aplikasi utama reservasi kelas
session_start();

// Konfigurasi database dengan password 'anin'
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

// Redirect jika belum login
if (!isset($_SESSION['user'])) {
    header("Location: login_app.php");
    exit;
}

// Logout
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: login_app.php");
    exit;
}

// Process pesan kelas
$message = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['pesan_kelas'])) {
    $data = [
        'mata_kuliah' => $_POST['mata_kuliah'],
        'kelas' => $_POST['kelas'],
        'jam_awal' => $_POST['jam_awal'],
        'jam_akhir' => $_POST['jam_akhir'],
        'ruang' => $_POST['ruang'],
        'tanggal' => $_POST['tanggal']
    ];
    
    // Validasi
    if ($data['jam_akhir'] <= $data['jam_awal']) {
        $message = '<div class="alert alert-danger">Jam akhir harus lebih besar dari jam awal</div>';
    } else {
        // Cek ketersediaan
        $stmt = $conn->prepare("SELECT COUNT(*) as total FROM jadwal_reservasi 
                               WHERE nama_ruang = ? AND tanggal = ? 
                               AND ((jam_mulai BETWEEN ? AND ?) OR (jam_akhir BETWEEN ? AND ?))");
        $stmt->execute([$data['ruang'], $data['tanggal'], $data['jam_awal'], $data['jam_akhir'], $data['jam_awal'], $data['jam_akhir']]);
        $result = $stmt->fetch();
        
        if ($result['total'] > 0) {
            $message = '<div class="alert alert-danger">Ruang tidak tersedia pada jam dan tanggal tersebut</div>';
        } else {
            // Simpan reservasi
            $stmt = $conn->prepare("INSERT INTO jadwal_reservasi (id_dosen, kode_mk, nama_kelas, jam_mulai, jam_akhir, tanggal, nama_ruang) 
                                   VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$_SESSION['user']['id_dosen'], $data['mata_kuliah'], $data['kelas'], $data['jam_awal'], $data['jam_akhir'], $data['tanggal'], $data['ruang']]);
            $message = '<div class="alert alert-success">Reservasi berhasil disimpan!</div>';
        }
    }
}

// Get data untuk dropdown
$mata_kuliah = $conn->query("SELECT * FROM mata_kuliah")->fetchAll();
$kelas = $conn->query("SELECT * FROM kelas")->fetchAll();
$ruang = $conn->query("SELECT * FROM ruang_kelas")->fetchAll();
$jam_kuliah = $conn->query("SELECT * FROM jam_kuliah")->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Aplikasi Reservasi Kelas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .sidebar { background: #343a40; color: white; height: 100vh; position: fixed; }
        .main-content { margin-left: 250px; padding: 20px; }
        .sidebar .nav-link { color: white; padding: 15px 20px; }
        .sidebar .nav-link:hover { background: #495057; }
        .sidebar .nav-link.active { background: #007bff; }
    </style>
</head>
<body>
    <div class="d-flex">
        <!-- Sidebar -->
        <div class="sidebar" style="width: 250px;">
            <div class="p-3">
                <h4 class="text-center">Reservasi Kelas</h4>
                <hr>
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link active" href="app.php">
                            <i class="fas fa-home me-2"></i>Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="app.php">
                            <i class="fas fa-calendar-plus me-2"></i>Pesan Kelas
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="daftar_kelas.php">
                            <i class="fas fa-list me-2"></i>Daftar Kelas
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="?logout=1">
                            <i class="fas fa-sign-out-alt me-2"></i>Logout
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Main Content -->
        <div class="main-content" style="flex: 1;">
            <nav class="navbar navbar-light bg-light mb-4">
                <div class="container-fluid">
                    <span class="navbar-brand">
                        <i class="fas fa-user me-2"></i>
                        Selamat datang, <?php echo $_SESSION['user']['nama']; ?> (<?php echo $_SESSION['user']['id_dosen']; ?>)
                    </span>
                </div>
            </nav>

            <div class="container-fluid">
                <h2 class="mb-4">Pesan Kelas</h2>
                
                <?php echo $message; ?>
                
                <div class="card">
                    <div class="card-body">
                        <form method="POST">
                            <input type="hidden" name="pesan_kelas" value="1">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Mata Kuliah</label>
                                        <select class="form-select" name="mata_kuliah" required>
                                            <option value="">Pilih Mata Kuliah</option>
                                            <?php foreach($mata_kuliah as $mk): ?>
                                                <option value="<?php echo $mk['kode_mk']; ?>">
                                                    <?php echo $mk['nama_mk']; ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Kelas</label>
                                        <select class="form-select" name="kelas" required>
                                            <option value="">Pilih Kelas</option>
                                            <?php foreach($kelas as $k): ?>
                                                <option value="<?php echo $k['nama_kelas']; ?>">
                                                    <?php echo $k['nama_kelas']; ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Jam Perkuliahan</label>
                                        <div class="row">
                                            <div class="col">
                                                <select class="form-select" name="jam_awal" required>
                                                    <option value="">Jam Awal</option>
                                                    <?php foreach($jam_kuliah as $jam): ?>
                                                        <option value="<?php echo $jam['jam_ke']; ?>">
                                                            <?php echo $jam['jam_ke'] . ' (' . substr($jam['waktu_mulai'], 0, 5) . ')'; ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="col">
                                                <select class="form-select" name="jam_akhir" required>
                                                    <option value="">Jam Akhir</option>
                                                    <?php foreach($jam_kuliah as $jam): ?>
                                                        <option value="<?php echo $jam['jam_ke']; ?>">
                                                            <?php echo $jam['jam_ke'] . ' (' . substr($jam['waktu_selesai'], 0, 5) . ')'; ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Ruang Kelas</label>
                                        <select class="form-select" name="ruang" required>
                                            <option value="">Pilih Ruang</option>
                                            <?php foreach($ruang as $r): ?>
                                                <option value="<?php echo $r['nama_ruang']; ?>">
                                                    <?php echo $r['nama_ruang'] . ' - ' . $r['gedung']; ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Tanggal</label>
                                        <input type="date" class="form-control" name="tanggal" 
                                               min="<?php echo date('Y-m-d'); ?>" 
                                               max="<?php echo date('Y-m-d', strtotime('+5 days')); ?>" 
                                               required>
                                    </div>

                                    <div class="mb-3">
                                        <button type="submit" class="btn btn-primary w-100 btn-lg">
                                            <i class="fas fa-calendar-check me-2"></i>Pesan Kelas
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>