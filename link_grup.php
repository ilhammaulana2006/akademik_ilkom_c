<?php
session_start();
if (!isset($_SESSION['nim'])) {
    header("Location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Whatsapp | Ilkom C</title>
    <!-- Tambahkan Font Awesome untuk ikon -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Font Google untuk kesan lebih keren -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
    <!-- Link ke file CSS eksternal -->
    <link rel="stylesheet" href="whatsapp.css">
    <style>
        /* Tambahan CSS untuk efek lebih keren */
        body {
            margin: 0;
            padding: 0;
            font-family: 'Montserrat', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            overflow-x: hidden;
            color: #fff;
        }

        .particles {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: -1;
        }

        .particle {
            position: absolute;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            animation: float 6s ease-in-out infinite;
        }

        .particle:nth-child(1) { width: 10px; height: 10px; top: 10%; left: 10%; animation-delay: 0s; }
        .particle:nth-child(2) { width: 15px; height: 15px; top: 20%; left: 80%; animation-delay: 1s; }
        .particle:nth-child(3) { width: 8px; height: 8px; top: 70%; left: 20%; animation-delay: 2s; }
        .particle:nth-child(4) { width: 12px; height: 12px; top: 50%; left: 60%; animation-delay: 3s; }
        .particle:nth-child(5) { width: 20px; height: 20px; top: 80%; left: 90%; animation-delay: 4s; }

        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(180deg); }
        }

        .button-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 20px;
            position: relative;
            z-index: 1;
        }

        .top-image {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            cursor: pointer;
            margin-bottom: 30px;
            animation: pulse 2s infinite;
        }

        .top-image:hover {
            transform: scale(1.1);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.5);
        }

        @keyframes pulse {
            0% { box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3); }
            50% { box-shadow: 0 10px 30px rgba(255, 255, 255, 0.2); }
            100% { box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3); }
        }

        .modern-php-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 15px 30px;
            margin: 10px 0;
            background: linear-gradient(45deg, #25d366, #128c7e);
            color: #fff;
            text-decoration: none;
            border-radius: 50px;
            font-size: 18px;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
            position: relative;
            overflow: hidden;
            width: 300px;
            max-width: 90%;
        }

        .modern-php-button::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s;
        }

        .modern-php-button:hover::before {
            left: 100%;
        }

        .modern-php-button:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.3);
        }

        .modern-php-button img {
            width: 30px;
            height: 30px;
            margin-right: 10px;
            border-radius: 50%;
        }

        .modern-php-button i {
            margin-right: 10px;
        }

        .dashboard-btn {
            background: linear-gradient(45deg, #ff6b6b, #ee5a24);
            margin-top: 20px;
        }

        .dashboard-btn:hover {
            background: linear-gradient(45deg, #ee5a24, #ff6b6b);
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.8);
            justify-content: center;
            align-items: center;
            animation: fadeIn 0.3s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .modal-content {
            max-width: 90%;
            max-height: 90%;
            transition: transform 0.3s ease;
        }

        .close {
            position: absolute;
            top: 20px;
            right: 30px;
            color: #fff;
            font-size: 40px;
            font-weight: bold;
            cursor: pointer;
            transition: color 0.3s ease;
        }

        .close:hover {
            color: #ff6b6b;
        }

        .zoom-controls {
            position: absolute;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            gap: 10px;
        }

        .zoom-btn {
            background: rgba(255, 255, 255, 0.2);
            border: none;
            color: #fff;
            font-size: 20px;
            padding: 10px 15px;
            border-radius: 50%;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        .zoom-btn:hover {
            background: rgba(255, 255, 255, 0.4);
        }

        /* Responsif */
        @media (max-width: 768px) {
            .modern-php-button {
                width: 250px;
                font-size: 16px;
            }
            .top-image {
                width: 120px;
                height: 120px;
            }
        }
    </style>
</head>
<body>
    <!-- Partikel background untuk efek keren -->
    <div class="particles">
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
    </div>

    <!-- Container untuk gambar dan tombol -->
    <div class="button-container">
        <!-- Gambar di atas tombol, sekarang bisa diklik untuk zoom -->
        <img src="ilkomc.png" alt="Logo ilkomc" class="top-image" onclick="openModal()">

        <!-- Tombol grup WhatsApp dengan logo -->
        <a href="https://chat.whatsapp.com/G5PaI1myXpO8wk5eCKNoFh" target="_blank" class="modern-php-button">
            <img src="ilkomc.png" alt="Logo ilkomc">
            <i class="fab fa-whatsapp"></i> Grup Kelas Ilkom C
        </a>

        <!-- Tombol nomor komite dengan ikon WhatsApp -->
        <a href="https://wa.me/6285845680894" target="_blank" class="modern-php-button">
            <i class="fab fa-whatsapp"></i> Komite Kelas Ilkom C
        </a>

        <!-- Tombol admin dengan ikon WhatsApp -->
        <a href="https://wa.me/6282253339176" target="_blank" class="modern-php-button">
            <i class="fab fa-whatsapp"></i> Admin Kelas
        </a>

        <!-- Tombol kembali ke dashboard dengan ikon panah kiri -->
        <a href="dashboard.php" class="modern-php-button dashboard-btn">
            <i class="fas fa-arrow-left"></i> Kembali ke Utama
        </a>
    </div>

    <!-- Modal untuk zoom gambar -->
    <div id="imageModal" class="modal">
        <span class="close" onclick="closeModal()">&times;</span>
        <img class="modal-content" id="modalImage" src="ilkomc.png" alt="Logo ilkomc">
        <div class="zoom-controls">
            <button class="zoom-btn" onclick="zoomOut()">-</button>
            <button class="zoom-btn" onclick="zoomIn()">+</button>
        </div>
    </div>

    <script>
        let scale = 1; // Skala awal
        const minScale = 0.5; // Skala minimum
        const maxScale = 3; // Skala maksimum
        const modalImage = document.getElementById('modalImage');

        // Fungsi untuk membuka modal
        function openModal() {
            document.getElementById('imageModal').style.display = 'flex';
            scale = 1; // Reset scale saat buka modal
            modalImage.style.transform = `scale(${scale})`;
        }

        // Fungsi untuk menutup modal
        function closeModal() {
            document.getElementById('imageModal').style.display = 'none';
        }

        // Fungsi zoom in
        function zoomIn() {
            if (scale < maxScale) {
                scale += 0.1;
                modalImage.style.transform = `scale(${scale})`;
            }
        }

        // Fungsi zoom out
        function zoomOut() {
            if (scale > minScale) {
                scale -= 0.1;
                modalImage.style.transform = `scale(${scale})`;
            }
        }

        // Tutup modal jika klik di luar gambar
        window.onclick = function(event) {
            var modal = document.getElementById('imageModal');
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        }
    </script>
</body>
</html>