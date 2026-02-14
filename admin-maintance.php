<?php
// Halaman admin untuk mengontrol status maintenance
// Ganti $admin_ips dengan IP admin Anda
$admin_ips = ['39.194.5.10', '203.0.113.1']; // Hanya IP ini yang bisa akses

// Cek akses admin
if (!in_array($_SERVER['REMOTE_ADDR'], $admin_ips)) {
    header('HTTP/1.1 403 Forbidden');
    echo 'Akses ditolak. Hanya admin yang diizinkan.';
    exit;
}

$maintenance_flag = __DIR__ . '/maintenance.flag'; // Path ke file flag
$message = '';

// Proses toggle status
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['activate'])) {
        // Aktifkan maintenance: buat file flag
        file_put_contents($maintenance_flag, 'Maintenance mode activated at ' . date('Y-m-d H:i:s'));
        $message = 'Mode maintenance telah diaktifkan.';
    } elseif (isset($_POST['deactivate'])) {
        // Nonaktifkan maintenance: hapus file flag
        if (file_exists($maintenance_flag)) {
            unlink($maintenance_flag);
        }
        $message = 'Mode maintenance telah dinonaktifkan.';
    }
}

// Cek status saat ini
$status = file_exists($maintenance_flag) ? 'Aktif' : 'Nonaktif';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kontrol Maintenance - Admin</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            padding: 50px;
            background: #f4f4f4;
        }
        .container {
            max-width: 400px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }
        button {
            padding: 10px 20px;
            margin: 10px;
            font-size: 1em;
            cursor: pointer;
        }
        .activate { background: #d9534f; color: white; }
        .deactivate { background: #5cb85c; color: white; }
        .status { font-weight: bold; color: #d9534f; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Kontrol Mode Maintenance</h1>
        <p>Status Saat Ini: <span class="status"><?php echo $status; ?></span></p>
        <?php if ($message): ?>
            <p><?php echo $message; ?></p>
        <?php endif; ?>
        <form method="post">
            <button type="submit" name="activate" class="activate">Aktifkan Maintenance</button>
            <button type="submit" name="deactivate" class="deactivate">Nonaktifkan Maintenance</button>
        </form>
        <p><a href="/maintenance.php">Kembali ke Halaman Maintenance</a></p>
    </div>
</body>
</html>