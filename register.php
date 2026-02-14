<?php
session_start();
include 'config.php';

$message = ''; // Variabel untuk pesan
$messageType = ''; // Tipe pesan: success, error, warning

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nim = trim($_POST['nim'] ?? '');
    $nama = trim($_POST['nama'] ?? '');
    $email = trim($_POST['email'] ?? ''); // Tambahkan input email
    $phone = trim($_POST['phone'] ?? ''); // Tambahkan input phone
    $password = trim($_POST['password'] ?? '');
    
    // Validasi dasar
    if (empty($nim) || empty($nama) || empty($password)) {
        $message = "❌ NIM, Nama, dan Password wajib diisi!";
        $messageType = 'error';
    } elseif (strlen($password) < 6) {
        $message = "❌ Password minimal 6 karakter!";
        $messageType = 'warning';
    } elseif (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        // Validasi format email jika diisi
        $message = "❌ Format Email tidak valid!";
        $messageType = 'error';
    } elseif (!empty($phone) && !preg_match('/^[+\d\s-]+$/', $phone)) {
        // Validasi format phone: hanya angka, spasi, tanda +, dan -
        $message = "❌ Format Nomor Telepon tidak valid!";
        $messageType = 'error';
    } else {
        // Hash password untuk keamanan
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Periksa apakah NIM sudah ada
        $stmt = $conn->prepare("SELECT nim FROM mahasiswa WHERE nim = ?");
        if (!$stmt) {
            $message = "❌ Kesalahan database!";
            $messageType = 'error';
        } else {
            $stmt->bind_param("s", $nim);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                $message = "❌ NIM sudah terdaftar!";
                $messageType = 'error';
            } else {
                // Periksa apakah Email sudah ada (jika diisi)
                if (!empty($email)) {
                    $stmt_email = $conn->prepare("SELECT email FROM mahasiswa WHERE email = ?");
                    if ($stmt_email) {
                        $stmt_email->bind_param("s", $email);
                        $stmt_email->execute();
                        $result_email = $stmt_email->get_result();
                        if ($result_email->num_rows > 0) {
                            $message = "❌ Email sudah terdaftar!";
                            $messageType = 'error';
                        }
                        $stmt_email->close();
                    }
                }
                
                // Periksa apakah Nomor Telepon sudah ada (jika diisi)
                if (!empty($phone)) {
                    $stmt_phone = $conn->prepare("SELECT phone FROM mahasiswa WHERE phone = ?");
                    if ($stmt_phone) {
                        $stmt_phone->bind_param("s", $phone);
                        $stmt_phone->execute();
                        $result_phone = $stmt_phone->get_result();
                        if ($result_phone->num_rows > 0) {
                            $message = "❌ Nomor Telepon sudah terdaftar!";
                            $messageType = 'error';
                        }
                        $stmt_phone->close();
                    }
                }
                
                if (empty($message)) {
                    // Set email dan phone ke NULL jika kosong untuk menghindari duplikasi pada unique key
                    $email_value = !empty($email) ? $email : NULL;
                    $phone_value = !empty($phone) ? $phone : NULL;
                    
                    // Insert data baru (email, phone sekarang nullable)
                    $stmt_insert = $conn->prepare("INSERT INTO mahasiswa (nim, nama, email, phone, password) VALUES (?, ?, ?, ?, ?)");
                    if (!$stmt_insert) {
                        $message = "❌ Kesalahan database!";
                        $messageType = 'error';
                    } else {
                        $stmt_insert->bind_param("sssss", $nim, $nama, $email_value, $phone_value, $hashed_password);
                        if ($stmt_insert->execute()) {
                            $message = "✅ Registrasi berhasil! Silakan login.";
                            $messageType = 'success';
                            // Opsional: redirect setelah sukses
                            // header("Location: login.php?msg=Registrasi berhasil! Silakan login.");
                            // exit;
                        } else {
                            $message = "❌ Gagal registrasi!";
                            $messageType = 'error';
                        }
                        $stmt_insert->close();
                    }
                }
            }
            $stmt->close();
        }
    }
}
// Tutup koneksi database di akhir (setelah semua operasi)
$conn->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrasi | Ilkom C</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            overflow: hidden;
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
            animation: float 20s linear infinite;
        }
        @keyframes float {
            0% { transform: translateY(0); }
            100% { transform: translateY(-100px); }
        }
        .container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.3), inset 0 1px 0 rgba(255, 255, 255, 0.6);
            padding: 50px;
            max-width: 450px;
            width: 100%;
            animation: slideIn 0.8s ease-out;
            position: relative;
            z-index: 1;
            text-align: center;
        }
        @keyframes slideIn {
            from { opacity: 0; transform: translateY(50px) scale(0.9); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }
        .register-icon {
            font-size: 3rem;
            margin-bottom: 20px;
            animation: bounce 2s infinite;
        }
        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% { transform: translateY(0); }
            40% { transform: translateY(-10px); }
            60% { transform: translateY(-5px); }
        }
        h2 {
            color: #333;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }
        .form-control {
            border-radius: 12px;
            border: 2px solid transparent;
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            padding: 15px 20px;
            transition: all 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94);
            box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .form-control:focus {
            border-color: #667eea;
            background: white;
            box-shadow: 0 0 20px rgba(102, 126, 234, 0.4), inset 0 2px 4px rgba(0, 0, 0, 0.1);
            transform: translateY(-2px);
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%);
            border: none;
            border-radius: 12px;
            padding: 15px;
            width: 100%;
            font-weight: bold;
            color: white;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            margin-top: 20px;
        }
        .btn-primary::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
            transition: left 0.5s;
        }
        .btn-primary:hover::before {
            left: 100%;
        }
        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
        }
        .input-group-text {
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            border: 2px solid transparent;
            border-radius: 12px 0 0 12px;
            color: #6c757d;
            transition: all 0.3s ease;
        }
        .input-group:focus-within .input-group-text {
            border-color: #667eea;
            background: white;
        }
        .alert {
            border-radius: 12px;
            border: none;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            animation: fadeIn 0.5s ease-in-out;
        }
        .form-label {
            font-weight: 600;
            color: #495057;
            text-align: left;
            display: block;
            margin-bottom: 5px;
        }
        p {
            margin-top: 20px;
            color: #6c757d;
        }
        a {
            color: #667eea;
            text-decoration: none;
            font-weight: bold;
        }
        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="register-icon">📝</div>
        <h2>Registrasi | Ilkom C</h2>
        <?php if (!empty($message)): ?>
            <div class="alert alert-<?php echo $messageType === 'success' ? 'success' : ($messageType === 'warning' ? 'warning' : 'danger'); ?>" role="alert">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
        <form method="POST" id="registerForm">
            <div class="mb-3">
                <label for="nim" class="form-label">NIM</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-id-card"></i></span>
                    <input type="text" class="form-control" id="nim" name="nim" placeholder="Masukkan NIM" required>
                </div>
            </div>
            <div class="mb-3">
                <label for="nama" class="form-label">Nama</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-user"></i></span>
                    <input type="text" class="form-control" id="nama" name="nama" placeholder="Masukkan Nama" required>
                </div>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email (Opsional)</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                    <input type="email" class="form-control" id="email" name="email" placeholder="Masukkan Email">
                </div>
            </div>
            <div class="mb-3">
                <label for="phone" class="form-label">Nomor Telepon (Opsional)</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-phone"></i></span>
                    <input type="text" class="form-control" id="phone" name="phone" placeholder="Masukkan Nomor Telepon">
                </div>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                    <input type="password" class="form-control" id="password" name="password" placeholder="Masukkan Password" required>
                </div>
            </div>
            <button type="submit" class="btn btn-primary">Daftar</button>
        </form>
        <p>Sudah punya akun? <a href="login.php">Login di sini</a></p>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            if (password.length < 6) {
                e.preventDefault();
                alert('Password minimal 6 karakter!');
            }
        });
    </script>
</body>
</html>