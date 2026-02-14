<?php
// Include file koneksi database
require_once 'config.php';

// Query untuk mengambil data jadwal (disusun ulang untuk cocok dengan header tabel: Hari, Waktu, Kode, Mata Kuliah, SKS, Dosen, Ruang)
// Menggunakan mysqli seperti di dashboard untuk konsistensi
$query = "SELECT hari, waktu, kode, mata_kuliah, sks, dosen, ruangan FROM jadwal ORDER BY FIELD(hari, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'), waktu";
$result = $conn->query($query);
$jadwal = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $jadwal[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jadwal Kuliah - Universitas Muhammadiyah Palangkaraya</title>
    <link rel="stylesheet" href="index.css">
    <!-- Favicon (Logo Tab) -->
    <link rel="icon" type="image/x-icon" href="android-chrome-512x512.png">  <!-- Ganti dengan path file logo Anda -->
    <!-- Atau jika PNG: <link rel="icon" type="image/png" href="logo.png"> -->

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome untuk ikon -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Google Fonts untuk font yang lebih keren -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            margin: 0;
            padding: 0;
        }
        .container {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 5px; /* Lebih kecil drastis */
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1); /* Lebih ringan */
            padding: 10px; /* Dikurangi drastis dari 20px */
            margin: 10px auto; /* Margin minimal */
            max-width: 900px; /* Lebih kecil untuk kompak */
        }
        h1 {
            color: #333;
            font-weight: 700;
            font-size: 1.4rem; /* Lebih kecil drastis */
            text-shadow: 0.5px 0.5px 1px rgba(0, 0, 0, 0.1); /* Lebih halus */
            margin-bottom: 8px; /* Dikurangi drastis */
        }
        .text-muted {
            color: #666 !important;
            font-size: 0.75rem; /* Lebih kecil drastis */
        }
        .table {
            border-radius: 4px; /* Lebih kecil */
            overflow: hidden;
            box-shadow: 0 1px 5px rgba(0, 0, 0, 0.05); /* Lebih ringan */
            font-size: 0.7rem; /* Font tabel lebih kecil drastis */
            margin-bottom: 10px; /* Dikurangi */
        }
        .table thead th {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            font-weight: 600;
            border: none;
            text-transform: uppercase;
            letter-spacing: 0.3px; /* Lebih kecil */
            padding: 4px; /* Dikurangi drastis */
            font-size: 0.65rem; /* Lebih kecil drastis */
        }
        .table tbody tr {
            animation: stagger 0.2s ease-out forwards; /* Lebih cepat */
            opacity: 0;
            transform: translateY(5px); /* Lebih kecil */
            height: 30px; /* Tinggi baris dikurangi drastis */
        }
        .table tbody tr:nth-child(1) { animation-delay: 0.05s; }
        .table tbody tr:nth-child(2) { animation-delay: 0.1s; }
        .table tbody tr:nth-child(3) { animation-delay: 0.15s; }
        .table tbody tr:nth-child(n+4) { animation-delay: 0.2s; }

        @keyframes stagger {
            to { opacity: 1; transform: translateY(0); }
        }
        .table tbody tr:hover {
            background-color: rgba(102, 126, 234, 0.05); /* Lebih halus */
            transform: scale(1.005); /* Scale minimal */
            transition: all 0.15s ease; /* Lebih cepat */
        }
        .table tbody td {
            vertical-align: middle;
            padding: 4px; /* Dikurangi drastis dari 8px */
            font-size: 0.65rem; /* Lebih kecil drastis */
            line-height: 1.2; /* Lebih padat */
        }
        .btn-login {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border: none;
            padding: 5px 15px; /* Dikurangi drastis */
            border-radius: 15px; /* Lebih kecil */
            text-decoration: none;
            font-weight: bold;
            font-size: 0.75rem; /* Lebih kecil drastis */
            transition: all 0.2s ease; /* Lebih cepat */
            display: inline-block;
            margin-top: 8px; /* Dikurangi drastis */
            box-shadow: 0 1px 5px rgba(102, 126, 234, 0.3); /* Lebih ringan */
        }
        .btn-login:hover {
            transform: translateY(-1px); /* Lebih kecil */
            box-shadow: 0 2px 8px rgba(102, 126, 234, 0.5);
            background: linear-gradient(135deg, #764ba2, #667eea);
            color: white;
        }
        .icon-day {
            margin-right: 3px; /* Dikurangi drastis */
            font-size: 0.6rem; /* Lebih kecil drastis */
        }
        .no-data {
            text-align: center;
            padding: 15px; /* Dikurangi drastis */
            color: #999;
            font-style: italic;
            font-size: 0.75rem; /* Lebih kecil drastis */
        }
        .header-section {
            text-align: center;
            margin-bottom: 10px; /* Dikurangi drastis */
        }
        .header-section i {
            font-size: 1.8rem; /* Dikurangi drastis dari 2.5rem */
            color: #667eea;
            margin-bottom: 5px; /* Dikurangi drastis */
        }
        /* Warna ikon hari berdasarkan hari */
        .hari-senin { color: #3498db; }
        .hari-selasa { color: #e74c3c; }
        .hari-rabu { color: #f39c12; }
        .hari-kamis { color: #9b59b6; }
        .hari-jumat { color: #1abc9c; }
        .hari-sabtu { color: #34495e; }
        .hari-minggu { color: #e67e22; }
        /* Responsif */
        @media (max-width: 768px) {
            .container {
                padding: 8px; /* Lebih kecil drastis di mobile */
                margin: 5px;
            }
            .table {
                font-size: 0.6rem; /* Lebih kecil drastis di mobile */
            }
            .table thead th, .table tbody td {
                padding: 3px; /* Dikurangi drastis di mobile */
            }
            h1 {
                font-size: 1.2rem; /* Lebih kecil drastis di mobile */
            }
            .header-section i {
                font-size: 1.5rem; /* Lebih kecil drastis di mobile */
            }
            .btn-login {
                padding: 4px 12px; /* Dikurangi drastis di mobile */
                font-size: 0.7rem; /* Lebih kecil drastis di mobile */
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header-section">
            <i class="fas fa-calendar-alt"></i>
            <h1>Jadwal Kuliah</h1>
            <p class="text-muted">Fakultas Bisnis & Informatika - Universitas Muhammadiyah Palangkaraya</p>
            <p class="text-muted">Ilmu Komputer Kelas C</p>
        </div>
        
        <div class="table-responsive">
            <table class="table table-hover"> <!-- Hapus table-striped untuk lebih padat -->
                <thead>
                    <tr>
                        <th><i class="fas fa-calendar-day icon-day"></i>Hari</th>
                        <th><i class="fas fa-clock icon-day"></i>Waktu</th>
                        <th><i class="fas fa-hashtag icon-day"></i>Kode</th>
                        <th><i class="fas fa-book icon-day"></i>Mata Kuliah</th>
                        <th><i class="fas fa-graduation-cap icon-day"></i>SKS</th>
                        <th><i class="fas fa-user-tie icon-day"></i>Dosen</th>
                        <th><i class="fas fa-map-marker-alt icon-day"></i>Ruang</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($jadwal)): ?>
                        <tr>
                            <td colspan="7" class="no-data">
                                <i class="fas fa-exclamation-triangle fa-lg"></i><br> <!-- fa-lg lebih kecil dari fa-2x -->
                                Tidak ada jadwal tersedia.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($jadwal as $index => $row): ?>
                            <tr>
                                <td><i class="fas fa-calendar-day icon-day hari-<?php echo strtolower($row['hari'] ?? ''); ?>"></i><?php echo htmlspecialchars($row['hari'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($row['waktu'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($row['kode'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($row['mata_kuliah'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($row['sks'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($row['dosen'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($row['ruangan'] ?? ''); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <div class="text-center">
            <a href="login.php" class="btn-login">
                <i class="fas fa-sign-in-alt"></i> Kembali ke Login
            </a>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>