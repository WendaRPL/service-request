<?php
require_once 'config.php';
header('Content-Type: application/json');

$id       = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';
$retype   = $_POST['retype_password'] ?? '';
$role     = isset($_POST['role']) ? (int)$_POST['role'] : 0;

if ($id <= 0 || $username === '' || $role <= 0) {
    echo json_encode(['status'=>false,'message'=>'Data tidak lengkap']);
    exit;
}

// password optional
if (!empty($password)) {
    if ($password !== $retype) {
        echo json_encode(['status'=>false,'message'=>'Password tidak sama']);
        exit;
    }
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("UPDATE users SET username=?, password=?, role=? WHERE id=?");
    $stmt->bind_param("ssii", $username, $hash, $role, $id);
} else {
    $stmt = $conn->prepare("UPDATE users SET username=?, role=? WHERE id=?");
    $stmt->bind_param("sii", $username, $role, $id);
}

if ($stmt->execute()) {
    echo json_encode(['status'=>true,'message'=>'User berhasil diupdate']);
} else {
    echo json_encode(['status'=>false,'message'=>'Gagal update user']);
}
