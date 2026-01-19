<?php
require_once 'config.php';

$id = $_POST['id'] ?? '';

if (!$id) {
    echo json_encode([
        'status' => false,
        'message' => 'ID tidak valid'
    ]);
    exit;
}

$stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    echo json_encode([
        'status' => true,
        'message' => 'User berhasil dihapus'
    ]);
} else {
    echo json_encode([
        'status' => false,
        'message' => 'Gagal hapus user'
    ]);
}
