<?php
session_start(); // Mulai session untuk menyimpan pesan

// Koneksi ke database (sesuaikan dengan konfigurasi Anda)
// Untuk Laragon, default user adalah 'root' dan password kosong.
// Ganti 'username' dan 'password' dengan kredensial sebenarnya jika berbeda.
$conn = new mysqli('localhost', 'root', '', 'mahasiswa_ilkom_c');
if ($conn->connect_error) {
    $_SESSION['error'] = 'Koneksi database gagal: ' . $conn->connect_error;
    header("Location: admin_dashboard.php");
    exit;
}

// Ambil ID dari POST
$id = $_POST['id'] ?? null;
if (!$id) {
    $_SESSION['error'] = 'ID mahasiswa tidak ditemukan.';
    header("Location: admin_dashboard.php");
    exit;
}

// Sanitasi ID (pastikan integer)
$id = intval($id);

// Query delete
$sql = "DELETE FROM mahasiswa WHERE id = ?";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    $_SESSION['error'] = 'Gagal mempersiapkan query.';
    header("Location: admin_dashboard.php");
    exit;
}
$stmt->bind_param('i', $id);

if ($stmt->execute()) {
    $_SESSION['message'] = 'Mahasiswa berhasil dihapus.';
} else {
    $_SESSION['error'] = 'Gagal menghapus mahasiswa: ' . $stmt->error;
}

$stmt->close();
$conn->close();

// Redirect otomatis ke admin_dashboard.php
header("Location: admin_dashboard.php");
exit;
?>