<?php 
require_once __DIR__ . "/../direct/config.php";
// functions.php
function isActivePage($page_name) {
    $current_page = basename($_SERVER['PHP_SELF']);
    return ($current_page == $page_name) ? 'active' : '';
}

// Atau versi yang lebih advanced untuk handle subpages
function isActiveMenu($menu_pages) {
    $current_page = basename($_SERVER['PHP_SELF']);
    return in_array($current_page, $menu_pages) ? 'active' : '';
}
// Ambil nama file saat ini
$current_page = basename($_SERVER['PHP_SELF']);

// Tentukan title berdasarkan halaman dengan pattern matching
function getPageTitle($page) {
    $titles = [
        'home.php' => 'HOME',
        'done_cancelled.php' => 'DONE & CANCELLED',
        'reporting.php' => 'REPORTING', // Untuk semua halaman report*
        'master_data.php' => 'MASTER DATA', // Untuk semua halaman master*
        'user_management.php' => 'USER MANAGEMENT', // Untuk semua halaman user*
    ];
    
    // Cek exact match dulu
    if (isset($titles[$page])) {
        return $titles[$page];
    }
    
    // Cek partial match
    foreach ($titles as $key => $title) {
        if (strpos($page, $key) !== false) {
            return $title;
        }
    }
    
    return 'HOME';
}

$page_title = getPageTitle($current_page);
?>
?>