<?php
require_once 'config/database.php';

function pesan_kelas($data) {
    $database = new Database();
    $conn = $database->getConnection();
    
    $errors = [];
    
    // Validasi data
    if (empty($data['mata_kuliah']) || empty($data['kelas']) || 
        empty($data['jam_awal']) || empty($data['jam_akhir']) || 
        empty($data['ruang']) || empty($data['tanggal'])) {
        $errors[] = "Semua field wajib diisi";
        return ['success' => false, 'errors' => $errors];
    }
    
    // Validasi jam
    if ($data['jam_akhir'] <= $data['jam_awal']) {
        $errors[] = "Jam akhir harus lebih besar dari jam awal";
        return ['success' => false, 'errors' => $errors];
    }
    
    // Validasi tanggal
    $tanggal = DateTime::createFromFormat('Y-m-d', $data['tanggal']);
    $hari_ini = new DateTime();
    $hari_ini->setTime(0, 0, 0);
    
    if ($tanggal < $hari_ini) {
        $errors[] = "Tidak bisa memesan hari sebelumnya";
        return ['success' => false, 'errors' => $errors];
    }
    
    // Batas maksimal 5 hari ke depan
    $max_date = clone $hari_ini;
    $max_date->modify('+5 days');
    if ($tanggal > $max_date) {
        $errors[] = "Maksimal pemesanan 5 hari ke depan";
        return ['success' => false, 'errors' => $errors];
    }
    
    // Cek hari minggu
    if ($tanggal->format('w') == 0) {
        $errors[] = "Tidak bisa memesan hari Minggu";
        return ['success' => false, 'errors' => $errors];
    }
    
    // Cek ketersediaan ruang
    if (!cekKetersediaanRuang($data['ruang'], $data['tanggal'], $data['jam_awal'], $data['jam_akhir'])) {
        $errors[] = "Ruang tidak tersedia pada jam dan tanggal yang dipilih";
        return ['success' => false, 'errors' => $errors];
    }
    
    // Simpan reservasi
    try {
        $query = "INSERT INTO jadwal_reservasi (id_dosen, kode_mk, nama_kelas, jam_mulai, jam_akhir, tanggal, nama_ruang) 
                  VALUES (:id_dosen, :kode_mk, :nama_kelas, :jam_mulai, :jam_akhir, :tanggal, :nama_ruang)";
        
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':id_dosen', $_SESSION['user']['id_dosen']);
        $stmt->bindParam(':kode_mk', $data['mata_kuliah']);
        $stmt->bindParam(':nama_kelas', $data['kelas']);
        $stmt->bindParam(':jam_mulai', $data['jam_awal']);
        $stmt->bindParam(':jam_akhir', $data['jam_akhir']);
        $stmt->bindParam(':tanggal', $data['tanggal']);
        $stmt->bindParam(':nama_ruang', $data['ruang']);
        
        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Reservasi berhasil disimpan'];
        } else {
            $errors[] = "Gagal menyimpan reservasi";
            return ['success' => false, 'errors' => $errors];
        }
    } catch(PDOException $e) {
        $errors[] = "Terjadi kesalahan: " . $e->getMessage();
        return ['success' => false, 'errors' => $errors];
    }
}

function cekKetersediaanRuang($ruang, $tanggal, $jam_awal, $jam_akhir) {
    $database = new Database();
    $conn = $database->getConnection();
    
    $query = "SELECT COUNT(*) as total FROM jadwal_reservasi 
              WHERE nama_ruang = :ruang AND tanggal = :tanggal 
              AND ((jam_mulai BETWEEN :jam_awal AND :jam_akhir-1) 
              OR (jam_akhir BETWEEN :jam_awal+1 AND :jam_akhir)
              OR (:jam_awal BETWEEN jam_mulai AND jam_akhir-1)
              OR (:jam_akhir BETWEEN jam_mulai+1 AND jam_akhir))";
    
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':ruang', $ruang);
    $stmt->bindParam(':tanggal', $tanggal);
    $stmt->bindParam(':jam_awal', $jam_awal);
    $stmt->bindParam(':jam_akhir', $jam_akhir);
    $stmt->execute();
    
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result['total'] == 0;
}

function getMataKuliah() {
    $database = new Database();
    $conn = $database->getConnection();
    
    $query = "SELECT * FROM mata_kuliah ORDER BY nama_mk";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getKelas() {
    $database = new Database();
    $conn = $database->getConnection();
    
    $query = "SELECT * FROM kelas ORDER BY nama_kelas";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getRuangKelas() {
    $database = new Database();
    $conn = $database->getConnection();
    
    $query = "SELECT * FROM ruang_kelas ORDER BY nama_ruang";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getJamKuliah() {
    $database = new Database();
    $conn = $database->getConnection();
    
    $query = "SELECT * FROM jam_kuliah ORDER BY jam_ke";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getRuangTersedia($filter = []) {
    $database = new Database();
    $conn = $database->getConnection();
    
    $query = "SELECT r.* FROM ruang_kelas r WHERE 1=1";
    $params = [];
    
    if (!empty($filter['region'])) {
        $query .= " AND r.region = :region";
        $params[':region'] = $filter['region'];
    }
    
    if (!empty($filter['gedung'])) {
        $query .= " AND r.gedung = :gedung";
        $params[':gedung'] = $filter['gedung'];
    }
    
    if (!empty($filter['lantai'])) {
        $query .= " AND r.lantai = :lantai";
        $params[':lantai'] = $filter['lantai'];
    }
    
    $query .= " ORDER BY r.nama_ruang";
    
    $stmt = $conn->prepare($query);
    $stmt->execute($params);
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>