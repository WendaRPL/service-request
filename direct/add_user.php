<?php
require_once 'config.php';

$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';
$retype   = $_POST['retype_password'] ?? '';
$role     = $_POST['role'] ?? '';

if (!$username || !$password || !$retype || !$role) {
    echo json_encode([
        'status' => false,
        'message' => 'Semua field wajib diisi'
    ]);
    exit;
}

if ($password !== $retype) {
    echo json_encode([
        'status' => false,
        'message' => 'Password tidak sama'
    ]);
    exit;
}

$hash = password_hash($password, PASSWORD_DEFAULT);

$stmt = $conn->prepare("
    INSERT INTO users (username, password, role, created_at)
    VALUES (?, ?, ?, NOW())
");
$stmt->bind_param("ssi", $username, $hash, $role);

if ($stmt->execute()) {
    echo json_encode([
        'status' => true,
        'message' => 'User berhasil ditambahkan'
    ]);
} else {
    echo json_encode([
        'status' => false,
        'message' => 'Gagal tambah user'
    ]);
}
