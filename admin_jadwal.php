<?php
session_start();

// Set session timeout ke 2 jam (7200 detik)
$timeout_duration = 7200; // 2 jam

// Jika session belum ada last_activity, set sekarang
if (!isset($_SESSION['last_activity'])) {
    $_SESSION['last_activity'] = time();
}

// Cek apakah session sudah expired
if (time() - $_SESSION['last_activity'] > $timeout_duration) {
    // Destroy session dan redirect ke halaman login admin
    session_unset();
    session_destroy();
    header("Location: admin_login.php"); // Asumsi ada halaman login admin
    exit();
}

// Update waktu terakhir aktivitas
$_SESSION['last_activity'] = time();

// Cek apakah user adalah admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: admin_login.php");
    exit;
}

include 'config.php'; // Asumsi config.php berisi koneksi database, sesuaikan jika perlu
$admin_username = $_SESSION['admin_username']; // Asumsi ada username admin

// Handle form tambah jadwal
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['tambah_jadwal'])) {
    $hari = $_POST['hari'];
    $waktu = $_POST['waktu'];
    $kode = $_POST['kode'];
    $mata_kuliah = $_POST['mata_kuliah'];
    $sks = $_POST['sks'];
    $dosen = $_POST['dosen'];
    $ruangan = $_POST['ruangan'];
    $kelas = $_POST['kelas'];
    
    $stmt = $conn->prepare("INSERT INTO jadwal (hari, waktu, kode, mata_kuliah, sks, dosen, ruangan, kelas) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssssss", $hari, $waktu, $kode, $mata_kuliah, $sks, $dosen, $ruangan, $kelas);
    $stmt->execute();
    $stmt->close();
    header("Location: admin_jadwal.php?added=1");
    exit();
}

// Handle form edit jadwal
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_jadwal'])) {
    $id = $_POST['id'];
    $hari = $_POST['hari'];
    $waktu = $_POST['waktu'];
    $kode = $_POST['kode'];
    $mata_kuliah = $_POST['mata_kuliah'];
    $sks = $_POST['sks'];
    $dosen = $_POST['dosen'];
    $ruangan = $_POST['ruangan'];
    $kelas = $_POST['kelas'];
    
    $stmt = $conn->prepare("UPDATE jadwal SET hari=?, waktu=?, kode=?, mata_kuliah=?, sks=?, dosen=?, ruangan=?, kelas=? WHERE id=?");
    $stmt->bind_param("ssssssssi", $hari, $waktu, $kode, $mata_kuliah, $sks, $dosen, $ruangan, $kelas, $id);
    $stmt->execute();
    $stmt->close();
    header("Location: admin_jadwal.php?updated=1");
    exit();
}

// Handle hapus jadwal
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM jadwal WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    header("Location: admin_jadwal.php?deleted=1");
    exit();
}

// Handle search dan filter
$search = isset($_GET['search']) ? $_GET['search'] : '';
$filter_hari = isset($_GET['filter_hari']) ? $_GET['filter_hari'] : '';

