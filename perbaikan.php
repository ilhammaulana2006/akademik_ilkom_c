<?php
// Header untuk mencegah caching agar halaman selalu terbaru
header("Cache-Control: no-cache, must-revalidate");
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");

// Asumsikan koneksi database sudah ada (ganti dengan koneksi Anda jika berbeda)
require_once 'config.php'; // Ganti dengan file koneksi database Anda, misalnya 'config.php' yang berisi $conn

// Periksa status maintenance dari database
$query = $conn->prepare("SELECT status FROM maintenance WHERE id = 1");
$query->execute();
$result = $query->get_result();
$maintenance = $result->fetch_assoc();
$query->close();

// Jika maintenance tidak aktif (status != 1), redirect ke dashboard
if (!$maintenance || $maintenance['status'] != 1) {
    header("Location: dashboard.php"); // Ganti dengan URL dashboard yang sesuai
    exit;
}

// Jika maintenance aktif, lanjutkan menampilkan halaman maintenance
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sedang Ada Perbaikan</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', Arial, sans-serif;
            text-align: center;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #fff;
            margin: 0;
            padding: 0;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            overflow: hidden;
        }
        .container {
            max-width: 600px;
            padding: 40px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            animation: fadeIn 1s ease-in-out;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .icon {
            width: 100px;
            height: 100px;
            margin: 0 auto 20px;
            animation: spin 2s linear infinite;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        h1 {
            font-size: 2.5em;
            margin-bottom: 10px;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
        }
        p {
            font-size: 1.2em;
            margin: 10px 0;
            line-height: 1.6;
        }
        a {
            color: #ffd700;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s;
        }
        a:hover {
            color: #fff;
        }
    </style>
</head>
<body>
    <div class="container">
        <svg class="icon" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M12 2L13.09 8.26L20 9L13.09 9.74L12 16L10.91 9.74L4 9L10.91 8.26L12 2Z" fill="#ffd700"/>
            <circle cx="12" cy="12" r="10" stroke="#fff" stroke-width="2"/>
        </svg>
        <h1>Sedang Ada Perbaikan</h1>
        <p>Maaf, sedang dalam proses perbaikan. Kami bekerja keras untuk segera kembali!</p>
        <p>Estimasi waktu: <strong> sampai selesai </strong></p>
        <p>Jika ada pertanyaan, hubungi kami di <a href="mailto:ilhamaulanasamuda@gmail.com">ilhamulanasamuda@gmail.com</a></p>
    </div>
</body>
</html>