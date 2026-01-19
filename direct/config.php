<?php
// direct/config.php
session_start();

$conn = new mysqli("localhost", "root", "", "service_request");
if ($conn->connect_error) {
    die("DB Error");
}

$username = $_SESSION['username'] ?? null;
$user_id  = $_SESSION['user_id'] ?? null;
$role_id  = $_SESSION['role_id'] ?? null;

$permissions = [];

if ($role_id) {
    $sql = "SELECT role_key_name, key_value_bool, key_value_string, key_value_datetime 
            FROM transaksi_user_role WHERE role_id = ?";
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
 * Cek akses fitur (Boolean atau String Toko)
 * @param string $key Nama permission (contoh: 'input_request')
 * @param string $toko Kode toko yg mau dicek (contoh: 'HK') - Opsional
 */
function can($key, $toko = null): bool {
    global $permissions;
    if (!isset($permissions[$key])) return false;

    $v = $permissions[$key];

    // 1. Jika tipenya murni boolean (1/0)
    if ($v === 1) return true;
    if ($v === 0) return false;

    // 2. Jika tipenya string (List Toko)
    if (is_string($v)) {
        $v = strtolower($v);
        if ($v === 'none') return false;
        if ($v === 'all') return true;
        
        // Jika butuh pengecekan spesifik toko (misal: can('input_request', 'HK'))
        if ($toko !== null) {
            $allowedStores = explode(',', $v);
            return in_array(strtolower($toko), $allowedStores);
        }
        
        // Jika cuma cek "punya akses gak?" tanpa peduli toko mana
        return $v !== '';
    }

    return false;
}

/**
 * Mendapatkan list toko dalam bentuk array
 * Berguna untuk query: WHERE kode_toko IN (...)
 */
function get_allowed_stores($key): array {
    global $permissions;
    $v = $permissions[$key] ?? 'none';
    
    if ($v === 'all' || $v === 'none') return [$v];
    return explode(',', $v);
}

function isStaffIT(): bool {
    // Staff IT biasanya punya handling_request atau update_role
    return (can('handling_request') || can('update_role'));
}

function permission($key, $default = null) {
    global $permissions;
    return $permissions[$key] ?? $default;
}