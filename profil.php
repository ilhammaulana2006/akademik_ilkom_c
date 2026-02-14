<?php
session_start();
if (!isset($_SESSION['nim']) || !isset($_SESSION['nama'])) {
    header("Location: login.php");
    exit();
}
require_once 'config.php'; // Sertakan file koneksi

$nim = $_SESSION['nim'];
$nama = $_SESSION['nama'];

// Query untuk mendapatkan biodata mahasiswa
$query_biodata = $conn->prepare("SELECT prodi, kelas, semester, jenis_kelamin, agama, nomor_hp, media_sosial FROM biodata WHERE nim = ?");
$query_biodata->bind_param("s", $nim);
$query_biodata->execute();
$result_biodata = $query_biodata->get_result();

if ($result_biodata->num_rows > 0) {
    $biodata = $result_biodata->fetch_assoc();
    $prodi = $biodata['prodi'] ?? 'Belum diisi';
    $kelas = $biodata['kelas'] ?? 'Belum diisi';
    $semester = $biodata['semester'] ?? 'Belum diisi';
    $jenis_kelamin = $biodata['jenis_kelamin'] ?? 'Belum diisi';
    $agama = $biodata['agama'] ?? 'Belum diisi';
    $nomor_hp = $biodata['nomor_hp'] ?? 'Belum diisi';
    $media_sosial = $biodata['media_sosial'] ?? 'Belum diisi';
} else {
    // Jika biodata tidak ditemukan, set default
    $prodi = "Belum diisi";
    $semester = "Belum diisi";
    $kelas = "Belum diisi";
    $jenis_kelamin = "Belum diisi";
    $agama = "Belum diisi";
    $nomor_hp = "Belum diisi";
    $media_sosial = "Belum diisi";
}
$query_biodata->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Mahasiswa | Ilkom C</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            overflow-x: hidden;
            position: relative;
        }
        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="50" cy="50" r="2" fill="rgba(255,255,255,0.1)"/></svg>') repeat;
            animation: float 20s infinite linear;
        }
        @keyframes float {
            0% { transform: translateY(0); }
            100% { transform: translateY(-100px); }
        }
        .container {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            border-radius: 25px;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.15);
            padding: 50px;
            max-width: 700px;
            width: 100%;
            position: relative;
            z-index: 1;
            animation: slideIn 1s ease-out;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        @keyframes slideIn {
            from { transform: translateY(100px) scale(0.9); opacity: 0; }
            to { transform: translateY(0) scale(1); opacity: 1; }
        }
        .profile-header {
            text-align: center;
            margin-bottom: 40px;
            animation: fadeInDown 1s ease-out;
        }
        @keyframes fadeInDown {
            from { opacity: 0; transform: translateY(-30px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .profile-avatar {
            width: 120px;
            height: 120px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            animation: bounceIn 1s ease-out;
        }
        @keyframes bounceIn {
            0% { transform: scale(0); }
            50% { transform: scale(1.2); }
            100% { transform: scale(1); }
        }
        .profile-avatar i {
            font-size: 60px;
            color: #fff;
        }
        .profile-name {
            font-size: 2rem;
            font-weight: 700;
            color: #fff;
            text-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
        }
        .profile-nim {
            font-size: 1.1rem;
            color: rgba(255, 255, 255, 0.8);
            margin-top: 5px;
        }
        .biodata-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }
        .biodata-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 20px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: all 0.3s ease;
            animation: fadeInUp 0.8s ease-out forwards;
            opacity: 0;
        }
        .biodata-card:nth-child(1) { animation-delay: 0.1s; }
        .biodata-card:nth-child(2) { animation-delay: 0.2s; }
        .biodata-card:nth-child(3) { animation-delay: 0.3s; }
        .biodata-card:nth-child(4) { animation-delay: 0.4s; }
        .biodata-card:nth-child(5) { animation-delay: 0.5s; }
        .biodata-card:nth-child(6) { animation-delay: 0.6s; }
        .biodata-card:nth-child(7) { animation-delay: 0.7s; }
        @keyframes fadeInUp {
            to { opacity: 1; transform: translateY(0); }
            from { opacity: 0; transform: translateY(20px); }
        }
        .biodata-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.2);
        }
        .biodata-card h3 {
            font-size: 1.2rem;
            color: #fff;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
        }
        .biodata-card h3 i {
            margin-right: 10px;
            color: #667eea;
        }
        .biodata-card p {
            font-size: 1rem;
            color: rgba(255, 255, 255, 0.9);
            font-weight: 500;
        }
        .action-buttons {
            display: flex;
            justify-content: space-between;
            gap: 20px;
        }
        .btn {
            flex: 1;
            padding: 15px;
            border: none;
            border-radius: 15px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            text-align: center;
            backdrop-filter: blur(5px);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: #fff;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        }
        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.4);
        }
        .btn-secondary {
            background: rgba(255, 255, 255, 0.2);
            color: #fff;
        }
        .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }
        .whatsapp-cs {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 1000;
            animation: bounceIn 1s ease-out;
        }
        .whatsapp-cs a {
            display: inline-block;
            width: 70px;
            height: 70px;
            background: linear-gradient(135deg, #25d366, #128c7e);
            border-radius: 50%;
            text-align: center;
            line-height: 70px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            transition: all 0.3s ease;
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0% { box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3); }
            50% { box-shadow: 0 10px 30px rgba(37, 211, 102, 0.6); }
            100% { box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3); }
        }
        .whatsapp-cs a:hover {
            transform: scale(1.1) rotate(5deg);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.4);
        }
        .whatsapp-cs img {
            width: 35px;
            height: 35px;
            vertical-align: middle;
        }
        @media (max-width: 768px) {
            .container {
                padding: 30px;
                margin: 20px;
            }
            .profile-name {
                font-size: 1.5rem;
            }
            .biodata-grid {
                grid-template-columns: 1fr;
            }
            .action-buttons {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="profile-header">
            <div class="profile-avatar">
                <i class="fas fa-user"></i>
            </div>
            <h1 class="profile-name"><?php echo htmlspecialchars($nama); ?></h1>
            <p class="profile-nim">NIM: <?php echo htmlspecialchars($nim); ?></p>
        </div>

        <div class="biodata-grid">
            <div class="biodata-card">
                <h3><i class="fas fa-graduation-cap"></i> Prodi</h3>
                <p><?php echo htmlspecialchars($prodi); ?></p>
            </div>
            <div class="biodata-card">
                <h3><i class="fas fa-graduation-cap"></i> Kelas</h3>
                <p><?php echo htmlspecialchars($kelas); ?></p>
            </div>
            <div class="biodata-card">
                <h3><i class="fas fa-calendar-alt"></i> Semester</h3>
                <p><?php echo htmlspecialchars($semester); ?></p>
            </div>
            <div class="biodata-card">
                <h3><i class="fas fa-venus-mars"></i> Jenis Kelamin</h3>
                <p><?php echo htmlspecialchars($jenis_kelamin); ?></p>
            </div>
            <div class="biodata-card">
                <h3><i class="fas fa-pray"></i> Agama</h3>
                <p><?php echo htmlspecialchars($agama); ?></p>
            </div>
            <div class="biodata-card">
                <h3><i class="fas fa-phone"></i> Nomor HP</h3>
                <p><?php echo htmlspecialchars($nomor_hp); ?></p>
            </div>
            <div class="biodata-card">
                <h3><i class="fab fa-instagram"></i> Media Sosial</h3>
                <p><?php echo htmlspecialchars($media_sosial); ?></p>
            </div>
        </div>

        <div class="action-buttons">
            <a href="biodata_diri.php" class="btn btn-primary"><i class="fas fa-edit"></i> Edit Biodata</a>
            <a href="dashboard.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Kembali ke Dashboard</a>
        </div>
    </div>

    <!-- Logo WhatsApp CS di kanan bawah -->
    <div class="whatsapp-cs">
        <a href="https://wa.me/6285165877506?text=Halo%20CS,%20saya%20butuh%20bantuan" target="_blank" title="Hubungi CS via WhatsApp">
            <img src="https://upload.wikimedia.org/wikipedia/commons/6/6b/WhatsApp.svg" alt="WhatsApp CS">
        </a>
    </div>
</body>
</html>