<?php
ob_start();
require_once __DIR__ . '/config.php';  
header('Content-Type: application/json');

$search = $_GET['q'] ?? '';
$results = [];

try {
    if (!empty($search)) {   
        $sql = "SELECT id, username FROM users WHERE role != 1 AND username LIKE ? LIMIT 10";   
        $stmt = $conn->prepare($sql);
        
        if (!$stmt) {
            throw new Exception("Gagal prepare: " . $conn->error);
        }

        $term = "%" . $search . "%";
        $stmt->bind_param("s", $term);
        $stmt->execute();
        $res = $stmt->get_result();

        while ($row = $res->fetch_assoc()) {
            $results[] = [
                "id" => $row['id'],
                "username" => $row['username']
            ];
        }
    }
    
    ob_clean();
    echo json_encode($results);

} catch (Exception $e) {
    ob_clean();
    http_response_code(500); // Kasih tau JS kalau ada error server
    echo json_encode(["error" => $e->getMessage()]);
}
exit;