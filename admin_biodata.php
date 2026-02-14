<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: admin_login.php");
    exit;
}
include 'config.php';

$biodata = null; // Pastikan variabel selalu didefinisikan
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['nim'])) {
    $nim = trim($_POST['nim']); // Sanitasi input NIM
    if (!empty($nim)) {
        $stmt = $conn->prepare("SELECT * FROM biodata WHERE nim = ?");
        $stmt->bind_param("s", $nim);
        $stmt->execute();
        $result = $stmt->get_result();
        $biodata = $result->fetch_assoc();
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cek Biodata Mahasiswa | Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css">
    <link rel="stylesheet" href="admin_biodata.css"> <!-- Link ke file CSS terpisah -->
    <style>
        /* Tambahan CSS khusus untuk mobile (hamburger menu) */
        .hamburger {
            display: none;
            flex-direction: column;
            cursor: pointer;
            padding: 10px;
            background: none;
            border: none;
            font-size: 24px;
        }
        .hamburger span {
            height: 3px;
            width: 25px;
            background: #333;
            margin: 3px 0;
            transition: 0.3s;
        }
        .nav-container {
            display: flex;
            gap: 20px;
        }
        .nav-container a {
            text-decoration: none;
            color: #333;
            font-weight: 500;
        }
        @media (max-width: 868px) {
            .hamburger {
                display: flex;
            }
            .nav-container {
                display: none;
                flex-direction: column;
                position: absolute;
                top: 60px;
                left: 0;
                width: 100%;
                background: #f8f9fa;
                padding: 20px;
                box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            }
            .nav-container.active {
                display: flex;
            }
            .nav-container a {
                margin: 10px 0;
            }
        }

        /* Styling untuk tombol Edit dan Delete */
        .edit-button-container {
            margin-top: 20px;
            display: flex;
            gap: 10px;
            justify-content: center;
        }
        .edit-button, .delete-button {
            padding: 10px 15px;
            text-decoration: none;
            border-radius: 5px;
            font-weight: 500;
            display: inline-block;
            transition: background-color 0.3s ease;
        }
        .edit-button {
            background-color: #007bff;
            color: white;
        }
        .edit-button:hover {
            background-color: #0056b3;
        }
        .delete-button {
            background-color: #dc3545;
            color: white;
        }
        .delete-button:hover {
            background-color: #c82333;
        }
    </style>
</head>
<body>
    <div class="container" data-aos="fade-up">
        <!-- Header Welcome -->
        <div class="welcome-header">
            <h2>📄 Cek Biodata Mahasiswa</h2>
        </div>
        
        <!-- Navbar Admin dengan Hamburger Menu untuk Mobile -->
        <nav class="navbar">
            <button class="hamburger" id="hamburger">
                <span></span>
                <span></span>
                <span></span>
            </button>
            <div class="nav-container" id="navContainer">
                <a href="admin_dashboard.php">🏠 Dashboard</a>
                <a href="admin_daftar_mahasiswa.php">👥 Daftar Mahasiswa</a>
                <a href="admin_logout.php">🚪 Logout</a>
            </div>
        </nav>
        
        <!-- Section untuk Form dan Biodata -->
        <div class="schedule-section">
            <h3>🔍 Pilih NIM untuk Melihat Biodata</h3>
            <div class="form-container">
                <form method="POST" action="">
                    <input type="text" name="nim" placeholder="Masukkan NIM Mahasiswa" required title="Masukkan NIM yang valid, contoh: 12345678">
                    <button type="submit">Cek Biodata</button>
                </form>
            </div>
            
            <?php if (isset($biodata) && $biodata): ?>
                <div class="biodata-display" data-aos="zoom-in">
                    <h4>📋 Biodata Mahasiswa</h4>
                    <div class="biodata-item"><strong>NIM:</strong> <span><?php echo htmlspecialchars($biodata['nim'] ?? ''); ?></span></div>
                    <div class="biodata-item"><strong>Nama:</strong> <span><?php echo htmlspecialchars($biodata['nama'] ?? ''); ?></span></div>
                    <div class="biodata-item"><strong>Prodi:</strong> <span><?php echo htmlspecialchars($biodata['prodi'] ?? ''); ?></span></div>
                    <div class="biodata-item"><strong>Kelas:</strong> <span><?php echo htmlspecialchars($biodata['kelas'] ?? ''); ?></span></div>
                    <div class="biodata-item"><strong>Semester:</strong> <span><?php echo htmlspecialchars($biodata['semester'] ?? ''); ?></span></div>
                    <div class="biodata-item"><strong>Jenis Kelamin:</strong> <span><?php echo htmlspecialchars($biodata['jenis_kelamin'] ?? ''); ?></span></div>
                    <div class="biodata-item"><strong>Agama:</strong> <span><?php echo htmlspecialchars($biodata['agama'] ?? ''); ?></span></div>
                    <div class="biodata-item"><strong>Nomor HP:</strong> <span><?php echo htmlspecialchars($biodata['nomor_hp'] ?? ''); ?></span></div>
                    <div class="biodata-item"><strong>Media Sosial:</strong> <span><?php echo htmlspecialchars($biodata['media_sosial'] ?? ''); ?></span></div>
                    <div class="edit-button-container">
                        <a href="admin_edit_biodata.php?nim=<?php echo urlencode($biodata['nim']); ?>" class="edit-button">✏️ Edit Biodata</a>
                        <a href="delete_biodata.php?nim=<?php echo urlencode($biodata['nim']); ?>" class="delete-button" onclick="return confirm('Apakah Anda yakin ingin menghapus biodata ini?')">🗑️ Delete Biodata</a>
                    </div>
                </div>
            <?php elseif ($_SERVER['REQUEST_METHOD'] == 'POST'): ?>
                <p class="error">❌ NIM tidak ditemukan atau data tidak tersedia.</p>
            <?php endif; ?>
            
        </div>
    </div>

    <!-- Dark Mode Toggle -->
    <div class="dark-mode-toggle" id="darkModeToggle" title="Toggle Dark Mode">
        🌙
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>
    <script>
        AOS.init({
            duration: 800,
            once: true
        });
        
        // Dark Mode Toggle
        const darkModeToggle = document.getElementById('darkModeToggle');
        darkModeToggle.addEventListener('click', () => {
            document.body.classList.toggle('dark-mode');
            darkModeToggle.textContent = document.body.classList.contains('dark-mode') ? '☀️' : '🌙';
            localStorage.setItem('darkMode', document.body.classList.contains('dark-mode'));
        });

        // Persist Dark Mode
        if (localStorage.getItem('darkMode') === 'true') {
            document.body.classList.add('dark-mode');
            darkModeToggle.textContent = '☀️';
        }

        // Loading Effect on Form Submit
        const form = document.querySelector('form');
        form.addEventListener('submit', () => {
            const button = form.querySelector('button');
            button.textContent = '🔄 Memproses...';
            button.disabled = true;
        });

        // Hamburger Menu Toggle untuk Mobile
        const hamburger = document.getElementById('hamburger');
        const navContainer = document.getElementById('navContainer');
        hamburger.addEventListener('click', () => {
            navContainer.classList.toggle('active');
        });
    </script>
</body>
</html>