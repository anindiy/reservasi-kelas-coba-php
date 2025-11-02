<?php
require_once 'includes/header.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

redirectIfNotLoggedIn();
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <h2>Pesan Kelas</h2>
            <div class="card">
                <div class="card-body">
                    <?php
                    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                        $result = pesan_kelas($_POST);
                        
                        if ($result['success']) {
                            echo '<div class="alert alert-success">' . $result['message'] . '</div>';
                        } else {
                            echo '<div class="alert alert-danger">';
                            foreach ($result['errors'] as $error) {
                                echo '<div>' . htmlspecialchars($error) . '</div>';
                            }
                            echo '</div>';
                        }
                    }
                    ?>

                    <form method="POST" action="">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="mata_kuliah" class="form-label">Mata Kuliah</label>
                                    <select class="form-select" id="mata_kuliah" name="mata_kuliah" required>
                                        <option value="">Pilih Mata Kuliah</option>
                                        <?php
                                        $mata_kuliah = getMataKuliah();
                                        foreach ($mata_kuliah as $mk): ?>
                                            <option value="<?php echo $mk['kode_mk']; ?>">
                                                <?php echo htmlspecialchars($mk['nama_mk']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label for="kelas" class="form-label">Kelas</label>
                                    <select class="form-select" id="kelas" name="kelas" required>
                                        <option value="">Pilih Kelas</option>
                                        <?php
                                        $kelas = getKelas();
                                        foreach ($kelas as $k): ?>
                                            <option value="<?php echo $k['nama_kelas']; ?>">
                                                <?php echo htmlspecialchars($k['nama_kelas']); ?>
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
                                                <?php
                                                $jam_kuliah = getJamKuliah();
                                                foreach ($jam_kuliah as $jam): ?>
                                                    <option value="<?php echo $jam['jam_ke']; ?>">
                                                        <?php echo $jam['jam_ke'] . ' (' . substr($jam['waktu_mulai'], 0, 5) . ')'; ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="col">
                                            <select class="form-select" name="jam_akhir" required>
                                                <option value="">Jam Akhir</option>
                                                <?php foreach ($jam_kuliah as $jam): ?>
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
                                    <select class="form-select" id="ruang" name="ruang" required>
                                        <option value="">Pilih Ruang Kelas</option>
                                        <?php
                                        $ruang_kelas = getRuangKelas();
                                        foreach ($ruang_kelas as $ruang): ?>
                                            <option value="<?php echo $ruang['nama_ruang']; ?>">
                                                <?php echo htmlspecialchars($ruang['nama_ruang'] . ' - ' . $ruang['gedung']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label for="tanggal" class="form-label">Tanggal</label>
                                    <input type="date" class="form-control" id="tanggal" name="tanggal" 
                                           min="<?php echo date('Y-m-d'); ?>" 
                                           max="<?php echo date('Y-m-d', strtotime('+5 days')); ?>" 
                                           required>
                                </div>

                                <div class="mb-3">
                                    <button type="submit" class="btn btn-primary w-100">
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

<?php include 'includes/footer.php'; ?>