<?php
session_start();

// Set session timeout ke 2 jam (7200 detik)
$timeout_duration = 9200; // 2 jam

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

// Include config.php dari direktori yang sama
include 'config.php';
$admin_username = $_SESSION['admin_username']; // Asumsi ada username admin

// Ambil status maintenance dari database (tabel maintenance dengan id = 1, sesuai dengan login.php)
$result_maintenance = $conn->query("SELECT status FROM maintenance WHERE id = 1");
$maintenance_mode = $result_maintenance->fetch_assoc()['status'] ?? '0'; // Default 0 (tidak aktif)

// Ambil total mahasiswa dari tabel mahasiswa (sesuai dengan database registrasi)
$result_total = $conn->query("SELECT COUNT(*) as total_mahasiswa FROM mahasiswa");
$row_total = $result_total->fetch_assoc();
$total_mahasiswa = $row_total['total_mahasiswa'];

// Hapus bagian prodi karena kolom tidak ada. Ganti dengan data dummy atau hapus.
// Untuk grafik, kita gunakan status saja.

// Ambil daftar mahasiswa terbaru untuk "Recent Users" dari tabel mahasiswa (limit 5, urutkan berdasarkan nim DESC)
// Status: Asumsikan berdasarkan nim genap/ganjil seperti sebelumnya, atau ganti logika jika ada kolom status.
$query_recent = "SELECT nim, nama, email, phone FROM mahasiswa ORDER BY nim DESC LIMIT 5";
$result_recent = $conn->query($query_recent);
$recent_users = [];
while ($row = $result_recent->fetch_assoc()) {
    // Konversi NIM string ke int dengan menghapus titik, lalu cek genap/ganjil
    $nim_numeric = (int) str_replace('.', '', $row['nim']); // Hapus titik dan konversi ke int
    $status = ($nim_numeric % 2 == 0) ? 'Active' : 'Inactive'; // Sekarang aman dari warning
    $recent_users[] = [
        'id' => $row['nim'],
        'name' => $row['nama'],
        'email' => $row['email'] ?? 'N/A', // Email opsional
        'phone' => $row['phone'] ?? 'N/A', // Phone opsional
        'status' => $status
    ];
}

// Hitung total active dari recent_users untuk stats card
$total_active = count(array_filter($recent_users, fn($u) => $u['status'] == 'Active'));

// Ambil daftar mahasiswa lengkap dari tabel mahasiswa (dengan pagination dan search)
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10; // Jumlah mahasiswa per halaman
$offset = ($page - 1) * $limit;
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';

// Query untuk daftar mahasiswa dengan limit, offset, dan search
$query_mahasiswa = "SELECT nim, nama, email, phone FROM mahasiswa WHERE nama LIKE '%$search%' OR nim LIKE '%$search%' ORDER BY nama ASC LIMIT $limit OFFSET $offset";
$result_mahasiswa = $conn->query($query_mahasiswa);

// Hitung total mahasiswa untuk pagination (dengan search)
$query_total = "SELECT COUNT(*) as total FROM mahasiswa WHERE nama LIKE '%$search%' OR nim LIKE '%$search%'";
$result_total_pages = $conn->query($query_total);
$row_total_pages = $result_total_pages->fetch_assoc();
$total_mahasiswa_filtered = $row_total_pages['total'];
$total_pages = ceil($total_mahasiswa_filtered / $limit);

// Fungsi logging untuk edukasi
function logAction($action, $userId) {
    $log = date('Y-m-d H:i:s') . " - Action: $action, User ID: $userId\n";
    file_put_contents('admin_log.txt', $log, FILE_APPEND);
}

// Handle AJAX untuk refresh Recent Users
if (isset($_GET['ajax']) && $_GET['ajax'] == 'recent_users') {
    $query_recent_ajax = "SELECT nim, nama, email, phone FROM mahasiswa ORDER BY nim DESC LIMIT 5";
    $result_recent_ajax = $conn->query($query_recent_ajax);
    $recent_users_ajax = [];
    while ($row = $result_recent_ajax->fetch_assoc()) {
        $nim_numeric = (int) str_replace('.', '', $row['nim']);
        $status = ($nim_numeric % 2 == 0) ? 'Active' : 'Inactive';
        $recent_users_ajax[] = [
            'id' => $row['nim'],
            'name' => $row['nama'],
            'email' => $row['email'] ?? 'N/A',
            'phone' => $row['phone'] ?? 'N/A',
            'status' => $status
        ];
    }
    echo json_encode($recent_users_ajax);
    exit;
}

