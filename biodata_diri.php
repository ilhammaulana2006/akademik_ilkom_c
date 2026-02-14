<?php
session_start(); // Mulai sesi untuk menyimpan NIM dan nama permanen
if (!isset($_SESSION['nim']) || !isset($_SESSION['nama'])) {
    header("Location: login.php");
    exit();
}
require_once 'config.php'; // Sertakan file koneksi, gunakan require_once untuk konsistensi

$nim = $_SESSION['nim'];
$nama = $_SESSION['nama'];

// Ambil data biodata yang sudah ada (jika ada) untuk pre-fill form menggunakan prepared statement
// Pindahkan ke atas agar $row tersedia sebelum submit
$stmt_select = $conn->prepare("SELECT * FROM biodata WHERE nim=?");
$stmt_select->bind_param("s", $nim);
$stmt_select->execute();
$result = $stmt_select->get_result();
$row = $result->fetch_assoc();
$stmt_select->close();

// Jika form disubmit (via AJAX atau POST), simpan data ke database menggunakan prepared statements untuk keamanan
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $prodi = $_POST['prodi'];
    $kelas = $_POST['kelas']; // Konsistensi nama variabel
    $jenis_kelamin = $_POST['jenis_kelamin'];
    $agama = $_POST['agama'];
    $nomor_hp = $_POST['nomor_hp'];
    $media_sosial = $_POST['media_sosial'];
    $semester = $_POST['semester']; // Tambahkan semester

    // Gunakan prepared statement untuk insert atau update (jika NIM sudah ada, update) - tambahkan semester
    $stmt = $conn->prepare("INSERT INTO biodata (nim, nama, prodi, kelas, jenis_kelamin, agama, nomor_hp, media_sosial, semester) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?) 
                            ON DUPLICATE KEY UPDATE 
                            prodi=?, kelas=?, jenis_kelamin=?, agama=?, nomor_hp=?, media_sosial=?, semester=?");
    // Bind parameter: 9 untuk insert, 7 untuk update, total 16
    $stmt->bind_param("ssssssssssssssss", $nim, $nama, $prodi, $kelas, $jenis_kelamin, $agama, $nomor_hp, $media_sosial, $semester, 
                      $prodi, $kelas, $jenis_kelamin, $agama, $nomor_hp, $media_sosial, $semester);

    if ($stmt->execute()) {
        // Jika sukses, kirim respons JSON untuk AJAX
        if (isset($_POST['ajax'])) {
            echo json_encode(['status' => 'success', 'message' => 'Biodata berhasil disimpan!']);
            exit();
        } else {
            echo "<div class='message success'>Biodata berhasil disimpan!</div>";
        }
    } else {
        if (isset($_POST['ajax'])) {
            echo json_encode(['status' => 'error', 'message' => 'Error: ' . $stmt->error]);
            exit();
        } else {
            echo "<div class='message error'>Error: " . $stmt->error . "</div>";
        }
    }
    $stmt->close();
}
$conn->close(); // Tutup koneksi di akhir PHP
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Biodata | Ilkom C</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css"> <!-- Untuk ikon -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet"> <!-- Font modern -->
    <link rel="stylesheet" href="biodata_diri.css"> <!-- Link ke file CSS eksternal -->
