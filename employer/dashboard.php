<?php
session_start();
require_once '../config/database.php';
require_once '../config/functions.php';
require_once '../config/constants.php';

check_role('employer');

$user_id = $_SESSION['user_id'];

// Get company info
$company_query = "SELECT * FROM companies WHERE user_id = $user_id";
$company_result = mysqli_query($conn, $company_query);
$company = mysqli_fetch_assoc($company_result);

if(!$company) {
    header("Location: profile.php?setup=1");
    exit();
}

$company_id = $company['id'];

// Get statistics
$stats_query = "SELECT 
    (SELECT COUNT(*) FROM jobs WHERE company_id = $company_id) as total_jobs,
    (SELECT COUNT(*) FROM jobs WHERE company_id = $company_id AND status = 'active') as active_jobs,
    (SELECT COUNT(*) FROM applications WHERE job_id IN (SELECT id FROM jobs WHERE company_id = $company_id)) as total_applications,
    (SELECT COUNT(*) FROM applications WHERE job_id IN (SELECT id FROM jobs WHERE company_id = $company_id) AND status = 'pending') as pending_apps,
    (SELECT COUNT(*) FROM applications WHERE job_id IN (SELECT id FROM jobs WHERE company_id = $company_id) AND status = 'shortlisted') as shortlisted,
    (SELECT SUM(views) FROM jobs WHERE company_id = $company_id) as total_views";
$stats = mysqli_fetch_assoc(mysqli_query($conn, $stats_query));

// Get recent jobs
$jobs_query = "SELECT j.*, 
               (SELECT COUNT(*) FROM applications WHERE job_id = j.id) as applicant_count
               FROM jobs j
               WHERE j.company_id = $company_id
               ORDER BY j.created_at DESC
               LIMIT 5";
$jobs_result = mysqli_query($conn, $jobs_query);

// Get recent applications
$apps_query = "SELECT a.*, j.title, u.name as applicant_name, u.email
               FROM applications a
               JOIN jobs j ON a.job_id = j.id
               JOIN users u ON a.user_id = u.id
               WHERE j.company_id = $company_id
               ORDER BY a.applied_at DESC
               LIMIT 8";
$apps_result = mysqli_query($conn, $apps_query);

// Get application statistics by status
$status_stats_query = "SELECT status, COUNT(*) as count 
                       FROM applications 
                       WHERE job_id IN (SELECT id FROM jobs WHERE company_id = $company_id)
                       GROUP BY status";
$status_stats_result = mysqli_query($conn, $status_stats_query);