// Handle AJAX untuk refresh Daftar Lengkap Mahasiswa
if (isset($_GET['ajax']) && $_GET['ajax'] == 'mahasiswa_list') {
    $page_ajax = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $search_ajax = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
    $offset_ajax = ($page_ajax - 1) * $limit;
    $query_mahasiswa_ajax = "SELECT nim, nama, email, phone FROM mahasiswa WHERE nama LIKE '%$search_ajax%' OR nim LIKE '%$search_ajax%' ORDER BY nama ASC LIMIT $limit OFFSET $offset_ajax";
    $result_mahasiswa_ajax = $conn->query($query_mahasiswa_ajax);
    $mahasiswa_list = [];
    while ($row = $result_mahasiswa_ajax->fetch_assoc()) {
        $mahasiswa_list[] = [
            'nim' => $row['nim'],
            'nama' => $row['nama'],
            'email' => $row['email'] ?? 'N/A',
            'phone' => $row['phone'] ?? 'N/A'
        ];
    }
    // Hitung total pages untuk pagination
    $query_total_ajax = "SELECT COUNT(*) as total FROM mahasiswa WHERE nama LIKE '%$search_ajax%' OR nim LIKE '%$search_ajax%'";
    $result_total_ajax = $conn->query($query_total_ajax);
    $total_mahasiswa_filtered_ajax = $result_total_ajax->fetch_assoc()['total'];
    $total_pages_ajax = ceil($total_mahasiswa_filtered_ajax / $limit);
    echo json_encode(['mahasiswa' => $mahasiswa_list, 'total_pages' => $total_pages_ajax, 'current_page' => $page_ajax]);
    exit;
}

// Handle Toggle Maintenance Mode (menggunakan tabel maintenance seperti login.php)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'toggle_maintenance') {
    $new_status = ($maintenance_mode == '1') ? '0' : '1';
    $stmt = $conn->prepare("UPDATE maintenance SET status = ? WHERE id = 1");
    $stmt->bind_param("s", $new_status);
    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => ($new_status == '1') ? 'Mode perbaikan diaktifkan.' : 'Mode perbaikan dinonaktifkan.', 'new_status' => $new_status]);
        logAction('Toggle Maintenance', $admin_username);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Gagal toggle maintenance mode.']);
    }
    $stmt->close();
    exit;
}

// Handle Edit (Update) Mahasiswa
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'update') {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $email = $_POST['email'] ?? NULL;
    $phone = $_POST['phone'] ?? NULL;
    
    // Validasi sederhana
    if (empty($id) || empty($name)) {
        echo json_encode(['status' => 'error', 'message' => 'ID dan Nama wajib diisi!']);
        exit;
    }
    
    // Update query
    $stmt = $conn->prepare("UPDATE mahasiswa SET nama = ?, email = ?, phone = ? WHERE nim = ?");
    $stmt->bind_param("ssss", $name, $email, $phone, $id);
    if ($stmt->execute()) {
        logAction('Update', $id);
        echo json_encode(['status' => 'success', 'message' => 'Data berhasil diupdate!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Gagal update data!']);
    }
    $stmt->close();
    exit;
}

// Handle Delete Mahasiswa
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'delete') {
    $id = $_POST['id'];
    
    if (empty($id)) {
        echo json_encode(['status' => 'error', 'message' => 'ID wajib diisi!']);
        exit;
    }
    
    // Delete query
    $stmt = $conn->prepare("DELETE FROM mahasiswa WHERE nim = ?");
    $stmt->bind_param("s", $id);
    if ($stmt->execute()) {
        logAction('Delete', $id);
        echo json_encode(['status' => 'success', 'message' => 'Data berhasil dihapus!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Gagal hapus data!']);
    }
    $stmt->close();
    exit;
}

// Handle CRUD untuk Tugas Kuliah
// Ambil daftar tugas untuk display
$query_tugas = "SELECT * FROM tugas ORDER BY deadline ASC";
$result_tugas = $conn->query($query_tugas);

