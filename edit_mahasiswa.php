<?php
session_start();
// Session check seperti di admin_dashboard.php
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: admin_login.php");
    exit;
}
include 'config.php';

// Fungsi logAction untuk mencatat aksi (diasumsikan tabel logs ada dengan kolom action, nim, timestamp)
function logAction($action, $nim) {
    global $conn;
    $stmt = $conn->prepare("INSERT INTO logs (action, nim, timestamp) VALUES (?, ?, NOW())");
    $stmt->bind_param("ss", $action, $nim);
    $stmt->execute();
}

$id = $_GET['id'] ?? '';
if (empty($id)) {
    die("ID tidak valid.");
}

// Ambil data mahasiswa (ditambahkan status dan catatan_admin)
$stmt = $conn->prepare("SELECT nim, nama, email, phone, status, catatan_admin FROM mahasiswa WHERE nim = ?");
$stmt->bind_param("s", $id);
$stmt->execute();
$result = $stmt->get_result();
$mahasiswa = $result->fetch_assoc();
if (!$mahasiswa) {
    die("Mahasiswa tidak ditemukan.");
}

// Handle update jika form disubmit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'] ?? NULL;
    $phone = $_POST['phone'] ?? NULL;
    $status = $_POST['status']; // Tambahan: status
    $catatan_admin = $_POST['catatan_admin'] ?? NULL; // Tambahan: catatan admin

    if (empty($name)) {
        echo "Nama wajib diisi!";
        exit;
    }

    // Validasi status (opsional, pastikan hanya nilai yang diizinkan)
    $allowed_status = ['Aktif', 'Nonaktif', 'Keluar'];
    if (!in_array($status, $allowed_status)) {
        echo "Status tidak valid!";
        exit;
    }

    // Update query ditambahkan status dan catatan_admin
    $stmt = $conn->prepare("UPDATE mahasiswa SET nama = ?, email = ?, phone = ?, status = ?, catatan_admin = ? WHERE nim = ?");
    $stmt->bind_param("ssssss", $name, $email, $phone, $status, $catatan_admin, $id);
    if ($stmt->execute()) {
        logAction('Update', $id); // Fungsi log dari kode asli
        header("Location: admin_dashboard.php?success=Data berhasil diupdate!");
        exit;
    } else {
        echo "Gagal update!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- Tambahan untuk responsivitas HP -->
    <title>Edit Mahasiswa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Custom Styles untuk responsivitas HP */
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }

        .container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            padding: 20px;
            margin-top: 20px;
        }

        /* Responsive Design untuk Handphone (HP) - Media Queries */
        @media (max-width: 868px) {
            .container {
                margin: 7px; /* Kurangi margin untuk layar kecil */
                padding: 15px; /* Kurangi padding */
                border-radius: 5px; /* Kurangi radius */
            }

            h2 {
                font-size: 1.5em; /* Kurangi ukuran font header */
                text-align: center;
            }

            .mb-3 {
                margin-bottom: 1rem; /* Sesuaikan margin bawah */
            }

            .form-control, .form-select {
                font-size: 1em; /* Pastikan font readable */
                padding: 0.75rem; /* Sesuaikan padding untuk touch */
            }

            .btn {
                width: 100%; /* Buat button full width untuk kemudahan akses */
                margin-bottom: 10px; /* Tambahkan margin bawah untuk stack */
                font-size: 1em;
                padding: 0.75rem;
            }

            .btn-secondary {
                margin-bottom: 0; /* Hapus margin bawah untuk button terakhir */
            }

            textarea {
                resize: vertical; /* Izinkan resize vertikal saja */
                min-height: 100px; /* Pastikan tinggi minimum untuk touch */
            }
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <h2>Edit Mahasiswa</h2>
        <form method="POST">
            <div class="mb-3">
                <label>NIM</label>
                <input type="text" class="form-control" value="<?php echo htmlspecialchars($mahasiswa['nim']); ?>" readonly>
            </div>
            <div class="mb-3">
                <label>Nama</label>
                <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($mahasiswa['nama']); ?>" required>
            </div>
            <div class="mb-3">
                <label>Email</label>
                <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($mahasiswa['email'] ?? ''); ?>">
            </div>
            <div class="mb-3">
                <label>Phone</label>
                <input type="text" name="phone" class="form-control" value="<?php echo htmlspecialchars($mahasiswa['phone'] ?? ''); ?>">
            </div>
            <!-- Tambahan: Pilih Status -->
            <div class="mb-3">
                <label>Status</label>
                <select name="status" class="form-select" required>
                    <option value="Aktif" <?php echo ($mahasiswa['status'] == 'Aktif') ? 'selected' : ''; ?>>Aktif</option>
                    <option value="Nonaktif" <?php echo ($mahasiswa['status'] == 'Nonaktif') ? 'selected' : ''; ?>>Nonaktif</option>
                    <option value="Keluar" <?php echo ($mahasiswa['status'] == 'Keluar') ? 'selected' : ''; ?>>Keluar</option>
                </select>
            </div>
            <!-- Tambahan: Catatan Admin -->
            <div class="mb-3">
                <label>Catatan Admin (untuk beritahu mahasiswa)</label>
                <textarea name="catatan_admin" class="form-control" rows="3" placeholder="Masukkan catatan atau pesan untuk mahasiswa"><?php echo htmlspecialchars($mahasiswa['catatan_admin'] ?? ''); ?></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Simpan</button>
            <a href="admin_dashboard.php" class="btn btn-secondary">Kembali</a>
        </form>
    </div>
</body>
</html>