<?php
session_start();
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nim = trim($_POST['nim'] ?? '');
    $nama = trim($_POST['nama'] ?? '');
    $password = trim($_POST['password'] ?? '');
    
    if (empty($nim) || empty($nama) || empty($password)) {
        header("Location: register_error.php?error=empty_fields");
        exit;
    }
    
    // Hash password untuk keamanan
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // Periksa apakah NIM sudah ada
    $stmt = $conn->prepare("SELECT nim FROM mahasiswa WHERE nim = ?");
    if (!$stmt) {
        header("Location: register_error.php?error=database_error");
        exit;
    }
    $stmt->bind_param("s", $nim);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        header("Location: register_error.php?error=nim_exists");
        exit;
    }
    $stmt->close();
    
    // Insert data baru
    $stmt = $conn->prepare("INSERT INTO mahasiswa (nim, nama, password) VALUES (?, ?, ?)");
    if (!$stmt) {
        header("Location: register_error.php?error=database_error");
        exit;
    }
    $stmt->bind_param("sss", $nim, $nama, $hashed_password);
    if ($stmt->execute()) {
        header("Location: login.php?msg=Registrasi berhasil! Silakan login.");
        exit;
    } else {
        header("Location: register_error.php?error=registration_failed");
        exit;
    }
    $stmt->close();
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrasi | Ilkom C</title>
    <link rel="stylesheet" href="error.css">
</head>
<body>
    <div class="container">
        <div class="register-icon">📝</div>
        <h2>Registrasi | Ilkom C</h2>
        <form method="POST">
            <input type="text" name="nim" placeholder="NIM" required>
            <input type="text" name="nama" placeholder="Nama" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Daftar</button>
        </form>
        <p>Sudah punya akun? <a href="login.php">Login di sini</a></p>
    </div>
</body>
</html>