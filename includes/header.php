<?php
// includes/header.php - FIXED VERSION
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Reservasi Kelas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
</head>
<body>
    <?php if (isset($_SESSION['user'])): ?>
    <div class="d-flex">
        <!-- Sidebar -->
        <div class="bg-dark text-white sidebar">
            <div class="p-3">
                <h4 class="text-center">Reservasi Kelas</h4>
                <hr>
                <ul class="nav nav-pills flex-column">
                    <li class="nav-item">
                        <a href="dashboard.php" class="nav-link text-white">
                            <i class="fas fa-home me-2"></i>Home
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="daftar_kelas.php" class="nav-link text-white">
                            <i class="fas fa-list me-2"></i>Daftar Kelas
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
        <div class="flex-grow-1">
            <nav class="navbar navbar-light bg-light">
                <div class="container-fluid">
                    <span class="navbar-brand mb-0 h1">
                        Selamat datang, <?php echo $_SESSION['user']['nama']; ?>
                    </span>
                </div>
            </nav>
            <div class="container-fluid mt-3">
    <?php endif; ?>