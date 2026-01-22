<?php
ob_start();
require_once __DIR__ . '/config.php';
header('Content-Type: application/json');

try {
    // Query untuk User Management: Ambil SEMUA data tanpa filter pengecualian
    $sql = "
        SELECT 
            u.id, 
            u.username, 
            u.created_at, 
            u.role AS role_id, 
            r.role_name
        FROM users u
        LEFT JOIN role r ON u.role = r.id
        ORDER BY u.id ASC
    ";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception("Gagal prepare query: " . $conn->error);
    }

    $stmt->execute();
    $res = $stmt->get_result();

    $results = [];
    while ($row = $res->fetch_assoc()) {
        $results[] = [
            'id'        => $row['id'],
            'username'  => $row['username'],
            'role_id'   => $row['role_id'],
            'role_name' => $row['role_name'] ?? 'No Role',
            'created'   => isset($row['created_at']) ? date('d M Y H:i', strtotime($row['created_at'])) : '-'
        ];
    }

    ob_clean();
    echo json_encode($results);

} catch (Exception $e) {
    ob_clean();
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ]);
}
exit;