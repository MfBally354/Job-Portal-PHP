<?php
session_start();
require_once '../config/database.php';
require_once '../config/functions.php';
require_once '../config/constants.php';

// Check if user is logged in as jobseeker
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'jobseeker') {
    header("Location: ../auth/login.php");
    exit();
}

if(!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: jobs.php");
    exit();
}

$job_id = (int)$_GET['id'];
$user_id = $_SESSION['user_id'];

// Get job details
$job_query = "SELECT j.*, c.name as company_name, c.logo 
              FROM jobs j
              LEFT JOIN companies c ON j.company_id = c.id
              WHERE j.id = $job_id AND j.status = 'active'";
$job_result = mysqli_query($conn, $job_query);

if(mysqli_num_rows($job_result) == 0) {
    header("Location: jobs.php");
    exit();
}

$job = mysqli_fetch_assoc($job_result);

// Check if already applied
if(has_applied($user_id, $job_id)) {
    $_SESSION['error'] = 'You have already applied for this job!';
    header("Location: job_detail.php?id=$job_id");
    exit();
}

// Get user profile
$profile_query = "SELECT * FROM jobseeker_profiles WHERE user_id = $user_id";
$profile_result = mysqli_query($conn, $profile_query);
$profile = mysqli_fetch_assoc($profile_result);

$error = '';
$success = '';

