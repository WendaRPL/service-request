<?php
require_once __DIR__ . '/config.php';

/* =========================
    AUTH
========================= */
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    exit('Unauthorized');
}

$createdBy = $_SESSION['user_id'];
$tab = $_POST['tab'] ?? '';

/* =========================
    MASTER TAB
========================= */
switch ($tab) {

    case 'toko':
        $stmt = $conn->prepare("
            INSERT INTO master_toko (nama_toko, kode_toko, created_at, created_by)
            VALUES (?, ?, NOW(), ?)
        ");
        $stmt->bind_param("ssi", $_POST['nama'], $_POST['kode'], $createdBy);
        $stmt->execute();
        echo "OK";
        exit;

    case 'karyawan':
        $stmt = $conn->prepare("
            INSERT INTO master_karyawan (username, user_toko, created_at, created_by)
            VALUES (?, ?, NOW(), ?)
        ");
        $stmt->bind_param("sii", $_POST['nama'], $_POST['toko'], $createdBy);
        $stmt->execute();
        echo "OK";
        exit;

    case 'jenis_kendala':
        $hasChild = ($_POST['turunan'] === 'Ya') ? 1 : 0;
        $stmt = $conn->prepare("
            INSERT INTO master_jenis_kendala
            (tipe_kendala, nama_kendala, has_child, created_at, created_by)
            VALUES (?, ?, ?, NOW(), ?)
        ");
        $stmt->bind_param("ssii", $_POST['tipe'], $_POST['jenis'], $hasChild, $createdBy);
        $stmt->execute();
        echo "OK";
        exit;

    case 'role':
        $roleName = trim($_POST['nama'] ?? '');
        $keys     = $_POST['keys'] ?? [];

        if ($roleName === '') {
            http_response_code(400);
            exit('Role name required');
        }

        $conn->begin_transaction();
        try {
            // 1. INSERT ROLE BARU
            $stmtRole = $conn->prepare("INSERT INTO role (role_name, created_at, created_by) VALUES (?, NOW(), ?)");
            $stmtRole->bind_param("si", $roleName, $createdBy);
            $stmtRole->execute();
            $roleId = $conn->insert_id;

            // 2. PREPARE STMT UNTUK KEYS
            $stmtBool = $conn->prepare("INSERT INTO transaksi_user_role (role_id, role_key_name, key_value_bool) VALUES (?, ?, ?)");
            $stmtString = $conn->prepare("INSERT INTO transaksi_user_role (role_id, role_key_name, key_value_string) VALUES (?, ?, ?)");

            // 3. SIMPAN EXPIRATION
            $expVal = $keys['expiration_date'] ?? 'forever';
            $expKey = 'expiration_date';
            $stmtString->bind_param("iss", $roleId, $expKey, $expVal);
            $stmtString->execute();

            // 4. SIMPAN BOOLEAN KEYS (enable, update_toko, dll)
            $boolKeysList = ['enable', 'update_toko', 'update_role', 'handling_request'];
            foreach ($boolKeysList as $k) {
                $val = (isset($keys[$k]) && ($keys[$k] == 1 || $keys[$k] == '1')) ? 1 : 0;
                $stmtBool->bind_param("isi", $roleId, $k, $val);
                $stmtBool->execute();
            }

            // 5. SIMPAN STRING KEYS (manage_user, dll)
            // KUNCI: Karena JS sudah ngirim "HK,RJ", kita tinggal terima bersih.
            $stringKeysList = ['manage_user','update_user_toko','input_request','cancel_request','update_request','delete_request'];
            foreach ($stringKeysList as $k) {
                $finalVal = trim($keys[$k] ?? 'none');
                
                if ($finalVal === '') {
                    $finalVal = 'none';
                }

                $stmtString->bind_param("iss", $roleId, $k, $finalVal);
                $stmtString->execute();
            }

            $conn->commit();
            echo "OK";

        } catch (Throwable $e) {
            $conn->rollback();
            http_response_code(500);
            echo "Error: " . $e->getMessage();
        }
        exit;
}