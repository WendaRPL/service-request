<?php
// Pastikan tidak ada spasi atau baris kosong sebelum tag PHP ini
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "config.php"; 

// Matikan display errors agar tidak merusak JSON
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Bersihkan buffer untuk membuang Notice/Warning yang mungkin muncul di config.php
if (ob_get_length()) ob_clean(); 

header("Content-Type: application/json");

$userId = $_SESSION["user_id"] ?? 0;
if (!$userId) {
    echo json_encode(["status" => false, "message" => "Not logged in"]);
    exit;
}

// Tentukan path folder dari root project
// Sesuaikan "service-request" dengan nama folder folder project kamu di Wamp
$projectSubFolder = "/service-request"; 
$avatarFolder = "/uploads/user/avatar/";
$fullUrlPath = $projectSubFolder . $avatarFolder;

// 1. Cek Database
$sql = "SELECT username, profile_pic FROM users WHERE id = ?"; 
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

$userData = [
    "username" => "User",
    "profile_pic" => null
];

if ($row = $result->fetch_assoc()) {
    $userData["username"] = $row['username'];
    
    if (!empty($row['profile_pic'])) {
        $fileName = $row['profile_pic'];

        // Cek apakah di DB tersimpan full path atau cuma nama file
        $relativePath = (strpos($fileName, '/uploads/') !== false) ? $fileName : $fullUrlPath . $fileName;
        
        // Gabungkan DOCUMENT_ROOT dengan path relatif untuk cek fisik
        // Hasilnya: C:/wamp64/www/service-request/uploads/user/avatar/file.jpg
        $physicalPath = $_SERVER['DOCUMENT_ROOT'] . $relativePath;
        
        // Ganti backslash jadi forward slash (Windows compatibility)
        $physicalPath = str_replace('\\', '/', $physicalPath);

        if (file_exists($physicalPath)) {
            $userData["profile_pic"] = $relativePath;
            $_SESSION["profile_pic"] = $relativePath;
        } else {
            // Jika file fisik tidak ada, biarkan null
            $userData["profile_pic"] = null;
        }
    }
}

$stmt->close();

echo json_encode([
    "status" => true,
    "data" => $userData,
    "source" => "database_checked"
]);
exit;