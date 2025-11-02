<?php
// includes/auth.php - FIXED VERSION
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

function cek_login($username, $password) {
    require_once 'config/database.php';
    
    $database = new Database();
    $conn = $database->getConnection();
    
    $errors = [];
    
    // Validasi input
    if (empty($username)) {
        $errors[] = "Masukkan ID/Username";
    }
    if (empty($password)) {
        $errors[] = "Password wajib diisi";
    }
    
    if (!empty($errors)) {
        return ['success' => false, 'errors' => $errors];
    }
    
    // Cek username di database
    $query = "SELECT * FROM dosen WHERE id_dosen = :username";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':username', $username);
    $stmt->execute();
    
    if ($stmt->rowCount() == 0) {
        $errors[] = "Username tidak ditemukan";
        return ['success' => false, 'errors' => $errors];
    }
    
    $dosen = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Verifikasi password
    if (!password_verify($password, $dosen['password'])) {
        $errors[] = "Password salah";
        return ['success' => false, 'errors' => $errors, 'keep_username' => true];
    }
    
    // Login berhasil
    $_SESSION['user'] = [
        'id_dosen' => $dosen['id_dosen'],
        'nama' => $dosen['nama']
    ];
    
    return ['success' => true];
}

function isLoggedIn() {
    return isset($_SESSION['user']);
}

function redirectIfNotLoggedIn() {
    if (!isLoggedIn()) {
        header("Location: index.php");
        exit();
    }
}
?>