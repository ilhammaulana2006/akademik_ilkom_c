<?php
session_start();
session_regenerate_id(true); // Mencegah session fixation
if (!isset($_SESSION['nim'])) {
    header("Location: login.php");
    exit;
}
include 'config.php';
$nim = $_SESSION['nim'];
$nama = $_SESSION['nama'];

// Pastikan koneksi database tersedia
if (!$conn) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

// Cek status maintenance
$maintenance_query = $conn->query("SELECT status FROM maintenance WHERE id = 1");
$maintenance_active = false;
if ($maintenance_query && $row = $maintenance_query->fetch_assoc()) {
    $maintenance_active = $row['status'] == 1;
}
if ($maintenance_active) {
    // Jika maintenance aktif, redirect langsung ke halaman maintenance
    header("Location: perbaikan.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tugas Kuliah | Ilkom C</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Style dipisahkan ke file CSS terpisah, misalnya style.css -->
    <link rel="stylesheet" href="dashboard.css">
</head>
<body>
    <!-- Hamburger Menu untuk HP -->
    <div class="hamburger" id="hamburger">
        <span></span>
        <span></span>
        <span></span>
    </div>

    <!-- Sidebar untuk HP -->
    <div class="sidebar" id="sidebar">
        <a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
        <a href="daftar_mahasiswa.php"><i class="fas fa-users"></i> Daftar Mahasiswa</a>
        <a href="absen.php"><i class="fas fa-clipboard-check"></i> Absen Kelas</a>
        <a href="profil.php"><i class="fas fa-id-card"></i> Profile</a>
        <a href="link_grup.php"><i class="fab fa-whatsapp"></i> WhatsApp</a>
        <a href="tugas.php"><i class="fas fa-tasks"></i> Tugas Kuliah</a>
        <a href="ubah_password.php"><i class="fas fa-key"></i> Ubah Password</a>
        <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>

    <!-- Overlay -->
    <div class="overlay" id="overlay"></div>

    <div class="container">
        <!-- Header Welcome dengan ikon dan efek -->
        <div class="welcome-header">
            <h2>🎉 Selamat Datang, <?php echo htmlspecialchars($nama); ?> (NIM: <?php echo htmlspecialchars($nim); ?>) 🎉</h2>
            <h2>🏛️ Universitas Muhammadiyah Palangkaraya Fakultas Bisnis & Informatika</h2>
            <h3>💻 Ilmu Komputer C | Semester IV</h3>
        </div>
        
        <!-- Navigasi Desktop (Grid Cards) -->
        <nav class="nav-links">
            <a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
            <a href="daftar_mahasiswa.php"><i class="fas fa-users"></i> Daftar Mahasiswa</a>
            <a href="absen.php"><i class="fas fa-clipboard-check"></i> Absen Kelas</a>
            <a href="profil.php"><i class="fas fa-id-card"></i> profile</a>
            <a href="link_grup.php"><i class="fab fa-whatsapp"></i> WhatsApp</a>
            <a href="tugas.php"><i class="fas fa-tasks"></i> Tugas Kuliah</a>
            <a href="ubah_password.php"><i class="fas fa-key"></i> Ubah Password</a>
            <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </nav>
        
        <!-- Daftar Tugas Kuliah dalam table-container -->
        <div class="schedule-section">
            <h3>📝 Daftar Tugas Kuliah</h3>
            <div class="table-container">
                <table>
                    <tr>
                        <th>Mata Kuliah</th>
                        <th>Deskripsi Tugas</th>
                        <th>Deadline</th>
                        <th>Status</th>
                        <th>Share ke WhatsApp</th>
                    </tr>
                    <?php
                    // Asumsi ada tabel tugas dengan kolom: id, mata_kuliah, deskripsi, deadline, status (misalnya 'Belum Dikerjakan', 'Sedang Dikerjakan', 'Selesai')
                    $result = $conn->query("SELECT * FROM tugas ORDER BY deadline ASC");
                    if ($result && $result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            $status_class = '';
                            if ($row['status'] == 'Selesai') {
                                $status_class = 'style="color: #28a745;"'; // Hijau untuk selesai
                            } elseif ($row['status'] == 'Sedang Dikerjakan') {
                                $status_class = 'style="color: #ffc107;"'; // Kuning untuk sedang
                            } else {
                                $status_class = 'style="color: #dc3545;"'; // Merah untuk belum
                            }
                            // Buat pesan share untuk WhatsApp
                            $pesan = "Ada tugas kuliah: " . htmlspecialchars($row['mata_kuliah']) . " - " . htmlspecialchars($row['deskripsi']) . " - Deadline: " . htmlspecialchars($row['deadline']) . " - Status: " . htmlspecialchars($row['status']);
                            $url_share = "https://wa.me/?text=" . urlencode($pesan);
                            echo "<tr>
                                <td>" . htmlspecialchars($row['mata_kuliah']) . "</td>
                                <td>" . htmlspecialchars($row['deskripsi']) . "</td>
                                <td>" . htmlspecialchars($row['deadline']) . "</td>
                                <td " . $status_class . ">" . htmlspecialchars($row['status']) . "</td>
                                <td><a href='" . $url_share . "' target='_blank' class='btn btn-success btn-sm'><i class='fab fa-whatsapp'></i> Share</a></td>
                            </tr>";
                        }
                    } else {
                        echo "<tr><td colspan='5' style='text-align: center; color: #ff6b6b;'>❌ Tidak ada tugas kuliah tersedia.</td></tr>";
                    }
                    ?>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Logo WhatsApp CS di kanan bawah -->
    <div class="whatsapp-cs">
        <a href="https://wa.me/message/WUFEKYAQO7RKD1" target="_blank" title="Hubungi CS via WhatsApp">
            <img src="https://upload.wikimedia.org/wikipedia/commons/6/6b/WhatsApp.svg" alt="WhatsApp CS">
        </a>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        // JavaScript untuk toggle sidebar di HP
        const hamburger = document.getElementById('hamburger');
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('overlay');

        hamburger.addEventListener('click', () => {
            hamburger.classList.toggle('open');
            sidebar.classList.toggle('open');
            overlay.classList.toggle('open');
        });

        overlay.addEventListener('click', () => {
            hamburger.classList.remove('open');
            sidebar.classList.remove('open');
            overlay.classList.remove('open');
        });

        // Toast Function (jika diperlukan untuk pesan)
        function showToast(message, type) {
            var toastClass = type === 'success' ? 'bg-success' : 'bg-danger';
            var toast = $('<div class="toast align-items-center text-white ' + toastClass + ' border-0" role="alert" aria-live="assertive" aria-atomic="true"><div class="d-flex"><div class="toast-body">' + message + '</div><button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button></div></div>');
            $('#toastContainer').append(toast);
            var bsToast = new bootstrap.Toast(toast[0], { autohide: true, delay: 5000 });
            bsToast.show();
            toast.on('hidden.bs.toast', function() {
                toast.remove();
            });
        }
    </script>

    <!-- Container untuk Toast -->
    <div id="toastContainer" class="toast-container position-fixed top-0 end-0 p-3"></div>

    <?php
    // Tutup koneksi database
    $conn->close();
    ?>
</body>
</html>