// Handle Add Tugas
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'add_tugas') {
    $mata_kuliah = $_POST['mata_kuliah'];
    $deskripsi = $_POST['deskripsi'];
    $deadline = $_POST['deadline'];
    $status = $_POST['status'];
    
    if (empty($mata_kuliah) || empty($deskripsi) || empty($deadline)) {
        echo json_encode(['status' => 'error', 'message' => 'Mata Kuliah, Deskripsi, dan Deadline wajib diisi!']);
        exit;
    }
    
    $stmt = $conn->prepare("INSERT INTO tugas (mata_kuliah, deskripsi, deadline, status) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $mata_kuliah, $deskripsi, $deadline, $status);
    if ($stmt->execute()) {
        logAction('Add Tugas', $mata_kuliah);
        echo json_encode(['status' => 'success', 'message' => 'Tugas berhasil ditambahkan!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Gagal menambahkan tugas!']);
    }
    $stmt->close();
    exit;
}

// Handle Update Tugas
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'update_tugas') {
    $id = $_POST['id'];
    $mata_kuliah = $_POST['mata_kuliah'];
    $deskripsi = $_POST['deskripsi'];
    $deadline = $_POST['deadline'];
    $status = $_POST['status'];
    
    if (empty($id) || empty($mata_kuliah) || empty($deskripsi) || empty($deadline)) {
        echo json_encode(['status' => 'error', 'message' => 'ID, Mata Kuliah, Deskripsi, dan Deadline wajib diisi!']);
        exit;
    }
    
    $stmt = $conn->prepare("UPDATE tugas SET mata_kuliah = ?, deskripsi = ?, deadline = ?, status = ? WHERE id = ?");
    $stmt->bind_param("ssssi", $mata_kuliah, $deskripsi, $deadline, $status, $id);
    if ($stmt->execute()) {
        logAction('Update Tugas', $id);
        echo json_encode(['status' => 'success', 'message' => 'Tugas berhasil diupdate!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Gagal update tugas!']);
    }
    $stmt->close();
    exit;
}

// Handle Delete Tugas
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'delete_tugas') {
    $id = $_POST['id'];
    
    if (empty($id)) {
        echo json_encode(['status' => 'error', 'message' => 'ID wajib diisi!']);
        exit;
    }
    
    $stmt = $conn->prepare("DELETE FROM tugas WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        logAction('Delete Tugas', $id);
        echo json_encode(['status' => 'success', 'message' => 'Tugas berhasil dihapus!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Gagal hapus tugas!']);
    }
    $stmt->close();
    exit;
}

