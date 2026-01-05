<?php
// direct/config.php
session_start();

$conn = new mysqli("localhost", "root", "", "service_request");
if ($conn->connect_error) {
    die("DB Error");
}

// Basic session data
$username = $_SESSION['username'] ?? null;
$user_id  = $_SESSION['user_id'] ?? null;
$role_id  = $_SESSION['role_id'] ?? null;

// Load permissions
$permissions = [];

if ($role_id) {
    $sql = "
        SELECT role_key_name,
               key_value_bool,
               key_value_string,
               key_value_datetime
        FROM transaksi_user_role
        WHERE role_id = ?
    ";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $role_id);
    $stmt->execute();
    $res = $stmt->get_result();

    while ($row = $res->fetch_assoc()) {
        if (!is_null($row['key_value_bool'])) {
            $val = (int)$row['key_value_bool'];
        } elseif (!is_null($row['key_value_string'])) {
            $val = trim($row['key_value_string']);
        } elseif (!is_null($row['key_value_datetime'])) {
            $val = $row['key_value_datetime'];
        } else {
            $val = null;
        }

        $permissions[$row['role_key_name']] = $val;
    }
}

/**
 * Check boolean-like permission
 * cocok buat enable, update_toko, dll
 */
function can($key): bool {
    global $permissions;
    if (!isset($permissions[$key])) return false;

    $v = $permissions[$key];

    if ($v === 1) return true;
    if (is_string($v)) {
        return in_array(strtolower($v), ['yes', 'true', 'allow', 'enabled']);
    }

    return false;
}

function isStaffIT(): bool {
    return (
        can('handling_request') ||
        can('update_request') ||
        can('manage_user') ||
        can('update_toko') ||
        can('update_role')
    );
}


/**
 * Get raw permission value (STRING / DATETIME)
 * cocok buat manage-user, update_request, dll
 */
function permission($key, $default = null) {
    global $permissions;
    return $permissions[$key] ?? $default;
}

/**
 * Advanced check untuk permission STRING
 * contoh:
 * permission_is('manage-user', ['write','full'])
 */
function permission_is($key, array $allowed): bool {
    $val = permission($key);
    if (!$val) return false;
    return in_array(strtolower($val), array_map('strtolower', $allowed));
}
