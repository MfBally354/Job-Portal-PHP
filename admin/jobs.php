<?php
session_start();
require_once '../config/database.php';
require_once '../config/functions.php';
require_once '../config/constants.php';

check_role('admin');

// Get filters
$filter = isset($_GET['filter']) ? sanitize_input($_GET['filter']) : '';
$search = isset($_GET['search']) ? sanitize_input($_GET['search']) : '';
$company_filter = isset($_GET['company']) ? (int)$_GET['company'] : 0;

// Build query
$where = "WHERE 1=1";

if($filter == 'active') {
    $where .= " AND j.status = 'active'";
} elseif($filter == 'draft') {
    $where .= " AND j.status = 'draft'";
} elseif($filter == 'closed') {
    $where .= " AND j.status = 'closed'";
}

if(!empty($search)) {
    $where .= " AND (j.title LIKE '%$search%' OR c.name LIKE '%$search%' OR j.city LIKE '%$search%')";
}

if($company_filter > 0) {
    $where .= " AND j.company_id = $company_filter";
}

$query = "SELECT j.*, c.name as company_name, c.logo, cat.name as category_name,
          (SELECT COUNT(*) FROM applications WHERE job_id = j.id) as applicant_count
          FROM jobs j
          LEFT JOIN companies c ON j.company_id = c.id
          LEFT JOIN job_categories cat ON j.category_id = cat.id
          $where
          ORDER BY j.created_at DESC";

$pagination = paginate($query, 15);
$jobs = $pagination['result'];

// Get companies for filter
$companies_query = "SELECT id, name FROM companies ORDER BY name";
$companies_list = mysqli_query($conn, $companies_query);

