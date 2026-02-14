<?php
session_start();
include 'config.php';  // Pastikan $conn didefinisikan

// Periksa status maintenance
$query = $conn->prepare("SELECT status FROM maintenance WHERE id = 1");
$query->execute();
$result = $query->get_result();
$maintenance = $result->fetch_assoc();
$query->close();

if ($maintenance && $maintenance['status'] == 1) {
    // Jika maintenance aktif, tampilkan pesan dan hentikan eksekusi
    $maintenance_message = "<div class='alert alert-warning text-center'><i class='fas fa-tools'></i> Sistem sedang dalam perbaikan oleh admin. Silakan coba lagi nanti.</div>";
} else {
    $maintenance_message = "";
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && empty($maintenance_message)) {
    $nim = trim($_POST['nim'] ?? '');
    $password = trim($_POST['password'] ?? '');
    
    // Validasi input
    if (empty($nim) || empty($password)) {
        header("Location: login_error.php?error=empty_fields");
        exit;
    }
    if (strlen($password) < 8) {
        header("Location: login_error.php?error=invalid_password_length");
        exit;
    }
    
    // Ambil data user berdasarkan NIM
    $stmt = $conn->prepare("SELECT id, nama, password FROM mahasiswa WHERE nim = ?");
    if (!$stmt) {
        header("Location: login_error.php?error=database_error");
        exit;
    }
    $stmt->bind_param("s", $nim);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();
    
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['nim'] = $nim;
        $_SESSION['nama'] = $user['nama'];
        header("Location: dashboard.php");
        exit;
    } else {
        header("Location: login_error.php?error=invalid_credentials");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Ilkom C</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome untuk ikon -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Google Fonts untuk font yang lebih keren -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="login.css">
</head>
<body>
    <div class="container">
        <h2><i class="fas fa-user-circle"></i> Login Akademik | Ilkom C</h2>
        
        <!-- Tampilkan pesan maintenance jika aktif -->
        <?php echo $maintenance_message; ?>
        
        <!-- Form login hanya ditampilkan jika tidak maintenance -->
        <?php if (empty($maintenance_message)): ?>
        <form method="POST">
            <input type="text" name="nim" class="form-control" placeholder="NIM" required>
            <input type="password" name="password" class="form-control" placeholder="Password" required>
            <button type="submit" class="btn btn-primary"><i class="fas fa-sign-in-alt"></i> Login</button>
        </form>
        
        <div class="links">
            <p>Belum punya akun? <a href="register.php">Daftar di sini</a></p>
            <button class="btn btn-outline-info" onclick="window.location.href='reset_sandi.php'">
                <i class="fas fa-key"></i> Lupa Kata Sandi
            </button>
        </div>
        
        <button class="btn btn-secondary" onclick="window.location.href='index.php'">
            <i class="fas fa-calendar-alt"></i> Lihat Jadwal
        </button>
        <?php endif; ?>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>