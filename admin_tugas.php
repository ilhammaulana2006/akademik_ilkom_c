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

// Ambil daftar tugas untuk display
$query_tugas = "SELECT * FROM tugas ORDER BY deadline ASC";
$result_tugas = $conn->query($query_tugas);

// Fungsi logging untuk edukasi
function logAction($action, $userId) {
    $log = date('Y-m-d H:i:s') . " - Action: $action, User ID: $userId\n";
    file_put_contents('admin_log.txt', $log, FILE_APPEND);
}

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
    <title> Kelola Tugas Kuliah | Admin</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Animate.css -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" rel="stylesheet">
    <!-- Custom CSS (Dipisahkan ke file eksternal untuk kemudahan pemeliharaan) -->
    <link rel="stylesheet" href="admin_dashboard.css">
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
                        <h2><i class="fas fa-tasks"></i> Kelola Tugas Kuliah</h2>
                        <p>Kelola tugas kuliah yang akan ditampilkan kepada mahasiswa.</p>
                    </div>
                    <div>
                        <button class="btn btn-success" id="addTugasBtn"><i class="fas fa-plus"></i> Tambah Tugas</button>
                    </div>
                </div>

                <!-- Daftar Tugas -->
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
                $('body').toggleClass('bg-dark background-white');
                $('.card').toggleClass('bg-dark background-white');
                $('.table').toggleClass('table-dark');
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