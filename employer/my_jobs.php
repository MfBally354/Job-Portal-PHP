<?php
session_start();
require_once '../config/database.php';
require_once '../config/functions.php';
require_once '../config/constants.php';

check_role('employer');

$user_id = $_SESSION['user_id'];

// Get company
$company = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM companies WHERE user_id = $user_id"));
$company_id = $company['id'];

// Get filter
$status_filter = isset($_GET['status']) ? sanitize_input($_GET['status']) : '';
$search = isset($_GET['search']) ? sanitize_input($_GET['search']) : '';

// Build query
$where = "WHERE j.company_id = $company_id";

if(!empty($status_filter)) {
    $where .= " AND j.status = '$status_filter'";
}

if(!empty($search)) {
    $where .= " AND (j.title LIKE '%$search%' OR j.location LIKE '%$search%')";
}

$query = "SELECT j.*, cat.name as category_name,
          (SELECT COUNT(*) FROM applications WHERE job_id = j.id) as applicant_count,
          (SELECT COUNT(*) FROM applications WHERE job_id = j.id AND status = 'pending') as pending_count
          FROM jobs j
          LEFT JOIN job_categories cat ON j.category_id = cat.id
          $where
          ORDER BY j.created_at DESC";

$pagination = paginate($query, 10);
$jobs = $pagination['result'];

