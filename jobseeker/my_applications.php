<?php
session_start();
require_once '../config/database.php';
require_once '../config/functions.php';
require_once '../config/constants.php';

check_role('jobseeker');

$user_id = $_SESSION['user_id'];

// Get filter
$status_filter = isset($_GET['status']) ? sanitize_input($_GET['status']) : '';
$search = isset($_GET['search']) ? sanitize_input($_GET['search']) : '';

// Build query
$where = "WHERE a.user_id = $user_id";

if(!empty($status_filter)) {
    $where .= " AND a.status = '$status_filter'";
}

if(!empty($search)) {
    $where .= " AND (j.title LIKE '%$search%' OR c.name LIKE '%$search%')";
}

$query = "SELECT a.*, j.title, j.type, j.location, j.city, j.salary_min, j.salary_max,
          c.name as company_name, c.logo, c.city as company_city
          FROM applications a
          JOIN jobs j ON a.job_id = j.id
          JOIN companies c ON j.company_id = c.id
          $where
          ORDER BY a.applied_at DESC";

$pagination = paginate($query, 10);
$applications = $pagination['result'];

// Get statistics
$stats_query = "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
    SUM(CASE WHEN status = 'reviewed' THEN 1 ELSE 0 END) as reviewed,
    SUM(CASE WHEN status = 'shortlisted' THEN 1 ELSE 0 END) as shortlisted,
    SUM(CASE WHEN status = 'interview' THEN 1 ELSE 0 END) as interview,
    SUM(CASE WHEN status = 'offered' THEN 1 ELSE 0 END) as offered,
    SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected
    FROM applications WHERE user_id = $user_id";
$stats = mysqli_fetch_assoc(mysqli_query($conn, $stats_query));

// Handle withdraw
if(isset($_GET['withdraw'])) {
    $app_id = (int)$_GET['withdraw'];
    mysqli_query($conn, "UPDATE applications SET status = 'withdrawn' WHERE id = $app_id AND user_id = $user_id");
    $_SESSION['success'] = 'Application withdrawn successfully';
    header("Location: my_applications.php");
    exit();
}

