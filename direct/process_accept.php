<?php
require_once __DIR__ . '/config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['request_ids'])) {
    // Decode data dari JS
    $requestIds = json_decode($_POST['request_ids']);
    $action = $_POST['action'] ?? '';
    $staffId = $_SESSION['user_id'] ?? 1; // ID Staff yang sedang login
    $datetime = date('Y-m-d H:i:s');

    if (empty($requestIds)) {
        echo json_encode(['success' => false, 'message' => 'Tidak ada ID yang dipilih']);
        exit;
    }

    $conn->begin_transaction();

    try {
        if ($action === 'accept') {
            $statusOnProcess = 2; // ID 'On Process'

            foreach ($requestIds as $id) {
                // PERBAIKAN: Gunakan nama kolom 'status', bukan 'id_status'
                $stmt1 = $conn->prepare("UPDATE transaksi_request SET handling_by = ?, status = ? WHERE id = ?");
                $stmt1->bind_param("iii", $staffId, $statusOnProcess, $id);
                $stmt1->execute();

                // 2. INSERT ke history/log
                // Pastikan kolom di tabel transaksi_status sudah benar (current_status, dll)
                $stmt2 = $conn->prepare("INSERT INTO transaksi_status (request_id, current_status, status_changer, change_datetime) VALUES (?, ?, ?, ?)");
                $stmt2->bind_param("iiis", $id, $statusOnProcess, $staffId, $datetime);
                $stmt2->execute();
            }       
        }
        elseif ($action === 'change_urgency') {
            // --- LOGIKA GANTI URGENSI ---
            $newUrgency = $_POST['urgency_level'] ?? 'Medium';

            foreach ($requestIds as $id) {
                $stmt3 = $conn->prepare("UPDATE transaksi_request SET level_urgensi = ? WHERE id = ?");
                $stmt3->bind_param("si", $newUrgency, $id);
                $stmt3->execute();
            }
        }

        $conn->commit();

        $pesan = 'Request berhasil diproses';  
        if ($action === 'accept') {
            $pesan = 'Berhasil: Permintaan layanan telah diterima dan status diperbarui menjadi On Process.';
        } elseif ($action === 'change_urgency') {
            $pesan = 'Berhasil: Level urgensi telah diperbarui.';
        }

        echo json_encode([
            'success' => true,
            'message' => $pesan
        ]);

    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}