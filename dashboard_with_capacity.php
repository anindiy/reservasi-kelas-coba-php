<?php
// dashboard_with_capacity.php - Dashboard dengan fitur kapasitas
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Redirect jika belum login
if (!isset($_SESSION['user'])) {
    header("Location: index_fixed.php");
    exit();
}

// Koneksi database
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

// Process reservasi dengan validasi kapasitas
$message = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $mata_kuliah = $_POST['mata_kuliah'] ?? '';
    $kelas = $_POST['kelas'] ?? '';
    $jam_awal = $_POST['jam_awal'] ?? '';
    $jam_akhir = $_POST['jam_akhir'] ?? '';
    $ruang = $_POST['ruang'] ?? '';
    $tanggal = $_POST['tanggal'] ?? '';
    
    // Validasi dasar
    if (empty($mata_kuliah) || empty($kelas) || empty($jam_awal) || empty($jam_akhir) || empty($ruang) || empty($tanggal)) {
        $message = '<div class="alert alert-danger">Semua field harus diisi</div>';
    } elseif ($jam_akhir <= $jam_awal) {
        $message = '<div class="alert alert-danger">Jam akhir harus lebih besar dari jam awal</div>';
    } else {
        // Validasi kapasitas
        $stmt = $conn->prepare("SELECT k.jumlah_mahasiswa, r.kapasitas 
                               FROM kelas k, ruang_kelas r 
                               WHERE k.nama_kelas = ? AND r.nama_ruang = ?");
        $stmt->execute([$kelas, $ruang]);
        $kapasitas = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($kapasitas) {
            $jumlah_mahasiswa = $kapasitas['jumlah_mahasiswa'];
            $kapasitas_ruang = $kapasitas['kapasitas'];
            
            if ($jumlah_mahasiswa > $kapasitas_ruang) {
                $message = '<div class="alert alert-danger"> 
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Kapasitas tidak mencukupi!</strong><br>
                    Kelas ' . $kelas . ' memiliki ' . $jumlah_mahasiswa . ' mahasiswa, 
                    tetapi ruang ' . $ruang . ' hanya berkapasitas ' . $kapasitas_ruang . ' mahasiswa.
                </div>';
            } else {
                // Cek ketersediaan ruang
                $stmt = $conn->prepare("SELECT COUNT(*) as total FROM jadwal_reservasi 
                                       WHERE nama_ruang = ? AND tanggal = ? 
                                       AND ((jam_mulai BETWEEN ? AND ?) OR (jam_akhir BETWEEN ? AND ?))");
                $stmt->execute([$ruang, $tanggal, $jam_awal, $jam_akhir, $jam_awal, $jam_akhir]);
                $result = $stmt->fetch();
                
                if ($result['total'] > 0) {
                    $message = '<div class="alert alert-danger">Ruang tidak tersedia pada jam dan tanggal tersebut</div>';
                } else {
                    // Simpan reservasi
                    $stmt = $conn->prepare("INSERT INTO jadwal_reservasi (id_dosen, kode_mk, nama_kelas, jam_mulai, jam_akhir, tanggal, nama_ruang) 
                                           VALUES (?, ?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$_SESSION['user']['id_dosen'], $mata_kuliah, $kelas, $jam_awal, $jam_akhir, $tanggal, $ruang]);
                    $message = '<div class="alert alert-success">
                        <i class="fas fa-check-circle me-2"></i>
                        <strong>Reservasi berhasil disimpan!</strong><br>
                        Kelas ' . $kelas . ' (' . $jumlah_mahasiswa . ' mahasiswa) di ruang ' . $ruang . ' (' . $kapasitas_ruang . ' kapasitas)
                    </div>';
                }
            }
        }
    }
}

// Get data untuk dropdown
$mata_kuliah = $conn->query("SELECT * FROM mata_kuliah ORDER BY nama_mk")->fetchAll();
$kelas_list = $conn->query("SELECT * FROM kelas ORDER BY nama_kelas")->fetchAll();
$ruang_list = $conn->query("SELECT * FROM ruang_kelas ORDER BY nama_ruang")->fetchAll();
$jam_list = $conn->query("SELECT * FROM jam_kuliah ORDER BY jam_ke")->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Reservasi Kelas dengan Kapasitas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .sidebar {
            width: 250px;
            min-height: 100vh;
            position: fixed;
        }
        .main-content {
            margin-left: 250px;
            padding: 20px;
        }
        .nav-link {
            border-radius: 5px;
            margin-bottom: 5px;
        }
        .nav-link:hover, .nav-link.active {
            background-color: #495057;
        }
        .capacity-badge {
            font-size: 0.75em;
            margin-left: 5px;
        }
        .ruang-info {
            background: #f8f9fa;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 20px;
        }
        .progress {
            height: 10px;
            margin-top: 5px;
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
                        <a href="dashboard_with_capacity.php" class="nav-link text-white active">
                            <i class="fas fa-home me-2"></i>Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="daftar_kelas_capacity.php" class="nav-link text-white">
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

        <!-- Main content -->
        <div class="main-content" style="flex: 1;">
            <nav class="navbar navbar-light bg-light mb-4">
                <div class="container-fluid">
                    <span class="navbar-brand mb-0 h1">
                        <i class="fas fa-user me-2"></i>
                        Selamat datang, <?php echo $_SESSION['user']['nama']; ?>
                    </span>
                </div>
            </nav>

            <div class="container-fluid">
                <h2 class="mb-4">
                    <i class="fas fa-calendar-plus me-2"></i>Pesan Kelas
                    <small class="text-muted fs-6">dengan Validasi Kapasitas</small>
                </h2>
                
                <?php echo $message; ?>
                
                <!-- Info Kapasitas -->
                <div class="ruang-info">
                    <h5><i class="fas fa-info-circle me-2"></i>Informasi Kapasitas</h5>
                    <div class="row">
                        <div class="col-md-6">
                            <strong>Kelas:</strong>
                            <?php foreach($kelas_list as $k): ?>
                                <span class="badge bg-secondary me-1">
                                    <?php echo $k['nama_kelas']; ?> (<?php echo $k['jumlah_mahasiswa']; ?> mhs)
                                </span>
                            <?php endforeach; ?>
                        </div>
                        <div class="col-md-6">
                            <strong>Ruang:</strong>
                            <?php foreach($ruang_list as $r): ?>
                                <span class="badge bg-info me-1">
                                    <?php echo $r['nama_ruang']; ?> (<?php echo $r['kapasitas']; ?> kursi)
                                </span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-body">
                        <form method="POST" action="">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="mata_kuliah" class="form-label">Mata Kuliah</label>
                                        <select class="form-select" id="mata_kuliah" name="mata_kuliah" required>
                                            <option value="">Pilih Mata Kuliah</option>
                                            <?php foreach($mata_kuliah as $mk): ?>
                                                <option value="<?php echo $mk['kode_mk']; ?>">
                                                    <?php echo htmlspecialchars($mk['nama_mk']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <div class="mb-3">
                                        <label for="kelas" class="form-label">Kelas</label>
                                        <select class="form-select" id="kelas" name="kelas" required onchange="updateKelasInfo(this.value)">
                                            <option value="">Pilih Kelas</option>
                                            <?php foreach($kelas_list as $k): ?>
                                                <option value="<?php echo $k['nama_kelas']; ?>" data-jumlah="<?php echo $k['jumlah_mahasiswa']; ?>">
                                                    <?php echo htmlspecialchars($k['nama_kelas']); ?> 
                                                    <small class="text-muted">(<?php echo $k['jumlah_mahasiswa']; ?> mahasiswa)</small>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <div id="kelasInfo" class="form-text"></div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Jam Perkuliahan</label>
                                        <div class="row">
                                            <div class="col">
                                                <select class="form-select" name="jam_awal" required>
                                                    <option value="">Jam Awal</option>
                                                    <?php foreach($jam_list as $jam): ?>
                                                        <option value="<?php echo $jam['jam_ke']; ?>">
                                                            <?php echo $jam['jam_ke'] . ' (' . substr($jam['waktu_mulai'], 0, 5) . ')'; ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="col">
                                                <select class="form-select" name="jam_akhir" required>
                                                    <option value="">Jam Akhir</option>
                                                    <?php foreach($jam_list as $jam): ?>
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
                                        <label for="ruang" class="form-label">Ruang Kelas</label>
                                        <select class="form-select" id="ruang" name="ruang" required onchange="updateRuangInfo(this.value)">
                                            <option value="">Pilih Ruang Kelas</option>
                                            <?php foreach($ruang_list as $r): ?>
                                                <option value="<?php echo $r['nama_ruang']; ?>" data-kapasitas="<?php echo $r['kapasitas']; ?>">
                                                    <?php echo htmlspecialchars($r['nama_ruang'] . ' - ' . $r['gedung']); ?> 
                                                    <small class="text-muted">(Kapasitas: <?php echo $r['kapasitas']; ?> kursi)</small>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <div id="ruangInfo" class="form-text"></div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="tanggal" class="form-label">Tanggal</label>
                                        <input type="date" class="form-control" id="tanggal" name="tanggal" 
                                               min="<?php echo date('Y-m-d'); ?>" 
                                               max="<?php echo date('Y-m-d', strtotime('+5 days')); ?>" 
                                               required>
                                    </div>

                                    <!-- Kapasitas Check -->
                                    <div class="mb-3">
                                        <div id="capacityCheck" class="alert alert-info">
                                            <i class="fas fa-sync-alt me-2"></i>
                                            Pilih kelas dan ruang untuk mengecek kapasitas
                                        </div>
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

    <script>
        function updateKelasInfo(kelas) {
            const select = document.getElementById('kelas');
            const option = select.options[select.selectedIndex];
            const jumlah = option.getAttribute('data-jumlah');
            
            document.getElementById('kelasInfo').innerHTML = 
                `Jumlah mahasiswa: <strong>${jumlah}</strong> orang`;
            checkCapacity();
        }

        function updateRuangInfo(ruang) {
            const select = document.getElementById('ruang');
            const option = select.options[select.selectedIndex];
            const kapasitas = option.getAttribute('data-kapasitas');
            
            document.getElementById('ruangInfo').innerHTML = 
                `Kapasitas ruang: <strong>${kapasitas}</strong> kursi`;
            checkCapacity();
        }

        function checkCapacity() {
            const kelasSelect = document.getElementById('kelas');
            const ruangSelect = document.getElementById('ruang');
            const capacityDiv = document.getElementById('capacityCheck');
            
            if (kelasSelect.value && ruangSelect.value) {
                const kelasOption = kelasSelect.options[kelasSelect.selectedIndex];
                const ruangOption = ruangSelect.options[ruangSelect.selectedIndex];
                
                const jumlahMahasiswa = parseInt(kelasOption.getAttribute('data-jumlah'));
                const kapasitasRuang = parseInt(ruangOption.getAttribute('data-kapasitas'));
                const selisih = kapasitasRuang - jumlahMahasiswa;
                
                if (selisih >= 0) {
                    capacityDiv.className = 'alert alert-success';
                    capacityDiv.innerHTML = `
                        <i class="fas fa-check-circle me-2"></i>
                        <strong>Kapasitas mencukupi!</strong><br>
                        Kelas ${kelasSelect.value} (${jumlahMahasiswa} mhs) → Ruang ${ruangSelect.value} (${kapasitasRuang} kursi)<br>
                        <small>Sisa kursi: ${selisih}</small>
                        <div class="progress mt-2">
                            <div class="progress-bar bg-success" style="width: ${(jumlahMahasiswa/kapasitasRuang)*100}%"></div>
                        </div>
                    `;
                } else {
                    capacityDiv.className = 'alert alert-danger';
                    capacityDiv.innerHTML = `
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Kapasitas tidak mencukupi!</strong><br>
                        Kelas ${kelasSelect.value} (${jumlahMahasiswa} mhs) → Ruang ${ruangSelect.value} (${kapasitasRuang} kursi)<br>
                        <small>Kekurangan: ${Math.abs(selisih)} kursi</small>
                        <div class="progress mt-2">
                            <div class="progress-bar bg-danger" style="width: 100%"></div>
                        </div>
                    `;
                }
            } else {
                capacityDiv.className = 'alert alert-info';
                capacityDiv.innerHTML = '<i class="fas fa-sync-alt me-2"></i>Pilih kelas dan ruang untuk mengecek kapasitas';
            }
        }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>