<?php
require __DIR__ . '/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method not allowed');
}

$tab = $_POST['tab'] ?? '';
$id  = intval($_POST['id'] ?? 0);

if (!$tab || !$id) {
    http_response_code(400);
    exit('Invalid request');
}

/* Mapping tab ke tabel */
$tableMap = [
    'toko'          => 'master_toko',
    'karyawan'      => 'master_karyawan',
    'jenis_kendala' => 'master_jenis_kendala',
    'role'          => 'role'
];

if (!isset($tableMap[$tab])) {
    http_response_code(400);
    exit('Tab tidak valid');
}

$table = $tableMap[$tab];

/* OPTIONAL: proteksi data penting */
if ($tab === 'role' && $id == 1) {
    http_response_code(403);
    exit('Role ini tidak boleh dihapus');
}

/* DELETE pakai prepared statement */
$stmt = $conn->prepare("DELETE FROM {$table} WHERE id = ?");
$stmt->bind_param('i', $id);

if (!$stmt->execute()) {
    http_response_code(500);
    exit('Gagal menghapus data');
}

echo json_encode(['status' => 'success']);
