<?php
$message = htmlspecialchars($_GET['msg'] ?? 'Terjadi kesalahan.');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error - Oops!</title>
    <link rel="stylesheet" href="error.css">
</head>
<body>
    <div class="error-container">
        <div class="error-icon">😵</div>
        <div class="error-message"><?php echo $message; ?></div>
        <a href="register.php" class="back-link">Kembali ke Registrasi</a>
    </div>
</body>
</html>