<?php
// index_fixed.php - Fixed login without session conflict
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Jika sudah login, redirect ke dashboard
if (isset($_SESSION['user'])) {
    header("Location: dashboard.php");
    exit();
}

// Koneksi database sederhana
$host = "localhost";
$dbname = "reservasi_kelas";
$username = "root";
$password = "anin";  // Password MySQL Anda

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("<div style='color: red; padding: 20px;'>Database Error: " . $e->getMessage() . 
        "<br><a href='final_init.php'>Jalankan Init Database</a></div>");
}

// Fungsi login sederhana
function cek_login_simple($username, $password, $conn) {
    $errors = [];
    
    if (empty($username)) $errors[] = "Masukkan ID/Username";
    if (empty($password)) $errors[] = "Password wajib diisi";
    
    if (!empty($errors)) return ['success' => false, 'errors' => $errors];
    
    $query = "SELECT * FROM dosen WHERE id_dosen = :username";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':username', $username);
    $stmt->execute();
    
    if ($stmt->rowCount() == 0) {
        $errors[] = "Username tidak ditemukan";
        return ['success' => false, 'errors' => $errors];
    }
    
    $dosen = $stmt->fetch(PDO::FETCH_ASSOC);
    
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

// Proses login
$errors = [];
$username = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    $result = cek_login_simple($username, $password, $conn);
    
    if ($result['success']) {
        header("Location: dashboard.php");
        exit();
    } else {
        $errors = $result['errors'];
        if (!isset($result['keep_username'])) {
            $username = '';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistem Reservasi Kelas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            height: 100vh;
            display: flex;
            align-items: center;
        }
        .login-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-4">
                <div class="login-container p-4">
                    <div class="text-center mb-4">
                        <i class="fas fa-university fa-3x text-primary mb-3"></i>
                        <h2>Login Dosen</h2>
                        <p class="text-muted">Sistem Reservasi Ruang Kelas</p>
                    </div>

                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <?php foreach ($errors as $error): ?>
                                <div><?php echo htmlspecialchars($error); ?></div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="">
                        <div class="mb-3">
                            <label for="username" class="form-label">ID/NIP Dosen</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-user"></i></span>
                                <input type="text" class="form-control" id="username" name="username" 
                                       value="<?php echo htmlspecialchars($username); ?>" 
                                       placeholder="Masukkan ID/NIP" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                <input type="password" class="form-control" id="password" name="password" 
                                       placeholder="Masukkan password" required>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-sign-in-alt me-2"></i>Login
                        </button>
                    </form>

                    <div class="mt-3 text-center">
                        <small class="text-muted">
                            Gunakan: D001 / password123
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>