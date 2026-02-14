<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: admin_login.php");
    exit;
}
include 'config.php';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Mahasiswa</title>
    <link rel="stylesheet" href="style1.css"> <!-- Link ke file CSS eksternal -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet"> <!-- Font modern dari Google Fonts -->
    <style>
        @media (max-width: 575.9px) {
        /* Styling untuk logo WhatsApp CS (sama seperti sebelumnya untuk konsistensi) */
        .whatsapp-cs {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 1000;
        }
        .whatsapp-cs a {
            display: inline-block;
            width: 60px;
            height: 60px;
            background-color: #25d366;
            border-radius: 50%;
            text-align: center;
            line-height: 60px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
            transition: all 0.3s ease;
        }
        .whatsapp-cs a:hover {
            transform: scale(1.1);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.4);
        }
        .whatsapp-cs img {
            width: 30px;
            height: 30px;
            vertical-align: middle;
        }
        }
        /* Styling untuk badge status */
        .badge {
            display: inline-block;
            padding: 0.25em 0.5em;
            font-size: 0.75em;
            font-weight: 700;
            line-height: 1;
            text-align: center;
            white-space: nowrap;
            vertical-align: baseline;
            border-radius: 0.25rem;
        }
        .bg-success {
            color: #fff;
            background-color: #28a745;
        }
        .bg-secondary {
            color: #fff;
            background-color: #6c757d;
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h2>Daftar Mahasiswa | Ilkom C</h2>
            <p>Kelola dan lihat data mahasiswa.</p>
        </header>
        <div class="table-container">
            <?php
            // Query untuk mengambil data mahasiswa (ditambahkan status dan catatan_admin)
            $sql = "SELECT nim, nama, status, catatan_admin FROM mahasiswa";
            $result = $conn->query($sql);

            // Menampilkan dalam tabel HTML (ditambahkan kolom Status dan Catatan Admin)
            echo "<table>";
            echo "<thead><tr><th>NIM</th><th>NAMA</th><th>STATUS</th><th>CATATAN</th></tr></thead>";
            echo "<tbody>";

            if ($result && $result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($row['nim'] ?? '') . "</td>";
                    echo "<td>" . htmlspecialchars($row['nama'] ?? '') . "</td>";
                    // Tampilkan status dengan badge (hijau jika Aktif, abu-abu jika lainnya)
                    $statusClass = ($row['status'] == 'Aktif') ? 'bg-success' : 'bg-secondary';
                    echo "<td><span class='badge " . $statusClass . "'>" . htmlspecialchars($row['status'] ?? '') . "</span></td>";
                    // Tampilkan catatan admin jika ada, jika tidak tampilkan "-"
                    echo "<td>" . (!empty($row['catatan_admin']) ? htmlspecialchars($row['catatan_admin']) : '-') . "</td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='4'>Tidak ada data mahasiswa.</td></tr>";
            }

            echo "</tbody></table>";

            // Tutup koneksi
            $conn->close();
            ?>
        </div>
        <a href="admin_dashboard.php" class="back-link">← Kembali ke Halaman Utama</a>
    </div>

    <!-- Logo WhatsApp CS di kanan bawah -->
    <div class="whatsapp-cs">
        <a href="https://wa.me/6285165877506?text=Halo%20CS,%20saya%20butuh%20bantuan" target="_blank" title="Hubungi CS via WhatsApp">
            <img src="https://upload.wikimedia.org/wikipedia/commons/6/6b/WhatsApp.svg" alt="WhatsApp CS">
        </a>
    </div>
</body>
</html>