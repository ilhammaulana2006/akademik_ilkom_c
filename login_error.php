<?php
session_start(); // Opsional, jika perlu session
$error = $_GET['error'] ?? 'unknown';

$message = '';
switch ($error) {
    case 'empty_fields':
        $message = '❌ NIM, Nama, dan Password wajib didaftar!';
        break;
    case 'database_error':
        $message = '❌ Terjadi kesalahan pada database. Coba lagi nanti.';
        break;
    case 'invalid_credentials':
        $message = '❌ NIM, Nama, atau Password salah! Anda Belum Daftar Akunnya';
        break;
    default:
        $message = '❌ Terjadi kesalahan yang tidak diketahui.';
        break;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login Gagal | Ilkom C</title>
    <link rel="stylesheet" href="login_error.css"> <!-- Gunakan CSS yang sama jika ada -->
</head>
<body>
    <div class="container">
        <h2>Login Gagal</h2>
        <p><?php echo htmlspecialchars($message); ?></p>
        <p><a href="login.php">Kembali ke Login</a></p>
    </div>
</body>
</html>