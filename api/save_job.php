<?php
// ==========================================
// SAVE_JOB.PHP API
// api/save_job.php
// ===========================================
session_start();
require_once '../config/database.php';
require_once '../config/functions.php';

header('Content-Type: application/json');

// Check if user is logged in
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'jobseeker') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$user_id = $_SESSION['user_id'];

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);

if(!isset($data['job_id']) || !isset($data['action'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit();
}

$job_id = (int)$data['job_id'];
$action = $data['action'];

// Verify job exists
$job_check = mysqli_query($conn, "SELECT id FROM jobs WHERE id = $job_id AND status = 'active'");
if(mysqli_num_rows($job_check) == 0) {
    echo json_encode(['success' => false, 'message' => 'Job not found']);
    exit();
}

if($action == 'save') {
    // Check if already saved
    $check = mysqli_query($conn, "SELECT id FROM saved_jobs WHERE user_id = $user_id AND job_id = $job_id");
    
    if(mysqli_num_rows($check) > 0) {
        echo json_encode(['success' => false, 'message' => 'Job already saved']);
        exit();
    }
    
    // Save job
    $query = "INSERT INTO saved_jobs (user_id, job_id) VALUES ($user_id, $job_id)";
    
    if(mysqli_query($conn, $query)) {
        // Get job title for notification
        $job_data = mysqli_fetch_assoc(mysqli_query($conn, "SELECT title FROM jobs WHERE id = $job_id"));
        
        send_notification(
            $user_id,
            'Job Saved',
            'You saved: ' . $job_data['title'],
            'info',
            '../jobseeker/saved_jobs.php'
        );
        
        echo json_encode(['success' => true, 'message' => 'Job saved successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to save job']);
    }
    
} elseif($action == 'unsave') {
    // Remove from saved jobs
    $query = "DELETE FROM saved_jobs WHERE user_id = $user_id AND job_id = $job_id";
    
    if(mysqli_query($conn, $query)) {
        echo json_encode(['success' => true, 'message' => 'Job removed from saved']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to remove job']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid action']);
}
?>
