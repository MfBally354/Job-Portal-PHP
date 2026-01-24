<?php
// ==========================================
// LOGOUT.PHP
// auth/logout.php
// ==========================================
session_start();

// Log activity before destroying session
if(isset($_SESSION['user_id'])) {
    require_once '../config/database.php';
    require_once '../config/functions.php';
    
    $user_id = $_SESSION['user_id'];
    log_activity($user_id, 'logout', 'User logged out');
}

// Destroy session
session_destroy();

// Delete remember me cookie
if(isset($_COOKIE['remember_user'])) {
    setcookie('remember_user', '', time() - 3600, '/');
}

// Redirect to home
header("Location: ../index.php");
exit();
?>
