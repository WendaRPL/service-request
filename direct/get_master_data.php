<?php
require_once __DIR__ . "/config.php";

header('Content-Type: application/json');

$tab = $_GET['tab'] ?? '';
$data = [];

// --- LOGIKA FILTER TOKO ---
// Ambil ijin manage_user (isinya bisa 'all', 'none', atau 'HK,TM')
$manage_permission = permission('manage_user'); 

// Fungsi pembantu buat bikin filter SQL
function getStoreFilterSQL($columnName, $permissionValue) {
    if ($permissionValue === 'all') return "1=1"; // Liat semua
    if ($permissionValue === 'none' || empty($permissionValue)) {
        return "($columnName = 'none' OR $columnName IS NULL)";
    }
    
    // Jika isinya list 'HK,TM'
    $allowed = explode(',', $permissionValue);
    $escaped = implode("','", $allowed);
    return "($columnName IN ('$escaped') OR $columnName = 'none' OR $columnName IS NULL)";
}

switch ($tab) {
    case 'toko':
        // Cukup cek ijin fitur secara global (Boolean)
        if (can('update_toko')) {
            $sql = "
                SELECT 
                    t.id,
                    t.nama_toko AS nama,
                    t.kode_toko AS kode,
                    t.created_at AS tanggal,
                    COALESCE(u.username, 'System') AS dibuat_oleh
                FROM master_toko t
                LEFT JOIN users u ON u.id = t.created_by
                ORDER BY t.id ASC
            ";
        } else {
            // Kalau false, kasih array kosong
            echo json_encode([]);
            exit;
        }
        break;

    case 'karyawan':
        // Filter karyawan berdasarkan toko tempat dia bernaung
        $filter = getStoreFilterSQL('t.kode_toko', $manage_permission);
        $sql = "
            SELECT 
                k.id,
                k.username AS nama,
                COALESCE(t.nama_toko, '-') AS toko,
                k.created_at AS tanggal,
                COALESCE(u.username, 'System') AS dibuat_oleh
            FROM master_karyawan k
            LEFT JOIN master_toko t ON t.id = k.user_toko
            LEFT JOIN users u ON u.id = k.created_by
            WHERE $filter
            ORDER BY k.username ASC
        ";
        break;

    case 'jenis_kendala':
     if (can('handling_request')) {
        $sql = "
            SELECT
                j.id,
                j.tipe_kendala AS tipe,
                j.nama_kendala AS jenis,
                CASE WHEN j.has_child = 1 THEN 'Ya' ELSE 'Tidak' END AS turunan,
                j.created_at AS tanggal,
                COALESCE(u.username, 'System') AS dibuat_oleh
            FROM master_jenis_kendala j
            LEFT JOIN users u ON u.id = j.created_by
            ORDER BY j.tipe_kendala, j.nama_kendala
        ";
             } else {
            // Kalau gak punya akses, balikin array kosong atau handle sesuai kebijakan
            echo json_encode([]);
            exit;
        }
        break;

    case 'role':
        if (can('update_role')) {
            $sql = "
                SELECT
                    r.id,
                    r.role_name AS nama,
                    r.created_at AS tanggal,
                    COALESCE(u.username, 'System') AS dibuat_oleh
                FROM role r
                LEFT JOIN users u ON u.id = r.created_by
                ORDER BY r.id ASC
            ";
        } else {
            // Kalau gak punya akses, balikin array kosong atau handle sesuai kebijakan
            echo json_encode([]);
            exit;
        }
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
