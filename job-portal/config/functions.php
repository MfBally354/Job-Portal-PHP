<?php
function sanitize_input($data) {
    global $conn;
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return mysqli_real_escape_string($conn, $data);
}

function check_login() {
    if(!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }
}

function is_admin() {
    return isset($_SESSION['role']) && $_SESSION['role'] == 'admin';
}

function is_employer() {
    return isset($_SESSION['role']) && $_SESSION['role'] == 'employer';
}

function time_ago($datetime) {
    $timestamp = strtotime($datetime);
    $difference = time() - $timestamp;
    
    if($difference < 60) return 'Baru saja';
    if($difference < 3600) return floor($difference/60) . ' menit yang lalu';
    if($difference < 86400) return floor($difference/3600) . ' jam yang lalu';
    if($difference < 604800) return floor($difference/86400) . ' hari yang lalu';
    return date('d M Y', $timestamp);
}
?>
