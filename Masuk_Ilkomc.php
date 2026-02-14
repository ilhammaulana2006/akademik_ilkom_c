<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk | Ilkom C</title> <!-- Diubah judul agar sesuai konten -->
    <link rel="stylesheet" href="style.css"> <!-- Link ke file CSS eksternal jika ada -->
    <!-- Tambahkan Font Awesome untuk ikon -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            margin: 0;
            padding: 0;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        
        .container {
            max-width: 1200px; /* Ditingkatkan dari 600px untuk box yang lebih lebar */
            width: 100%;
            margin: 20px; /* Dikurangi dari 90px untuk lebih proporsional */
            padding: 40px; /* Dikurangi dari 80px untuk lebih kompak */
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            backdrop-filter: blur(10px);
            transition: transform 0.3s ease;
            text-align: center; /* Menambahkan untuk memusatkan teks di dalam container */
        }

        .container:hover {
            transform: translateY(-5px);
        }

        h1 {
            color: #ff0000;
            margin: 0 0 20px 0; /* Margin bawah untuk jarak, atas 0 */
            font-weight: 500;
            font-size: 2rem; /* Ditingkatkan sedikit untuk visibilitas */
        }

        h2 {
            text-align: center;
            color: #333;
            margin: 0 0 30px 0; /* Margin bawah dikurangi dari 250px ke 30px untuk lebih proporsional */
            font-weight: 500;
            font-size: 1.5rem;
        }

        h4 {
            text-align: center;
            color: #333;
            margin: 0 0 30px 0; /* Margin bawah dikurangi dari 250px ke 30px untuk proporsional */
            font-weight: 500;
            font-size: 1.5rem;
        }

        .warning {
            color: red;
            font-weight: bold;
            margin-bottom: 20px; /* Menambahkan margin bawah untuk jarak */
        }
        
        /* Gabungkan style tombol menjadi satu class utama */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(45deg, #25D366, #128C7E); /* Gradien hijau WhatsApp */
            color: white;
            padding: 14px 28px;
            border: none;
            border-radius: 50px;
            cursor: pointer;
            font-size: 18px;
            font-weight: bold;
            text-decoration: none;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            transition: all 0.3s ease;
            margin-top: 20px;
            width: 100%;
            box-sizing: border-box;
        }
        
        .btn:hover {
            background: linear-gradient(45deg, #128C7E, #25D366);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.3);
            transform: translateY(-2px);
        }
        
        .btn i {
            margin-right: 10px;
        }

        /* Media Queries untuk Responsivitas */
        @media only screen and (max-width: 400px) {
            .container {
                padding: 20px;
                margin: 10px;
            }
            h1 {
                font-size: 1.5rem;
            }
            h2, h4 {
                font-size: 1.2rem;
                margin-bottom: 20px;
            }
            .btn {
                padding: 12px 24px;
                font-size: 16px;
            }
        }

        /* Tambah breakpoint untuk tablet */
        @media only screen and (max-width: 768px) and (min-width: 401px) {
            .container {
                padding: 30px;
                margin: 15px;
            }
            h1 {
                font-size: 1.8rem;
            }
            h2, h4 {
                font-size: 1.4rem;
                margin-bottom: 25px;
            }
            .btn {
                padding: 13px 26px;
                font-size: 17px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Peringatan!! Dibaca Dulu, Sebelum Masuk!!</h1>
        
        <!-- Pesan peringatan untuk pengguna yang belum paham atau belum login -->
        <div class="warning">
            <h2>Silakan Daftar akun Anda terlebih dahulu. Apabila Lupa/salah passwordnya, hubungi CS dan Bikin daftar ulang. Selamat bergabung.</h2>
        </div>

        <h4></h4>
        
        <!-- Tombol untuk kembali ke login -->
        <a href="login.php" class="btn">
            <i class="fas fa-arrow-left"></i> Masuk Login
        </a>
        
        <!-- Tombol untuk menghubungi admin via WhatsApp -->
        <a href="https://wa.me/message/WUFEKYAQO7RKD1" target="_blank" class="btn">
            <i class="fab fa-whatsapp"></i> Customer Service (CS)
        </a>
    </div>
</body>
</html>