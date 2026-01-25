<?php
session_start();
require_once '../config/database.php';
require_once '../config/functions.php';
require_once '../config/constants.php';

check_role('employer');

$user_id = $_SESSION['user_id'];
$company = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM companies WHERE user_id = $user_id"));
$company_id = $company['id'];

// Get filter
$job_filter = isset($_GET['job_id']) ? (int)$_GET['job_id'] : 0;
$status_filter = isset($_GET['status']) ? sanitize_input($_GET['status']) : '';
$search = isset($_GET['search']) ? sanitize_input($_GET['search']) : '';

// Build query
$where = "WHERE j.company_id = $company_id";

if($job_filter > 0) {
    $where .= " AND a.job_id = $job_filter";
}

if(!empty($status_filter)) {
    $where .= " AND a.status = '$status_filter'";
}

if(!empty($search)) {
    $where .= " AND (u.name LIKE '%$search%' OR u.email LIKE '%$search%')";
}

$query = "SELECT a.*, j.title as job_title, u.name, u.email, u.phone
          FROM applications a
          JOIN jobs j ON a.job_id = j.id
          JOIN users u ON a.user_id = u.id
          $where
          ORDER BY a.applied_at DESC";

$pagination = paginate($query, 15);
$applicants = $pagination['result'];

// Get jobs for filter
$jobs_query = "SELECT id, title FROM jobs WHERE company_id = $company_id ORDER BY title";
$jobs_list = mysqli_query($conn, $jobs_query);

// Get statistics
$stats_query = "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN a.status = 'pending' THEN 1 ELSE 0 END) as pending,
    SUM(CASE WHEN a.status = 'reviewed' THEN 1 ELSE 0 END) as reviewed,
    SUM(CASE WHEN a.status = 'shortlisted' THEN 1 ELSE 0 END) as shortlisted,
    SUM(CASE WHEN a.status = 'interview' THEN 1 ELSE 0 END) as interview,
    SUM(CASE WHEN a.status = 'offered' THEN 1 ELSE 0 END) as offered,
    SUM(CASE WHEN a.status = 'rejected' THEN 1 ELSE 0 END) as rejected
    FROM applications a
    JOIN jobs j ON a.job_id = j.id
    WHERE j.company_id = $company_id";

if($job_filter > 0) {
    $stats_query .= " AND a.job_id = $job_filter";
}

$stats = mysqli_fetch_assoc(mysqli_query($conn, $stats_query));

// Handle status update
if(isset($_POST['update_status'])) {
    $app_id = (int)$_POST['application_id'];
    $new_status = sanitize_input($_POST['new_status']);
    $notes = sanitize_input($_POST['notes']);
    
    $update = "UPDATE applications SET status = '$new_status', notes = '$notes' 
               WHERE id = $app_id";
    
    if(mysqli_query($conn, $update)) {
        // Send notification to applicant
        $app_data = mysqli_fetch_assoc(mysqli_query($conn, 
            "SELECT a.user_id, j.title FROM applications a JOIN jobs j ON a.job_id = j.id WHERE a.id = $app_id"));
        
        send_notification(
            $app_data['user_id'],
            'Application Status Updated',
            'Your application for ' . $app_data['title'] . ' has been updated to: ' . ucfirst($new_status),
            'info',
            '../jobseeker/my_applications.php'
        );
        
        $_SESSION['success'] = 'Application status updated successfully!';
    } else {
        $_SESSION['error'] = 'Failed to update status';
    }
    
    header("Location: applicants.php?job_id=$job_filter&status=$status_filter&search=$search");
    exit();
}

