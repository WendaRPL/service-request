<?php
require_once "config.php";

// Ambil parameter pencarian (jika ada)
$query = $_GET['query'] ?? '';
$is_search = isset($_GET['search_karyawan']); // Flag khusus buat filter karyawan

$sql = "
    SELECT u.id, u.username, u.created_at, r.id AS role_id, r.role_name
    FROM users u
    LEFT JOIN role r ON u.role = r.id
    WHERE 1=1
";

// Filter 1: Kalau lagi cari buat Karyawan, jangan tampilin yang sudah terdaftar
if ($is_search) {
    $sql .= " AND u.id NOT IN (SELECT user_id FROM master_karyawan) ";
}

// Filter 2: Kalau ada keyword pencarian
if ($query !== '') {
    $sql .= " AND u.username LIKE '%" . $conn->real_escape_string($query) . "%' ";
}

$sql .= " ORDER BY u.id DESC LIMIT 10"; // Limit biar gak keberatan loadnya

$res = $conn->query($sql);

$data = [];
while ($row = $res->fetch_assoc()) {
    $data[] = [
        // Sesuai format yang lu punya
        'id'        => $row['id'],
        'username'  => $row['username'],
        'role_id'   => $row['role_id'],
        'role_name' => $row['role_name'],
        'created'   => date('d M Y H:i', strtotime($row['created_at']))
    ];
}

echo json_encode($data);