<?php
require_once __DIR__ . '/config.php'; 

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Sesi berakhir, silakan login kembali.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';
    $data = json_decode($_POST['data'], true);
    
    if (!$data) {
        echo json_encode(['success' => false, 'message' => 'Data tidak valid!']);
        exit;
    }

    $staffId = $_SESSION['user_id']; 
    $requestId = $data['request_id'];
    $tindakan = $data['tindakan'];
    $hasil = $data['hasil'];
    $tipe = $data['tipe']; 
    $jenis = $data['jenis'];
    $kodeHardware = $data['kodeHardware'] ?? '';
    $penerimaId = $data['penerima_id']; 
    $statusNama = $data['status_baru']; 
    $ketidaksesuaian = $data['ketidaksesuaian_detail']; 
    
    // AMBIL DATA TERJADI PADA DARI JSON
    $terjadiPada = $data['terjadi_pada'] ?? ''; 
    
    // PENENTUAN STATUS ID
    if ($statusNama === 'done') {
        $statusId = 4;
    } elseif ($statusNama === 'canceled') {
        $statusId = 5;
    } else {
        $statusId = 3; 
    }

    // 1. Validasi Password USER PENERIMA
    $stmtUser = $conn->prepare("SELECT password, username FROM users WHERE id = ?");
    $stmtUser->bind_param("i", $penerimaId);
    $stmtUser->execute();
    $resUser = $stmtUser->get_result()->fetch_assoc();

    if (!$resUser) {
        echo json_encode(['success' => false, 'message' => 'User penerima tidak ditemukan!']);
        exit;
    }

    if (!password_verify($password, $resUser['password'])) {
        echo json_encode(['success' => false, 'message' => 'Password Konfirmasi User ' . $resUser['username'] . ' Salah!']);
        exit;
    }

    $conn->begin_transaction();
    try {
        // 2. Update Database transaksi_request (DITAMBAHKAN kolom terjadi_pada)
        $sql = "UPDATE transaksi_request 
                SET status = ?, 
                    tindakan_it = ?, 
                    hasil_it = ?, 
                    tipe_kendala = ?, 
                    jenis_kendala = ?, 
                    kode_hardware = ?, 
                    user_penerima = ?, 
                    ketidaksesuaian_detail = ?,
                    terjadi_pada = ?
                WHERE id = ?";
        
        $stmtUpdate = $conn->prepare($sql);
        // Bind Param disesuaikan (tambah satu "s" untuk terjadi_pada dan variabel $terjadiPada)
        $stmtUpdate->bind_param("isssssissi", 
            $statusId, 
            $tindakan, 
            $hasil, 
            $tipe, 
            $jenis, 
            $kodeHardware, 
            $penerimaId, 
            $ketidaksesuaian, 
            $terjadiPada, 
            $requestId
        );
        $stmtUpdate->execute();

        // 3. Catat di history (transaksi_status)
        $stmtLog = $conn->prepare("INSERT INTO transaksi_status (request_id, current_status, status_changer, change_datetime) VALUES (?, ?, ?, NOW())");
        $stmtLog->bind_param("iii", $requestId, $statusId, $staffId);
        $stmtLog->execute();

        $conn->commit();
        echo json_encode(['success' => true, 'message' => 'Status berhasil diperbarui menjadi ' . $statusNama]);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}