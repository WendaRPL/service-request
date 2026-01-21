<?php
require_once __DIR__ . '/config.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'message' => 'Sesi berakhir, silakan login kembali.']);
        exit;
    }

    $userId = $_SESSION['user_id'];
    
    // 1. Ambil Multi-Toko
    $tokoIds = $_POST['toko_id'] ?? [];
    if (empty($tokoIds)) {
        echo json_encode(['success' => false, 'message' => 'Wajib memilih minimal satu toko.']);
        exit;
    }
    // Gabung ID toko jadi string "1,2,3"
    $tokoString = is_array($tokoIds) ? implode(',', $tokoIds) : (string)$tokoIds;

    // 2. Ambil Input dengan Fallback
    $tipeKendala   = $_POST['tipe_kendala'] ?? '-';
    $jenisKendala  = $_POST['jenis_kendala'] ?? '-';
    $kodeHardware  = $_POST['kode_hardware'] ?? '-';
    $terjadiPada   = $_POST['terjadi_pada'] ?? '-'; 
    $deskripsi     = $_POST['deskripsi'] ?? '-';
    $inputDatetime = date('Y-m-d H:i:s');
    
    // 3. User Penerima
    $untukSiapa    = $_POST['untuk_siapa'] ?? 'diri_sendiri';
    // Gunakan ID user_penerima yang dikirim dari JS
    $userPenerima  = ($untukSiapa === 'orang_lain') ? ($_POST['user_penerima'] ?: $userId) : $userId;

    // 4. Urgensi & Status
    $levelUrgensi = ($terjadiPada === 'semua') ? 'High' : (($terjadiPada === 'beberapa') ? 'Medium' : 'Low');
    $statusOpen = 1; 

    // 5. File Upload  
    $uploadName = '';
    if (isset($_FILES['file_upload']) && $_FILES['file_upload']['error'] === 0) {
        $targetDir = "uploads/";
        // Buat folder jika belum ada
        if (!file_exists($targetDir)) { mkdir($targetDir, 0777, true); }

        $fileExtension = pathinfo($_FILES['file_upload']['name'], PATHINFO_EXTENSION);
        // Rename file agar unik untuk menghindari duplikasi
        $uploadName = time() . '_' . uniqid() . '.' . $fileExtension;
        $targetFilePath = $targetDir . $uploadName;

        move_uploaded_file($_FILES['file_upload']['tmp_name'], $targetFilePath);
    }
 

    $conn->begin_transaction();
    try {
        $sqlReq = "INSERT INTO transaksi_request (
            user_request, user_penerima, user_toko, input_datetime, 
            jenis_kendala, kode_hardware, tipe_kendala, status, 
            description, upload, level_urgensi, terjadi_pada
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmtReq = $conn->prepare($sqlReq);
        
        // PASTIKAN: "iisssssissss"
        // user_toko (tokoString) adalah parameter ke-3 (s)
        $stmtReq->bind_param("iisssssissss", 
            $userId, $userPenerima, $tokoString, $inputDatetime, 
            $jenisKendala, $kodeHardware, $tipeKendala, $statusOpen, 
            $deskripsi, $uploadName, $levelUrgensi, $terjadiPada
        );
        
        $stmtReq->execute();
        $newRequestId = $conn->insert_id;

        // Log History
        $sqlLog = "INSERT INTO transaksi_status (request_id, current_status, status_changer, change_datetime) VALUES (?, ?, ?, ?)";
        $stmtLog = $conn->prepare($sqlLog);
        $stmtLog->bind_param("iiis", $newRequestId, $statusOpen, $userId, $inputDatetime);
        $stmtLog->execute();

        $conn->commit();
        echo json_encode(['success' => true, 'message' => 'Request berhasil diajukan!']);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => 'Database Error: ' . $e->getMessage()]);
    }
}