<?php
// edit_jadwal.php
// Pastikan file ini diakses dengan aman, misalnya dengan session check jika diperlukan

// Include koneksi database
include 'config.php';

// Inisialisasi variabel
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$message = '';
$messageClass = '';

// Jika form disubmit (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $hari = trim($_POST['hari']);
    $dosen = trim($_POST['dosen']);
    $kode = trim($_POST['kode']);
    $mata_kuliah = trim($_POST['mata_kuliah']);
    $sks = intval($_POST['sks']);
    $ruangan = trim($_POST['ruangan']);
    $waktu = trim($_POST['waktu']);

    // Validasi sederhana
    if (empty($hari) || empty($dosen) || empty($kode) || empty($mata_kuliah) || $sks <= 0 || empty($ruangan) || empty($waktu)) {
        $message = "Semua field harus diisi dengan benar.";
        $messageClass = 'error';
    } else {
        // Update database menggunakan prepared statement
        $stmt = $conn->prepare("UPDATE jadwal SET hari = ?, dosen = ?, kode = ?, mata_kuliah = ?, sks = ?, ruangan = ?, waktu = ? WHERE id = ?");
        $stmt->bind_param("ssssissi", $hari, $dosen, $kode, $mata_kuliah, $sks, $ruangan, $waktu, $id);
        if ($stmt->execute()) {
            $message = "Jadwal berhasil diperbarui.";
            $messageClass = 'success';
            // Redirect ke halaman utama setelah update
            header("Location: admin_jadwal.php"); // Ganti dengan nama halaman utama Anda
            exit;
        } else {
            $message = "Gagal memperbarui jadwal: " . $conn->error;
            $messageClass = 'error';
        }
        $stmt->close();
    }
}

// Ambil data jadwal berdasarkan ID
$row = null;
if ($id > 0) {
    $stmt = $conn->prepare("SELECT * FROM jadwal WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
    }
    $stmt->close();
}

// Jika tidak ada data, tampilkan error
if (!$row) {
    die("Jadwal tidak ditemukan.");
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- Tambahan untuk responsivitas HP -->
    <title>Edit Jadwal Kuliah</title>
    <!-- Link ke Font Awesome untuk ikon -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- Link ke CSS terpisah -->
    <link rel="stylesheet" href="edit_jadwal.css">
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
            margin: 20px auto;
            max-width: 600px;
        }

        h2 {
            text-align: center;
            color: #333;
        }

        .message {
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 5px;
        }

        .message.success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .message.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .form-group {
            margin-bottom: 15px;
        }

        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }

        input, select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box;
        }

        button {
            background-color: #007bff;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            width: 100%;
            font-size: 16px;
        }

        button:hover {
            background-color: #0056b3;
        }

        .back-link {
            text-align: center;
            margin-top: 20px;
        }

        .back-link a {
            color: #007bff;
            text-decoration: none;
        }

        .back-link a:hover {
            text-decoration: underline;
        }

        /* Responsive Design untuk Handphone (HP) - Media Queries */
        @media (max-width: 868px) {
            .container {
                margin: 10px; /* Kurangi margin untuk layar kecil */
                padding: 15px; /* Kurangi padding */
                border-radius: 5px; /* Kurangi radius */
            }

            h2 {
                font-size: 1.5em; /* Kurangi ukuran font header */
            }

            .form-group {
                margin-bottom: 1rem; /* Sesuaikan margin bawah */
            }

            input, select {
                font-size: 1em; /* Pastikan font readable */
                padding: 0.75rem; /* Sesuaikan padding untuk touch */
            }

            button {
                width: 100%; /* Buat button full width untuk kemudahan akses */
                margin-bottom: 10px; /* Tambahkan margin bawah untuk stack */
                font-size: 1em;
                padding: 0.75rem;
            }

            .back-link {
                margin-top: 15px;
            }

            .back-link a {
                font-size: 1em;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h2><i class="fas fa-edit"></i> Edit Jadwal Kuliah</h2>
        <?php if ($message): ?>
            <div class="message <?php echo $messageClass; ?>"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="form-group">
                <label for="hari"><i class="fas fa-calendar-day"></i> Hari:</label>
                <select id="hari" name="hari">
                    <option value="">Pilih Hari</option>
                    <option value="-" <?php echo ($row['hari'] == '-') ? 'selected' : ''; ?>>-</option>
                    <option value="Senin" <?php echo ($row['hari'] == 'Senin') ? 'selected' : ''; ?>>Senin</option>
                    <option value="Selasa" <?php echo ($row['hari'] == 'Selasa') ? 'selected' : ''; ?>>Selasa</option>
                    <option value="Rabu" <?php echo ($row['hari'] == 'Rabu') ? 'selected' : ''; ?>>Rabu</option>
                    <option value="Kamis" <?php echo ($row['hari'] == 'Kamis') ? 'selected' : ''; ?>>Kamis</option>
                    <option value="Jumat" <?php echo ($row['hari'] == 'Jumat') ? 'selected' : ''; ?>>Jumat</option>
                    <option value="Sabtu" <?php echo ($row['hari'] == 'Sabtu') ? 'selected' : ''; ?>>Sabtu</option>
                    <option value="Minggu" <?php echo ($row['hari'] == 'Minggu') ? 'selected' : ''; ?>>Minggu</option>
                </select>
            </div>

            <div class="form-group">
                <label for="dosen"><i class="fas fa-user-tie"></i> Dosen:</label>
                <input type="text" id="dosen" name="dosen" value="<?php echo htmlspecialchars($row['dosen']); ?>">
            </div>

            <div class="form-group">
                <label for="kode"><i class="fas fa-hashtag"></i> Kode:</label>
                <input type="text" id="kode" name="kode" value="<?php echo htmlspecialchars($row['kode']); ?>">
            </div>

            <div class="form-group">
                <label for="mata_kuliah"><i class="fas fa-book"></i> Mata Kuliah:</label>
                <input type="text" id="mata_kuliah" name="mata_kuliah" value="<?php echo htmlspecialchars($row['mata_kuliah']); ?>">
            </div>

            <div class="form-group">
                <label for="sks"><i class="fas fa-clock"></i> SKS:</label>
                <input type="number" id="sks" name="sks" value="<?php echo htmlspecialchars($row['sks']); ?>" min="1">
            </div>

            <div class="form-group">
                <label for="ruangan"><i class="fas fa-building"></i> Ruangan:</label>
                <input type="text" id="ruangan" name="ruangan" value="<?php echo htmlspecialchars($row['ruangan']); ?>">
            </div>

            <div class="form-group">
                <label for="waktu"><i class="fas fa-clock"></i> Waktu:</label>
                <input type="text" id="waktu" name="waktu" value="<?php echo htmlspecialchars($row['waktu']); ?>" placeholder="e.g., 08:00-10:00">
            </div>

            <button type="submit"><i class="fas fa-save"></i> Simpan Perubahan</button>
        </form>
        <div class="back-link">
            <a href="admin_jadwal.php"><i class="fas fa-arrow-left"></i> Kembali ke Jadwal</a>
        </div>
    </div>
</body>
</html>

<?php
// Tutup koneksi
$conn->close();
?>