<?php
require_once "config.php";

$res = $conn->query("SELECT id, role_name FROM role ORDER BY role_name ASC");

$data = [];
while ($r = $res->fetch_assoc()) {
    $data[] = [
        'id' => $r['id'],
        'text' => $r['role_name']
    ];
}

header('Content-Type: application/json');
echo json_encode($data);