// Get statistics
$stats = mysqli_fetch_assoc(mysqli_query($conn, "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active,
    SUM(CASE WHEN status = 'draft' THEN 1 ELSE 0 END) as draft,
    SUM(CASE WHEN status = 'closed' THEN 1 ELSE 0 END) as closed,
    SUM(views) as total_views
    FROM jobs WHERE company_id = $company_id"));

// Handle status change
if(isset($_GET['change_status'])) {
    $job_id = (int)$_GET['change_status'];
    $new_status = sanitize_input($_GET['new_status']);
    
    mysqli_query($conn, "UPDATE jobs SET status = '$new_status' WHERE id = $job_id AND company_id = $company_id");
    $_SESSION['success'] = 'Job status updated successfully!';
    header("Location: my_jobs.php");
    exit();
}

// Handle delete
if(isset($_GET['delete'])) {
    $job_id = (int)$_GET['delete'];
    
    // Delete related records first
    mysqli_query($conn, "DELETE FROM job_skills WHERE job_id = $job_id");
    mysqli_query($conn, "DELETE FROM saved_jobs WHERE job_id = $job_id");
    mysqli_query($conn, "DELETE FROM applications WHERE job_id = $job_id");
    mysqli_query($conn, "DELETE FROM job_views WHERE job_id = $job_id");
    
    // Delete job
    mysqli_query($conn, "DELETE FROM jobs WHERE id = $job_id AND company_id = $company_id");
    
    $_SESSION['success'] = 'Job deleted successfully!';
    header("Location: my_jobs.php");
    exit();
}

$page_title = 'My Jobs';
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
        .stat-card {
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 15px;
            transition: all 0.3s;
            cursor: pointer;
        }
        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .job-row {
            transition: all 0.2s;
        }
        .job-row:hover {
            background-color: #f8f9fc;
        }
        .status-badge {
            padding: 5px 12px;
            border-radius: 15px;
            font-size: 0.8rem;
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
                <a href="post_job.php" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus"></i> Post New Job
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

            <div class="row">
                <!-- Sidebar Stats -->
                <div class="col-md-3">
                    <h5 class="mb-3">Job Statistics</h5>
                    
                    <a href="my_jobs.php" class="text-decoration-none">
                        <div class="stat-card bg-primary text-white">
                            <h6>Total Jobs</h6>
                            <h2 class="mb-0"><?php echo $stats['total']; ?></h2>
                            <small>All job postings</small>
                        </div>
                    </a>

                    <a href="?status=active" class="text-decoration-none">
                        <div class="stat-card bg-success text-white">
                            <h6>Active Jobs</h6>
                            <h2 class="mb-0"><?php echo $stats['active']; ?></h2>
                            <small>Currently accepting applications</small>
                        </div>
                    </a>

                    <a href="?status=draft" class="text-decoration-none">
                        <div class="stat-card bg-warning text-white">
                            <h6>Draft Jobs</h6>
                            <h2 class="mb-0"><?php echo $stats['draft']; ?></h2>
                            <small>Not yet published</small>
                        </div>
                    </a>

                    <a href="?status=closed" class="text-decoration-none">
                        <div class="stat-card bg-secondary text-white">
                            <h6>Closed Jobs</h6>
                            <h2 class="mb-0"><?php echo $stats['closed']; ?></h2>
                            <small>No longer accepting</small>
                        </div>
                    </a>

                    <div class="stat-card bg-info text-white">
                        <h6>Total Views</h6>
                        <h2 class="mb-0"><?php echo number_format($stats['total_views']); ?></h2>
                        <small>All time views</small>
                    </div>
                </div>

                <!-- Jobs List -->
                <div class="col-md-9">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2><i class="fas fa-list"></i> My Job Postings</h2>
                        <a href="post_job.php" class="btn btn-primary">
                            <i class="fas fa-plus-circle"></i> Post New Job
                        </a>
                    </div>

                    <!-- Search & Filter -->
                    <div class="card mb-4">
                        <div class="card-body">
                            <form method="GET" action="">
                                <div class="row">
                                    <div class="col-md-6 mb-2">
                                        <input type="text" class="form-control" name="search" 
                                               placeholder="Search jobs..." value="<?php echo htmlspecialchars($search); ?>">
                                    </div>
                                    <div class="col-md-3 mb-2">
                                        <select class="form-select" name="status">
                                            <option value="">All Status</option>
                                            <option value="active" <?php echo $status_filter == 'active' ? 'selected' : ''; ?>>Active</option>
                                            <option value="draft" <?php echo $status_filter == 'draft' ? 'selected' : ''; ?>>Draft</option>
                                            <option value="closed" <?php echo $status_filter == 'closed' ? 'selected' : ''; ?>>Closed</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3 mb-2">
                                        <button type="submit" class="btn btn-primary w-100">
                                            <i class="fas fa-search"></i> Search
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Jobs Table -->
                    <?php if(mysqli_num_rows($jobs) > 0): ?>
                        <div class="card">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Job Title</th>
                                            <th>Category</th>
                                            <th>Type</th>
                                            <th>Applicants</th>
                                            <th>Views</th>
                                            <th>Status</th>
                                            <th>Posted</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while($job = mysqli_fetch_assoc($jobs)): ?>
                                        <tr class="job-row">
                                            <td>
                                                <strong><?php echo htmlspecialchars($job['title']); ?></strong>
                                                <br>
                                                <small class="text-muted">
                                                    <i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($job['city']); ?>
                                                </small>
                                            </td>
                                            <td><?php echo htmlspecialchars($job['category_name'] ?? 'Uncategorized'); ?></td>
                                            <td>
                                                <span class="badge bg-<?php echo get_job_type_badge($job['type']); ?>">
                                                    <?php echo ucfirst(str_replace('-', ' ', $job['type'])); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <a href="applicants.php?job_id=<?php echo $job['id']; ?>" class="text-decoration-none">
                                                    <strong><?php echo $job['applicant_count']; ?></strong>
                                                    <?php if($job['pending_count'] > 0): ?>
                                                        <span class="badge bg-warning"><?php echo $job['pending_count']; ?> new</span>
                                                    <?php endif; ?>
                                                </a>
                                            </td>
                                            <td>
                                                <i class="fas fa-eye"></i> <?php echo number_format($job['views']); ?>
                                            </td>
                                            <td>
                                                <?php if($job['status'] == 'active'): ?>
                                                    <span class="status-badge bg-success text-white">Active</span>
                                                <?php elseif($job['status'] == 'draft'): ?>
                                                    <span class="status-badge bg-warning text-white">Draft</span>
                                                <?php elseif($job['status'] == 'closed'): ?>
                                                    <span class="status-badge bg-secondary text-white">Closed</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <small><?php echo time_ago($job['created_at']); ?></small>
                                            </td>
                                            <td>
                                                <div class="dropdown">
                                                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" 
                                                            type="button" data-bs-toggle="dropdown">
                                                        <i class="fas fa-cog"></i>
                                                    </button>
                                                    <ul class="dropdown-menu">
                                                        <li>
                                                            <a class="dropdown-item" href="../public/job_detail.php?id=<?php echo $job['id']; ?>" target="_blank">
                                                                <i class="fas fa-eye"></i> View
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <a class="dropdown-item" href="edit_job.php?id=<?php echo $job['id']; ?>">
                                                                <i class="fas fa-edit"></i> Edit
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <a class="dropdown-item" href="applicants.php?job_id=<?php echo $job['id']; ?>">
                                                                <i class="fas fa-users"></i> View Applicants (<?php echo $job['applicant_count']; ?>)
                                                            </a>
                                                        </li>
                                                        <li><hr class="dropdown-divider"></li>
                                                        <?php if($job['status'] == 'active'): ?>
                                                            <li>
                                                                <a class="dropdown-item text-warning" href="?change_status=<?php echo $job['id']; ?>&new_status=closed">
                                                                    <i class="fas fa-pause"></i> Close Job
                                                                </a>
                                                            </li>
                                                        <?php elseif($job['status'] == 'closed' || $job['status'] == 'draft'): ?>
                                                            <li>
                                                                <a class="dropdown-item text-success" href="?change_status=<?php echo $job['id']; ?>&new_status=active">
                                                                    <i class="fas fa-play"></i> Activate Job
                                                                </a>
                                                            </li>
                                                        <?php endif; ?>
                                                        <?php if($job['status'] == 'active'): ?>
                                                            <li>
                                                                <a class="dropdown-item" href="?change_status=<?php echo $job['id']; ?>&new_status=draft">
                                                                    <i class="fas fa-file"></i> Move to Draft
                                                                </a>
                                                            </li>
                                                        <?php endif; ?>
                                                        <li><hr class="dropdown-divider"></li>
                                                        <li>
                                                            <a class="dropdown-item text-danger" href="?delete=<?php echo $job['id']; ?>" 
                                                               onclick="return confirm('Delete this job posting? This will also delete all applications.')">
                                                                <i class="fas fa-trash"></i> Delete
                                                            </a>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

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
                                <i class="fas fa-briefcase fa-4x text-muted mb-3"></i>
                                <h4>No Jobs Found</h4>
                                <p class="text-muted">
                                    <?php if($status_filter || $search): ?>
                                        Try adjusting your filters
                                    <?php else: ?>
                                        You haven't posted any jobs yet
                                    <?php endif; ?>
                                </p>
                                <a href="post_job.php" class="btn btn-primary">
                                    <i class="fas fa-plus-circle"></i> Post Your First Job
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>