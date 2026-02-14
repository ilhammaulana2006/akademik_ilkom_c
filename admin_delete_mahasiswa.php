<?php
// admin_delete_mahasiswa.php
// File ini menangani penghapusan mahasiswa dari database.
// Pastikan untuk mengganti detail koneksi database sesuai dengan setup Anda.
// Asumsi: Tabel 'mahasiswa' dengan kolom 'id' (primary key).

// Mulai session untuk pengecekan login admin (opsional, tambahkan jika diperlukan)
session_start();

// Cek apakah admin sudah login (opsional, sesuaikan dengan sistem autentikasi Anda)
// if (!isset($_SESSION['admin_logged_in'])) {
//     header('Location: login.php');
//     exit;
// }

// Koneksi database menggunakan PDO (ganti detail sesuai setup Anda)
$host = 'localhost'; // Ganti dengan host database Anda
$dbname = 'mahasiswa_ilkom_c'; // Ganti dengan nama database Anda
$user = 'username'; // Ganti dengan username database Anda
$pass = 'password'; // Ganti dengan password database Anda

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // Jika koneksi gagal, log error dan redirect
    error_log("Database connection failed: " . $e->getMessage());
    header('Location: admin_dashboard.php?error=Koneksi database gagal');
    exit;
}

// Fungsi untuk menghapus mahasiswa
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = trim($_POST['id']);
    
    // Validasi ID (pastikan ID adalah angka positif)
    if (!is_numeric($id) || $id <= 0) {
        // Redirect dengan pesan error
        header('Location: admin_dashboard.php?error=Invalid ID');
        exit;
    }
    
    // Gunakan PDO untuk keamanan
    try {
        $stmt = $pdo->prepare("DELETE FROM mahasiswa WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        
        // Cek apakah ada baris yang terhapus
        if ($stmt->rowCount() > 0) {
            // Redirect dengan pesan sukses
            header('Location: admin_mahasiswa.php?success=Mahasiswa berhasil dihapus');
        } else {
            // Redirect dengan pesan error jika ID tidak ditemukan
            header('Location: admin_mahasiswa.php?error=Mahasiswa tidak ditemukan');
        }
    } catch (PDOException $e) {
        // Log error (jangan tampilkan ke user untuk keamanan)
        error_log("Error deleting mahasiswa: " . $e->getMessage());
        // Redirect dengan pesan error umum
        header('Location: admin_mahasiswa.php?error=Gagal menghapus mahasiswa');
    }
} else {
    // Jika bukan POST atau ID tidak ada, redirect
    header('Location: admin_mahasiswa.php?error=Permintaan tidak valid');
}
exit;
?>