$page_title = 'Employer Dashboard';
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
        .sidebar {
            background: #2c3e50;
            min-height: calc(100vh - 56px);
            color: white;
        }
        .sidebar .nav-link {
            color: #ecf0f1;
            padding: 12px 20px;
            transition: all 0.3s;
        }
        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            background: #34495e;
            color: white;
        }
        .stat-card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: transform 0.2s;
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
        .chart-container {
            position: relative;
            height: 300px;
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
                        <a class="nav-link" href="../index.php">
                            <i class="fas fa-home"></i> Home
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-building"></i> <?php echo htmlspecialchars($company['name']); ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="profile.php"><i class="fas fa-edit"></i> Edit Profile</a></li>
                            <li><a class="dropdown-item" href="settings.php"><i class="fas fa-cog"></i> Settings</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="../auth/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 px-0 sidebar">
                <div class="p-3">
                    <h6 class="text-light mb-3">EMPLOYER MENU</h6>
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link active" href="dashboard.php">
                                <i class="fas fa-tachometer-alt"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="profile.php">
                                <i class="fas fa-building"></i> Company Profile
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="post_job.php">
                                <i class="fas fa-plus-circle"></i> Post New Job
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="my_jobs.php">
                                <i class="fas fa-list"></i> My Jobs
                                <span class="badge bg-primary ms-2"><?php echo $stats['total_jobs']; ?></span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="applicants.php">
                                <i class="fas fa-users"></i> Applicants
                                <span class="badge bg-warning ms-2"><?php echo $stats['pending_apps']; ?></span>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 px-4 py-4">
                <?php if(!$company['is_verified']): ?>
                <div class="alert alert-warning alert-dismissible fade show">
                    <i class="fas fa-exclamation-triangle"></i>
                    <strong>Company Not Verified!</strong> Your company profile is pending verification. 
                    Some features may be limited. <a href="profile.php" class="alert-link">Complete your profile</a>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>

                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h2>Welcome, <?php echo htmlspecialchars($company['name']); ?>! 👋</h2>
                        <p class="text-muted">Manage your job postings and applicants</p>
                    </div>
                    <a href="post_job.php" class="btn btn-primary">
                        <i class="fas fa-plus-circle"></i> Post New Job
                    </a>
                </div>

                <!-- Statistics Cards -->
                <div class="row mb-4">
                    <div class="col-md-3 mb-3">
                        <div class="card stat-card bg-primary text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1">Total Jobs</h6>
                                        <h3 class="mb-0"><?php echo $stats['total_jobs']; ?></h3>
                                        <small><?php echo $stats['active_jobs']; ?> active</small>
                                    </div>
                                    <i class="fas fa-briefcase fa-3x opacity-50"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card stat-card bg-success text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1">Total Applications</h6>
                                        <h3 class="mb-0"><?php echo $stats['total_applications']; ?></h3>
                                        <small><?php echo $stats['pending_apps']; ?> pending</small>
                                    </div>
                                    <i class="fas fa-file-alt fa-3x opacity-50"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card stat-card bg-warning text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1">Shortlisted</h6>
                                        <h3 class="mb-0"><?php echo $stats['shortlisted']; ?></h3>
                                        <small>Candidates</small>
                                    </div>
                                    <i class="fas fa-star fa-3x opacity-50"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card stat-card bg-info text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1">Total Views</h6>
                                        <h3 class="mb-0"><?php echo number_format($stats['total_views'] ?? 0); ?></h3>
                                        <small>All jobs</small>
                                    </div>
                                    <i class="fas fa-eye fa-3x opacity-50"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <!-- Application Status Chart -->
                    <div class="col-lg-5 mb-4">
                        <div class="card">
                            <div class="card-header bg-white">
                                <h5 class="mb-0"><i class="fas fa-chart-pie"></i> Applications by Status</h5>
                            </div>
                            <div class="card-body">
                                <canvas id="statusChart"></canvas>
                                <div class="mt-3">
                                    <?php 
                                    mysqli_data_seek($status_stats_result, 0);
                                    while($stat = mysqli_fetch_assoc($status_stats_result)): 
                                    ?>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="badge bg-<?php echo get_status_badge($stat['status']); ?>">
                                            <?php echo ucfirst($stat['status']); ?>
                                        </span>
                                        <strong><?php echo $stat['count']; ?></strong>
                                    </div>
                                    <?php endwhile; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Jobs -->
                    <div class="col-lg-7 mb-4">
                        <div class="card">
                            <div class="card-header bg-white">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0"><i class="fas fa-list"></i> Recent Job Postings</h5>
                                    <a href="my_jobs.php" class="btn btn-sm btn-outline-primary">View All</a>
                                </div>
                            </div>
                            <div class="card-body">
                                <?php if(mysqli_num_rows($jobs_result) > 0): ?>
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Job Title</th>
                                                    <th>Type</th>
                                                    <th>Applicants</th>
                                                    <th>Views</th>
                                                    <th>Status</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php while($job = mysqli_fetch_assoc($jobs_result)): ?>
                                                <tr>
                                                    <td>
                                                        <strong><?php echo htmlspecialchars($job['title']); ?></strong>
                                                        <br><small class="text-muted">Posted <?php echo time_ago($job['created_at']); ?></small>
                                                    </td>
                                                    <td><span class="badge bg-<?php echo get_job_type_badge($job['type']); ?>"><?php echo ucfirst($job['type']); ?></span></td>
                                                    <td>
                                                        <a href="applicants.php?job_id=<?php echo $job['id']; ?>" class="text-decoration-none">
                                                            <i class="fas fa-users"></i> <?php echo $job['applicant_count']; ?>
                                                        </a>
                                                    </td>
                                                    <td><i class="fas fa-eye"></i> <?php echo number_format($job['views']); ?></td>
                                                    <td>
                                                        <?php if($job['status'] == 'active'): ?>
                                                            <span class="badge bg-success">Active</span>
                                                        <?php else: ?>
                                                            <span class="badge bg-secondary"><?php echo ucfirst($job['status']); ?></span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <div class="btn-group btn-group-sm">
                                                            <a href="edit_job.php?id=<?php echo $job['id']; ?>" class="btn btn-outline-primary" title="Edit">
                                                                <i class="fas fa-edit"></i>
                                                            </a>
                                                            <a href="applicants.php?job_id=<?php echo $job['id']; ?>" class="btn btn-outline-success" title="View Applicants">
                                                                <i class="fas fa-users"></i>
                                                            </a>
                                                        </div>
                                                    </td>
                                                </tr>
                                                <?php endwhile; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php else: ?>
                                    <div class="text-center py-4">
                                        <i class="fas fa-briefcase fa-3x text-muted mb-3"></i>
                                        <p class="text-muted">You haven't posted any jobs yet</p>
                                        <a href="post_job.php" class="btn btn-primary">
                                            <i class="fas fa-plus-circle"></i> Post Your First Job
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Applications -->
                <div class="card">
                    <div class="card-header bg-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><i class="fas fa-file-alt"></i> Recent Applications</h5>
                            <a href="applicants.php" class="btn btn-sm btn-outline-primary">View All</a>
                        </div>
                    </div>
                    <div class="card-body">
                        <?php if(mysqli_num_rows($apps_result) > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Applicant</th>
                                            <th>Job Position</th>
                                            <th>Applied Date</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while($app = mysqli_fetch_assoc($apps_result)): ?>
                                        <tr>
                                            <td>
                                                <strong><?php echo htmlspecialchars($app['applicant_name']); ?></strong>
                                                <br><small class="text-muted"><?php echo htmlspecialchars($app['email']); ?></small>
                                            </td>
                                            <td><?php echo htmlspecialchars($app['title']); ?></td>
                                            <td><?php echo time_ago($app['applied_at']); ?></td>
                                            <td>
                                                <span class="badge bg-<?php echo get_status_badge($app['status']); ?>">
                                                    <?php echo ucfirst($app['status']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <a href="view_application.php?id=<?php echo $app['id']; ?>" class="btn btn-sm btn-outline-primary">
                                                    Review
                                                </a>
                                            </td>
                                        </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-4">
                                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                <p class="text-muted">No applications received yet</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Status Chart
        const ctx = document.getElementById('statusChart');
        if(ctx) {
            const statusData = {
                labels: [<?php 
                    mysqli_data_seek($status_stats_result, 0);
                    $labels = [];
                    while($s = mysqli_fetch_assoc($status_stats_result)) {
                        $labels[] = "'" . ucfirst($s['status']) . "'";
                    }
                    echo implode(',', $labels);
                ?>],
                datasets: [{
                    data: [<?php 
                        mysqli_data_seek($status_stats_result, 0);
                        $data = [];
                        while($s = mysqli_fetch_assoc($status_stats_result)) {
                            $data[] = $s['count'];
                        }
                        echo implode(',', $data);
                    ?>],
                    backgroundColor: [
                        '#f6c23e', '#36b9cc', '#4e73df', '#1cc88a', '#e74a3b', '#858796', '#6c757d'
                    ]
                }]
            };

            new Chart(ctx, {
                type: 'doughnut',
                data: statusData,
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
        }
    </script>
</body>
</html>