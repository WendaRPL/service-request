<?php
require_once __DIR__ . '/config.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $requestId = $_POST['request_id'] ?? null;
    $targetStaffId = $_POST['target_staff_id'] ?? null;
    $myId = $_SESSION['user_id'] ?? 1;

    if (!$requestId || !$targetStaffId) {
        echo json_encode(['success' => false, 'message' => 'Data tidak lengkap']);
        exit;
    }

    $conn->begin_transaction();
    try {
        // Update staff pengelola
        $stmt = $conn->prepare("UPDATE transaksi_request SET handling_by = ? WHERE id = ?");
        $stmt->bind_param("ii", $targetStaffId, $requestId);
        $stmt->execute();

        // History transfer (status tetap 2 = On Process)
        $stmt2 = $conn->prepare("INSERT INTO transaksi_status (request_id, current_status, status_changer, change_datetime) VALUES (?, 2, ?, NOW())");
        $stmt2->bind_param("ii", $requestId, $myId);
        $stmt2->execute();

        $conn->commit();
        echo json_encode(['success' => true, 'message' => 'Request berhasil dipindah tugaskan.']);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}