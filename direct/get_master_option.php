<?php
require_once __DIR__ . '/config.php';

header('Content-Type: application/json');

$type = $_GET['type'] ?? '';
$data = [];

switch ($type) {

    /* =========================
       TOKO
       dipakai oleh:
       - karyawan
       - role permission (explode)
    ========================= */
    case 'toko':
        $sql = "
            SELECT 
                id,
                nama_toko AS text,
                kode_toko
            FROM master_toko
            ORDER BY nama_toko ASC
        ";
        break;

    /* =========================
       KARYAWAN
    ========================= */
    case 'karyawan':
        $sql = "
            SELECT 
                id,
                username AS text
            FROM master_karyawan
            ORDER BY username ASC
        ";
        break;

    /* =========================
       JENIS KENDALA
    ========================= */
    case 'jenis_kendala':
        $sql = "
            SELECT 
                id,
                nama_kendala AS text
            FROM master_jenis_kendala
            ORDER BY nama_kendala ASC
        ";
        break;

    /* =========================
       ROLE
    ========================= */
    case 'role':
        $sql = "
            SELECT 
                id,
                role_name AS text
            FROM role
            ORDER BY role_name ASC
        ";
        break;

    default:
        echo json_encode([]);
        exit;
}

$q = $conn->query($sql);

if (!$q) {
    echo json_encode([]);
    exit;
}

while ($row = $q->fetch_assoc()) {
    $data[] = [
        'id'   => $row['id'],
        'text' => $row['text'],
        'kode' => $row['kode_toko']
    ];
}

echo json_encode($data);
