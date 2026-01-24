<?php
session_start();
require_once '../config/database.php';
require_once '../config/functions.php';
require_once '../config/constants.php';

if(!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: jobs.php");
    exit();
}

$job_id = (int)$_GET['id'];

// Get job details
$query = "SELECT j.*, c.name as company_name, c.logo, c.description as company_description,
          c.website, c.address as company_address, c.city as company_city, 
          c.employee_count, c.industry, cat.name as category_name
          FROM jobs j
          LEFT JOIN companies c ON j.company_id = c.id
          LEFT JOIN job_categories cat ON j.category_id = cat.id
          WHERE j.id = $job_id AND j.status = 'active'";

$result = mysqli_query($conn, $query);

if(mysqli_num_rows($result) == 0) {
    header("Location: jobs.php");
    exit();
}

$job = mysqli_fetch_assoc($result);

// Increment views
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
$ip_address = $_SERVER['REMOTE_ADDR'];
increment_job_views($job_id, $user_id, $ip_address);

// Check if already applied
$has_applied = false;
$is_saved = false;
if(isset($_SESSION['user_id']) && $_SESSION['role'] == 'jobseeker') {
    $has_applied = has_applied($_SESSION['user_id'], $job_id);
    $is_saved = is_job_saved($_SESSION['user_id'], $job_id);
}

// Get job skills
$skills_query = "SELECT * FROM job_skills WHERE job_id = $job_id";
$skills_result = mysqli_query($conn, $skills_query);

// Get similar jobs
$similar_query = "SELECT j.*, c.name as company_name, c.logo 
                  FROM jobs j
                  LEFT JOIN companies c ON j.company_id = c.id
                  WHERE j.id != $job_id 
                  AND j.status = 'active'
                  AND (j.category_id = {$job['category_id']} OR j.city = '{$job['city']}')
                  ORDER BY j.created_at DESC
                  LIMIT 3";
$similar_result = mysqli_query($conn, $similar_query);

