<?php
require_once __DIR__ . '/config.php'; 
header('Content-Type: application/json');
date_default_timezone_set('Asia/Jakarta');
$inputDatetime = date('Y-m-d H:i:s');  

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Sesi berakhir, silakan login kembali.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = $_SESSION['user_id'];
    $tokoIds = $_POST['toko_id'] ?? [];
    $tokoString = is_array($tokoIds) ? implode(',', $tokoIds) : (string)$tokoIds;

    $tipeKendala   = $_POST['tipe_kendala'] ?? '-';
    $jenisKendala  = $_POST['jenis_kendala'] ?? '-';
    $kodeHardware  = $_POST['kode_hardware'] ?? '-';
    $terjadiPada   = $_POST['terjadi_pada'] ?? '-'; 
    $deskripsi     = $_POST['deskripsi'] ?? '-';
    $levelUrgensi  = $_POST['level_urgensi'] ?? 'Low';
    
    // Logika Auto Urgensi jika kosong
    if (empty($_POST['level_urgensi'])) {
        if ($terjadiPada === 'Semua Orang/Toko') $levelUrgensi = 'Very High';
        elseif ($terjadiPada === 'Beberapa Orang') $levelUrgensi = 'High';
        elseif ($terjadiPada === 'Diri Sendiri') $levelUrgensi = 'Medium';
    }
    
    $untukSiapa    = $_POST['untuk_siapa'] ?? 'diri_sendiri';
    $userPenerima  = ($untukSiapa === 'orang_lain') ? ($_POST['user_penerima'] ?: $userId) : $userId;
    $statusOpen    = 1; 

    // FIX: Inisialisasi awal agar tidak Undefined Variable
    $uploadName = ''; 

    if (isset($_FILES['upload_file']) && $_FILES['upload_file']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['upload_file']['tmp_name'];
        $fileName = $_FILES['upload_file']['name'];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'pdf', 'xlsx', 'xls', 'docx', 'doc', 'txt'];
        
        if (in_array($fileExtension, $allowedExtensions)) {
            $newFileName = time() . '_' . preg_replace("/[^a-zA-Z0-9.]/", "_", $fileName);
            $uploadFileDir = __DIR__ . '/../uploads/';
            if (!is_dir($uploadFileDir)) mkdir($uploadFileDir, 0777, true);
            if (move_uploaded_file($fileTmpPath, $uploadFileDir . $newFileName)) {
                $uploadName = $newFileName;
            }
        }
    }

    $conn->begin_transaction();
    try { 
        $sqlReq = "INSERT INTO transaksi_request (
            user_request, user_penerima, user_toko, input_datetime, 
            jenis_kendala, kode_hardware, tipe_kendala, status, 
            description, upload, level_urgensi, terjadi_pada
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmtReq = $conn->prepare($sqlReq); 
        $stmtReq->bind_param("iisssssissss", 
            $userId, $userPenerima, $tokoString, $inputDatetime, 
            $jenisKendala, $kodeHardware, $tipeKendala, $statusOpen, 
            $deskripsi, $uploadName, $levelUrgensi, $terjadiPada
        );
        $stmtReq->execute();
        
        $newRequestId = $conn->insert_id;
        $sqlLog = "INSERT INTO transaksi_status (request_id, current_status, status_changer, change_datetime) VALUES (?, ?, ?, ?)";
        $stmtLog = $conn->prepare($sqlLog); 
        $stmtLog->bind_param("iiis", $newRequestId, $statusOpen, $userId, $inputDatetime);
        $stmtLog->execute();

        $conn->commit();
        echo json_encode(['success' => true, 'message' => 'Request berhasil diajukan!']);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => 'DB Error: ' . $e->getMessage()]);
    }
}