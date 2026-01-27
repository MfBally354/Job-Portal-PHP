<?php
// ==========================================
// FUNCTIONS.PHP - Fungsi Helper Lengkap
// ==========================================

// Sanitize input
function sanitize_input($data) {
    global $conn;
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return mysqli_real_escape_string($conn, $data);
}

// Check if user is logged in
function check_login() {
    if(!isset($_SESSION['user_id'])) {
        header("Location: " . BASE_URL . "auth/login.php");
        exit();
    }
}

// Check user role
function check_role($required_role) {
    check_login();
    if($_SESSION['role'] != $required_role) {
        header("Location: " . BASE_URL . "index.php");
        exit();
    }
}

// Check if user is admin
function is_admin() {
    return isset($_SESSION['role']) && $_SESSION['role'] == 'admin';
}

// Check if user is employer
function is_employer() {
    return isset($_SESSION['role']) && $_SESSION['role'] == 'employer';
}

// Check if user is jobseeker
function is_jobseeker() {
    return isset($_SESSION['role']) && $_SESSION['role'] == 'jobseeker';
}

// Time ago function
function time_ago($datetime) {
    $timestamp = strtotime($datetime);
    $difference = time() - $timestamp;
    
    $periods = array(
        'tahun' => 31536000,
        'bulan' => 2592000,
        'minggu' => 604800,
        'hari' => 86400,
        'jam' => 3600,
        'menit' => 60,
        'detik' => 1
    );
    
    foreach($periods as $name => $seconds) {
        $num = floor($difference / $seconds);
        if($num > 0) {
            return $num . ' ' . $name . ' yang lalu';
        }
    }
    return 'Baru saja';
}

// Format currency
function format_currency($amount) {
    return 'Rp ' . number_format($amount, 0, ',', '.');
}

// Generate slug
function generate_slug($string) {
    $string = strtolower(trim($string));
    $string = preg_replace('/[^a-z0-9-]/', '-', $string);
    $string = preg_replace('/-+/', '-', $string);
    return trim($string, '-');
}

// Upload file function
function upload_file($file, $target_dir, $allowed_extensions = ['pdf', 'doc', 'docx'], $max_size = 5242880) {
    if($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => 'Error uploading file'];
    }
    
    $file_name = $file['name'];
    $file_size = $file['size'];
    $file_tmp = $file['tmp_name'];
    $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
    
    if(!in_array($file_ext, $allowed_extensions)) {
        return ['success' => false, 'message' => 'File type not allowed'];
    }
    
    if($file_size > $max_size) {
        return ['success' => false, 'message' => 'File size exceeds limit'];
    }
    
    $new_file_name = uniqid() . '_' . time() . '.' . $file_ext;
    $target_file = $target_dir . $new_file_name;
    
    if(move_uploaded_file($file_tmp, $target_file)) {
        return ['success' => true, 'file_name' => $new_file_name];
    }
    
    return ['success' => false, 'message' => 'Failed to move uploaded file'];
}

// Upload image function
function upload_image($file, $target_dir, $max_size = 2097152) {
    if($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => 'Error uploading image'];
    }
    
    $allowed = ['jpg', 'jpeg', 'png', 'gif'];
    $file_name = $file['name'];
    $file_size = $file['size'];
    $file_tmp = $file['tmp_name'];
    $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
    
    if(!in_array($file_ext, $allowed)) {
        return ['success' => false, 'message' => 'Image type not allowed'];
    }
    
    if($file_size > $max_size) {
        return ['success' => false, 'message' => 'Image size exceeds limit (max 2MB)'];
    }
    
    // Resize image if needed
    list($width, $height) = getimagesize($file_tmp);
    
    $new_file_name = uniqid() . '_' . time() . '.' . $file_ext;
    $target_file = $target_dir . $new_file_name;
    
    if(move_uploaded_file($file_tmp, $target_file)) {
        return ['success' => true, 'file_name' => $new_file_name];
    }
    
    return ['success' => false, 'message' => 'Failed to upload image'];
}

// Send notification
function send_notification($user_id, $title, $message, $type = 'info', $link = null) {
    global $conn;
    $title = sanitize_input($title);
    $message = sanitize_input($message);
    $type = sanitize_input($type);
    $link = $link ? sanitize_input($link) : null;
    
    $query = "INSERT INTO notifications (user_id, title, message, type, link) 
              VALUES ('$user_id', '$title', '$message', '$type', " . ($link ? "'$link'" : "NULL") . ")";
    
    return mysqli_query($conn, $query);
}

// Get unread notifications count
function get_unread_notifications_count($user_id) {
    global $conn;
    $query = "SELECT COUNT(*) as count FROM notifications WHERE user_id = $user_id AND is_read = 0";
    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_assoc($result);
    return $row['count'];
}

// Check if job is saved
function is_job_saved($user_id, $job_id) {
    global $conn;
    $query = "SELECT id FROM saved_jobs WHERE user_id = $user_id AND job_id = $job_id";
    $result = mysqli_query($conn, $query);
    return mysqli_num_rows($result) > 0;
}

// Check if already applied
function has_applied($user_id, $job_id) {
    global $conn;
    $query = "SELECT id FROM applications WHERE user_id = $user_id AND job_id = $job_id";
    $result = mysqli_query($conn, $query);
    return mysqli_num_rows($result) > 0;
}

