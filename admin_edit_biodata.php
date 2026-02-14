<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: admin_login.php");
    exit;
}
require_once 'config.php'; // Sertakan file koneksi

// Ambil NIM dari query string
$nim = isset($_GET['nim']) ? trim($_GET['nim']) : '';
if (empty($nim)) {
    header("Location: admin_biodata.php"); // Redirect jika NIM tidak ada
    exit;
}

// Fungsi untuk mengambil data biodata berdasarkan NIM
function getBiodata($conn, $nim) {
    $stmt_select = $conn->prepare("SELECT * FROM biodata WHERE nim = ?");
    $stmt_select->bind_param("s", $nim);
    $stmt_select->execute();
    $result = $stmt_select->get_result();
    $row = $result->fetch_assoc();
    $stmt_select->close();
    return $row;
}

// Ambil data biodata berdasarkan NIM
$row = getBiodata($conn, $nim);

if (!$row) {
    echo "<div class='message error'>Data biodata tidak ditemukan untuk NIM: " . htmlspecialchars($nim) . "</div>";
    exit;
}

// Jika form disubmit, update data
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama = $_POST['nama'];
    $prodi = $_POST['prodi'];
    $kelas = $_POST['kelas'];
    $semester = $_POST['semester'];
    $jenis_kelamin = $_POST['jenis_kelamin'];
    $agama = $_POST['agama'];
    $nomor_hp = $_POST['nomor_hp'];
    $media_sosial = $_POST['media_sosial'];

    // Update menggunakan prepared statement
    $stmt_update = $conn->prepare("UPDATE biodata SET nama = ?, prodi = ?, kelas = ?, semester = ?, jenis_kelamin = ?, agama = ?, nomor_hp = ?, media_sosial = ? WHERE nim = ?");
    $stmt_update->bind_param("sssssssss", $nama, $prodi, $kelas, $semester, $jenis_kelamin, $agama, $nomor_hp, $media_sosial, $nim);

    if ($stmt_update->execute()) {
        echo "<div class='message success'>Biodata berhasil diperbarui!</div>";
        // Refresh data setelah update
        $row = getBiodata($conn, $nim);
    } else {
        echo "<div class='message error'>Error: " . $stmt_update->error . "</div>";
    }
    $stmt_update->close();
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Biodata Mahasiswa | Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css"> <!-- Untuk ikon -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet"> <!-- Font modern -->
    <style>
        /* Tambahan styling untuk membuat lebih keren */
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            animation: fadeIn 1s ease-in-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            padding: 40px;
            max-width: 600px;
            width: 100%;
            animation: slideUp 0.8s ease-out;
        }

        @keyframes slideUp {
            from { transform: translateY(50px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        h1 {
            text-align: center;
            color: #333;
            margin-bottom: 30px;
            font-weight: 600;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .form-group {
            margin-bottom: 25px;
            position: relative;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #555;
            font-weight: 500;
            transition: color 0.3s;
        }

        .form-group input, .form-group select {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 16px;
            transition: all 0.3s ease;
            background: #f9f9f9;
        }

        .form-group input:focus, .form-group select:focus {
            border-color: #667eea;
            box-shadow: 0 0 10px rgba(102, 126, 234, 0.3);
            background: #fff;
        }

        .form-group input[readonly] {
            background: #f0f0f0;
            cursor: not-allowed;
        }

        .submit-btn {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 18px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
        }

        .back-btn {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            background: #f0f0f0;
            color: #333;
            text-decoration: none;
            border-radius: 10px;
            transition: all 0.3s ease;
            text-align: center;
            width: 100%;
        }

        .back-btn:hover {
            background: #e0e0e0;
            transform: translateY(-1px);
        }

        .message {
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            text-align: center;
            font-weight: 500;
            opacity: 1;
            transition: opacity 0.5s ease;
        }

        .message.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .message.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        /* Responsif */
        @media (max-width: 768px) {
            .container {
                padding: 20px;
                margin: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1><i class="fas fa-user-edit"></i> Edit Biodata Mahasiswa</h1>
        <form method="POST" action="">
            <!-- NIM readonly -->
            <div class="form-group">
                <label for="nim"><i class="fas fa-id-card"></i> NIM :</label>
                <input type="text" id="nim" name="nim" value="<?php echo htmlspecialchars($row['nim'] ?? ''); ?>" readonly>
            </div>

            <!-- Nama editable -->
            <div class="form-group">
                <label for="nama"><i class="fas fa-user"></i> Nama :</label>
                <input type="text" id="nama" name="nama" value="<?php echo htmlspecialchars($row['nama'] ?? ''); ?>" required>
            </div>

            <!-- Prodi -->
            <div class="form-group">
                <label for="prodi"><i class="fas fa-graduation-cap"></i> Prodi :</label>
                <input type="text" id="prodi" name="prodi" value="<?php echo htmlspecialchars($row['prodi'] ?? ''); ?>">
            </div>

            <!-- Kelas -->
            <div class="form-group">
                <label for="kelas"><i class="fas fa-graduation-cap"></i> Kelas :</label>
                <select id="kelas" name="kelas">
                    <option value="">Pilih Kelas</option>
                    <?php
                    $kelas_options = ['A', 'B', 'C'];
                    foreach ($kelas_options as $kelas_option) {
                        $selected = (($row['kelas'] ?? '') == $kelas_option) ? 'selected' : '';
                        echo "<option value=\"$kelas_option\" $selected>$kelas_option</option>";
                    }
                    ?>
                </select>
            </div>

            <!-- Semester -->
            <div class="form-group">
                <label for="semester"><i class="fas fa-calendar-alt"></i> Semester :</label>
                <select id="semester" name="semester">
                    <option value="">Pilih Semester</option>
                    <?php for ($i = 1; $i <= 8; $i++): ?>
                        <option value="<?php echo $i; ?>" <?php if (($row['semester'] ?? '') == $i) echo 'selected'; ?>><?php echo $i; ?></option>
                    <?php endfor; ?>
                </select>
            </div>

            <!-- Jenis Kelamin -->
            <div class="form-group">
                <label for="jenis_kelamin"><i class="fas fa-venus-mars"></i> Jenis Kelamin :</label>
                <select id="jenis_kelamin" name="jenis_kelamin" required>
                    <option value="">Pilih</option>
                    <option value="Laki-laki" <?php if (($row['jenis_kelamin'] ?? '') == 'Laki-laki') echo 'selected'; ?>>Laki-laki</option>
                    <option value="Perempuan" <?php if (($row['jenis_kelamin'] ?? '') == 'Perempuan') echo 'selected'; ?>>Perempuan</option>
                </select>
            </div>

            <!-- Agama -->
            <div class="form-group">
                <label for="agama"><i class="fas fa-pray"></i> Agama :</label>
                <input type="text" id="agama" name="agama" value="<?php echo htmlspecialchars($row['agama'] ?? ''); ?>" required>
            </div>

            <!-- Nomor HP -->
            <div class="form-group">
                <label for="nomor_hp"><i class="fas fa-phone"></i> Nomor HP :</label>
                <input type="text" id="nomor_hp" name="nomor_hp" value="<?php echo htmlspecialchars($row['nomor_hp'] ?? ''); ?>" required>
            </div>

            <!-- Media Sosial -->
            <div class="form-group">
                <label for="media_sosial"><i class="fab fa-instagram"></i> Media Sosial :</label>
                <input type="text" id="media_sosial" name="media_sosial" value="<?php echo htmlspecialchars($row['media_sosial'] ?? ''); ?>" placeholder="e.g., @username_instagram">
            </div>

            <button type="submit" class="submit-btn"><i class="fas fa-save"></i> Simpan Perubahan</button>
        </form>

        <!-- Tombol Kembali -->
        <a href="admin_biodata.php" class="back-btn">
            <i class="fas fa-arrow-left"></i> Kembali ke Cek Biodata
        </a>
    </div>

    <script>
        // JavaScript untuk efek fokus pada label
        document.addEventListener('DOMContentLoaded', function() {
            const inputs = document.querySelectorAll('input, select');
            inputs.forEach(input => {
                input.addEventListener('focus', function() {
                    this.previousElementSibling.style.color = '#667eea';
                });
                input.addEventListener('blur', function() {
                    this.previousElementSibling.style.color = '#555';
                });
            });
        });
    </script>
</body>
</html>