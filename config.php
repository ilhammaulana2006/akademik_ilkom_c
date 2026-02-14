<?php
// config.php - File konfigurasi database
$host = 'localhost';
$user = 'root';  // Ganti dengan username database Anda jika berbeda
$pass = '';      // Ganti dengan password database Anda jika berbeda
$dbname = 'mahasiswa_ilkom_c';  // Menggunakan database 'admin_ilkom_c' sesuai permintaan

// Membuat koneksi
$conn = new mysqli($host, $user, $pass, $dbname);

// Cek koneksi
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}
?>