// Handle AJAX untuk refresh Daftar Tugas
if (isset($_GET['ajax']) && $_GET['ajax'] == 'tugas_list') {
    $query_tugas_ajax = "SELECT * FROM tugas ORDER BY deadline ASC";
    $result_tugas_ajax = $conn->query($query_tugas_ajax);
    $tugas_list = [];
    while ($row = $result_tugas_ajax->fetch_assoc()) {
        $tugas_list[] = [
            'id' => $row['id'],
            'mata_kuliah' => $row['mata_kuliah'],
            'deskripsi' => $row['deskripsi'],
            'deadline' => $row['deadline'],
            'status' => $row['status']
        ];
    }
    echo json_encode(['tugas' => $tugas_list]);
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title> Admin Akademik | Ilkom C</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Animate.css -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" rel="stylesheet">
    <!-- Custom CSS (Dipisahkan ke file eksternal untuk kemudahan pemeliharaan) -->
    <link rel="stylesheet" href="admin_dashboard.css">
    <!-- Chart.js dihapus karena charts section dihapus -->
    <style>
        /* Optimasi tambahan untuk HP (max-width: 480px) */
        @media (max-width: 480px) {
            .sidebar {
                width: 200px; /* Lebih kecil untuk HP kecil */
            }
            .btn {
                font-size: 16px; /* Pastikan tombol tidak terlalu kecil */
                padding: 0.75rem 1.5rem;
            }
            .card-text.fs-3 {
                font-size: 1.2rem !important; /* Angka lebih kecil di HP kecil */
            }
            .table th, .table td {
                padding: 0.5rem; /* Padding lebih kecil */
            }
            .pagination a {
                padding: 0.75rem 1rem;
                font-size: 16px;
            }
            .header h2 {
                font-size: 1.2rem;
            }
            .header p {
                font-size: 10px;
            }
            .search-bar input {
                font-size: 16px;
            }
            .toast {
                font-size: 14px;
            }
        }
        /* Swipe gesture untuk sidebar di mobile */
        .sidebar.swipe-open {
            left: 0;
        }
        .sidebar.swipe-close {
            left: -250px;
        }
    </style>
</head>
<body>
    <!-- Toast Notifications -->
    <div class="toast-container" id="toastContainer"></div>

    <!-- Maintenance Alert -->
    <?php if ($maintenance_mode == '1'): ?>
        <div class="alert alert-warning alert-dismissible fade show" role="alert" id="maintenanceAlert">
            <i class="fas fa-tools"></i> Sistem sedang dalam perbaikan. Beberapa fitur mungkin tidak tersedia.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- Sidebar Toggle Button -->
    <button class="btn btn-primary d-md-none animate__animated animate__bounceIn" id="sidebarToggle" style="position: fixed; top: 10px; left: 10px; z-index: 1100; border-radius: 50%; width: 50px; height: 50px;">
        <i class="fas fa-bars"></i>
    </button>

    <div class="container-fluid">
        <div class="row">
           <!-- Sidebar -->
            <nav class="col-md-2 sidebar p-3 animate__animated animate__fadeInLeft" id="sidebar">
                <h4 class="text-center mb-4">
                    <i class="fas fa-tachometer-alt"></i> Akademik Admin
                </h4>
                <ul class="list-unstyled">
                                        <li><a href="admin_dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
                    <li><a href="admin_daftar_mahasiswa.php"><i class="fas fa-users"></i> Daftar Mahasiswa</a></li>
                    <li><a href="admin_biodata.php"><i class="fas fa-id-card"></i> Cek Biodata</a></li>
                    <li><a href="admin_jadwal.php"><i class="fas fa-calendar-alt"></i> Kelola Jadwal</a></li>
                    <li><a href="admin_tugas.php"><i class="fas fa-tasks"></i> Kelola Tugas</a></li>
                    <li><a href="admin_ubah_password.php"><i class="fas fa-key"></i> Ubah Password</a></li>
                    <li><a href="admin_logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
                <!-- Dark Mode Toggle -->
                <div class="mt-4">
                    <button class="btn btn-outline-light w-100" id="darkModeToggle">
                        <i class="fas fa-moon"></i> Toggle Dark Mode
                    </button>
                </div>
            </nav>

            <!-- Main Content -->
            <main class="col-md-10 main-content animate__animated animate__fadeInRight" id="mainContent">
                <!-- Header -->
                <div class="header animate__animated animate__zoomIn d-flex justify-content-between align-items-center">
                    <div>
                        <h2><i class="fas fa-users"></i> Admin Dashboard </h2>
                        <p>Kelola dan lihat daftar mahasiswa yang terdaftar di sistem Ilmu Komputer C.</p>
                    </div>
                    <!-- Maintenance Toggle Button -->
                    <div>
                        <button class="btn btn-warning" id="maintenanceToggle">
                            <i class="fas fa-tools"></i> 
                            <?php echo ($maintenance_mode == '1') ? 'Nonaktifkan Perbaikan' : 'Aktifkan Perbaikan'; ?>
                        </button>
                    </div>
                </div>

                <!-- Stats Cards -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="card text-white bg-primary mb-3 animate__animated animate__zoomIn">
                            <div class="card-body">
                                <h5 class="card-title"><i class="fas fa-users"></i> Total Mahasiswa</h5>
                                <p class="card-text fs-3"><?php echo number_format($total_mahasiswa); ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card text-white bg-success mb-3 animate__animated animate__zoomIn">
                            <div class="card-body">
                                <h5 class="card-title"><i class="fas fa-user-check"></i> Total Active Mahasiswa</h5>
                                <p class="card-text fs-3"><?php echo $total_active; ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card text-white bg-warning mb-3 animate__animated animate__zoomIn">
                            <div class="card-body">
                                <h5 class="card-title"><i class="fas fa-clock"></i> Active Sessions</h5>
                                <p class="card-text fs-3"><?php echo rand(50, 100); // Simulasi ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Users Table -->
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h2><i class="fas fa-users"></i> Recent Users</h2>
                    <button class="btn btn-secondary" id="refreshRecentUsers"><i class="fas fa-sync-alt"></i> Refresh</button>
                </div>
                <div class="table-container mb-4">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>NIM</th>
                                <th>Nama</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="recentUsersTableBody">
                            <?php foreach ($recent_users as $mahasiswa): ?>
                                <tr class="animate__animated animate__fadeInUp">
                                    <td><?php echo htmlspecialchars($mahasiswa['id']); ?></td>
                                    <td><?php echo htmlspecialchars($mahasiswa['name']); ?></td>
                                    <td><?php echo htmlspecialchars($mahasiswa['email']); ?></td>
                                    <td><?php echo htmlspecialchars($mahasiswa['status']);?></td>
                                    <td><?php echo htmlspecialchars($mahasiswa['phone']); ?></td>
                                    <td><span class="badge bg-<?php echo ($mahasiswa['status'] == 'Active') ? 'success' : 'danger'; ?> animate__animated animate__pulse"><?php echo htmlspecialchars($mahasiswa['status']); ?></span></td>
                                    <td>
                                        <button class="btn btn-sm btn-primary edit-mahasiswa-btn animate__animated animate__bounceIn" 
                                                data-id="<?php echo htmlspecialchars($mahasiswa['id']); ?>" 
                                                data-name="<?php echo htmlspecialchars($mahasiswa['name']); ?>" 
                                                data-email="<?php echo htmlspecialchars($mahasiswa['email']); ?>"
                                                data-status="<?php echo htmlspecialchars($mahasiswa['status']);?>" 
                                                data-phone="<?php echo htmlspecialchars($mahasiswa['phone']); ?>" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#editMahasiswaModal">
                                            <i class="fas fa-edit"></i> Edit
                                        </button>
                                        <button class="btn btn-sm btn-danger delete-mahasiswa-btn animate__animated animate__bounceIn" 
                                                data-id="<?php echo htmlspecialchars($mahasiswa['id']); ?>" 
                                                data-name="<?php echo htmlspecialchars($mahasiswa['name']); ?>" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#deleteMahasiswaModal">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Daftar Lengkap Mahasiswa -->
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h2><i class="fas fa-list"></i> Daftar Lengkap Mahasiswa</h2>
                    <div class="search-bar">
                        <form method="GET" action="">
                            <input type="text" class="form-control" id="searchInput" name="search" placeholder="Cari berdasarkan nama atau NIM..." value="<?php echo htmlspecialchars($search); ?>">
                            <button type="submit" class="btn btn-primary">Cari</button>
                        </form>
                    </div>
                </div>
                <div class="table-container mb-4">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>NIM</th>
                                <th>Nama</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="mahasiswaTableBody">
                            <?php while ($row = $result_mahasiswa->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['nim']); ?></td>
                                    <td><?php echo htmlspecialchars($row['nama']); ?></td>
                                    <td><?php echo htmlspecialchars($row['email'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($row['phone'] ?? 'N/A'); ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-primary edit-mahasiswa-btn" 
                                                data-id="<?php echo htmlspecialchars($row['nim']); ?>" 
                                                data-name="<?php echo htmlspecialchars($row['nama']); ?>" 
                                                data-email="<?php echo htmlspecialchars($row['email'] ?? ''); ?>" 
                                                data-phone="<?php echo htmlspecialchars($row['phone'] ?? ''); ?>" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#editMahasiswaModal">
                                            <i class="fas fa-edit"></i> Edit
                                        </button>
                                        <button class="btn btn-sm btn-danger delete-mahasiswa-btn" 
                                                data-id="<?php echo htmlspecialchars($row['nim']); ?>" 
                                                data-name="<?php echo htmlspecialchars($row['nama']); ?>" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#deleteMahasiswaModal">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <nav aria-label="Page navigation">
                    <ul class="pagination justify-content-center" id="pagination">
                        <?php if ($page > 1): ?>
                            <li class="page-item"><a class="page-link" href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>">Previous</a></li>
                        <?php endif; ?>
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>"><a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>"><?php echo $i; ?></a></li>
                        <?php endfor; ?>
                        <?php if ($page < $total_pages): ?>
                            <li class="page-item"><a class="page-link" href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>">Next</a></li>
                        <?php endif; ?>
                    </ul>
                </nav>

                <!-- Kelola Tugas Kuliah -->
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h2><i class="fas fa-tasks"></i> Kelola Tugas Kuliah</h2>
                    <button class="btn btn-success" id="addTugasBtn"><i class="fas fa-plus"></i> Tambah Tugas</button>
                </div>
                <div class="table-container mb-4">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Mata Kuliah</th>
                                <th>Deskripsi</th>
                                <th>Deadline</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="tugasTableBody">
                            <?php while ($row = $result_tugas->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['mata_kuliah']); ?></td>
                                    <td><?php echo htmlspecialchars($row['deskripsi']); ?></td>
                                    <td><?php echo htmlspecialchars($row['deadline']); ?></td>
                                    <td><span class="badge bg-<?php echo ($row['status'] == 'Selesai') ? 'success' : (($row['status'] == 'Sedang Dikerjakan') ? 'warning' : 'danger'); ?>"><?php echo htmlspecialchars($row['status']); ?></span></td>
                                    <td>
                                        <button class="btn btn-sm btn-primary edit-tugas-btn" data-id="<?php echo $row['id']; ?>" data-mata_kuliah="<?php echo htmlspecialchars($row['mata_kuliah']); ?>" data-deskripsi="<?php echo htmlspecialchars($row['deskripsi']); ?>" data-deadline="<?php echo $row['deadline']; ?>" data-status="<?php echo $row['status']; ?>"><i class="fas fa-edit"></i> Edit</button>
                                        <button class="btn btn-sm btn-danger delete-tugas-btn" data-id="<?php echo $row['id']; ?>"><i class="fas fa-trash"></i> Delete</button>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </main>
        </div>
    </div>

    <!-- Modal Edit Mahasiswa -->
    <div class="modal fade animate__animated animate__zoomIn" id="editMahasiswaModal" tabindex="-1" aria-labelledby="editMahasiswaModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border-radius: 15px; box-shadow: 0 20px 40px rgba(0,0,0,0.3);">
                <form id="editMahasiswaForm" method="POST" action="">
                    <div class="modal-header bg-primary text-white" style="border-radius: 15px 15px 0 0;">
                        <h5 class="modal-title" id="editMahasiswaModalLabel"><i class="fas fa-edit"></i> Edit Mahasiswa</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="action" value="update">
                        <input type="hidden" name="id" id="editId">
                        <div class="mb-3">
                            <label for="editName" class="form-label"><i class="fas fa-user"></i> Nama</label>
                            <input type="text" class="form-control" id="editName" name="name" required style="border-radius: 10px;">
                        </div>
                        <div class="mb-3">
                            <label for="editEmail" class="form-label"><i class="fas fa-envelope"></i> Email</label>
                            <input type="email" class="form-control" id="editEmail" name="email" style="border-radius: 10px;">
                        </div>
                        <div class="mb-3">
                            <label for="editPhone" class="form-label"><i class="fas fa-phone"></i> Phone</label>
                            <input type="text" class="form-control" id="editPhone" name="phone" style="border-radius: 10px;">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" style="border-radius: 25px;"><i class="fas fa-times"></i> Batal</button>
                        <button type="submit" class="btn btn-primary" style="border-radius: 25px;"><i class="fas fa-save"></i> Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Delete Mahasiswa -->
    <div class="modal fade animate__animated animate__zoomIn" id="deleteMahasiswaModal" tabindex="-1" aria-labelledby="deleteMahasiswaModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border-radius: 15px; box-shadow: 0 20px 40px rgba(0,0,0,0.3);">
                <form id="deleteMahasiswaForm" method="POST" action="">
                    <div class="modal-header bg-danger text-white" style="border-radius: 15px 15px 0 0;">
                        <h5 class="modal-title" id="deleteMahasiswaModalLabel"><i class="fas fa-trash"></i> Hapus Mahasiswa</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" id="deleteId">
                        <p class="text-center"><i class="fas fa-exclamation-triangle text-warning fa-2x"></i></p>
                        <p class="text-center">Apakah Anda yakin ingin menghapus mahasiswa <strong id="deleteNameText"></strong> dengan NIM <strong id="deleteIdText"></strong>?</p>
                        <p class="text-center text-muted">Tindakan ini tidak dapat dibatalkan.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" style="border-radius: 25px;"><i class="fas fa-times"></i> Batal</button>
                        <button type="submit" class="btn btn-danger" style="border-radius: 25px;"><i class="fas fa-trash"></i> Hapus</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal untuk Add/Edit Tugas -->
    <div class="modal fade" id="tugasModal" tabindex="-1" aria-labelledby="tugasModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="tugasModalLabel">Tambah/Edit Tugas</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="tugasForm">
                        <input type="hidden" id="tugasId" name="id">
                        <div class="mb-3">
                            <label for="mataKuliah" class="form-label">Mata Kuliah</label>
                            <input type="text" class="form-control" id="mataKuliah" name="mata_kuliah" required>
                        </div>
                        <div class="mb-3">
                            <label for="deskripsi" class="form-label">Deskripsi</label>
                            <textarea class="form-control" id="deskripsi" name="deskripsi" rows="3" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="deadline" class="form-label">Deadline</label>
                            <input type="date" class="form-control" id="deadline" name="deadline" required>
                        </div>
                        <div class="mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-control" id="status" name="status" required>
                                <option value="Belum Dikerjakan">Belum Dikerjakan</option>
                                <option value="Sedang Dikerjakan">Sedang Dikerjakan</option>
                                <option value="Selesai">Selesai</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </form>
                                </div>
        </div>
    </div>
</div>

<!-- Modal untuk Delete Tugas -->
<div class="modal fade" id="deleteTugasModal" tabindex="-1" aria-labelledby="deleteTugasModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteTugasModalLabel">Konfirmasi Hapus</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Apakah Anda yakin ingin menghapus tugas ini?</p>
                <form id="deleteTugasForm">
                    <input type="hidden" id="deleteTugasId" name="id">
                    <button type="submit" class="btn btn-danger">Hapus</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        // Sidebar Toggle
        $('#sidebarToggle').click(function() {
            $('#sidebar').toggleClass('d-none d-md-block');
        });

        // Dark Mode Toggle
        $('#darkModeToggle').click(function() {
            $('body').toggleClass('bg-dark text-white');
            $('.card').toggleClass('bg-dark text-white');
            $('.table').toggleClass('table-dark');
        });

        // Maintenance Toggle
        $('#maintenanceToggle').click(function() {
            $.post('', { action: 'toggle_maintenance' }, function(data) {
                if (data.status === 'success') {
                    showToast(data.message, 'success');
                    location.reload();
                } else {
                    showToast(data.message, 'error');
                }
            }, 'json');
        });

        // Isi Modal Edit Mahasiswa saat tombol diklik
        $('.edit-mahasiswa-btn').click(function() {
            const id = $(this).data('id');
            const name = $(this).data('name');
            const email = $(this).data('email');
            const phone = $(this).data('phone');
            $('#editId').val(id);
            $('#editName').val(name);
            $('#editEmail').val(email);
            $('#editPhone').val(phone);
        });

        // Submit Form Edit Mahasiswa via AJAX
        $('#editMahasiswaForm').submit(function(e) {
            e.preventDefault();
            const formData = $(this).serialize();
            $.post('', formData, function(data) {
                if (data.status === 'success') {
                    showToast(data.message, 'success');
                    $('#editMahasiswaModal').modal('hide');
                    location.reload(); // Atau refresh tabel via AJAX
                } else {
                    showToast(data.message, 'error');
                }
            }, 'json');
        });

        // Isi Modal Delete Mahasiswa saat tombol diklik
        $('.delete-mahasiswa-btn').click(function() {
            const id = $(this).data('id');
            const name = $(this).data('name');
            $('#deleteId').val(id);
            $('#deleteIdText').text(id);
            $('#deleteNameText').text(name);
        });

        // Submit Form Delete Mahasiswa via AJAX
        $('#deleteMahasiswaForm').submit(function(e) {
            e.preventDefault();
            const formData = $(this).serialize();
            $.post('', formData, function(data) {
                if (data.status === 'success') {
                    showToast(data.message, 'success');
                    $('#deleteMahasiswaModal').modal('hide');
                    location.reload(); // Atau refresh tabel via AJAX
                } else {
                    showToast(data.message, 'error');
                }
            }, 'json');
        });

        // Refresh Recent Users
        $('#refreshRecentUsers').click(function() {
            $.get('?ajax=recent_users', function(data) {
                const tbody = $('#recentUsersTableBody');
                tbody.empty();
                data.forEach(function(mahasiswa) {
                    const badgeClass = mahasiswa.status === 'Active' ? 'success' : 'danger';
                    const row = `
                        <tr>
                            <td>${mahasiswa.id}</td>
                            <td>${mahasiswa.name}</td>
                            <td>${mahasiswa.email}</td>
                            <td>${mahasiswa.phone}</td>
                            <td><span class="badge bg-${badgeClass}">${mahasiswa.status}</span></td>
                            <td>
                                <button class="btn btn-sm btn-primary edit-mahasiswa-btn" data-id="${mahasiswa.id}" data-name="${mahasiswa.name}" data-email="${mahasiswa.email}" data-phone="${mahasiswa.phone}" data-bs-toggle="modal" data-bs-target="#editMahasiswaModal"><i class="fas fa-edit"></i> Edit</button>
                                <button class="btn btn-sm btn-danger delete-mahasiswa-btn" data-id="${mahasiswa.id}" data-name="${mahasiswa.name}" data-bs-toggle="modal" data-bs-target="#deleteMahasiswaModal"><i class="fas fa-trash"></i> Delete</button>
                            </td>
                        </tr>
                    `;
                    tbody.append(row);
                });
                showToast('Recent Users refreshed!', 'success');
            }, 'json');
        });

        // Search and Pagination AJAX for Mahasiswa
        function loadMahasiswaList(page = 1, search = '') {
            $.get('?ajax=mahasiswa_list&page=' + page + '&search=' + encodeURIComponent(search), function(data) {
                const tbody = $('#mahasiswaTableBody');
                tbody.empty();
                data.mahasiswa.forEach(function(row) {
                    const tr = `
                        <tr>
                            <td>${row.nim}</td>
                            <td>${row.nama}</td>
                            <td>${row.email}</td>
                            <td>${row.phone}</td>
                            <td>
                                <button class="btn btn-sm btn-primary edit-mahasiswa-btn" data-id="${row.nim}" data-name="${row.nama}" data-email="${row.email}" data-phone="${row.phone}" data-bs-toggle="modal" data-bs-target="#editMahasiswaModal"><i class="fas fa-edit"></i> Edit</button>
                                <button class="btn btn-sm btn-danger delete-mahasiswa-btn" data-id="${row.nim}" data-name="${row.nama}" data-bs-toggle="modal" data-bs-target="#deleteMahasiswaModal"><i class="fas fa-trash"></i> Delete</button>
                            </td>
                        </tr>
                    `;
                    tbody.append(tr);
                });
                // Update Pagination
                const pagination = $('#pagination');
                pagination.empty();
                if (data.current_page > 1) {
                    pagination.append(`<li class="page-item"><a class="page-link" href="#" data-page="${data.current_page - 1}">Previous</a></li>`);
                }
                for (let i = 1; i <= data.total_pages; i++) {
                    const active = i === data.current_page ? 'active' : '';
                    pagination.append(`<li class="page-item ${active}"><a class="page-link" href="#" data-page="${i}">${i}</a></li>`);
                }
                if (data.current_page < data.total_pages) {
                    pagination.append(`<li class="page-item"><a class="page-link" href="#" data-page="${data.current_page + 1}">Next</a></li>`);
                }
            }, 'json');
        }

        $('#searchInput').on('input', function() {
            const search = $(this).val();
            loadMahasiswaList(1, search);
        });

        $(document).on('click', '.page-link', function(e) {
            e.preventDefault();
            const page = $(this).data('page');
            const search = $('#searchInput').val();
            loadMahasiswaList(page, search);
        });

        // Add Tugas Button
        $('#addTugasBtn').click(function() {
            $('#tugasId').val('');
            $('#mataKuliah').val('');
            $('#deskripsi').val('');
            $('#deadline').val('');
            $('#status').val('Belum Dikerjakan');
            $('#tugasModalLabel').text('Tambah Tugas');
            $('#tugasForm').attr('data-action', 'add_tugas');
            $('#tugasModal').modal('show');
        });

        // Edit Tugas Button
        $(document).on('click', '.edit-tugas-btn', function() {
            const id = $(this).data('id');
            const mataKuliah = $(this).data('mata_kuliah');
            const deskripsi = $(this).data('deskripsi');
            const deadline = $(this).data('deadline');
            const status = $(this).data('status');
            $('#tugasId').val(id);
            $('#mataKuliah').val(mataKuliah);
            $('#deskripsi').val(deskripsi);
            $('#deadline').val(deadline);
            $('#status').val(status);
            $('#tugasModalLabel').text('Edit Tugas');
            $('#tugasForm').attr('data-action', 'update_tugas');
            $('#tugasModal').modal('show');
        });

        // Tugas Form Submit
        $('#tugasForm').submit(function(e) {
            e.preventDefault();
            const action = $(this).attr('data-action');
            const formData = $(this).serialize() + '&action=' + action;
            $.post('', formData, function(data) {
                if (data.status === 'success') {
                    showToast(data.message, 'success');
                    $('#tugasModal').modal('hide');
                    loadTugasList(); // Refresh tabel tugas
                } else {
                    showToast(data.message, 'error');
                }
            }, 'json');
        });

        // Delete Tugas Button
        $(document).on('click', '.delete-tugas-btn', function() {
            const id = $(this).data('id');
            $('#deleteTugasId').val(id);
            $('#deleteTugasModal').modal('show');
        });

        // Delete Tugas Form Submit
        $('#deleteTugasForm').submit(function(e) {
            e.preventDefault();
            const formData = $(this).serialize() + '&action=delete_tugas';
            $.post('', formData, function(data) {
                if (data.status === 'success') {
                    showToast(data.message, 'success');
                    $('#deleteTugasModal').modal('hide');
                    loadTugasList(); // Refresh tabel tugas
                } else {
                    showToast(data.message, 'error');
                }
            }, 'json');
        });

        // Load Tugas List Function
        function loadTugasList() {
            $.get('?ajax=tugas_list', function(data) {
                const tbody = $('#tugasTableBody');
                tbody.empty();
                data.tugas.forEach(function(row) {
                    const badgeClass = row.status === 'Selesai' ? 'success' : (row.status === 'Sedang Dikerjakan' ? 'warning' : 'danger');
                    const tr = `
                        <tr>
                            <td>${row.mata_kuliah}</td>
                            <td>${row.deskripsi}</td>
                            <td>${row.deadline}</td>
                            <td><span class="badge bg-${badgeClass}">${row.status}</span></td>
                            <td>
                                <button class="btn btn-sm btn-primary edit-tugas-btn" data-id="${row.id}" data-mata_kuliah="${row.mata_kuliah}" data-deskripsi="${row.deskripsi}" data-deadline="${row.deadline}" data-status="${row.status}"><i class="fas fa-edit"></i> Edit</button>
                                <button class="btn btn-sm btn-danger delete-tugas-btn" data-id="${row.id}"><i class="fas fa-trash"></i> Delete</button>
                            </td>
                        </tr>
                    `;
                    tbody.append(tr);
                });
            }, 'json');
        }

        // Toast Function
        function showToast(message, type) {
            const toast = $(`
                <div class="toast align-items-center text-white bg-${type === 'success' ? 'success' : 'danger'} border-0" role="alert" aria-live="assertive" aria-atomic="true">
                    <div class="d-flex">
                        <div class="toast-body">${message}</div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                    </div>
                </div>
            `);
            $('#toastContainer').append(toast);
            const bsToast = new bootstrap.Toast(toast[0]);
            bsToast.show();
            setTimeout(() => toast.remove(), 5000);
        }
    });
</script>
 </body>          
 </html>     