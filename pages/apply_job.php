<?php
session_start();
require_once '../config/database.php';
require_once '../config/functions.php';

check_login();

if($_SESSION['role'] != 'jobseeker') {
    header("Location: ../index.php");
    exit();
}

if(!isset($_GET['id'])) {
    header("Location: jobs.php");
    exit();
}

$job_id = intval($_GET['id']);
$user_id = $_SESSION['user_id'];

// Cek apakah sudah melamar
$check = "SELECT * FROM applications WHERE job_id = $job_id AND user_id = $user_id";
$result = mysqli_query($conn, $check);
if(mysqli_num_rows($result) > 0) {
    header("Location: job_detail.php?id=$job_id");
    exit();
}

// Get job info
$job_query = "SELECT j.*, c.name as company_name FROM jobs j LEFT JOIN companies c ON j.company_id = c.id WHERE j.id = $job_id";
$job_result = mysqli_query($conn, $job_query);
if(mysqli_num_rows($job_result) == 0) {
    header("Location: jobs.php");
    exit();
}
$job = mysqli_fetch_assoc($job_result);

$error = '';
$success = '';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $cover_letter = sanitize_input($_POST['cover_letter']);
    
    // Upload CV
    $cv_path = '';
    if(isset($_FILES['cv']) && $_FILES['cv']['error'] == 0) {
        $allowed = ['pdf', 'doc', 'docx'];
        $filename = $_FILES['cv']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if(in_array($ext, $allowed)) {
            $new_filename = 'cv_' . $user_id . '_' . time() . '.' . $ext;
            $upload_path = '../assets/uploads/cv/' . $new_filename;
            
            if(move_uploaded_file($_FILES['cv']['tmp_name'], $upload_path)) {
                $cv_path = $new_filename;
            } else {
                $error = 'Gagal mengupload CV';
            }
        } else {
            $error = 'Format CV harus PDF, DOC, atau DOCX';
        }
    }
    
    if(empty($error)) {
        $query = "INSERT INTO applications (job_id, user_id, cv_path, cover_letter, status) 
                  VALUES ($job_id, $user_id, '$cv_path', '$cover_letter', 'pending')";
        
        if(mysqli_query($conn, $query)) {
            $success = 'Lamaran berhasil dikirim!';
            header("refresh:2;url=my_applications.php");
        } else {
            $error = 'Gagal mengirim lamaran: ' . mysqli_error($conn);
        }
    }
}

$page_title = 'Lamar Pekerjaan - JobPortal';
$css_path = '../assets/css/style.css';
$js_path = '../assets/js/main.js';
$base_url = '../';
include '../includes/header.php';
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow">
                <div class="card-body p-4">
                    <h3 class="mb-4">Lamar Pekerjaan</h3>
                    
                    <div class="alert alert-info">
                        <h5><?php echo htmlspecialchars($job['title']); ?></h5>
                        <p class="mb-0"><?php echo htmlspecialchars($job['company_name']); ?></p>
                    </div>
                    
                    <?php if($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    
                    <?php if($success): ?>
                        <div class="alert alert-success"><?php echo $success; ?></div>
                    <?php else: ?>
                    
                    <form method="POST" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label class="form-label">Upload CV (PDF/DOC/DOCX) <span class="text-danger">*</span></label>
                            <input type="file" class="form-control" name="cv" accept=".pdf,.doc,.docx" required>
                            <small class="text-muted">Maksimal 5MB</small>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Surat Lamaran</label>
                            <textarea class="form-control" name="cover_letter" rows="8" 
                                      placeholder="Ceritakan mengapa Anda cocok untuk posisi ini..."></textarea>
                        </div>
                        
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-paper-plane"></i> Kirim Lamaran
                            </button>
                            <a href="job_detail.php?id=<?php echo $job_id; ?>" class="btn btn-secondary">Batal</a>
                        </div>
                    </form>
                    
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
