<?php
require_once __DIR__ . '/config.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    exit('Unauthorized');
}

$updatedBy = $_SESSION['user_id'];
$tab = $_POST['tab'] ?? '';
$id  = intval($_POST['id'] ?? 0);
$keys = $_POST['keys'] ?? [];

if (!$tab || !$id) {
    http_response_code(400);
    exit('Invalid request');
}

switch ($tab) {

    case 'toko':
        $stmt = $conn->prepare("UPDATE master_toko SET nama_toko=?, kode_toko=? WHERE id=?");
        $stmt->bind_param("ssi", $_POST['nama'], $_POST['kode'], $id);
        $stmt->execute();
        echo "OK";
        exit;

    case 'karyawan':
        $stmt = $conn->prepare("UPDATE master_karyawan SET username=?, user_toko=? WHERE id=?");
        $stmt->bind_param("sii", $_POST['nama'], $_POST['toko'], $id);
        $stmt->execute();
        echo "OK";
        exit;

    case 'jenis_kendala':
        $hasChild = ($_POST['turunan']==='Ya') ? 1 : 0;
        $stmt = $conn->prepare("UPDATE master_jenis_kendala SET tipe_kendala=?, nama_kendala=?, has_child=? WHERE id=?");
        $stmt->bind_param("ssii", $_POST['tipe'], $_POST['jenis'], $hasChild, $id);
        $stmt->execute();
        echo "OK";
        exit;

case 'role':
$roleName = trim($_POST['nama'] ?? '');
        if ($roleName === '') {
            http_response_code(400);
            exit('Role name required');
        }

        $conn->begin_transaction();
        try {
            // 1. Update nama role
            $stmtRole = $conn->prepare("UPDATE role SET role_name=? WHERE id=?");
            $stmtRole->bind_param("si", $roleName, $id);
            $stmtRole->execute();

            // 2. Bersihkan permission lama
            $stmtClear = $conn->prepare("DELETE FROM transaksi_user_role WHERE role_id=?");
            $stmtClear->bind_param("i", $id);
            $stmtClear->execute();

            // 3. Prepare Insert
            $stmtBool = $conn->prepare("INSERT INTO transaksi_user_role (role_id, role_key_name, key_value_bool) VALUES (?,?,?)");
            $stmtString = $conn->prepare("INSERT INTO transaksi_user_role (role_id, role_key_name, key_value_string) VALUES (?,?,?)");

            // 4. Simpan Expiration (String)
            $exp = $keys['expiration_date'] ?? 'forever';
            $stmtString->bind_param("iss", $id, $k='expiration_date', $v=$exp);
            $stmtString->execute();

            // 5. Simpan Boolean Keys
            $boolKeys = ['enable','update_toko','update_role','handling_request'];
            foreach($boolKeys as $key){
                // Pastikan nilainya 1 atau 0
                $val = (isset($keys[$key]) && ($keys[$key] == 1 || $keys[$key] == '1')) ? 1 : 0;
                $stmtBool->bind_param("isi", $id, $key, $val);
                $stmtBool->execute();
            }

            // 6. Simpan String Keys (Toko)
            // KUNCI: JS sudah ngirim string "TKO01,TKO02" atau "all" atau "none"
            // Jadi PHP TIDAK PERLU lagi query SELECT kode_toko ke database.
            $stringKeys = ['manage_user','update_user_toko','input_request','cancel_request','update_request','delete_request'];
            foreach($stringKeys as $key){
                $val = trim($keys[$key] ?? 'none');
                
                // Jika kosong, jadikan none
                if($val === '') $val = 'none';

                $stmtString->bind_param("iss", $id, $key, $val);
                $stmtString->execute();
            }

            $conn->commit();
            echo "OK";

        } catch(Throwable $e){
            $conn->rollback();
            http_response_code(500);
            echo "Error: " . $e->getMessage();
        }
        exit;
}