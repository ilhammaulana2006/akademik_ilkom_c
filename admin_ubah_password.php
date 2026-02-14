<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: admin_login.php");
    exit;
}
include 'config.php';

$message = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    $username = $_SESSION['admin_username'];
    $stmt = $conn->prepare("SELECT password FROM admin WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    if (password_verify($current_password, $row['password'])) {
        if ($new_password === $confirm_password) {
            $hashed_new_password = password_hash($new_password, PASSWORD_DEFAULT);
            $update_stmt = $conn->prepare("UPDATE admin SET password = ? WHERE username = ?");
            $update_stmt->bind_param("ss", $hashed_new_password, $username);
            if ($update_stmt->execute()) {
                $message = "Password berhasil diubah.";
            } else {
                $message = "Gagal mengubah password.";
            }
            $update_stmt->close();
        } else {
            $message = "Password baru dan konfirmasi tidak cocok.";
        }
    } else {
        $message = "Password lama salah.";
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- Tambahan untuk responsivitas HP -->
    <title>Ubah Password Admin</title>
    <link rel="stylesheet" href="admin_ubahpassword.css">
    <!-- Gunakan CSS mirip dengan login -->
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
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }

        .welcome-header {
            text-align: center;
            margin-bottom: 20px;
        }

        .navbar .nav-container {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-bottom: 20px;
        }

        .navbar a {
            color: #667eea;
            text-decoration: none;
            padding: 10px 15px;
            border-radius: 5px;
            background: rgba(102, 126, 234, 0.1);
            transition: background 0.3s;
        }

        .navbar a:hover {
            background: rgba(102, 126, 234, 0.2);
        }

        .schedule-section form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .schedule-section input {
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 1em;
        }

        .schedule-section button {
            padding: 12px;
            background: #28a745;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1em;
            transition: background 0.3s;
        }

        .schedule-section button:hover {
            background: #218838;
        }

        .schedule-section p {
            text-align: center;
            color: #dc3545;
            font-weight: bold;
        }

        /* Responsive Design untuk Handphone (HP) - Media Queries */
        @media (max-width: 868px) {
            .container {
                margin: 10px; /* Kurangi margin untuk layar kecil */
                padding: 15px; /* Kurangi padding */
                border-radius: 5px; /* Kurangi radius */
            }

            .welcome-header h2 {
                font-size: 1.5em; /* Kurangi ukuran font header */
                text-align: center;
            }

            .navbar .nav-container {
                flex-direction: column; /* Stack vertikal untuk HP */
                gap: 10px;
            }

            .navbar a {
                width: 100%; /* Full width untuk kemudahan akses */
                text-align: center;
                padding: 12px; /* Sesuaikan padding untuk touch */
                font-size: 1em;
            }

            .schedule-section form {
                gap: 10px; /* Kurangi gap */
            }

            .schedule-section input, .schedule-section button {
                width: 100%; /* Full width */
                padding: 0.75rem; /* Sesuaikan untuk touch */
                font-size: 1em;
            }

            .schedule-section p {
                font-size: 0.9em;
                padding: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="welcome-header">
            <h2>🔑 Ubah Password Admin</h2>
        </div>
        <nav class="navbar">
            <div class="nav-container">
                <a href="admin_dashboard.php">🏠 Dashboard</a>
                <a href="admin_logout.php">🚪 Logout</a>
            </div>
        </nav>
        <div class="schedule-section">
            <form method="POST" action="">
                <input type="password" name="current_password" placeholder="Password Lama" required>
                <input type="password" name="new_password" placeholder="Password Baru" required>
                <input type="password" name="confirm_password" placeholder="Konfirmasi Password Baru" required>
                <button type="submit">Ubah Password</button>
            </form>
            <?php if ($message): ?>
                <p><?php echo htmlspecialchars($message); ?></p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>