// Increment job views
function increment_job_views($job_id, $user_id = null, $ip_address = null) {
    global $conn;
    
    // Update views count
    mysqli_query($conn, "UPDATE jobs SET views = views + 1 WHERE id = $job_id");
    
    // Log view
    $user_id_sql = $user_id ? $user_id : 'NULL';
    $ip_sql = $ip_address ? "'$ip_address'" : 'NULL';
    
    mysqli_query($conn, "INSERT INTO job_views (job_id, user_id, ip_address) 
                         VALUES ($job_id, $user_id_sql, $ip_sql)");
}

// Get job type badge class
function get_job_type_badge($type) {
    $badges = [
        'full-time' => 'success',
        'part-time' => 'info',
        'contract' => 'warning',
        'internship' => 'primary',
        'freelance' => 'secondary'
    ];
    return $badges[$type] ?? 'secondary';
}

// Get application status badge
function get_status_badge($status) {
    $badges = [
        'pending' => 'warning',
        'reviewed' => 'info',
        'shortlisted' => 'primary',
        'interview' => 'info',
        'offered' => 'success',
        'rejected' => 'danger',
        'withdrawn' => 'secondary'
    ];
    return $badges[$status] ?? 'secondary';
}

// Pagination function
function paginate($query, $per_page = 10) {
    global $conn;
    
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $page = max(1, $page);
    $offset = ($page - 1) * $per_page;
    
    // Get total records
    $count_query = "SELECT COUNT(*) as total FROM ($query) as count_table";
    $count_result = mysqli_query($conn, $count_query);
    $count_row = mysqli_fetch_assoc($count_result);
    $total_records = $count_row['total'];
    $total_pages = ceil($total_records / $per_page);
    
    // Add limit to query
    $query .= " LIMIT $offset, $per_page";
    $result = mysqli_query($conn, $query);
    
    return [
        'result' => $result,
        'current_page' => $page,
        'total_pages' => $total_pages,
        'total_records' => $total_records,
        'per_page' => $per_page
    ];
}

/**
 * Function paginate dengan support JOIN
 * Mengatasi duplicate column dengan SELECT spesifik columns
 */
function paginate_with_join($select_columns, $from_clause, $where = '', $order = 'id DESC', $per_page = 10) {
    global $conn;
    
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $offset = ($page - 1) * $per_page;
    
    // Build the base query
    $base_query = "FROM $from_clause";
    if (!empty($where)) {
        $base_query .= " WHERE $where";
    }
    
    // Get total records
    $count_query = "SELECT COUNT(DISTINCT " . explode(',', $select_columns)[0] . ") as total $base_query";
    $count_result = mysqli_query($conn, $count_query);
    
    if (!$count_result) {
        error_log("Paginate count query error: " . mysqli_error($conn));
        return false;
    }
    
    $count_row = mysqli_fetch_assoc($count_result);
    $total = $count_row['total'];
    $total_pages = ceil($total / $per_page);
    
    // Get paginated data dengan kolom spesifik
    $data_query = "SELECT $select_columns $base_query ORDER BY $order LIMIT $per_page OFFSET $offset";
    $data_result = mysqli_query($conn, $data_query);
    
    if (!$data_result) {
        error_log("Paginate data query error: " . mysqli_error($conn));
        error_log("Query: $data_query");
        return false;
    }
    
    $data = [];
    while ($row = mysqli_fetch_assoc($data_result)) {
        $data[] = $row;
    }
    
    return [
        'data' => $data,
        'pagination' => [
            'current_page' => $page,
            'total_pages' => $total_pages,
            'total_records' => $total,
            'per_page' => $per_page,
            'has_prev' => $page > 1,
            'has_next' => $page < $total_pages
        ]
    ];
}

// Render pagination
function render_pagination($current_page, $total_pages, $base_url) {
    if($total_pages <= 1) return '';
    
    $html = '<nav><ul class="pagination justify-content-center">';
    
    // Previous
    if($current_page > 1) {
        $html .= '<li class="page-item"><a class="page-link" href="' . $base_url . '&page=' . ($current_page - 1) . '">Previous</a></li>';
    }
    
    // Page numbers
    $start = max(1, $current_page - 2);
    $end = min($total_pages, $current_page + 2);
    
    if($start > 1) {
        $html .= '<li class="page-item"><a class="page-link" href="' . $base_url . '&page=1">1</a></li>';
        if($start > 2) $html .= '<li class="page-item disabled"><span class="page-link">...</span></li>';
    }
    
    for($i = $start; $i <= $end; $i++) {
        $active = $i == $current_page ? 'active' : '';
        $html .= '<li class="page-item ' . $active . '"><a class="page-link" href="' . $base_url . '&page=' . $i . '">' . $i . '</a></li>';
    }
    
    if($end < $total_pages) {
        if($end < $total_pages - 1) $html .= '<li class="page-item disabled"><span class="page-link">...</span></li>';
        $html .= '<li class="page-item"><a class="page-link" href="' . $base_url . '&page=' . $total_pages . '">' . $total_pages . '</a></li>';
    }
    
    // Next
    if($current_page < $total_pages) {
        $html .= '<li class="page-item"><a class="page-link" href="' . $base_url . '&page=' . ($current_page + 1) . '">Next</a></li>';
    }
    
    $html .= '</ul></nav>';
    return $html;
}

// Validate email
function validate_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Validate phone
function validate_phone($phone) {
    return preg_match('/^[0-9]{10,15}$/', $phone);
}

// Generate random password
function generate_password($length = 12) {
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*';
    return substr(str_shuffle($chars), 0, $length);
}

// Log activity
function log_activity($user_id, $action, $description = null) {
    global $conn;
    $action = sanitize_input($action);
    $description = $description ? sanitize_input($description) : null;
    
    $query = "INSERT INTO activity_logs (user_id, action, description) 
              VALUES ($user_id, '$action', " . ($description ? "'$description'" : "NULL") . ")";
    
    return mysqli_query($conn, $query);
}
?>
