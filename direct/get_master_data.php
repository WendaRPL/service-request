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
        // Gunakan LEFT JOIN ke tabel users kalau perlu, 
        // tapi kalau username sudah ada di master_karyawan, pakai ini:
        $sql = "SELECT * FROM master_karyawan ORDER BY id DESC";
        $res = $conn->query($sql);
        
        if (!$res) {
            die("Error Query: " . $conn->error); // Biar ketauan kalau SQL-nya salah
        }

        $data = [];
        while ($row = $res->fetch_assoc()) {
            $tokoIds = $row['user_toko'] ?? ''; 
            $tokoNames = [];
            
            if (!empty($tokoIds)) {
                // explode memecah string jadi array [1, 2]
                $idArray = explode(',', $tokoIds); 
                
                // Lalu kita bersihkan biar beneran integer
                $cleanIds = implode(',', array_map('intval', $idArray)); 
                
                $qToko = $conn->query("SELECT nama_toko FROM master_toko WHERE id IN ($cleanIds)");
                
                    if ($qToko) {
                        while ($t = $qToko->fetch_assoc()) {
                            $tokoNames[] = $t['nama_toko'];
                        }
                    }
                }

            $data[] = [
                'id'        => $row['id'],
                'username'  => $row['username'] ?? 'No Name',
                'toko_text' => !empty($tokoNames) ? implode(', ', $tokoNames) : '-',
                'created_at'=> isset($row['created_at']) ? date('d M Y', strtotime($row['created_at'])) : '-'
            ];
        }
        echo json_encode($data);
        exit;
        
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
