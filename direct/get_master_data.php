<?php
require_once __DIR__ . "/config.php";

header('Content-Type: application/json');

$tab = $_GET['tab'] ?? '';
$data = [];

switch ($tab) {

    case 'toko':
        $sql = "
            SELECT 
                id,
                nama_toko   AS nama,
                kode_toko   AS kode,
                created_at  AS tanggal,
                created_by  AS dibuat_oleh
            FROM master_toko
            ORDER BY id ASC
        ";
        break;

    case 'karyawan':
        $sql = "
            SELECT 
                k.id,
                k.username              AS nama,
                COALESCE(t.nama_toko, '-') AS toko,
                k.created_at            AS tanggal,
                k.created_by            AS dibuat_oleh
            FROM master_karyawan k
            LEFT JOIN master_toko t ON t.id = k.user_toko
            ORDER BY k.username ASC
        ";
        break;

    case 'jenis_kendala':
        $sql = "
            SELECT
                id,
                tipe_kendala    AS tipe,
                nama_kendala    AS jenis,
                CASE 
                    WHEN has_child = 1 THEN 'Ya'
                    ELSE 'Tidak'
                END             AS turunan,
                created_at      AS tanggal,
                created_by      AS dibuat_oleh
            FROM master_jenis_kendala
            ORDER BY tipe_kendala, nama_kendala
        ";
        break;

    case 'role':
        $sql = "
            SELECT
                id,
                role_name   AS nama,
                created_at  AS tanggal,
                created_by  AS dibuat_oleh
            FROM role
            ORDER BY id ASC
        ";
        break;

    default:
        echo json_encode([]);
        exit;
}

$result = $conn->query($sql);

if (!$result) {
    echo json_encode([
        'error' => true,
        'message' => $conn->error
    ]);
    exit;
}

while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

echo json_encode($data);
