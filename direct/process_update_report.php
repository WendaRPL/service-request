<?php 
require_once 'config.php'; 

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') { 
    $requestId = $_POST['id'] ?? null;
    $ketidaksesuaian = $_POST['ketidaksesuaian'] ?? '';

    if ($requestId) { 
        $stmt = $conn->prepare("UPDATE transaksi_request SET ketidaksesuaian_detail = ? WHERE id = ?");
        $stmt->bind_param("si", $ketidaksesuaian, $requestId);

        if ($stmt->execute()) { 
            echo json_encode(['success' => true]);
        } else { 
            echo json_encode(['success' => false, 'message' => $conn->error]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'ID tidak ditemukan']);
    }
    exit;
}