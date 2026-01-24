<?php
session_start();
require_once '../config/database.php';
require_once '../config/functions.php';
require_once '../config/constants.php';

check_role('jobseeker');

$user_id = $_SESSION['user_id'];

// Get statistics
$stats_query = "SELECT 
    (SELECT COUNT(*) FROM applications WHERE user_id = $user_id) as total_applications,
    (SELECT COUNT(*) FROM applications WHERE user_id = $user_id AND status = 'pending') as pending_apps,
    (SELECT COUNT(*) FROM applications WHERE user_id = $user_id AND status = 'interview') as interviews,
    (SELECT COUNT(*) FROM saved_jobs WHERE user_id = $user_id) as saved_jobs";
$stats = mysqli_fetch_assoc(mysqli_query($conn, $stats_query));

// Get recent applications
$apps_query = "SELECT a.*, j.title, j.type, j.location, c.name as company_name, c.logo
               FROM applications a
               JOIN jobs j ON a.job_id = j.id
               JOIN companies c ON j.company_id = c.id
               WHERE a.user_id = $user_id
               ORDER BY a.applied_at DESC
               LIMIT 5";
$apps_result = mysqli_query($conn, $apps_query);

// Get recommended jobs based on user profile/applications
$recommended_query = "SELECT j.*, c.name as company_name, c.logo, cat.name as category_name
                      FROM jobs j
                      LEFT JOIN companies c ON j.company_id = c.id
                      LEFT JOIN job_categories cat ON j.category_id = cat.id
                      WHERE j.status = 'active'
                      AND j.id NOT IN (SELECT job_id FROM applications WHERE user_id = $user_id)
                      ORDER BY j.created_at DESC
                      LIMIT 6";
$recommended_result = mysqli_query($conn, $recommended_query);

// Get saved jobs
$saved_query = "SELECT j.*, c.name as company_name, c.logo
                FROM saved_jobs s
                JOIN jobs j ON s.job_id = j.id
                JOIN companies c ON j.company_id = c.id
                WHERE s.user_id = $user_id AND j.status = 'active'
                ORDER BY s.created_at DESC
                LIMIT 4";
$saved_result = mysqli_query($conn, $saved_query);

// Get notifications
$notif_query = "SELECT * FROM notifications WHERE user_id = $user_id ORDER BY created_at DESC LIMIT 5";
$notif_result = mysqli_query($conn, $notif_query);
$unread_count = get_unread_notifications_count($user_id);