$query = "SELECT * FROM jadwal WHERE 1=1";
if (!empty($search)) {
    $query .= " AND (mata_kuliah LIKE '%$search%' OR dosen LIKE '%$search%' OR kode LIKE '%$search%')";
}
if (!empty($filter_hari)) {
    $query .= " AND hari = '$filter_hari'";
}
$query .= " ORDER BY FIELD(hari, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'), waktu";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- Tambahan untuk responsivitas HP -->
    <title>Kelola Jadwal | Admin Ilkom C</title>
    <link rel="stylesheet" href="adminjadwal.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <div class="container">
        <!-- Header Welcome -->
        <div class="welcome-header">
            <h2>📅 Kelola Jadwal</h2>
            <h3>Panel Admin Ilmu Komputer C</h3>
        </div>
        
        <!-- Navbar Admin -->
        <nav class="navbar">
            <div class="nav-container">
                <a href="admin_dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
                <a href="admin_daftar_mahasiswa.php"><i class="fas fa-users"></i> Daftar Mahasiswa</a>
                <a href="admin_biodata.php"><i class="fas fa-id-card"></i> Cek Biodata</a>
                <a href="admin_jadwal.php"><i class="fas fa-calendar-alt"></i> Kelola Jadwal</a>
                <a href="admin_ubah_password.php"><i class="fas fa-key"></i> Ubah Password</a>
                <a href="admin_logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </nav>

        <!-- Notifikasi -->
        <?php if (isset($_GET['added'])): ?>
            <div class="notification success">✅ Jadwal berhasil ditambahkan!</div>
        <?php elseif (isset($_GET['updated'])): ?>
            <div class="notification success">✅ Jadwal berhasil diperbarui!</div>
        <?php elseif (isset($_GET['deleted'])): ?>
            <div class="notification success">✅ Jadwal berhasil dihapus!</div>
        <?php endif; ?>

        <!-- Section Tambah Jadwal -->
        <div class="section">
            <h3>➕ Tambah Jadwal Baru</h3>
            <div class="add-form">
                <form method="POST">
                    <input type="text" name="hari" placeholder="Hari (e.g., Senin)" required>
                    <input type="text" name="waktu" placeholder="Waktu (e.g., 08:00-10:00)" required>
                    <input type="text" name="kode" placeholder="Kode Mata Kuliah" required>
                    <input type="text" name="mata_kuliah" placeholder="Nama Mata Kuliah" required>
                    <input type="number" name="sks" placeholder="SKS" required>
                    <input type="text" name="dosen" placeholder="Nama Dosen" required>
                    <input type="text" name="ruangan" placeholder="Ruangan" required>
                    <input type="text" name="kelas" placeholder="Kelas" required>
                    <button type="submit" name="tambah_jadwal">Tambah Jadwal</button>
                </form>
            </div>
        </div>

        <!-- Section Daftar Jadwal -->
        <div class="section">
            <h3>📋 Daftar Jadwal Kuliah</h3>
            <!-- Search dan Filter -->
            <div class="search-filter">
                <form method="GET" class="search-bar">
                    <input type="text" name="search" placeholder="Cari berdasarkan mata kuliah, dosen, atau kode..." value="<?php echo htmlspecialchars($search); ?>">
                    <button type="submit"><i class="fas fa-search"></i></button>
                </form>
                <form method="GET" class="filter-bar">
                    <select name="filter_hari">
                        <option value="">Semua Hari</option>
                        <option value="Senin" <?php if ($filter_hari == 'Senin') echo 'selected'; ?>>Senin</option>
                        <option value="Selasa" <?php if ($filter_hari == 'Selasa') echo 'selected'; ?>>Selasa</option>
                        <option value="Rabu" <?php if ($filter_hari == 'Rabu') echo 'selected'; ?>>Rabu</option>
                        <option value="Kamis" <?php if ($filter_hari == 'Kamis') echo 'selected'; ?>>Kamis</option>
                        <option value="Jumat" <?php if ($filter_hari == 'Jumat') echo 'selected'; ?>>Jumat</option>
                        <option value="Sabtu" <?php if ($filter_hari == 'Sabtu') echo 'selected'; ?>>Sabtu</option>
                        <option value="Minggu" <?php if ($filter_hari == 'Minggu') echo 'selected'; ?>>Minggu</option>
                    </select>
                    <button type="submit">Filter</button>
                </form>
            </div>
            <!-- Jadwal dalam Cards -->
            <div class="jadwal-cards">
                <?php if ($result && $result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <div class="jadwal-card">
                            <div class="card-header">
                                <h4><?php echo htmlspecialchars($row['mata_kuliah'] ?? ''); ?> (<?php echo htmlspecialchars($row['kode'] ?? ''); ?>)</h4>
                                <span class="hari"><?php echo htmlspecialchars($row['hari'] ?? ''); ?></span>
                            </div>
                            <div class="card-body">
                                <p><i class="fas fa-clock"></i> <strong>Waktu:</strong> <?php echo htmlspecialchars($row['waktu'] ?? ''); ?></p>
                                <p><i class="fas fa-user-tie"></i> <strong>Dosen:</strong> <?php echo htmlspecialchars($row['dosen'] ?? ''); ?></p>
                                <p><i class="fas fa-graduation-cap"></i> <strong>SKS:</strong> <?php echo htmlspecialchars($row['sks'] ?? ''); ?></p>
                                <p><i class="fas fa-map-marker-alt"></i> <strong>Ruangan:</strong> <?php echo htmlspecialchars($row['ruangan'] ?? ''); ?></p>
                                <p><i class="fas fa-users"></i> <strong>Kelas:</strong> <?php echo htmlspecialchars($row['kelas'] ?? ''); ?></p>
                            </div>
                            <div class="card-actions">
                                <a href="edit_jadwal.php?id=<?php echo htmlspecialchars($row['id'] ?? ''); ?>" class="btn-edit"><i class="fas fa-edit"></i> Edit</a>
                                <a href="?delete=<?php echo htmlspecialchars($row['id'] ?? ''); ?>" class="btn-delete" onclick="return confirm('Yakin hapus jadwal ini?')"><i class="fas fa-trash"></i> Hapus</a>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="no-data">❌ Tidak ada jadwal yang ditemukan.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- WhatsApp CS -->
    <div class="whatsapp-cs">
        <a href="https://wa.me/message/WUFEKYAQO7RKD1" target="_blank">
            <img src="https://img.icons8.com/color/48/000000/whatsapp.png" alt="WhatsApp">
        </a>
    </div>

    <script>
        // Auto-hide notifications after 5 seconds
        setTimeout(() => {
            const notifications = document.querySelectorAll('.notification');
            notifications.forEach(notif => notif.style.display = 'none');
        }, 5000);
    </script>
</body>
</html>