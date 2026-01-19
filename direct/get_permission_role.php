<?php
require_once __DIR__ . '/config.php';
header('Content-Type: application/json');

$role_id = $_GET['role_id'] ?? 0;
if (!$role_id) {
    echo json_encode([]);
    exit;
}

$sql = "
    SELECT 
        role_key_name,
        key_value_bool,
        key_value_string,
        key_value_integer,
        key_value_datetime
    FROM transaksi_user_role
    WHERE role_id = ?
";

$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $role_id);
$stmt->execute();
$res = $stmt->get_result();

$boolKeys   = [];
$stringKeys = [];
$expiration = 'forever';

while ($r = $res->fetch_assoc()) {
    $key = $r['role_key_name'];
    if (!$key) continue;

    // BOOLEAN
    if (!is_null($r['key_value_bool'])) {
        $boolKeys[$key] = (int)$r['key_value_bool'];
        continue;
    }

    // STRING
    if (!is_null($r['key_value_string'])) {
        if ($key === 'expiration_date') {
            $expiration = $r['key_value_string'];
        } else {
            $stringKeys[$key] = $r['key_value_string'];
        }
        continue;
    }

    // INTEGER (optional future)
    if (!is_null($r['key_value_integer'])) {
        $stringKeys[$key] = $r['key_value_integer'];
        continue;
    }

    // DATETIME (optional future)
    if (!is_null($r['key_value_datetime'])) {
        $stringKeys[$key] = $r['key_value_datetime'];
    }
}

echo json_encode([
    'bool'       => $boolKeys,
    'string'     => $stringKeys,
    'expiration' => $expiration
]);