$page_title = 'Dashboard';
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
            background: #f8f9fc;
            min-height: calc(100vh - 56px);
            border-right: 1px solid #e3e6f0;
        }
        .stat-card {
            border-left: 4px solid;
            transition: transform 0.2s;
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
        .stat-card.primary { border-color: #4e73df; }
        .stat-card.success { border-color: #1cc88a; }
        .stat-card.warning { border-color: #f6c23e; }
        .stat-card.info { border-color: #36b9cc; }
        .company-logo-sm {
            width: 50px;
            height: 50px;
            object-fit: contain;
        }
        .notification-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background: #e74a3b;
            color: white;
            border-radius: 50%;
            padding: 2px 6px;
            font-size: 10px;
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
                    <li class="nav-item">
                        <a class="nav-link" href="../public/jobs.php">
                            <i class="fas fa-search"></i> Browse Jobs
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle position-relative" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-bell"></i>
                            <?php if($unread_count > 0): ?>
                                <span class="notification-badge"><?php echo $unread_count; ?></span>
                            <?php endif; ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" style="width: 300px;">
                            <li class="dropdown-header">Notifications (<?php echo $unread_count; ?> unread)</li>
                            <?php if(mysqli_num_rows($notif_result) > 0): ?>
                                <?php while($notif = mysqli_fetch_assoc($notif_result)): ?>
                                    <li>
                                        <a class="dropdown-item <?php echo $notif['is_read'] ? '' : 'bg-light'; ?>" href="<?php echo $notif['link'] ?? '#'; ?>">
                                            <strong><?php echo htmlspecialchars($notif['title']); ?></strong>
                                            <p class="mb-0 small text-muted"><?php echo htmlspecialchars($notif['message']); ?></p>
                                            <small class="text-muted"><?php echo time_ago($notif['created_at']); ?></small>
                                        </a>
                                    </li>
                                    <li><hr class="dropdown-divider"></li>
                                <?php endwhile; ?>
                                <li><a class="dropdown-item text-center" href="notifications.php">View All</a></li>
                            <?php else: ?>
                                <li class="dropdown-item text-center text-muted">No notifications</li>
                            <?php endif; ?>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user"></i> <?php echo htmlspecialchars($_SESSION['name']); ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="profile.php"><i class="fas fa-user-edit"></i> Profile</a></li>
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
                    <h6 class="text-muted mb-3">MENU</h6>
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link active" href="dashboard.php">
                                <i class="fas fa-tachometer-alt"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="profile.php">
                                <i class="fas fa-user"></i> My Profile
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="my_applications.php">
                                <i class="fas fa-file-alt"></i> My Applications
                                <span class="badge bg-primary ms-2"><?php echo $stats['total_applications']; ?></span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="saved_jobs.php">
                                <i class="fas fa-heart"></i> Saved Jobs
                                <span class="badge bg-danger ms-2"><?php echo $stats['saved_jobs']; ?></span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="../public/jobs.php">
                                <i class="fas fa-search"></i> Browse Jobs
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 px-4 py-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h2>Welcome back, <?php echo htmlspecialchars($_SESSION['name']); ?>! 👋</h2>
                        <p class="text-muted">Here's what's happening with your job search</p>
                    </div>
                    <a href="../public/jobs.php" class="btn btn-primary">
                        <i class="fas fa-search"></i> Find Jobs
                    </a>
                </div>

                <!-- Statistics Cards -->
                <div class="row mb-4">
                    <div class="col-md-3 mb-3">
                        <div class="card stat-card primary">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-muted mb-1">Total Applications</h6>
                                        <h3 class="mb-0"><?php echo $stats['total_applications']; ?></h3>
                                    </div>
                                    <i class="fas fa-file-alt fa-2x text-primary"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card stat-card warning">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-muted mb-1">Pending</h6>
                                        <h3 class="mb-0"><?php echo $stats['pending_apps']; ?></h3>
                                    </div>
                                    <i class="fas fa-clock fa-2x text-warning"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card stat-card success">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-muted mb-1">Interviews</h6>
                                        <h3 class="mb-0"><?php echo $stats['interviews']; ?></h3>
                                    </div>
                                    <i class="fas fa-user-tie fa-2x text-success"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card stat-card info">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-muted mb-1">Saved Jobs</h6>
                                        <h3 class="mb-0"><?php echo $stats['saved_jobs']; ?></h3>
                                    </div>
                                    <i class="fas fa-heart fa-2x text-info"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <!-- Recent Applications -->
                    <div class="col-lg-8 mb-4">
                        <div class="card">
                            <div class="card-header bg-white">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0"><i class="fas fa-history"></i> Recent Applications</h5>
                                    <a href="my_applications.php" class="btn btn-sm btn-outline-primary">View All</a>
                                </div>
                            </div>
                            <div class="card-body">
                                <?php if(mysqli_num_rows($apps_result) > 0): ?>
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Job</th>
                                                    <th>Company</th>
                                                    <th>Applied</th>
                                                    <th>Status</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php while($app = mysqli_fetch_assoc($apps_result)): ?>
                                                <tr>
                                                    <td>
                                                        <strong><?php echo htmlspecialchars($app['title']); ?></strong>
                                                        <br><small class="text-muted"><?php echo ucfirst($app['type']); ?></small>
                                                    </td>
                                                    <td><?php echo htmlspecialchars($app['company_name']); ?></td>
                                                    <td><?php echo time_ago($app['applied_at']); ?></td>
                                                    <td>
                                                        <span class="badge bg-<?php echo get_status_badge($app['status']); ?>">
                                                            <?php echo ucfirst($app['status']); ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <a href="../public/job_detail.php?id=<?php echo $app['job_id']; ?>" 
                                                           class="btn btn-sm btn-outline-primary">
                                                            View
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
                                        <p class="text-muted">You haven't applied to any jobs yet</p>
                                        <a href="../public/jobs.php" class="btn btn-primary">
                                            <i class="fas fa-search"></i> Browse Jobs
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Saved Jobs -->
                    <div class="col-lg-4 mb-4">
                        <div class="card">
                            <div class="card-header bg-white">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0"><i class="fas fa-heart"></i> Saved Jobs</h5>
                                    <a href="saved_jobs.php" class="btn btn-sm btn-outline-primary">View All</a>
                                </div>
                            </div>
                            <div class="card-body">
                                <?php if(mysqli_num_rows($saved_result) > 0): ?>
                                    <?php while($saved = mysqli_fetch_assoc($saved_result)): ?>
                                    <div class="border-bottom pb-3 mb-3">
                                        <h6 class="mb-1">
                                            <a href="../public/job_detail.php?id=<?php echo $saved['id']; ?>" 
                                               class="text-decoration-none">
                                                <?php echo htmlspecialchars($saved['title']); ?>
                                            </a>
                                        </h6>
                                        <p class="mb-1 text-muted small"><?php echo htmlspecialchars($saved['company_name']); ?></p>
                                        <small class="text-muted">
                                            <i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($saved['city']); ?>
                                        </small>
                                    </div>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <div class="text-center py-3">
                                        <i class="far fa-heart fa-3x text-muted mb-2"></i>
                                        <p class="text-muted small mb-0">No saved jobs yet</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recommended Jobs -->
                <div class="card">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fas fa-star"></i> Recommended Jobs for You</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <?php while($job = mysqli_fetch_assoc($recommended_result)): ?>
                            <div class="col-md-6 col-lg-4 mb-3">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <div class="d-flex align-items-start mb-2">
                                            <?php if($job['logo']): ?>
                                                <img src="../assets/uploads/logos/<?php echo htmlspecialchars($job['logo']); ?>" 
                                                     alt="Logo" class="company-logo-sm me-3">
                                            <?php else: ?>
                                                <div class="company-logo-sm me-3 bg-light rounded d-flex align-items-center justify-content-center">
                                                    <i class="fas fa-building text-secondary"></i>
                                                </div>
                                            <?php endif; ?>
                                            <div>
                                                <h6 class="mb-0"><?php echo htmlspecialchars($job['title']); ?></h6>
                                                <small class="text-muted"><?php echo htmlspecialchars($job['company_name']); ?></small>
                                            </div>
                                        </div>
                                        <p class="small text-muted mb-2">
                                            <i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($job['city']); ?>
                                        </p>
                                        <?php if($job['salary_min']): ?>
                                        <p class="small text-success mb-2">
                                            <i class="fas fa-money-bill-wave"></i> 
                                            <?php echo format_currency($job['salary_min']); ?> - <?php echo format_currency($job['salary_max']); ?>
                                        </p>
                                        <?php endif; ?>
                                        <a href="../public/job_detail.php?id=<?php echo $job['id']; ?>" 
                                           class="btn btn-sm btn-primary w-100">
                                            View Details
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <?php endwhile; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>