// Get statistics
$stats = mysqli_fetch_assoc(mysqli_query($conn, "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active,
    SUM(CASE WHEN status = 'draft' THEN 1 ELSE 0 END) as draft,
    SUM(CASE WHEN status = 'closed' THEN 1 ELSE 0 END) as closed,
    SUM(views) as total_views
    FROM jobs"));

// Handle status change
if(isset($_GET['change_status'])) {
    $job_id = (int)$_GET['change_status'];
    $new_status = sanitize_input($_GET['new_status']);
    
    mysqli_query($conn, "UPDATE jobs SET status = '$new_status' WHERE id = $job_id");
    $_SESSION['success'] = 'Job status updated successfully!';
    header("Location: jobs.php");
    exit();
}

// Handle delete
if(isset($_GET['delete'])) {
    $job_id = (int)$_GET['delete'];
    
    mysqli_query($conn, "DELETE FROM job_skills WHERE job_id = $job_id");
    mysqli_query($conn, "DELETE FROM saved_jobs WHERE job_id = $job_id");
    mysqli_query($conn, "DELETE FROM applications WHERE job_id = $job_id");
    mysqli_query($conn, "DELETE FROM job_views WHERE job_id = $job_id");
    mysqli_query($conn, "DELETE FROM jobs WHERE id = $job_id");
    
    $_SESSION['success'] = 'Job deleted successfully!';
    header("Location: jobs.php");
    exit();
}

$page_title = 'Jobs Management';
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
        .company-logo {
            width: 50px;
            height: 50px;
            object-fit: contain;
            border: 1px solid #e3e6f0;
            padding: 5px;
            border-radius: 5px;
        }
        .stat-card {
            transition: transform 0.2s;
            cursor: pointer;
        }
        .stat-card:hover {
            transform: translateY(-5px);
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
            <div class="ms-auto">
                <a href="dashboard.php" class="btn btn-outline-light btn-sm">
                    <i class="fas fa-arrow-left"></i> Back to Dashboard
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

            <h2 class="mb-4"><i class="fas fa-briefcase"></i> Jobs Management</h2>

            <!-- Statistics -->
            <div class="row mb-4">
                <div class="col-md-3 mb-3">
                    <a href="jobs.php" class="text-decoration-none">
                        <div class="card stat-card text-center border-primary">
                            <div class="card-body">
                                <i class="fas fa-briefcase fa-2x text-primary mb-2"></i>
                                <h3 class="text-primary"><?php echo number_format($stats['total']); ?></h3>
                                <p class="text-muted mb-0">Total Jobs</p>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-md-3 mb-3">
                    <a href="?filter=active" class="text-decoration-none">
                        <div class="card stat-card text-center border-success">
                            <div class="card-body">
                                <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                                <h3 class="text-success"><?php echo number_format($stats['active']); ?></h3>
                                <p class="text-muted mb-0">Active</p>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-md-3 mb-3">
                    <a href="?filter=draft" class="text-decoration-none">
                        <div class="card stat-card text-center border-warning">
                            <div class="card-body">
                                <i class="fas fa-file fa-2x text-warning mb-2"></i>
                                <h3 class="text-warning"><?php echo number_format($stats['draft']); ?></h3>
                                <p class="text-muted mb-0">Draft</p>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-md-3 mb-3">
                    <a href="?filter=closed" class="text-decoration-none">
                        <div class="card stat-card text-center border-secondary">
                            <div class="card-body">
                                <i class="fas fa-times-circle fa-2x text-secondary mb-2"></i>
                                <h3 class="text-secondary"><?php echo number_format($stats['closed']); ?></h3>
                                <p class="text-muted mb-0">Closed</p>
                            </div>
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
                                <input type="text" class="form-control" name="search" 
                                       placeholder="Search by job title, company, or location..." 
                                       value="<?php echo htmlspecialchars($search); ?>">
                            </div>
                            <div class="col-md-3 mb-2">
                                <select class="form-select" name="company">
                                    <option value="0">All Companies</option>
                                    <?php 
                                    mysqli_data_seek($companies_list, 0);
                                    while($company = mysqli_fetch_assoc($companies_list)): 
                                    ?>
                                        <option value="<?php echo $company['id']; ?>" 
                                                <?php echo $company_filter == $company['id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($company['name']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="col-md-3 mb-2">
                                <select class="form-select" name="filter">
                                    <option value="">All Status</option>
                                    <option value="active" <?php echo $filter == 'active' ? 'selected' : ''; ?>>Active</option>
                                    <option value="draft" <?php echo $filter == 'draft' ? 'selected' : ''; ?>>Draft</option>
                                    <option value="closed" <?php echo $filter == 'closed' ? 'selected' : ''; ?>>Closed</option>
                                </select>
                            </div>
                            <div class="col-md-2 mb-2">
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
                                    <th>Company</th>
                                    <th>Category</th>
                                    <th>Location</th>
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
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($job['title']); ?></strong>
                                        <br><small class="text-muted"><?php echo ucfirst($job['level']); ?> Level</small>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <?php if($job['logo']): ?>
                                                <img src="../assets/uploads/logos/<?php echo htmlspecialchars($job['logo']); ?>" 
                                                     alt="Logo" class="company-logo me-2">
                                            <?php endif; ?>
                                            <span><?php echo htmlspecialchars($job['company_name']); ?></span>
                                        </div>
                                    </td>
                                    <td><?php echo htmlspecialchars($job['category_name'] ?? 'Uncategorized'); ?></td>
                                    <td><?php echo htmlspecialchars($job['city']); ?></td>
                                    <td>
                                        <span class="badge bg-<?php echo get_job_type_badge($job['type']); ?>">
                                            <?php echo ucfirst(str_replace('-', ' ', $job['type'])); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-info"><?php echo $job['applicant_count']; ?></span>
                                    </td>
                                    <td>
                                        <i class="fas fa-eye"></i> <?php echo number_format($job['views']); ?>
                                    </td>
                                    <td>
                                        <?php if($job['status'] == 'active'): ?>
                                            <span class="badge bg-success">Active</span>
                                        <?php elseif($job['status'] == 'draft'): ?>
                                            <span class="badge bg-warning">Draft</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Closed</span>
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
                                                <li><hr class="dropdown-divider"></li>
                                                <?php if($job['status'] != 'active'): ?>
                                                    <li>
                                                        <a class="dropdown-item text-success" 
                                                           href="?change_status=<?php echo $job['id']; ?>&new_status=active&filter=<?php echo $filter; ?>">
                                                            <i class="fas fa-check-circle"></i> Activate
                                                        </a>
                                                    </li>
                                                <?php endif; ?>
                                                <?php if($job['status'] != 'closed'): ?>
                                                    <li>
                                                        <a class="dropdown-item text-warning" 
                                                           href="?change_status=<?php echo $job['id']; ?>&new_status=closed&filter=<?php echo $filter; ?>">
                                                            <i class="fas fa-times-circle"></i> Close
                                                        </a>
                                                    </li>
                                                <?php endif; ?>
                                                <li><hr class="dropdown-divider"></li>
                                                <li>
                                                    <a class="dropdown-item text-danger" 
                                                       href="?delete=<?php echo $job['id']; ?>&filter=<?php echo $filter; ?>"
                                                       onclick="return confirm('Delete this job? This will also delete all applications!')">
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
                    $base_url = "?search=$search&filter=$filter&company=$company_filter";
                    echo render_pagination($pagination['current_page'], $pagination['total_pages'], $base_url); 
                    ?>
                </div>

            <?php else: ?>
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-briefcase fa-4x text-muted mb-3"></i>
                        <h4>No Jobs Found</h4>
                        <p class="text-muted">Try adjusting your filters</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>