$page_title = 'My Applications';
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
        .stat-box {
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 10px;
            cursor: pointer;
            transition: all 0.3s;
        }
        .stat-box:hover {
            transform: translateX(5px);
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .stat-box.active {
            background: #667eea;
            color: white;
        }
        .application-card {
            transition: all 0.3s;
            border-left: 4px solid #e9ecef;
        }
        .application-card:hover {
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .application-card.pending { border-left-color: #f6c23e; }
        .application-card.reviewed { border-left-color: #36b9cc; }
        .application-card.shortlisted { border-left-color: #4e73df; }
        .application-card.interview { border-left-color: #1cc88a; }
        .application-card.offered { border-left-color: #28a745; }
        .application-card.rejected { border-left-color: #e74a3b; }
        .company-logo {
            width: 60px;
            height: 60px;
            object-fit: contain;
        }
        .timeline {
            position: relative;
            padding-left: 30px;
        }
        .timeline::before {
            content: '';
            position: absolute;
            left: 8px;
            top: 0;
            bottom: 0;
            width: 2px;
            background: #e9ecef;
        }
        .timeline-item {
            position: relative;
            padding-bottom: 20px;
        }
        .timeline-item::before {
            content: '';
            position: absolute;
            left: -26px;
            top: 5px;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: #667eea;
        }
        .timeline-item.current::before {
            background: #1cc88a;
            box-shadow: 0 0 0 4px rgba(28, 200, 138, 0.2);
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
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user"></i> <?php echo htmlspecialchars($_SESSION['name']); ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="profile.php"><i class="fas fa-user-edit"></i> Profile</a></li>
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
                    <h6 class="text-muted mb-3">FILTER BY STATUS</h6>
                    
                    <a href="my_applications.php" class="text-decoration-none">
                        <div class="stat-box bg-light <?php echo empty($status_filter) ? 'active' : ''; ?>">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <small>All Applications</small>
                                    <h5 class="mb-0"><?php echo $stats['total']; ?></h5>
                                </div>
                                <i class="fas fa-list fa-2x opacity-50"></i>
                            </div>
                        </div>
                    </a>

                    <a href="?status=pending" class="text-decoration-none">
                        <div class="stat-box bg-warning bg-opacity-10 <?php echo $status_filter == 'pending' ? 'active' : ''; ?>">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <small>Pending</small>
                                    <h5 class="mb-0"><?php echo $stats['pending']; ?></h5>
                                </div>
                                <i class="fas fa-clock fa-2x opacity-50"></i>
                            </div>
                        </div>
                    </a>

                    <a href="?status=reviewed" class="text-decoration-none">
                        <div class="stat-box bg-info bg-opacity-10 <?php echo $status_filter == 'reviewed' ? 'active' : ''; ?>">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <small>Reviewed</small>
                                    <h5 class="mb-0"><?php echo $stats['reviewed']; ?></h5>
                                </div>
                                <i class="fas fa-eye fa-2x opacity-50"></i>
                            </div>
                        </div>
                    </a>

                    <a href="?status=shortlisted" class="text-decoration-none">
                        <div class="stat-box bg-primary bg-opacity-10 <?php echo $status_filter == 'shortlisted' ? 'active' : ''; ?>">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <small>Shortlisted</small>
                                    <h5 class="mb-0"><?php echo $stats['shortlisted']; ?></h5>
                                </div>
                                <i class="fas fa-star fa-2x opacity-50"></i>
                            </div>
                        </div>
                    </a>

                    <a href="?status=interview" class="text-decoration-none">
                        <div class="stat-box bg-success bg-opacity-10 <?php echo $status_filter == 'interview' ? 'active' : ''; ?>">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <small>Interview</small>
                                    <h5 class="mb-0"><?php echo $stats['interview']; ?></h5>
                                </div>
                                <i class="fas fa-user-tie fa-2x opacity-50"></i>
                            </div>
                        </div>
                    </a>

                    <a href="?status=offered" class="text-decoration-none">
                        <div class="stat-box bg-success bg-opacity-25 <?php echo $status_filter == 'offered' ? 'active' : ''; ?>">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <small>Offered</small>
                                    <h5 class="mb-0"><?php echo $stats['offered']; ?></h5>
                                </div>
                                <i class="fas fa-trophy fa-2x opacity-50"></i>
                            </div>
                        </div>
                    </a>

                    <a href="?status=rejected" class="text-decoration-none">
                        <div class="stat-box bg-danger bg-opacity-10 <?php echo $status_filter == 'rejected' ? 'active' : ''; ?>">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <small>Rejected</small>
                                    <h5 class="mb-0"><?php echo $stats['rejected']; ?></h5>
                                </div>
                                <i class="fas fa-times-circle fa-2x opacity-50"></i>
                            </div>
                        </div>
                    </a>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 px-4 py-4">
                <?php if(isset($_SESSION['success'])): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <i class="fas fa-check-circle"></i> <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h2><i class="fas fa-file-alt"></i> My Applications</h2>
                        <p class="text-muted">Track and manage your job applications</p>
                    </div>
                    <a href="../public/jobs.php" class="btn btn-primary">
                        <i class="fas fa-search"></i> Find More Jobs
                    </a>
                </div>

                <!-- Search -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="GET" action="">
                            <?php if($status_filter): ?>
                                <input type="hidden" name="status" value="<?php echo $status_filter; ?>">
                            <?php endif; ?>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-search"></i></span>
                                <input type="text" class="form-control" name="search" 
                                       placeholder="Search by job title or company name..." 
                                       value="<?php echo htmlspecialchars($search); ?>">
                                <button type="submit" class="btn btn-primary">Search</button>
                                <?php if($search || $status_filter): ?>
                                    <a href="my_applications.php" class="btn btn-outline-secondary">Clear</a>
                                <?php endif; ?>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Applications List -->
                <?php if(mysqli_num_rows($applications) > 0): ?>
                    <?php while($app = mysqli_fetch_assoc($applications)): ?>
                    <div class="card application-card <?php echo $app['status']; ?> mb-3">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-2 text-center mb-3 mb-md-0">
                                    <?php if($app['logo']): ?>
                                        <img src="../assets/uploads/logos/<?php echo htmlspecialchars($app['logo']); ?>" 
                                             alt="Logo" class="company-logo">
                                    <?php else: ?>
                                        <div class="company-logo bg-light d-inline-flex align-items-center justify-content-center rounded">
                                            <i class="fas fa-building fa-2x text-secondary"></i>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="col-md-7">
                                    <h5 class="mb-1">
                                        <a href="../public/job_detail.php?id=<?php echo $app['job_id']; ?>" class="text-decoration-none">
                                            <?php echo htmlspecialchars($app['title']); ?>
                                        </a>
                                    </h5>
                                    <h6 class="text-primary mb-2"><?php echo htmlspecialchars($app['company_name']); ?></h6>
                                    <p class="text-muted mb-2">
                                        <i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($app['city']); ?>
                                        <span class="ms-3"><i class="fas fa-briefcase"></i> <?php echo ucfirst(str_replace('-', ' ', $app['type'])); ?></span>
                                    </p>
                                    <?php if($app['salary_min'] && $app['salary_max']): ?>
                                    <p class="text-success mb-2">
                                        <i class="fas fa-money-bill-wave"></i>
                                        <?php echo format_currency($app['salary_min']); ?> - <?php echo format_currency($app['salary_max']); ?>
                                    </p>
                                    <?php endif; ?>
                                    <small class="text-muted">
                                        Applied: <?php echo date('d M Y, H:i', strtotime($app['applied_at'])); ?>
                                    </small>
                                </div>
                                <div class="col-md-3 text-md-end">
                                    <span class="badge bg-<?php echo get_status_badge($app['status']); ?> mb-2 d-inline-block">
                                        <?php echo ucfirst($app['status']); ?>
                                    </span>
                                    <div class="d-flex flex-column gap-2 mt-2">
                                        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#detailModal<?php echo $app['id']; ?>">
                                            <i class="fas fa-eye"></i> View Details
                                        </button>
                                        <?php if($app['status'] == 'pending' || $app['status'] == 'reviewed'): ?>
                                            <a href="?withdraw=<?php echo $app['id']; ?>" class="btn btn-sm btn-outline-danger" 
                                               onclick="return confirm('Withdraw this application?')">
                                                <i class="fas fa-times"></i> Withdraw
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Detail Modal -->
                    <div class="modal fade" id="detailModal<?php echo $app['id']; ?>" tabindex="-1">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Application Details</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="row mb-4">
                                        <div class="col-md-6">
                                            <h6 class="text-muted">Job Position</h6>
                                            <p><?php echo htmlspecialchars($app['title']); ?></p>
                                        </div>
                                        <div class="col-md-6">
                                            <h6 class="text-muted">Company</h6>
                                            <p><?php echo htmlspecialchars($app['company_name']); ?></p>
                                        </div>
                                        <div class="col-md-6">
                                            <h6 class="text-muted">Applied Date</h6>
                                            <p><?php echo date('d F Y, H:i', strtotime($app['applied_at'])); ?></p>
                                        </div>
                                        <div class="col-md-6">
                                            <h6 class="text-muted">Current Status</h6>
                                            <p>
                                                <span class="badge bg-<?php echo get_status_badge($app['status']); ?>">
                                                    <?php echo ucfirst($app['status']); ?>
                                                </span>
                                            </p>
                                        </div>
                                        <?php if($app['cv_path']): ?>
                                        <div class="col-md-6">
                                            <h6 class="text-muted">Submitted CV</h6>
                                            <p>
                                                <a href="<?php echo CV_URL . $app['cv_path']; ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-file-pdf"></i> View CV
                                                </a>
                                            </p>
                                        </div>
                                        <?php endif; ?>
                                    </div>

                                    <?php if($app['cover_letter']): ?>
                                    <div class="mb-4">
                                        <h6 class="text-muted">Cover Letter</h6>
                                        <div class="p-3 bg-light rounded">
                                            <?php echo nl2br(htmlspecialchars($app['cover_letter'])); ?>
                                        </div>
                                    </div>
                                    <?php endif; ?>

                                    <?php if($app['notes']): ?>
                                    <div class="mb-4">
                                        <h6 class="text-muted">Employer Notes</h6>
                                        <div class="alert alert-info">
                                            <?php echo nl2br(htmlspecialchars($app['notes'])); ?>
                                        </div>
                                    </div>
                                    <?php endif; ?>

                                    <!-- Application Timeline -->
                                    <div>
                                        <h6 class="text-muted mb-3">Application Timeline</h6>
                                        <div class="timeline">
                                            <div class="timeline-item">
                                                <small class="text-muted"><?php echo date('d M Y, H:i', strtotime($app['applied_at'])); ?></small>
                                                <p class="mb-0"><strong>Application Submitted</strong></p>
                                            </div>
                                            <?php if($app['status'] != 'pending'): ?>
                                            <div class="timeline-item <?php echo in_array($app['status'], ['reviewed', 'shortlisted', 'interview', 'offered']) ? 'current' : ''; ?>">
                                                <small class="text-muted"><?php echo date('d M Y', strtotime($app['updated_at'])); ?></small>
                                                <p class="mb-0"><strong><?php echo ucfirst($app['status']); ?></strong></p>
                                            </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                    <a href="../public/job_detail.php?id=<?php echo $app['job_id']; ?>" class="btn btn-primary">
                                        View Job Post
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endwhile; ?>

                    <!-- Pagination -->
                    <div class="mt-4">
                        <?php 
                        $base_url = "?search=$search&status=$status_filter";
                        echo render_pagination($pagination['current_page'], $pagination['total_pages'], $base_url); 
                        ?>
                    </div>

                <?php else: ?>
                    <div class="card">
                        <div class="card-body text-center py-5">
                            <i class="fas fa-inbox fa-4x text-muted mb-3"></i>
                            <h4>No Applications Found</h4>
                            <p class="text-muted">
                                <?php if($status_filter || $search): ?>
                                    Try adjusting your filters or search terms
                                <?php else: ?>
                                    You haven't applied to any jobs yet
                                <?php endif; ?>
                            </p>
                            <a href="../public/jobs.php" class="btn btn-primary">
                                <i class="fas fa-search"></i> Browse Jobs
                            </a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>