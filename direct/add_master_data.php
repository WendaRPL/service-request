<?php
require_once __DIR__ . '/config.php';
session_start();

// validasi login
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo "Unauthorized";
    exit;
}

$createdBy = $_SESSION['user_id'];
$tab = $_POST['tab'] ?? '';

switch ($tab) {

    case 'toko':
        $stmt = $conn->prepare("
            INSERT INTO master_toko (nama_toko, kode_toko, created_at, created_by)
            VALUES (?, ?, NOW(), ?)
        ");
        $stmt->bind_param(
            "ssi",
            $_POST['nama'],
            $_POST['kode'],
            $createdBy
        );
        break;

    case 'karyawan':
        $stmt = $conn->prepare("
            INSERT INTO master_karyawan (username, user_toko, created_at, created_by)
            VALUES (?, ?, NOW(), ?)
        ");
        $stmt->bind_param(
            "sii",
            $_POST['nama'],
            $_POST['toko'],
            $createdBy
        );
        break;

    case 'jenis_kendala':
        $hasChild = ($_POST['turunan'] === 'Ya') ? 1 : 0;

        $stmt = $conn->prepare("
            INSERT INTO master_jenis_kendala
            (tipe_kendala, nama_kendala, has_child, created_at, created_by)
            VALUES (?, ?, ?, NOW(), ?)
        ");
        $stmt->bind_param(
            "ssii",
            $_POST['tipe'],
            $_POST['jenis'],
            $hasChild,
            $createdBy
        );
        break;

    case 'role':
        $stmt = $conn->prepare("
            INSERT INTO role (role_name, created_at, created_by)
            VALUES (?, NOW(), ?)
        ");
        $stmt->bind_param(
            "si",
            $_POST['nama'],
            $createdBy
        );
        break;

    default:
        http_response_code(400);
        echo "Invalid tab";
        exit;
}

if ($stmt->execute()) {
    echo "OK";
} else {
    http_response_code(500);
    echo $stmt->error;
}
