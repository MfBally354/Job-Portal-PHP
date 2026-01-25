<?php
session_start();
require_once '../config/database.php';
require_once '../config/functions.php';
require_once '../config/constants.php';

check_role('admin');

// Get platform statistics
$stats_query = "SELECT 
    (SELECT COUNT(*) FROM users WHERE role = 'jobseeker') as jobseekers,
    (SELECT COUNT(*) FROM users WHERE role = 'employer') as employers,
    (SELECT COUNT(*) FROM companies) as companies,
    (SELECT COUNT(*) FROM companies WHERE is_verified = 1) as verified_companies,
    (SELECT COUNT(*) FROM jobs) as total_jobs,
    (SELECT COUNT(*) FROM jobs WHERE status = 'active') as active_jobs,
    (SELECT COUNT(*) FROM applications) as total_applications,
    (SELECT COUNT(*) FROM applications WHERE status = 'pending') as pending_applications,
    (SELECT SUM(views) FROM jobs) as total_views";
$stats = mysqli_fetch_assoc(mysqli_query($conn, $stats_query));

// Get recent users
$recent_users = mysqli_query($conn, "SELECT * FROM users ORDER BY created_at DESC LIMIT 5");

// Get recent jobs
$recent_jobs = mysqli_query($conn, "SELECT j.*, c.name as company_name 
    FROM jobs j 
    LEFT JOIN companies c ON j.company_id = c.id 
    ORDER BY j.created_at DESC LIMIT 5");

// Get companies pending verification
$pending_companies = mysqli_query($conn, "SELECT c.*, u.email 
    FROM companies c 
    JOIN users u ON c.user_id = u.id 
    WHERE c.is_verified = 0 
    ORDER BY c.created_at DESC LIMIT 5");

// Get monthly statistics for chart
$monthly_stats = mysqli_query($conn, "SELECT 
    DATE_FORMAT(created_at, '%Y-%m') as month,
    COUNT(*) as count 
    FROM users 
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
    GROUP BY month 
    ORDER BY month");

// Get job statistics by status
$job_status_stats = mysqli_query($conn, "SELECT status, COUNT(*) as count FROM jobs GROUP BY status");

// Get application statistics by status
$app_status_stats = mysqli_query($conn, "SELECT status, COUNT(*) as count FROM applications GROUP BY status");

$page_title = 'Admin Dashboard';
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
            background: #1a1d20;
            min-height: calc(100vh - 56px);
            color: white;
        }
        .sidebar .nav-link {
            color: #b8bbbe;
            padding: 12px 20px;
            transition: all 0.3s;
        }
        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            background: #2c2f33;
            color: white;
        }
        .stat-card {
            border: none;
            border-radius: 10px;
            transition: all 0.3s;
        }
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
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
                <i class="fas fa-briefcase me-2"></i><strong>JobPortal Admin</strong>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="../index.php" target="_blank">
                            <i class="fas fa-external-link-alt"></i> View Site
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user-shield"></i> <?php echo htmlspecialchars($_SESSION['name']); ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
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
                    <h6 class="text-muted mb-3">ADMIN MENU</h6>
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link active" href="dashboard.php">
                                <i class="fas fa-tachometer-alt"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="users.php">
                                <i class="fas fa-users"></i> Users Management
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="companies.php">
                                <i class="fas fa-building"></i> Companies
                                <?php if(mysqli_num_rows($pending_companies) > 0): ?>
                                    <span class="badge bg-danger ms-2"><?php echo mysqli_num_rows($pending_companies); ?></span>
                                <?php endif; ?>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="jobs.php">
                                <i class="fas fa-briefcase"></i> Jobs Management
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="categories.php">
                                <i class="fas fa-tags"></i> Categories
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="reports.php">
                                <i class="fas fa-chart-bar"></i> Reports & Analytics
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 px-4 py-4">
                <h2 class="mb-4">Dashboard Overview</h2>

                <!-- Statistics Cards -->
                <div class="row mb-4">
                    <div class="col-md-3 mb-3">
                        <div class="card stat-card bg-primary text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1">Job Seekers</h6>
                                        <h2 class="mb-0"><?php echo number_format($stats['jobseekers']); ?></h2>
                                    </div>
                                    <i class="fas fa-users fa-3x opacity-50"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card stat-card bg-success text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1">Employers</h6>
                                        <h2 class="mb-0"><?php echo number_format($stats['employers']); ?></h2>
                                    </div>
                                    <i class="fas fa-building fa-3x opacity-50"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card stat-card bg-warning text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1">Total Jobs</h6>
                                        <h2 class="mb-0"><?php echo number_format($stats['total_jobs']); ?></h2>
                                        <small><?php echo $stats['active_jobs']; ?> active</small>
                                    </div>
                                    <i class="fas fa-briefcase fa-3x opacity-50"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card stat-card bg-info text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1">Applications</h6>
                                        <h2 class="mb-0"><?php echo number_format($stats['total_applications']); ?></h2>
                                        <small><?php echo $stats['pending_applications']; ?> pending</small>
                                    </div>
                                    <i class="fas fa-file-alt fa-3x opacity-50"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Additional Stats -->
                <div class="row mb-4">
                    <div class="col-md-4 mb-3">
                        <div class="card stat-card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-muted">Total Companies</h6>
                                        <h3 class="mb-0"><?php echo number_format($stats['companies']); ?></h3>
                                        <small class="text-success"><?php echo $stats['verified_companies']; ?> verified</small>
                                    </div>
                                    <i class="fas fa-building fa-2x text-success"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="card stat-card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-muted">Total Job Views</h6>
                                        <h3 class="mb-0"><?php echo number_format($stats['total_views']); ?></h3>
                                        <small class="text-muted">All time</small>
                                    </div>
                                    <i class="fas fa-eye fa-2x text-info"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="card stat-card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-muted">Pending Verifications</h6>
                                        <h3 class="mb-0"><?php echo $stats['companies'] - $stats['verified_companies']; ?></h3>
                                        <small class="text-warning">Companies awaiting</small>
                                    </div>
                                    <i class="fas fa-exclamation-triangle fa-2x text-warning"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <!-- Charts -->
                    <div class="col-lg-6 mb-4">
                        <div class="card">
                            <div class="card-header bg-white">
                                <h5 class="mb-0"><i class="fas fa-chart-pie"></i> Jobs by Status</h5>
                            </div>
                            <div class="card-body">
                                <canvas id="jobStatusChart"></canvas>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-6 mb-4">
                        <div class="card">
                            <div class="card-header bg-white">
                                <h5 class="mb-0"><i class="fas fa-chart-pie"></i> Applications by Status</h5>
                            </div>
                            <div class="card-body">
                                <canvas id="appStatusChart"></canvas>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 mb-4">
                        <div class="card">
                            <div class="card-header bg-white">
                                <h5 class="mb-0"><i class="fas fa-chart-line"></i> User Growth (Last 6 Months)</h5>
                            </div>
                            <div class="card-body">
                                <canvas id="userGrowthChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Activity -->
                <div class="row">
                    <!-- Recent Users -->
                    <div class="col-lg-4 mb-4">
                        <div class="card">
                            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                                <h5 class="mb-0"><i class="fas fa-user-plus"></i> Recent Users</h5>
                                <a href="users.php" class="btn btn-sm btn-outline-primary">View All</a>
                            </div>
                            <div class="card-body">
                                <?php if(mysqli_num_rows($recent_users) > 0): ?>
                                    <div class="list-group list-group-flush">
                                        <?php while($user = mysqli_fetch_assoc($recent_users)): ?>
                                        <div class="list-group-item px-0">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <strong><?php echo htmlspecialchars($user['name']); ?></strong>
                                                    <br>
                                                    <small class="text-muted"><?php echo htmlspecialchars($user['email']); ?></small>
                                                    <br>
                                                    <span class="badge bg-<?php echo $user['role'] == 'employer' ? 'success' : 'primary'; ?>">
                                                        <?php echo ucfirst($user['role']); ?>
                                                    </span>
                                                </div>
                                                <small class="text-muted"><?php echo time_ago($user['created_at']); ?></small>
                                            </div>
                                        </div>
                                        <?php endwhile; ?>
                                    </div>
                                <?php else: ?>
                                    <p class="text-muted text-center">No recent users</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Jobs -->
                    <div class="col-lg-4 mb-4">
                        <div class="card">
                            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                                <h5 class="mb-0"><i class="fas fa-briefcase"></i> Recent Jobs</h5>
                                <a href="jobs.php" class="btn btn-sm btn-outline-primary">View All</a>
                            </div>
                            <div class="card-body">
                                <?php if(mysqli_num_rows($recent_jobs) > 0): ?>
                                    <div class="list-group list-group-flush">
                                        <?php while($job = mysqli_fetch_assoc($recent_jobs)): ?>
                                        <div class="list-group-item px-0">
                                            <strong><?php echo htmlspecialchars($job['title']); ?></strong>
                                            <br>
                                            <small class="text-muted"><?php echo htmlspecialchars($job['company_name']); ?></small>
                                            <br>
                                            <span class="badge bg-<?php echo $job['status'] == 'active' ? 'success' : 'secondary'; ?>">
                                                <?php echo ucfirst($job['status']); ?>
                                            </span>
                                            <small class="text-muted ms-2"><?php echo time_ago($job['created_at']); ?></small>
                                        </div>
                                        <?php endwhile; ?>
                                    </div>
                                <?php else: ?>
                                    <p class="text-muted text-center">No recent jobs</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Pending Verifications -->
                    <div class="col-lg-4 mb-4">
                        <div class="card">
                            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                                <h5 class="mb-0"><i class="fas fa-exclamation-circle"></i> Pending Verifications</h5>
                                <a href="companies.php?filter=unverified" class="btn btn-sm btn-outline-warning">View All</a>
                            </div>
                            <div class="card-body">
                                <?php if(mysqli_num_rows($pending_companies) > 0): ?>
                                    <div class="list-group list-group-flush">
                                        <?php while($company = mysqli_fetch_assoc($pending_companies)): ?>
                                        <div class="list-group-item px-0">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div>
                                                    <strong><?php echo htmlspecialchars($company['name']); ?></strong>
                                                    <br>
                                                    <small class="text-muted"><?php echo htmlspecialchars($company['email']); ?></small>
                                                    <br>
                                                    <small class="text-muted"><?php echo time_ago($company['created_at']); ?></small>
                                                </div>
                                                <a href="companies.php?verify=<?php echo $company['id']; ?>" class="btn btn-sm btn-success">
                                                    Verify
                                                </a>
                                            </div>
                                        </div>
                                        <?php endwhile; ?>
                                    </div>
                                <?php else: ?>
                                    <p class="text-muted text-center">No pending verifications</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Job Status Chart
        const jobCtx = document.getElementById('jobStatusChart');
        new Chart(jobCtx, {
            type: 'doughnut',
            data: {
                labels: [<?php 
                    $labels = [];
                    mysqli_data_seek($job_status_stats, 0);
                    while($s = mysqli_fetch_assoc($job_status_stats)) {
                        $labels[] = "'" . ucfirst($s['status']) . "'";
                    }
                    echo implode(',', $labels);
                ?>],
                datasets: [{
                    data: [<?php 
                        mysqli_data_seek($job_status_stats, 0);
                        $data = [];
                        while($s = mysqli_fetch_assoc($job_status_stats)) {
                            $data[] = $s['count'];
                        }
                        echo implode(',', $data);
                    ?>],
                    backgroundColor: ['#1cc88a', '#f6c23e', '#858796', '#e74a3b']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom' } }
            }
        });

        // Application Status Chart
        const appCtx = document.getElementById('appStatusChart');
        new Chart(appCtx, {
            type: 'doughnut',
            data: {
                labels: [<?php 
                    $labels = [];
                    mysqli_data_seek($app_status_stats, 0);
                    while($s = mysqli_fetch_assoc($app_status_stats)) {
                        $labels[] = "'" . ucfirst($s['status']) . "'";
                    }
                    echo implode(',', $labels);
                ?>],
                datasets: [{
                    data: [<?php 
                        mysqli_data_seek($app_status_stats, 0);
                        $data = [];
                        while($s = mysqli_fetch_assoc($app_status_stats)) {
                            $data[] = $s['count'];
                        }
                        echo implode(',', $data);
                    ?>],
                    backgroundColor: ['#f6c23e', '#36b9cc', '#4e73df', '#1cc88a', '#28a745', '#e74a3b', '#858796']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom' } }
            }
        });

        // User Growth Chart
        const growthCtx = document.getElementById('userGrowthChart');
        new Chart(growthCtx, {
            type: 'line',
            data: {
                labels: [<?php 
                    $labels = [];
                    mysqli_data_seek($monthly_stats, 0);
                    while($s = mysqli_fetch_assoc($monthly_stats)) {
                        $labels[] = "'" . date('M Y', strtotime($s['month'])) . "'";
                    }
                    echo implode(',', $labels);
                ?>],
                datasets: [{
                    label: 'New Users',
                    data: [<?php 
                        mysqli_data_seek($monthly_stats, 0);
                        $data = [];
                        while($s = mysqli_fetch_assoc($monthly_stats)) {
                            $data[] = $s['count'];
                        }
                        echo implode(',', $data);
                    ?>],
                    borderColor: '#4e73df',
                    backgroundColor: 'rgba(78, 115, 223, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });
    </script>
</body>
</html>