$page_title = $job['title'] . ' - ' . $job['company_name'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        .job-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 60px 0;
        }
        .company-logo-lg {
            width: 100px;
            height: 100px;
            object-fit: contain;
            background: white;
            padding: 10px;
            border-radius: 10px;
        }
        .apply-card {
            position: sticky;
            top: 20px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.1);
        }
        .skill-badge {
            display: inline-block;
            padding: 8px 15px;
            margin: 5px;
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 20px;
        }
        .company-info-card {
            border-left: 4px solid #667eea;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="../index.php">
                <i class="fas fa-briefcase me-2"></i><strong>JobPortal</strong>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="../index.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="jobs.php">Cari Lowongan</a></li>
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user"></i> <?php echo htmlspecialchars($_SESSION['name']); ?>
                            </a>
                            <ul class="dropdown-menu">
                                <?php if($_SESSION['role'] == 'jobseeker'): ?>
                                    <li><a class="dropdown-item" href="../jobseeker/dashboard.php">Dashboard</a></li>
                                <?php elseif($_SESSION['role'] == 'employer'): ?>
                                    <li><a class="dropdown-item" href="../employer/dashboard.php">Dashboard</a></li>
                                <?php endif; ?>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="../auth/logout.php">Logout</a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item"><a class="nav-link" href="../auth/login.php">Login</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Job Header -->
    <section class="job-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-2 text-center text-md-start mb-3 mb-md-0">
                    <?php if($job['logo']): ?>
                        <img src="../assets/uploads/logos/<?php echo htmlspecialchars($job['logo']); ?>" 
                             alt="Logo" class="company-logo-lg">
                    <?php else: ?>
                        <div class="company-logo-lg d-inline-flex align-items-center justify-content-center">
                            <i class="fas fa-building fa-3x text-secondary"></i>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="col-md-10">
                    <h1 class="mb-2"><?php echo htmlspecialchars($job['title']); ?></h1>
                    <h4 class="mb-3"><?php echo htmlspecialchars($job['company_name']); ?></h4>
                    <div class="d-flex flex-wrap gap-3">
                        <span><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($job['city']); ?></span>
                        <span><i class="fas fa-briefcase"></i> <?php echo ucfirst(str_replace('-', ' ', $job['type'])); ?></span>
                        <span><i class="fas fa-layer-group"></i> <?php echo ucfirst($job['level']); ?></span>
                        <span><i class="fas fa-eye"></i> <?php echo number_format($job['views']); ?> views</span>
                        <span><i class="far fa-clock"></i> Posted <?php echo time_ago($job['created_at']); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Job Content -->
    <section class="py-5">
        <div class="container">
            <div class="row">
                <!-- Main Content -->
                <div class="col-lg-8">
                    <!-- Job Overview -->
                    <div class="card mb-4">
                        <div class="card-body">
                            <h5 class="card-title mb-3"><i class="fas fa-info-circle"></i> Job Overview</h5>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <div class="d-flex">
                                        <div class="me-3">
                                            <i class="fas fa-calendar-alt fa-2x text-primary"></i>
                                        </div>
                                        <div>
                                            <small class="text-muted">Date Posted</small>
                                            <div><?php echo date('d M Y', strtotime($job['created_at'])); ?></div>
                                        </div>
                                    </div>
                                </div>
                                <?php if($job['deadline']): ?>
                                <div class="col-md-6 mb-3">
                                    <div class="d-flex">
                                        <div class="me-3">
                                            <i class="fas fa-hourglass-end fa-2x text-danger"></i>
                                        </div>
                                        <div>
                                            <small class="text-muted">Deadline</small>
                                            <div><?php echo date('d M Y', strtotime($job['deadline'])); ?></div>
                                        </div>
                                    </div>
                                </div>
                                <?php endif; ?>
                                <?php if($job['salary_min'] && $job['salary_max']): ?>
                                <div class="col-md-6 mb-3">
                                    <div class="d-flex">
                                        <div class="me-3">
                                            <i class="fas fa-money-bill-wave fa-2x text-success"></i>
                                        </div>
                                        <div>
                                            <small class="text-muted">Salary Range</small>
                                            <div class="fw-bold">
                                                <?php echo format_currency($job['salary_min']); ?> - 
                                                <?php echo format_currency($job['salary_max']); ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php endif; ?>
                                <div class="col-md-6 mb-3">
                                    <div class="d-flex">
                                        <div class="me-3">
                                            <i class="fas fa-graduation-cap fa-2x text-info"></i>
                                        </div>
                                        <div>
                                            <small class="text-muted">Education</small>
                                            <div><?php echo htmlspecialchars($job['education'] ?? 'Not specified'); ?></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="d-flex">
                                        <div class="me-3">
                                            <i class="fas fa-user-tie fa-2x text-warning"></i>
                                        </div>
                                        <div>
                                            <small class="text-muted">Experience</small>
                                            <div><?php echo htmlspecialchars($job['experience'] ?? 'Not specified'); ?></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="d-flex">
                                        <div class="me-3">
                                            <i class="fas fa-users fa-2x text-secondary"></i>
                                        </div>
                                        <div>
                                            <small class="text-muted">Positions Available</small>
                                            <div><?php echo $job['positions_available']; ?> position(s)</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Job Description -->
                    <div class="card mb-4">
                        <div class="card-body">
                            <h5 class="card-title mb-3"><i class="fas fa-file-alt"></i> Job Description</h5>
                            <div class="job-description">
                                <?php echo nl2br(htmlspecialchars($job['description'])); ?>
                            </div>
                        </div>
                    </div>

                    <!-- Responsibilities -->
                    <?php if($job['responsibilities']): ?>
                    <div class="card mb-4">
                        <div class="card-body">
                            <h5 class="card-title mb-3"><i class="fas fa-tasks"></i> Responsibilities</h5>
                            <div>
                                <?php echo nl2br(htmlspecialchars($job['responsibilities'])); ?>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Requirements -->
                    <?php if($job['requirements']): ?>
                    <div class="card mb-4">
                        <div class="card-body">
                            <h5 class="card-title mb-3"><i class="fas fa-clipboard-check"></i> Requirements</h5>
                            <div>
                                <?php echo nl2br(htmlspecialchars($job['requirements'])); ?>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Skills -->
                    <?php if(mysqli_num_rows($skills_result) > 0): ?>
                    <div class="card mb-4">
                        <div class="card-body">
                            <h5 class="card-title mb-3"><i class="fas fa-cogs"></i> Required Skills</h5>
                            <div>
                                <?php while($skill = mysqli_fetch_assoc($skills_result)): ?>
                                    <span class="skill-badge">
                                        <i class="fas fa-check-circle text-success"></i>
                                        <?php echo htmlspecialchars($skill['skill_name']); ?>
                                        <?php if($skill['is_required']): ?>
                                            <small class="text-danger">*Required</small>
                                        <?php endif; ?>
                                    </span>
                                <?php endwhile; ?>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Benefits -->
                    <?php if($job['benefits']): ?>
                    <div class="card mb-4">
                        <div class="card-body">
                            <h5 class="card-title mb-3"><i class="fas fa-gift"></i> Benefits</h5>
                            <div>
                                <?php echo nl2br(htmlspecialchars($job['benefits'])); ?>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Company Info -->
                    <div class="card company-info-card mb-4">
                        <div class="card-body">
                            <h5 class="card-title mb-3"><i class="fas fa-building"></i> About <?php echo htmlspecialchars($job['company_name']); ?></h5>
                            <?php if($job['company_description']): ?>
                                <p><?php echo nl2br(htmlspecialchars($job['company_description'])); ?></p>
                            <?php endif; ?>
                            <div class="row mt-3">
                                <?php if($job['industry']): ?>
                                <div class="col-md-6 mb-2">
                                    <strong>Industry:</strong> <?php echo htmlspecialchars($job['industry']); ?>
                                </div>
                                <?php endif; ?>
                                <?php if($job['employee_count']): ?>
                                <div class="col-md-6 mb-2">
                                    <strong>Company Size:</strong> <?php echo htmlspecialchars($job['employee_count']); ?> employees
                                </div>
                                <?php endif; ?>
                                <?php if($job['website']): ?>
                                <div class="col-md-6 mb-2">
                                    <strong>Website:</strong> 
                                    <a href="<?php echo htmlspecialchars($job['website']); ?>" target="_blank">
                                        <?php echo htmlspecialchars($job['website']); ?>
                                    </a>
                                </div>
                                <?php endif; ?>
                                <?php if($job['company_address']): ?>
                                <div class="col-md-6 mb-2">
                                    <strong>Location:</strong> <?php echo htmlspecialchars($job['company_address']); ?>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Similar Jobs -->
                    <?php if(mysqli_num_rows($similar_result) > 0): ?>
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title mb-3"><i class="fas fa-list"></i> Similar Jobs</h5>
                            <?php while($similar = mysqli_fetch_assoc($similar_result)): ?>
                            <div class="border-bottom pb-3 mb-3">
                                <h6>
                                    <a href="job_detail.php?id=<?php echo $similar['id']; ?>" class="text-decoration-none">
                                        <?php echo htmlspecialchars($similar['title']); ?>
                                    </a>
                                </h6>
                                <p class="mb-1 text-muted"><?php echo htmlspecialchars($similar['company_name']); ?></p>
                            </div>
                            <?php endwhile; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Sidebar -->
                <div class="col-lg-4">
                    <div class="apply-card card">
                        <div class="card-body text-center">
                            <?php if(isset($_SESSION['user_id']) && $_SESSION['role'] == 'jobseeker'): ?>
                                <?php if($has_applied): ?>
                                    <div class="alert alert-info">
                                        <i class="fas fa-check-circle"></i>
                                        <p class="mb-0">You have already applied for this job</p>
                                    </div>
                                    <a href="../jobseeker/my_applications.php" class="btn btn-outline-primary w-100">
                                        View My Applications
                                    </a>
                                <?php else: ?>
                                    <a href="apply_job.php?id=<?php echo $job_id; ?>" class="btn btn-primary btn-lg w-100 mb-3">
                                        <i class="fas fa-paper-plane"></i> Apply Now
                                    </a>
                                    <?php if($is_saved): ?>
                                        <button class="btn btn-outline-danger w-100" onclick="unsaveJob(<?php echo $job_id; ?>)">
                                            <i class="fas fa-heart"></i> Saved
                                        </button>
                                    <?php else: ?>
                                        <button class="btn btn-outline-primary w-100" onclick="saveJob(<?php echo $job_id; ?>)">
                                            <i class="far fa-heart"></i> Save Job
                                        </button>
                                    <?php endif; ?>
                                <?php endif; ?>
                            <?php elseif(isset($_SESSION['user_id']) && $_SESSION['role'] == 'employer'): ?>
                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    <p class="mb-0">Employers cannot apply for jobs</p>
                                </div>
                            <?php else: ?>
                                <p class="mb-3">Please login to apply for this job</p>
                                <a href="../auth/login.php?redirect=job_detail.php?id=<?php echo $job_id; ?>" class="btn btn-primary btn-lg w-100 mb-2">
                                    <i class="fas fa-sign-in-alt"></i> Login to Apply
                                </a>
                                <a href="../auth/register.php" class="btn btn-outline-primary w-100">
                                    Create Account
                                </a>
                            <?php endif; ?>

                            <hr class="my-4">

                            <div class="text-start">
                                <h6 class="mb-3">Share this job</h6>
                                <div class="d-flex gap-2">
                                    <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>" 
                                       target="_blank" class="btn btn-outline-primary flex-fill">
                                        <i class="fab fa-facebook"></i>
                                    </a>
                                    <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>&text=<?php echo urlencode($job['title']); ?>" 
                                       target="_blank" class="btn btn-outline-info flex-fill">
                                        <i class="fab fa-twitter"></i>
                                    </a>
                                    <a href="https://www.linkedin.com/sharing/share-offsite/?url=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>" 
                                       target="_blank" class="btn btn-outline-primary flex-fill">
                                        <i class="fab fa-linkedin"></i>
                                    </a>
                                    <button class="btn btn-outline-secondary flex-fill" onclick="copyLink()">
                                        <i class="fas fa-link"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Report Job -->
                    <div class="card mt-3">
                        <div class="card-body text-center">
                            <a href="#" class="text-muted text-decoration-none" data-bs-toggle="modal" data-bs-target="#reportModal">
                                <i class="fas fa-flag"></i> Report this job
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Report Modal -->
    <div class="modal fade" id="reportModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Report Job</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form>
                        <div class="mb-3">
                            <label class="form-label">Reason</label>
                            <select class="form-select">
                                <option>Spam</option>
                                <option>Misleading information</option>
                                <option>Inappropriate content</option>
                                <option>Duplicate posting</option>
                                <option>Other</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Additional details</label>
                            <textarea class="form-control" rows="3"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger">Submit Report</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4 mt-5">
        <div class="container text-center">
            <p class="mb-0">&copy; 2024 JobPortal. All rights reserved.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function saveJob(jobId) {
            fetch('../api/save_job.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({job_id: jobId, action: 'save'})
            })
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    location.reload();
                } else {
                    alert('Failed to save job');
                }
            });
        }

        function unsaveJob(jobId) {
            fetch('../api/save_job.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({job_id: jobId, action: 'unsave'})
            })
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    location.reload();
                } else {
                    alert('Failed to unsave job');
                }
            });
        }

        function copyLink() {
            navigator.clipboard.writeText(window.location.href);
            alert('Link copied to clipboard!');
        }
    </script>
</body>
</html>