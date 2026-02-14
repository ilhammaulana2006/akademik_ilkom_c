<?php
session_start();
if (!isset($_SESSION['nim'])) {
    header("Location: index.php");
    exit();
}
require_once 'config.php'; // Menggunakan require_once untuk konsistensi
$nim = $_SESSION['nim'];
$nama = $_SESSION['nama'];

// Query untuk mendapatkan biodata mahasiswa (termasuk prodi, semester, dan kelas jika diperlukan)
$query_biodata = $conn->prepare("SELECT prodi, semester, kelas FROM biodata WHERE nim = ?");
$query_biodata->bind_param("s", $nim);
$query_biodata->execute();
$result_biodata = $query_biodata->get_result();

if ($result_biodata->num_rows > 0) {
    $biodata = $result_biodata->fetch_assoc();
    $prodi = $biodata['prodi'];
    $semester = $biodata['semester'];
    $kelas = $biodata['kelas']; // Jika kelas juga ingin diambil dari DB, ganti hardcoded "C"
} else {
    // Jika biodata tidak ditemukan, set default atau handle error
    $prodi = "Tidak Diketahui";
    $semester = "Tidak Diketahui";
    $kelas = "C"; // Default jika tidak ada di DB
}
$query_biodata->close();

$message = ""; // Variabel untuk pesan yang akan ditampilkan di halaman
$success = false; // Flag untuk menandai apakah absen berhasil

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $status = $_POST['status'];
    $tanggal = date('Y-m-d');
    $waktu = date('H:i:s a'); // Waktu saat absen dikirim
    
    // Cek apakah sudah ada absen untuk NIM dan tanggal ini
    $check_stmt = $conn->prepare("SELECT id FROM absen WHERE nim = ? AND tanggal = ?");
    $check_stmt->bind_param("ss", $nim, $tanggal);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows > 0) {
        $message = "Anda sudah melakukan absen untuk hari ini.";
    } else {
        $stmt = $conn->prepare("INSERT INTO absen (nim, tanggal, status, waktu, prodi, semester) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssss", $nim, $tanggal, $status, $waktu, $prodi, $semester);
        if ($check_stmt->execute()) {
            // Absen berhasil, set flag sukses
            $success = true;
            $message = "Absen berhasil dikirim.";
            
            // Buat pesan WhatsApp berdasarkan status
            if ($status === 'hadir') {
                $pesan = "Assalamualaikum Wr. Wb. Selamat Pagi Bapak/Ibu. Nama: $nama Prodi: $prodi Semester: $semester Kelas: $kelas Saya hadir hari ini. Terima kasih. Absen berhasil dikirim.";
                $nomor_whatsapp = "6282253339176";  // Nomor untuk hadir
            } else {
                $pesan = "Assalamualaikum Wr. Wb. Selamat Pagi Bapak/Ibu. Mohon Maaf Menganggu Waktunya, Nama: $nama Prodi: $prodi Semester: $semester Kelas: $kelas Saya Hari ini tidak masuk pelajaran karena saya $status sekian terima kasih. Absen berhasil dikirim.";
                $nomor_whatsapp = "6282253339176";  // Nomor untuk alfa, izin, sakit
            }
            $url = "https://wa.me/$nomor_whatsapp?text=" . urlencode($pesan);
            
            // Buka WhatsApp otomatis setelah absen berhasil (dengan delay untuk memastikan pesan ditampilkan)
            echo "<script>setTimeout(function(){ window.open('$url', '_blank'); }, 1000);</script>";
            
        } else {
            $message = "Terjadi kesalahan saat menyimpan absen: " . $stmt->error;
        }
        $stmt->close();
    }
    $check_stmt->close();
}
$conn->close(); // Menutup koneksi database
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Absen Kelas | Ilkom C</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css"> <!-- Font Awesome untuk ikon -->
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            overflow-y: auto;
            animation: fadeIn 1s ease-in-out;
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        .container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            padding: 40px;
            max-width: 500px;
            width: 100%;
            text-align: center;
            animation: slideUp 0.8s ease-out;
        }
        @keyframes slideUp {
            from { transform: translateY(50px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        .absen-icon {
            font-size: 4rem;
            color: #667eea;
            margin-bottom: 20px;
            animation: bounce 1s ease-in-out;
        }
        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% { transform: translateY(0); }
            40% { transform: translateY(-10px); }
            60% { transform: translateY(-5px); }
        }
        h2 {
            font-weight: 700;
            color: #333;
            margin-bottom: 20px;
            font-size: 1.8rem;
        }
        .message {
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-weight: 500;
            animation: fadeIn 0.5s ease-in-out;
        }
        .message.success {
            background: linear-gradient(135deg, #4caf50, #66bb6a);
            color: white;
        }
        .message.error {
            background: linear-gradient(135deg, #f44336, #e57373);
            color: white;
        }
        form {
            margin-bottom: 30px;
        }
        .status-options {
            display: flex;
            flex-direction: column;
            gap: 15px;
            margin-bottom: 30px;
        }
        .status-options label {
            display: flex;
            align-items: center;
            padding: 15px;
            background: linear-gradient(135deg, #f5f7fa, #c3cfe2);
            border-radius: 15px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 500;
            color: #333;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        .status-options label:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
        }
        .status-options input[type="radio"] {
            margin-right: 15px;
            accent-color: #667eea;
        }
        button[type="submit"] {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border: none;
            padding: 15px 30px;
            border-radius: 25px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
            width: 100%;
        }
        button[type="submit"]:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.3);
        }
        .back-btn {
            background: linear-gradient(135deg, #ff6b6b, #ee5a24);
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 20px;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }
        .back-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
        }
        .whatsapp-cs {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 1000;
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }
        .whatsapp-cs a {
            display: inline-block;
            width: 70px;
            height: 70px;
            background: linear-gradient(135deg, #25d366, #128c7e);
            border-radius: 50%;
            text-align: center;
            line-height: 70px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.3);
            transition: all 0.3s ease;
        }
        .whatsapp-cs a:hover {
            transform: scale(1.1);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.4);
        }
        .whatsapp-cs img {
            width: 35px;
            height: 35px;
            vertical-align: middle;
        }
        @media (max-width: 600px) {
            .container {
                padding: 20px;
                margin: 20px;
            }
            h2 {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="absen-icon"><i class="fas fa-clipboard-check"></i></div>
        <h2>Absen Kelas untuk <?php echo htmlspecialchars($nama); ?></h2>
        <?php if (!empty($message)): ?>
            <div class="message <?php echo $success ? 'success' : 'error'; ?>"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="status-options">
                <label><input type="radio" name="status" value="hadir" required> <i class="fas fa-check-circle"></i> Hadir</label>
                <label><input type="radio" name="status" value="alfa"> <i class="fas fa-times-circle"></i> Alfa</label>
                <label><input type="radio" name="status" value="izin"> <i class="fas fa-calendar-alt"></i> Izin</label>
                <label><input type="radio" name="status" value="sakit"> <i class="fas fa-medkit"></i> Sakit</label>
            </div>
            <button type="submit"><i class="fas fa-paper-plane"></i> Kirim Absen</button>
        </form>
        <button type="button" class="back-btn" onclick="window.location.href='dashboard.php'">
            <i class="fas fa-arrow-left"></i> Kembali ke Utama
        </button>
    </div>

    <!-- Logo WhatsApp CS di kanan bawah -->
    <div class="whatsapp-cs">
        <a href="https://wa.me/message/WUFEKYAQO7RKD1" target="_blank" title="Hubungi CS via WhatsApp">
            <img src="https://upload.wikimedia.org/wikipedia/commons/6/6b/WhatsApp.svg" alt="WhatsApp CS">
        </a>
    </div>
</body>
</html>