</head>
<body>
    <div class="container">
        <h1><i class="fas fa-user-edit"></i> Biodata Diri | Ilkom C</h1>
        <div id="message-container"></div> <!-- Container untuk pesan AJAX -->
        <form id="biodata-form" method="POST" action="">
            <!-- NIM dan Nama permanen, readonly -->
            <div class="form-group">
                <label for="nim"><i class="fas fa-id-card"></i> NIM :</label>
                <input type="text" id="nim" name="nim" value="<?php echo htmlspecialchars($nim); ?>" readonly>
            </div>

            <div class="form-group">
                <label for="nama"><i class="fas fa-user"></i> Nama :</label>
                <input type="text" id="nama" name="nama" value="<?php echo htmlspecialchars($nama); ?>" readonly>
            </div>

            <!-- Field yang bisa diisi -->
            <div class="form-group">
                <label for="prodi"><i class="fas fa-graduation-cap"></i> Prodi :</label>
                <input type="text" id="prodi" name="prodi" value="<?php echo htmlspecialchars($row['prodi'] ?? ''); ?>" required>
            </div>

            <div class="form-group">
                <label for="kelas"><i class="fas fa-graduation-cap"></i> Kelas :</label>
                <select id="kelas" name="kelas" required>
                    <option value="">Pilih Kelas</option>
                    <?php
                    $kelas_options = ['A', 'B', 'C']; // Array opsi kelas
                    foreach ($kelas_options as $kelas_option) {
                        $selected = (($row['kelas'] ?? '') == $kelas_option) ? 'selected' : '';
                        echo "<option value=\"$kelas_option\" $selected>$kelas_option</option>";
                    }
                    ?>
                </select>
            </div>

            <div class="form-group">
                <label for="semester"><i class="fas fa-calendar-alt"></i> Semester :</label>
                <select id="semester" name="semester" required>
                    <option value="">Pilih Semester</option>
                    <?php for ($i = 1; $i <= 8; $i++): ?>
                        <option value="<?php echo $i; ?>" <?php if (($row['semester'] ?? '') == $i) echo 'selected'; ?>><?php echo $i; ?></option>
                    <?php endfor; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="jenis_kelamin"><i class="fas fa-venus-mars"></i> Jenis Kelamin :</label>
                <select id="jenis_kelamin" name="jenis_kelamin" required>
                    <option value="">Pilih</option>
                    <option value="Laki-laki" <?php if (($row['jenis_kelamin'] ?? '') == 'Laki-laki') echo 'selected'; ?>>Laki-laki</option>
                    <option value="Perempuan" <?php if (($row['jenis_kelamin'] ?? '') == 'Perempuan') echo 'selected'; ?>>Perempuan</option>
                </select>
            </div>

            <div class="form-group">
                <label for="agama"><i class="fas fa-pray"></i> Agama :</label>
                <input type="text" id="agama" name="agama" value="<?php echo htmlspecialchars($row['agama'] ?? ''); ?>" required>
            </div>

            <div class="form-group">
                <label for="nomor_hp"><i class="fas fa-phone"></i> Nomor HP :</label>
                <input type="text" id="nomor_hp" name="nomor_hp" value="<?php echo htmlspecialchars($row['nomor_hp'] ?? ''); ?>" required>
            </div>

            <div class="form-group">
                <label for="media_sosial"><i class="fab fa-instagram"></i> Media Sosial :</label>
                <input type="text" id="media_sosial" name="media_sosial" value="<?php echo htmlspecialchars($row['media_sosial'] ?? ''); ?>" placeholder="e.g., @username_instagram" required>
            </div>

            <button type="submit" class="submit-btn"><i class="fas fa-save"></i> Simpan Biodata</button>
        </form>

        <!-- Tombol profil -->
        <a href="profil.php" class="back-btn" id="back-btn">
            <i class="fas fa-arrow-left"></i> Profil
        </a>
    </div>

    <script>
        // Tambahkan JavaScript untuk validasi real-time, efek keren, dan AJAX submit
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('biodata-form');
            const inputs = document.querySelectorAll('input, select');
            const messageContainer = document.getElementById('message-container');
            const backBtn = document.getElementById('back-btn');

            // Efek fokus pada label
            inputs.forEach(input => {
                input.addEventListener('focus', function() {
                    this.previousElementSibling.style.color = '#667eea';
                });
                input.addEventListener('blur', function() {
                    this.previousElementSibling.style.color = '#555';
                });
            });

            // Fungsi untuk menghilangkan pesan setelah beberapa detik
            function hideMessage() {
                const message = messageContainer.querySelector('.message');
                if (message) {
                    message.style.opacity = '0';
                    setTimeout(() => {
                        messageContainer.innerHTML = '';
                    }, 500); // Tunggu transisi opacity selesai
                }
            }

            // Validasi sederhana sebelum submit
            form.addEventListener('submit', function(e) {
                e.preventDefault(); // Cegah submit default

                let valid = true;
                inputs.forEach(input => {
                    if (input.hasAttribute('required') && !input.value.trim()) {
                        input.style.borderColor = 'red';
                        valid = false;
                    } else {
                        input.style.borderColor = '#e0e0e0';
                    }
                });

                if (!valid) {
                    alert('Harap isi semua field yang wajib!');
                    return;
                }

                // Kirim data via AJAX
                const formData = new FormData(form);
                formData.append('ajax', '1'); // Tandai sebagai AJAX

                fetch('', { // Kirim ke halaman yang sama
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    // Tampilkan pesan
                    messageContainer.innerHTML = `<div class='message ${data.status}'>${data.message}</div>`;

                    if (data.status === 'success') {
                        // Tampilkan tombol kembali setelah sukses
                        backBtn.style.display = 'inline-block';
                        // Hilangkan pesan sukses setelah 3 detik
                        setTimeout(hideMessage, 3000);
                    } else {
                        // Hilangkan pesan error setelah 5 detik
                        setTimeout(hideMessage, 5000);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    messageContainer.innerHTML = '<div class="message error">Terjadi kesalahan saat menyimpan data.</div>';
                    // Hilangkan pesan error setelah 5 detik
                    setTimeout(hideMessage, 5000);
                });
            });
        });
    </script>
    
    <!-- Logo WhatsApp CS di kanan bawah -->
    <div class="whatsapp-cs">
        <a href="https://wa.me/message/WUFEKYAQO7RKD1" target="_blank">
            <img src="https://upload.wikimedia.org/wikipedia/commons/6/6b/WhatsApp.svg" alt="WhatsApp CS">
        </a>
    </div>
</body>
</html>