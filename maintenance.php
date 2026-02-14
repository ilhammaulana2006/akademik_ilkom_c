<?php
// Asumsikan ini bagian dari halaman admin, pastikan session admin sudah dimulai
session_start();
include 'config.php';  // Pastikan $conn didefinisikan

// Fungsi logAction (jika belum ada, tambahkan atau hapus jika tidak diperlukan)
function logAction($action, $username) {
    // Contoh implementasi sederhana: simpan ke file log atau database
    // Ganti dengan implementasi sesuai kebutuhan Anda
    $log_message = date('Y-m-d H:i:s') . " - $username: $action\n";
    file_put_contents('admin_log.txt', $log_message, FILE_APPEND);
}

// Ambil status maintenance saat ini dari database
$query = $conn->prepare("SELECT setting_value FROM settings WHERE setting_key = 'maintenance_mode'");
$query->execute();
$result = $query->get_result();
$maintenance_row = $result->fetch_assoc();
$query->close();
$maintenance_mode = $maintenance_row ? $maintenance_row['setting_value'] : '0';  // Default ke '0' jika tidak ada

// Ambil username admin dari session (asumsikan session admin sudah diset)
$admin_username = $_SESSION['admin_username'] ?? 'Unknown';  // Ganti dengan key session yang sesuai

// Handle Toggle Maintenance Mode
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'toggle_maintenance') {
    $new_status = ($maintenance_mode == '1') ? '0' : '1';
    $stmt = $conn->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = 'maintenance_mode'");
    if ($stmt) {
        $stmt->bind_param("s", $new_status);
        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => ($new_status == '1') ? 'Mode perbaikan diaktifkan.' : 'Mode perbaikan dinonaktifkan.']);
            logAction('Toggle Maintenance', $admin_username);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Gagal toggle maintenance mode.']);
        }
        $stmt->close();
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Query preparation failed.']);
    }
    exit;
}
?>