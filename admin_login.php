<?php
session_start();

// Jika sudah login sebagai admin, redirect ke dashboard
if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
    header("Location: admin_dashboard.php");
    exit;
}

include 'config.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if (!empty($username) && !empty($password)) {
        $stmt = $conn->prepare("SELECT password FROM admin WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            if (password_verify($password, $row['password'])) {
                // Login berhasil
                $_SESSION['role'] = 'admin';
                $_SESSION['admin_username'] = $username;
                $_SESSION['last_activity'] = time(); // Set last activity
                header("Location: admin_dashboard.php");
                exit;
            } else {
                $error = "Password salah.";
            }
        } else {
            $error = "Username tidak ditemukan.";
        }
        $stmt->close();
    } else {
        $error = "Harap isi semua field.";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no"> <!-- Tambahan user-scalable=no untuk mencegah zoom di HP -->
    <title>Login Admin | Ilkom C</title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* Styling mirip dengan kode asli, dengan penyesuaian untuk mobile */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            color: #333;
            line-height: 1.6;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            overflow-x: hidden;
            transition: background 0.3s ease, color 0.3s ease; /* Transisi untuk dark mode */
        }

        body.dark-mode {
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            color: #ecf0f1;
        }

        .login-container {
            background: rgba(255, 255, 255, 0.9);
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(10px);
            max-width: 400px;
            width: 90%; /* Lebih fleksibel untuk HP */
            text-align: center;
            animation: fadeInSlide 1.5s ease-out;
            transition: background 0.3s ease; /* Transisi untuk dark mode */
        }

        body.dark-mode .login-container {
            background: rgba(44, 62, 80, 0.9);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        }

        .login-container h2 {
            margin-bottom: 20px;
            color: #667eea;
            font-size: 2em;
            font-weight: 600;
        }

        body.dark-mode .login-container h2 {
            color: #ecf0f1;
        }

        .login-container form {
            display: flex;
            flex-direction: column;
        }

        .login-container input {
            padding: 15px;
            margin: 10px 0;
            border: 1px solid #ddd;
            border-radius: 10px;
            font-size: 16px; /* Minimum 16px untuk menghindari zoom di iOS */
            transition: border-color 0.3s ease, background 0.3s ease;
        }

        body.dark-mode .login-container input {
            background: #34495e;
            border-color: #7f8c8d;
            color: #ecf0f1;
        }

        .login-container input:focus {
            border-color: #667eea;
            outline: none;
        }

        .login-container button {
            padding: 15px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: transform 0.3s ease, background 0.3s ease;
            min-height: 50px; /* Touch-friendly untuk HP */
        }

        .login-container button:hover, .login-container button:active {
            transform: translateY(-3px);
        }

        .error {
            color: #ff6b6b;
            margin-bottom: 10px;
            font-weight: bold;
        }

        .register-link {
            margin-top: 20px;
            font-size: 14px;
        }

        .register-link a {
            color: #667eea;
            text-decoration: none;
        }

        body.dark-mode .register-link a {
            color: #ecf0f1;
        }

        .register-link a:hover {
            text-decoration: underline;
        }

        @keyframes fadeInSlide {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Dark Mode Toggle */
        .dark-mode-toggle {
            position: fixed;
            top: 20px;
            right: 20px;
            font-size: 24px;
            cursor: pointer;
            z-index: 1000;
            transition: transform 0.3s ease;
        }

        .dark-mode-toggle:hover {
            transform: scale(1.1);
        }

        /* Media Queries untuk HP (layar kecil) */
        @media (max-width: 768px) {
            .login-container {
                padding: 20px;
                width: 95%; /* Lebih lebar di HP */
            }
            .login-container h2 {
                font-size: 1.5em;
            }
            .login-container input, .login-container button {
                font-size: 18px; /* Lebih besar untuk kemudahan ketik di HP */
                padding: 18px;
            }
            .dark-mode-toggle {
                top: 10px;
                right: 10px;
                font-size: 20px;
            }
        }

        @media (max-width: 480px) {
            .login-container {
                padding: 15px;
                width: 100%;
                border-radius: 15px;
            }
            .login-container h2 {
                font-size: 1.3em;
            }
            .register-link {
                font-size: 12px;
            }
        }
    </style>
</head>
<body>
    <!-- Dark Mode Toggle -->
    <div class="dark-mode-toggle" id="darkModeToggle" title="Toggle Dark Mode">
        🌙
    </div>

    <div class="login-container">
        <h2>🔐 Login Admin</h2>
        <?php if ($error): ?>
            <p class="error"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>
        <form method="POST" action="">
            <input type="text" name="username" placeholder="Username" required autocomplete="username">
            <input type="password" name="password" placeholder="Password" required autocomplete="current-password">
            <button type="submit">Login</button>
        </form>
       
    </div>

    <script>
        // Dark Mode Toggle
        const darkModeToggle = document.getElementById('darkModeToggle');
        darkModeToggle.addEventListener('click', () => {
            document.body.classList.toggle('dark-mode');
            darkModeToggle.textContent = document.body.classList.contains('dark-mode') ? '☀️' : '🌙';
            localStorage.setItem('darkMode', document.body.classList.contains('dark-mode'));
        });

        // Persist Dark Mode
        if (localStorage.getItem('darkMode') === 'true') {
            document.body.classList.add('dark-mode');
            darkModeToggle.textContent = '☀️';
        }

        // Loading Effect on Form Submit (untuk feedback di HP)
        const form = document.querySelector('form');
        form.addEventListener('submit', () => {
            const button = form.querySelector('button');
            button.textContent = '🔄 Memproses...';
            button.disabled = true;
        });
    </script>
</body>
</html>