$page_title = 'Applicants';
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
        .applicant-card {
            transition: all 0.3s;
            border-left: 4px solid #e9ecef;
        }
        .applicant-card:hover {
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transform: translateY(-2px);
        }
        .applicant-card.pending { border-left-color: #f6c23e; }
        .applicant-card.reviewed { border-left-color: #36b9cc; }
        .applicant-card.shortlisted { border-left-color: #4e73df; }
        .applicant-card.interview { border-left-color: #1cc88a; }
        .applicant-card.offered { border-left-color: #28a745; }
        .applicant-card.rejected { border-left-color: #e74a3b; }
        .stat-box {
            padding: 15px;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s;
        }
        .stat-box:hover {
            transform: translateY(-3px);
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
            <div class="ms-auto d-flex gap-2">
                <a href="dashboard.php" class="btn btn-outline-light btn-sm">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
                <a href="my_jobs.php" class="btn btn-outline-light btn-sm">
                    <i class="fas fa-list"></i> My Jobs
                </a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <section class="py-4">
        <div class="container-fluid">
            <?php if(isset($_SESSION['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="fas fa-check-circle"></i> <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <?php if(isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <h2 class="mb-4"><i class="fas fa-users"></i> Manage Applicants</h2>

            <!-- Statistics -->
            <div class="row mb-4">
                <div class="col-md-2">
                    <a href="?job_id=<?php echo $job_filter; ?>" class="text-decoration-none">
                        <div class="stat-box bg-primary text-white text-center">
                            <h4 class="mb-0"><?php echo $stats['total']; ?></h4>
                            <small>Total</small>
                        </div>
                    </a>
                </div>
                <div class="col-md-2">
                    <a href="?status=pending&job_id=<?php echo $job_filter; ?>" class="text-decoration-none">
                        <div class="stat-box bg-warning text-white text-center">
                            <h4 class="mb-0"><?php echo $stats['pending']; ?></h4>
                            <small>Pending</small>
                        </div>
                    </a>
                </div>
                <div class="col-md-2">
                    <a href="?status=reviewed&job_id=<?php echo $job_filter; ?>" class="text-decoration-none">
                        <div class="stat-box bg-info text-white text-center">
                            <h4 class="mb-0"><?php echo $stats['reviewed']; ?></h4>
                            <small>Reviewed</small>
                        </div>
                    </a>
                </div>
                <div class="col-md-2">
                    <a href="?status=shortlisted&job_id=<?php echo $job_filter; ?>" class="text-decoration-none">
                        <div class="stat-box bg-primary bg-opacity-75 text-white text-center">
                            <h4 class="mb-0"><?php echo $stats['shortlisted']; ?></h4>
                            <small>Shortlisted</small>
                        </div>
                    </a>
                </div>
                <div class="col-md-2">
                    <a href="?status=interview&job_id=<?php echo $job_filter; ?>" class="text-decoration-none">
                        <div class="stat-box bg-success text-white text-center">
                            <h4 class="mb-0"><?php echo $stats['interview']; ?></h4>
                            <small>Interview</small>
                        </div>
                    </a>
                </div>
                <div class="col-md-2">
                    <a href="?status=offered&job_id=<?php echo $job_filter; ?>" class="text-decoration-none">
                        <div class="stat-box bg-success bg-opacity-50 text-white text-center">
                            <h4 class="mb-0"><?php echo $stats['offered']; ?></h4>
                            <small>Offered</small>
                        </div>
                    </a>
                </div>
            </div>

            <!-- Filters -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" action="">
                        <div class="row">
                            <div class="col-md-4 mb-2">
                                <select class="form-select" name="job_id" onchange="this.form.submit()">
                                    <option value="0">All Jobs</option>
                                    <?php 
                                    mysqli_data_seek($jobs_list, 0);
                                    while($job = mysqli_fetch_assoc($jobs_list)): 
                                    ?>
                                        <option value="<?php echo $job['id']; ?>" <?php echo $job_filter == $job['id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($job['title']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="col-md-3 mb-2">
                                <select class="form-select" name="status">
                                    <option value="">All Status</option>
                                    <?php foreach(APPLICATION_STATUS as $key => $val): ?>
                                        <option value="<?php echo $key; ?>" <?php echo $status_filter == $key ? 'selected' : ''; ?>>
                                            <?php echo $val; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3 mb-2">
                                <input type="text" class="form-control" name="search" 
                                       placeholder="Search by name or email..." value="<?php echo htmlspecialchars($search); ?>">
                            </div>
                            <div class="col-md-2 mb-2">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-search"></i> Filter
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Applicants List -->
            <?php if(mysqli_num_rows($applicants) > 0): ?>
                <?php while($app = mysqli_fetch_assoc($applicants)): ?>
                <div class="card applicant-card <?php echo $app['status']; ?> mb-3">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-4">
                                <h5 class="mb-1"><?php echo htmlspecialchars($app['name']); ?></h5>
                                <p class="text-muted mb-1">
                                    <i class="fas fa-envelope"></i> <?php echo htmlspecialchars($app['email']); ?>
                                </p>
                                <?php if($app['phone']): ?>
                                <p class="text-muted mb-0">
                                    <i class="fas fa-phone"></i> <?php echo htmlspecialchars($app['phone']); ?>
                                </p>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-3">
                                <small class="text-muted d-block">Applied for</small>
                                <strong><?php echo htmlspecialchars($app['job_title']); ?></strong>
                                <br>
                                <small class="text-muted">
                                    <i class="far fa-clock"></i> <?php echo time_ago($app['applied_at']); ?>
                                </small>
                            </div>
                            <div class="col-md-2">
                                <span class="badge bg-<?php echo get_status_badge($app['status']); ?> d-block mb-2">
                                    <?php echo ucfirst($app['status']); ?>
                                </span>
                                <?php if($app['cv_path']): ?>
                                    <a href="<?php echo CV_URL . $app['cv_path']; ?>" target="_blank" 
                                       class="btn btn-sm btn-outline-primary w-100">
                                        <i class="fas fa-file-pdf"></i> View CV
                                    </a>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-3 text-end">
                                <button class="btn btn-sm btn-primary mb-2 w-100" 
                                        data-bs-toggle="modal" data-bs-target="#detailModal<?php echo $app['id']; ?>">
                                    <i class="fas fa-eye"></i> View Full Application
                                </button>
                                <button class="btn btn-sm btn-success w-100" 
                                        data-bs-toggle="modal" data-bs-target="#statusModal<?php echo $app['id']; ?>">
                                    <i class="fas fa-edit"></i> Update Status
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Detail Modal -->
                <div class="modal fade" id="detailModal<?php echo $app['id']; ?>" tabindex="-1">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Application Details - <?php echo htmlspecialchars($app['name']); ?></h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <h6 class="text-muted">Applicant Name</h6>
                                        <p><?php echo htmlspecialchars($app['name']); ?></p>
                                    </div>
                                    <div class="col-md-6">
                                        <h6 class="text-muted">Email</h6>
                                        <p><?php echo htmlspecialchars($app['email']); ?></p>
                                    </div>
                                    <div class="col-md-6">
                                        <h6 class="text-muted">Phone</h6>
                                        <p><?php echo htmlspecialchars($app['phone'] ?? 'Not provided'); ?></p>
                                    </div>
                                    <div class="col-md-6">
                                        <h6 class="text-muted">Applied Date</h6>
                                        <p><?php echo date('d F Y, H:i', strtotime($app['applied_at'])); ?></p>
                                    </div>
                                </div>

                                <?php if($app['cover_letter']): ?>
                                <div class="mb-3">
                                    <h6 class="text-muted">Cover Letter</h6>
                                    <div class="p-3 bg-light rounded">
                                        <?php echo nl2br(htmlspecialchars($app['cover_letter'])); ?>
                                    </div>
                                </div>
                                <?php endif; ?>

                                <?php if($app['notes']): ?>
                                <div class="mb-3">
                                    <h6 class="text-muted">Your Notes</h6>
                                    <div class="alert alert-info">
                                        <?php echo nl2br(htmlspecialchars($app['notes'])); ?>
                                    </div>
                                </div>
                                <?php endif; ?>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                <?php if($app['cv_path']): ?>
                                    <a href="<?php echo CV_URL . $app['cv_path']; ?>" target="_blank" class="btn btn-primary">
                                        <i class="fas fa-file-pdf"></i> Download CV
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Status Update Modal -->
                <div class="modal fade" id="statusModal<?php echo $app['id']; ?>" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <form method="POST">
                                <input type="hidden" name="application_id" value="<?php echo $app['id']; ?>">
                                <div class="modal-header">
                                    <h5 class="modal-title">Update Application Status</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="mb-3">
                                        <label class="form-label">Applicant</label>
                                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($app['name']); ?>" readonly>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">New Status</label>
                                        <select class="form-select" name="new_status" required>
                                            <?php foreach(APPLICATION_STATUS as $key => $val): ?>
                                                <option value="<?php echo $key; ?>" <?php echo $app['status'] == $key ? 'selected' : ''; ?>>
                                                    <?php echo $val; ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Notes (Optional)</label>
                                        <textarea class="form-control" name="notes" rows="3"><?php echo htmlspecialchars($app['notes'] ?? ''); ?></textarea>
                                        <small class="text-muted">Add any notes about this applicant</small>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" name="update_status" class="btn btn-primary">Update Status</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>

                <!-- Pagination -->
                <div class="mt-4">
                    <?php 
                    $base_url = "?job_id=$job_filter&status=$status_filter&search=$search";
                    echo render_pagination($pagination['current_page'], $pagination['total_pages'], $base_url); 
                    ?>
                </div>

            <?php else: ?>
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-users fa-4x text-muted mb-3"></i>
                        <h4>No Applicants Found</h4>
                        <p class="text-muted">
                            <?php if($job_filter || $status_filter || $search): ?>
                                Try adjusting your filters
                            <?php else: ?>
                                No applications received yet
                            <?php endif; ?>
                        </p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>