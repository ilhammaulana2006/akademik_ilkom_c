<?php
include 'config.php';

$message = ''; // Variabel untuk pesan
$messageType = ''; // Tipe pesan: success, error, warning

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nim = trim($_POST['nim'] ?? '');
    $new_password = trim($_POST['new_password'] ?? '');
    $confirm_password = trim($_POST['confirm_password'] ?? '');
    
    // Validasi input
    if (empty($nim) || empty($new_password) || empty($confirm_password)) {
        $message = "❌ Semua field wajib diisi!";
        $messageType = 'error';
    } elseif ($new_password !== $confirm_password) {
        $message = "❌ Password baru dan konfirmasi tidak cocok!";
        $messageType = 'error';
    } elseif (strlen($new_password) < 6) {
        $message = "❌ Password baru minimal 6 karakter!";
        $messageType = 'warning';
    } else {
        // Cek apakah NIM ada di database
        $stmt = $conn->prepare("SELECT nim FROM mahasiswa WHERE nim = ?");
        if (!$stmt) {
            $message = "❌ Kesalahan database!";
            $messageType = 'error';
        } else {
            $stmt->bind_param("s", $nim);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows == 0) {
                $message = "❌ NIM tidak ditemukan!";
                $messageType = 'error';
                $stmt->close();
            } else {
                $stmt->close();
                
                // Hash password baru
                $hashed_new = password_hash($new_password, PASSWORD_DEFAULT);
                
                // Update password
                $stmt = $conn->prepare("UPDATE mahasiswa SET password = ? WHERE nim = ?");
                if (!$stmt) {
                    $message = "❌ Kesalahan database!";
                    $messageType = 'error';
                } else {
                    $stmt->bind_param("ss", $hashed_new, $nim);
                    if ($stmt->execute()) {
                        $message = "✅ Password berhasil direset! Anda akan diarahkan ke halaman login.";
                        $messageType = 'success';
                        // Redirect ke login setelah sukses dengan delay
                        echo "<script>setTimeout(function(){ window.location.href='login.php'; }, 2000);</script>";
                    } else {
                        $message = "❌ Gagal mereset password!";
                        $messageType = 'error';
                    }
                    $stmt->close();
                }
            }
        }
    }
}
$conn->close(); // Tutup koneksi database
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lupa Kata Sandi</title>
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
        .forgot-icon {
            font-size: 3rem;
            margin-bottom: 20px;
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
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
        <div class="forgot-icon">🔑</div>
        <h2>Lupa Kata Sandi</h2>
        <?php if (!empty($message)): ?>
            <div class="alert alert-<?php echo $messageType === 'success' ? 'success' : ($messageType === 'warning' ? 'warning' : 'danger'); ?>" role="alert">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
        <form method="POST" id="forgotForm">
            <div class="mb-3">
                <label for="nim" class="form-label">NIM</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-id-card"></i></span>
                    <input type="text" class="form-control" id="nim" name="nim" placeholder="Masukkan NIM" required>
                </div>
            </div>
            <div class="mb-3">
                <label for="new_password" class="form-label">Password Baru</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                    <input type="password" class="form-control" id="new_password" name="new_password" placeholder="Masukkan Password Baru" required>
                </div>
            </div>
            <div class="mb-3">
                <label for="confirm_password" class="form-label">Konfirmasi Password Baru</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-check"></i></span>
                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Konfirmasi Password Baru" required>
                </div>
            </div>
            <button type="submit" class="btn btn-primary">Reset Password</button>
        </form>
        <p>Ingat password Anda? <a href="login.php">Login di sini</a></p>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('forgotForm').addEventListener('submit', function(e) {
            const newPass = document.getElementById('new_password').value;
            const confirmPass = document.getElementById('confirm_password').value;
            if (newPass !== confirmPass) {
                e.preventDefault();
                alert('Password baru dan konfirmasi tidak cocok!');
            }
            if (newPass.length < 6) {
                e.preventDefault();
                alert('Password baru minimal 6 karakter!');
            }
        });
    </script>
</body>
</html>