<?php
require_once 'includes/header.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

redirectIfNotLoggedIn();

$filter = [
    'region' => $_GET['region'] ?? '',
    'gedung' => $_GET['gedung'] ?? '',
    'lantai' => $_GET['lantai'] ?? ''
];

$ruang_tersedia = getRuangTersedia($filter);
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <h2>Daftar Kelas Tersedia</h2>
            
            <!-- Filter -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" action="">
                        <div class="row">
                            <div class="col-md-3">
                                <label class="form-label">Region/Kampus</label>
                                <select class="form-select" name="region">
                                    <option value="">Semua Region</option>
                                    <option value="Kampus Pusat" <?php echo $filter['region'] == 'Kampus Pusat' ? 'selected' : ''; ?>>Kampus Pusat</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Gedung</label>
                                <select class="form-select" name="gedung">
                                    <option value="">Semua Gedung</option>
                                    <option value="Gedung A" <?php echo $filter['gedung'] == 'Gedung A' ? 'selected' : ''; ?>>Gedung A</option>
                                    <option value="Gedung B" <?php echo $filter['gedung'] == 'Gedung B' ? 'selected' : ''; ?>>Gedung B</option>
                                    <option value="Gedung C" <?php echo $filter['gedung'] == 'Gedung C' ? 'selected' : ''; ?>>Gedung C</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Lantai</label>
                                <select class="form-select" name="lantai">
                                    <option value="">Semua Lantai</option>
                                    <option value="1" <?php echo $filter['lantai'] == '1' ? 'selected' : ''; ?>>Lantai 1</option>
                                    <option value="2" <?php echo $filter['lantai'] == '2' ? 'selected' : ''; ?>>Lantai 2</option>
                                    <option value="3" <?php echo $filter['lantai'] == '3' ? 'selected' : ''; ?>>Lantai 3</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">&nbsp;</label>
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-filter me-2"></i>Filter
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Tabel Ruang Kelas -->
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Nama Ruang</th>
                                    <th>Gedung</th>
                                    <th>Lantai</th>
                                    <th>Region</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($ruang_tersedia)): ?>
                                    <tr>
                                        <td colspan="5" class="text-center">Tidak ada data ruang kelas</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($ruang_tersedia as $ruang): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($ruang['nama_ruang']); ?></td>
                                            <td><?php echo htmlspecialchars($ruang['gedung']); ?></td>
                                            <td><?php echo htmlspecialchars($ruang['lantai']); ?></td>
                                            <td><?php echo htmlspecialchars($ruang['region']); ?></td>
                                            <td>
                                                <span class="badge bg-success">Tersedia</span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>ssss