// Handle form submission
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $cover_letter = sanitize_input($_POST['cover_letter']);
    $cv_path = $profile['cv_path'] ?? '';
    
    // Handle CV upload if new file is uploaded
    if(isset($_FILES['cv']) && $_FILES['cv']['error'] == 0) {
        $upload_result = upload_file($_FILES['cv'], CV_PATH, ALLOWED_CV_EXT, MAX_CV_SIZE);
        
        if($upload_result['success']) {
            // Delete old CV if exists
            if(!empty($cv_path) && file_exists(CV_PATH . $cv_path)) {
                unlink(CV_PATH . $cv_path);
            }
            $cv_path = $upload_result['file_name'];
            
            // Update profile CV
            mysqli_query($conn, "UPDATE jobseeker_profiles SET cv_path = '$cv_path' WHERE user_id = $user_id");
        } else {
            $error = $upload_result['message'];
        }
    }
    
    if(empty($error)) {
        if(empty($cv_path)) {
            $error = 'Please upload your CV to apply!';
        } else {
            // Insert application
            $insert_query = "INSERT INTO applications (job_id, user_id, cv_path, cover_letter, status) 
                            VALUES ($job_id, $user_id, '$cv_path', '$cover_letter', 'pending')";
            
            if(mysqli_query($conn, $insert_query)) {
                // Send notification to user
                send_notification(
                    $user_id, 
                    'Application Submitted', 
                    'Your application for ' . $job['title'] . ' has been submitted successfully!',
                    'success',
                    '../jobseeker/my_applications.php'
                );
                
                // Send notification to employer
                $employer_query = "SELECT user_id FROM companies WHERE id = {$job['company_id']}";
                $employer_result = mysqli_query($conn, $employer_query);
                $employer = mysqli_fetch_assoc($employer_result);
                
                if($employer) {
                    send_notification(
                        $employer['user_id'],
                        'New Application Received',
                        'You have a new application for ' . $job['title'],
                        'info',
                        '../employer/applicants.php?job_id=' . $job_id
                    );
                }
                
                $_SESSION['success'] = 'Your application has been submitted successfully!';
                header("Location: job_detail.php?id=$job_id");
                exit();
            } else {
                $error = 'Failed to submit application. Please try again.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apply for <?php echo htmlspecialchars($job['title']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        .application-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px 0;
        }
        .company-logo {
            width: 80px;
            height: 80px;
            object-fit: contain;
            background: white;
            padding: 10px;
            border-radius: 10px;
        }
    </style>
</head>
<body class="bg-light">
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="../index.php">
                <i class="fas fa-briefcase me-2"></i><strong>JobPortal</strong>
            </a>
            <div class="ms-auto">
                <a href="../jobseeker/dashboard.php" class="btn btn-outline-light btn-sm">
                    <i class="fas fa-arrow-left"></i> Back to Dashboard
                </a>
            </div>
        </div>
    </nav>

    <!-- Application Header -->
    <section class="application-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-2 text-center mb-3 mb-md-0">
                    <?php if($job['logo']): ?>
                        <img src="../assets/uploads/logos/<?php echo htmlspecialchars($job['logo']); ?>" 
                             alt="Logo" class="company-logo">
                    <?php else: ?>
                        <div class="company-logo d-inline-flex align-items-center justify-content-center">
                            <i class="fas fa-building fa-2x text-secondary"></i>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="col-md-10">
                    <h2 class="mb-2">Apply for: <?php echo htmlspecialchars($job['title']); ?></h2>
                    <h5><?php echo htmlspecialchars($job['company_name']); ?></h5>
                </div>
            </div>
        </div>
    </section>

    <!-- Application Form -->
    <section class="py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <?php if($error): ?>
                        <div class="alert alert-danger alert-dismissible fade show">
                            <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <!-- Profile Check -->
                    <div class="card mb-4">
                        <div class="card-body">
                            <h5 class="card-title mb-3"><i class="fas fa-user-check"></i> Profile Completeness</h5>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <div class="d-flex align-items-center">
                                        <?php if($profile['cv_path']): ?>
                                            <i class="fas fa-check-circle text-success fa-2x me-3"></i>
                                            <div>
                                                <strong>CV Uploaded</strong>
                                                <br><small class="text-muted"><?php echo htmlspecialchars($profile['cv_path']); ?></small>
                                            </div>
                                        <?php else: ?>
                                            <i class="fas fa-times-circle text-danger fa-2x me-3"></i>
                                            <div>
                                                <strong>No CV uploaded</strong>
                                                <br><small class="text-danger">Please upload your CV below</small>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="d-flex align-items-center">
                                        <?php 
                                        $has_experience = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM jobseeker_experience WHERE user_id = $user_id LIMIT 1")) > 0;
                                        ?>
                                        <?php if($has_experience): ?>
                                            <i class="fas fa-check-circle text-success fa-2x me-3"></i>
                                            <div>
                                                <strong>Experience Added</strong>
                                                <br><small class="text-muted">Your work experience is on file</small>
                                            </div>
                                        <?php else: ?>
                                            <i class="fas fa-info-circle text-warning fa-2x me-3"></i>
                                            <div>
                                                <strong>No experience added</strong>
                                                <br><small class="text-muted">
                                                    <a href="../jobseeker/profile.php">Add experience</a>
                                                </small>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-3">
                                <a href="../jobseeker/profile.php" class="btn btn-outline-primary btn-sm">
                                    <i class="fas fa-edit"></i> Edit Profile
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Application Form -->
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title mb-4"><i class="fas fa-file-alt"></i> Application Details</h5>
                            
                            <form method="POST" action="" enctype="multipart/form-data">
                                <!-- CV Upload -->
                                <div class="mb-4">
                                    <label class="form-label">
                                        <i class="fas fa-file-pdf"></i> Upload CV 
                                        <span class="text-danger">*</span>
                                    </label>
                                    <?php if($profile['cv_path']): ?>
                                        <div class="alert alert-info mb-2">
                                            <i class="fas fa-info-circle"></i> 
                                            Current CV: <strong><?php echo htmlspecialchars($profile['cv_path']); ?></strong>
                                            <br><small>Upload a new file to replace it (optional)</small>
                                        </div>
                                    <?php endif; ?>
                                    <input type="file" class="form-control" name="cv" accept=".pdf,.doc,.docx">
                                    <small class="text-muted">
                                        Accepted formats: PDF, DOC, DOCX (Max 5MB)
                                        <?php if(!$profile['cv_path']): ?>
                                            <span class="text-danger">- CV is required!</span>
                                        <?php endif; ?>
                                    </small>
                                </div>

                                <!-- Cover Letter -->
                                <div class="mb-4">
                                    <label class="form-label">
                                        <i class="fas fa-envelope"></i> Cover Letter 
                                        <span class="text-muted">(Optional)</span>
                                    </label>
                                    <textarea class="form-control" name="cover_letter" rows="8" 
                                              placeholder="Write a compelling cover letter to introduce yourself and explain why you're a great fit for this position..."></textarea>
                                    <small class="text-muted">
                                        Tip: Mention your relevant experience and skills that match the job requirements
                                    </small>
                                </div>

                                <!-- Terms -->
                                <div class="mb-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" required id="terms">
                                        <label class="form-check-label" for="terms">
                                            I confirm that all information provided is accurate and I agree to the 
                                            <a href="#">terms and conditions</a>
                                        </label>
                                    </div>
                                </div>

                                <!-- Submit Buttons -->
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-paper-plane"></i> Submit Application
                                    </button>
                                    <a href="job_detail.php?id=<?php echo $job_id; ?>" class="btn btn-outline-secondary">
                                        <i class="fas fa-times"></i> Cancel
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Application Tips -->
                    <div class="card mt-4">
                        <div class="card-body">
                            <h5 class="card-title mb-3"><i class="fas fa-lightbulb"></i> Application Tips</h5>
                            <ul class="mb-0">
                                <li class="mb-2">Ensure your CV is up-to-date and relevant to the position</li>
                                <li class="mb-2">Tailor your cover letter to highlight skills matching job requirements</li>
                                <li class="mb-2">Double-check all information before submitting</li>
                                <li class="mb-2">Be professional and concise in your communication</li>
                                <li>You can track your application status in your dashboard</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4 mt-5">
        <div class="container text-center">
            <p class="mb-0">&copy; 2024 JobPortal. All rights reserved.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>