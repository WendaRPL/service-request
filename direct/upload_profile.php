<?php
require_once 'config.php'; // Sesuaikan path config lu
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => false, 'message' => 'Method tidak diizinkan']);
    exit;
}

// Pastikan user sudah login
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => false, 'message' => 'Sesi habis, silakan login ulang']);
    exit;
}

$user_id = $_SESSION['user_id'];
$uploadDir = __DIR__ . "/../uploads/user/avatar/"; // Folder simpan foto

// Buat folder jika belum ada
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

if (isset($_FILES['profile_pic'])) {
    $file = $_FILES['profile_pic'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'webp'];

    if (!in_array($ext, $allowed)) {
        echo json_encode(['status' => false, 'message' => 'Format file harus JPG, PNG, atau WEBP']);
        exit;
    }

    // Nama file unik biar ga bentrok
    $newFilename = "user_" . $user_id . "_" . time() . "." . $ext;
    $targetPath = $uploadDir . $newFilename;

    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        // Update database - ganti 'profile_pic' sesuai nama kolom di table user lu
        $stmt = $conn->prepare("UPDATE users SET profile_pic = ? WHERE id = ?");
        $stmt->bind_param("si", $newFilename, $user_id);

        if ($stmt->execute()) {
            echo json_encode([
                'status' => true, 
                'message' => 'Foto berhasil diupdate',
                'filename' => $newFilename
            ]);
        } else {
            echo json_encode(['status' => false, 'message' => 'Gagal update database']);
        }
    } else {
        echo json_encode(['status' => false, 'message' => 'Gagal memindahkan file']);
    }
} else {
    echo json_encode(['status' => false, 'message' => 'Tidak ada file yang diunggah']);
}