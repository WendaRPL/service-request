<?php
require_once __DIR__ . '/config.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'message' => 'Sesi berakhir.']);
        exit;
    }

    $requestId = $_POST['request_id'] ?? null;
    $myId = $_SESSION['user_id'];
    $statusCanceled = 5; // SESUAIKAN: Jika di tabel Anda Canceled = 5
    $datetime = date('Y-m-d H:i:s');

    $conn->begin_transaction();
    try {
        // Cek apakah data benar milik user dan status masih Open (1)
        $check = $conn->prepare("SELECT id FROM transaksi_request WHERE id = ? AND user_request = ? AND status = 1");
        $check->bind_param("ii", $requestId, $myId);
        $check->execute();
        if ($check->get_result()->num_rows === 0) {
            throw new Exception("Batal gagal: Tiket tidak ditemukan atau sudah diproses IT.");
        }

        // Update status ke Canceled (5)
        $upd = $conn->prepare("UPDATE transaksi_request SET status = ? WHERE id = ?");
        $upd->bind_param("ii", $statusCanceled, $requestId);
        $upd->execute();

        // Log ke history status
        $log = $conn->prepare("INSERT INTO transaksi_status (request_id, current_status, status_changer, change_datetime) VALUES (?, ?, ?, ?)");
        $log->bind_param("iiis", $requestId, $statusCanceled, $myId, $datetime);
        $log->execute();

        $conn->commit();
        echo json_encode(['success' => true, 'message' => 'Request berhasil dibatalkan.']);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}