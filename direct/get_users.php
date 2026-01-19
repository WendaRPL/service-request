<?php
require_once "config.php";

$q = "
    SELECT u.id, u.username, u.created_at, r.id AS role_id, r.role_name
    FROM users u
    LEFT JOIN role r ON u.role = r.id
    ORDER BY u.id DESC
";

$res = $conn->query($q);

$data = [];
while ($row = $res->fetch_assoc()) {
    $data[] = [
        'id'       => $row['id'],
        'username' => $row['username'],
        'role_id'  => $row['role_id'],
        'role_name'     => $row['role_name'],
        'created'  => date('d M Y H:i', strtotime($row['created_at']))
    ];
}

echo json_encode($data);
