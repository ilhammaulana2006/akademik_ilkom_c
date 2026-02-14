<?php
session_start();
// Session check seperti di admin_dashboard.php
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: admin_login.php");
    exit;
}
include 'config.php';

// Fungsi logging untuk edukasi (ditambahkan dari admin_dashboard.php)
function logAction($action, $userId) {
    $log = date('Y-m-d H:i:s') . " - Action: $action, User ID: $userId\n";
    file_put_contents('admin_log.txt', $log, FILE_APPEND);
}

// Ambil NIM dari GET (untuk konfirmasi) atau POST (untuk delete)
$nim = $_GET['nim'] ?? $_POST['id'] ?? '';
if (empty($nim)) {
    header("Location: admin_dashboard.php?error=NIM tidak valid!");
    exit;
}

// Jika POST (delete dikonfirmasi)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validasi apakah NIM ada di tabel biodata
    $check_stmt = $conn->prepare("SELECT nim, nama FROM biodata WHERE nim = ?");
    $check_stmt->bind_param("s", $nim);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    if ($result->num_rows == 0) {
        $check_stmt->close();
        header("Location: admin_dashboard.php?error=Mahasiswa tidak ditemukan!");
        exit;
    }
    $mahasiswa = $result->fetch_assoc();
    $check_stmt->close();

    // Delete dari tabel biodata
    $stmt = $conn->prepare("DELETE FROM biodata WHERE nim = ?");
    $stmt->bind_param("s", $nim);
    if ($stmt->execute()) {
        logAction('Delete Biodata', $nim);
        header("Location: admin_dashboard.php?success=Biodata mahasiswa berhasil dihapus!");
        exit;
    } else {
        header("Location: admin_dashboard.php?error=Gagal hapus biodata mahasiswa!");
        exit;
    }
}

// Jika GET (tampilkan konfirmasi)
$check_stmt = $conn->prepare("SELECT nim, nama FROM biodata WHERE nim = ?");
$check_stmt->bind_param("s", $nim);
$check_stmt->execute();
$result = $check_stmt->get_result();
if ($result->num_rows == 0) {
    $check_stmt->close();
    header("Location: admin_dashboard.php?error=Mahasiswa tidak ditemukan!");
    exit;
}
$mahasiswa = $result->fetch_assoc();
$check_stmt->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hapus Biodata Mahasiswa | Admin Ilkom C</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Custom CSS (opsional, jika ada admin_dashboard.css) -->
    <link rel="stylesheet" href="admin_dashboard.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .container {
            max-width: 600px;
            margin-top: 50px;
        }
        .card {
            border: none;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .btn-danger {
            background-color: #dc3545;
            border-color: #dc3545;
        }
        .btn-danger:hover {
            background-color: #c82333;
            border-color: #bd2130;
        }
        /* Styling untuk link (jika ada link biasa selain tombol) */
        a {
            color: #007bff;
            text-decoration: none;
        }
        a:hover {
            color: #0056b3;
            text-decoration: underline;
        }
        /* Jika link digunakan sebagai tombol, sudah menggunakan class btn-secondary */
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="card-header bg-danger text-white">
                <h4 class="card-title mb-0"><i class="fas fa-trash"></i> Konfirmasi Hapus Biodata Mahasiswa</h4>
            </div>
            <div class="card-body">
                <p class="lead">Apakah Anda yakin ingin menghapus biodata mahasiswa berikut?</p>
                <ul class="list-group list-group-flush">
                    <li class="list-group-item"><strong>NIM:</strong> <?php echo htmlspecialchars($mahasiswa['nim']); ?></li>
                    <li class="list-group-item"><strong>Nama:</strong> <?php echo htmlspecialchars($mahasiswa['nama']); ?></li>
                </ul>
                <p class="text-muted mt-3"><small>Tindakan ini tidak dapat dibatalkan dan akan menghapus semua data mahasiswa terkait.</small></p>
                <form method="POST" class="d-inline">
                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($nim); ?>">
                    <button type="submit" class="btn btn-danger"><i class="fas fa-trash"></i> Ya, Hapus</button>
                </form>
                <a href="admin_dashboard.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Batal</a>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>