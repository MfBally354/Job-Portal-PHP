<?php
session_start();
require_once '../config/database.php';
require_once '../config/functions.php';
require_once '../config/constants.php';

check_role('jobseeker');

$user_id = $_SESSION['user_id'];

// Get saved jobs
$query = "SELECT s.*, j.*, c.name as company_name, c.logo, cat.name as category_name
          FROM saved_jobs s
          JOIN jobs j ON s.job_id = j.id
          JOIN companies c ON j.company_id = c.id
          LEFT JOIN job_categories cat ON j.category_id = cat.id
          WHERE s.user_id = $user_id
          ORDER BY s.created_at DESC";

$pagination = paginate($query, 12);
$saved_jobs = $pagination['result'];

// Handle unsave
if(isset($_GET['unsave'])) {
    $job_id = (int)$_GET['unsave'];
    mysqli_query($conn, "DELETE FROM saved_jobs WHERE user_id = $user_id AND job_id = $job_id");
    $_SESSION['success'] = 'Job removed from saved list';
    header("Location: saved_jobs.php");
    exit();
}

$page_title = 'Saved Jobs';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - JobPortal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        .job-card {
            transition: all 0.3s ease;
            border: none;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            height: 100%;
        }
        .job-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.15);
        }
        .company-logo {
            width: 60px;
            height: 60px;
            object-fit: contain;
            border: 1px solid #e3e6f0;
            padding: 5px;
            border-radius: 8px;
        }
        .saved-heart {
            color: #e74a3b;
            animation: pulse 1s ease-in-out;
        }
        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.1); }
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="../index.php">
                <i class="fas fa-briefcase me-2"></i><strong>JobPortal</strong>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php">
                            <i class="fas fa-tachometer-alt"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../public/jobs.php">
                            <i class="fas fa-search"></i> Browse Jobs
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user"></i> <?php echo htmlspecialchars($_SESSION['name']); ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="profile.php"><i class="fas fa-user-edit"></i> Profile</a></li>
                            <li><a class="dropdown-item" href="my_applications.php"><i class="fas fa-file-alt"></i> My Applications</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="../auth/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Header -->
    <section class="bg-light py-4">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2><i class="fas fa-heart text-danger"></i> Saved Jobs</h2>
                    <p class="text-muted mb-0">Jobs you've saved for later</p>
                </div>
                <div>
                    <span class="badge bg-danger" style="font-size: 1.2rem;">
                        <?php echo $pagination['total_records']; ?> Jobs Saved
                    </span>
                </div>
            </div>
        </div>
    </section>

    <!-- Main Content -->
    <section class="py-5">
        <div class="container">
            <?php if(isset($_SESSION['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="fas fa-check-circle"></i> <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if(mysqli_num_rows($saved_jobs) > 0): ?>
                <div class="row">
                    <?php while($job = mysqli_fetch_assoc($saved_jobs)): ?>
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card job-card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <div class="d-flex align-items-start flex-grow-1">
                                        <?php if($job['logo']): ?>
                                            <img src="../assets/uploads/logos/<?php echo htmlspecialchars($job['logo']); ?>" 
                                                 alt="Logo" class="company-logo me-3">
                                        <?php else: ?>
                                            <div class="company-logo me-3 d-flex align-items-center justify-content-center bg-light">
                                                <i class="fas fa-building text-secondary"></i>
                                            </div>
                                        <?php endif; ?>
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1">
                                                <a href="../public/job_detail.php?id=<?php echo $job['job_id']; ?>" 
                                                   class="text-decoration-none text-dark">
                                                    <?php echo htmlspecialchars($job['title']); ?>
                                                </a>
                                            </h6>
                                            <p class="text-primary mb-0 small"><?php echo htmlspecialchars($job['company_name']); ?></p>
                                        </div>
                                    </div>
                                    <button class="btn btn-sm" onclick="unsaveJob(<?php echo $job['job_id']; ?>)" title="Remove from saved">
                                        <i class="fas fa-heart saved-heart fa-lg"></i>
                                    </button>
                                </div>
                                
                                <div class="mb-3">
                                    <span class="badge bg-<?php echo get_job_type_badge($job['type']); ?> me-1">
                                        <?php echo ucfirst(str_replace('-', ' ', $job['type'])); ?>
                                    </span>
                                    <?php if($job['category_name']): ?>
                                    <span class="badge bg-secondary">
                                        <?php echo htmlspecialchars($job['category_name']); ?>
                                    </span>
                                    <?php endif; ?>
                                </div>
                                
                                <p class="text-muted mb-2 small">
                                    <i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($job['city']); ?>
                                </p>
                                
                                <?php if($job['salary_min'] && $job['salary_max']): ?>
                                <p class="text-success fw-bold mb-3 small">
                                    <i class="fas fa-money-bill-wave"></i>
                                    <?php echo format_currency($job['salary_min']); ?> - <?php echo format_currency($job['salary_max']); ?>
                                </p>
                                <?php endif; ?>

                                <div class="mb-3">
                                    <small class="text-muted">
                                        <i class="far fa-clock"></i> Posted <?php echo time_ago($job['created_at']); ?>
                                    </small>
                                    <br>
                                    <small class="text-muted">
                                        <i class="fas fa-bookmark"></i> Saved <?php echo time_ago($job['created_at']); ?>
                                    </small>
                                </div>
                                
                                <div class="d-flex gap-2">
                                    <a href="../public/job_detail.php?id=<?php echo $job['job_id']; ?>" 
                                       class="btn btn-sm btn-primary flex-grow-1">
                                        <i class="fas fa-eye"></i> View Details
                                    </a>
                                    <?php if(!has_applied($user_id, $job['job_id'])): ?>
                                        <a href="../public/apply_job.php?id=<?php echo $job['job_id']; ?>" 
                                           class="btn btn-sm btn-success">
                                            <i class="fas fa-paper-plane"></i> Apply
                                        </a>
                                    <?php else: ?>
                                        <button class="btn btn-sm btn-secondary" disabled>
                                            <i class="fas fa-check"></i> Applied
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>

                <!-- Pagination -->
                <div class="mt-4">
                    <?php echo render_pagination($pagination['current_page'], $pagination['total_pages'], '?'); ?>
                </div>

            <?php else: ?>
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="far fa-heart fa-5x text-muted mb-4"></i>
                        <h3>No Saved Jobs Yet</h3>
                        <p class="text-muted mb-4">Start saving jobs you're interested in to view them later</p>
                        <a href="../public/jobs.php" class="btn btn-primary btn-lg">
                            <i class="fas fa-search"></i> Browse Jobs
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Unsave Confirmation Modal -->
    <div class="modal fade" id="unsaveModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Remove from Saved</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to remove this job from your saved list?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <a href="#" id="unsaveLink" class="btn btn-danger">Remove</a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function unsaveJob(jobId) {
            if(confirm('Remove this job from saved list?')) {
                window.location.href = '?unsave=' + jobId;
            }
        }
    </script>
</body>
</html>