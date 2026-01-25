<?php
session_start();
require_once '../config/database.php';
require_once '../config/functions.php';
require_once '../config/constants.php';

check_role('admin');

// Get filters
$filter = isset($_GET['filter']) ? sanitize_input($_GET['filter']) : '';
$search = isset($_GET['search']) ? sanitize_input($_GET['search']) : '';

// Build query
$where = "WHERE 1=1";

if($filter == 'verified') {
    $where .= " AND c.is_verified = 1";
} elseif($filter == 'unverified') {
    $where .= " AND c.is_verified = 0";
}

if(!empty($search)) {
    $where .= " AND (c.name LIKE '%$search%' OR c.industry LIKE '%$search%' OR u.email LIKE '%$search%')";
}

$query = "SELECT c.*, u.name as owner_name, u.email as owner_email, u.is_active,
          (SELECT COUNT(*) FROM jobs WHERE company_id = c.id) as job_count
          FROM companies c
          JOIN users u ON c.user_id = u.id
          $where
          ORDER BY c.created_at DESC";

$pagination = paginate($query, 15);
$companies = $pagination['result'];

// Get statistics
$stats = mysqli_fetch_assoc(mysqli_query($conn, "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN is_verified = 1 THEN 1 ELSE 0 END) as verified,
    SUM(CASE WHEN is_verified = 0 THEN 1 ELSE 0 END) as unverified
    FROM companies"));

// Handle verification
if(isset($_GET['verify'])) {
    $company_id = (int)$_GET['verify'];
    mysqli_query($conn, "UPDATE companies SET is_verified = 1 WHERE id = $company_id");
    
    // Notify employer
    $company_data = mysqli_fetch_assoc(mysqli_query($conn, "SELECT user_id, name FROM companies WHERE id = $company_id"));
    send_notification(
        $company_data['user_id'],
        'Company Verified',
        'Your company ' . $company_data['name'] . ' has been verified!',
        'success'
    );
    
    $_SESSION['success'] = 'Company verified successfully!';
    header("Location: companies.php");
    exit();
}

// Handle unverify
if(isset($_GET['unverify'])) {
    $company_id = (int)$_GET['unverify'];
    mysqli_query($conn, "UPDATE companies SET is_verified = 0 WHERE id = $company_id");
    $_SESSION['success'] = 'Company unverified!';
    header("Location: companies.php");
    exit();
}

// Handle delete
if(isset($_GET['delete'])) {
    $company_id = (int)$_GET['delete'];
    
    // Delete related jobs and applications
    mysqli_query($conn, "DELETE FROM applications WHERE job_id IN (SELECT id FROM jobs WHERE company_id = $company_id)");
    mysqli_query($conn, "DELETE FROM job_skills WHERE job_id IN (SELECT id FROM jobs WHERE company_id = $company_id)");
    mysqli_query($conn, "DELETE FROM saved_jobs WHERE job_id IN (SELECT id FROM jobs WHERE company_id = $company_id)");
    mysqli_query($conn, "DELETE FROM job_views WHERE job_id IN (SELECT id FROM jobs WHERE company_id = $company_id)");
    mysqli_query($conn, "DELETE FROM jobs WHERE company_id = $company_id");
    mysqli_query($conn, "DELETE FROM companies WHERE id = $company_id");
    
    $_SESSION['success'] = 'Company deleted successfully!';
    header("Location: companies.php");
    exit();
}

$page_title = 'Companies Management';
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

            <h2 class="mb-4"><i class="fas fa-building"></i> Companies Management</h2>

            <!-- Statistics -->
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="card text-center">
                        <div class="card-body">
                            <h3><?php echo number_format($stats['total']); ?></h3>
                            <p class="text-muted mb-0">Total Companies</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card text-center border-success">
                        <div class="card-body">
                            <h3 class="text-success"><?php echo number_format($stats['verified']); ?></h3>
                            <p class="text-muted mb-0">Verified</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card text-center border-warning">
                        <div class="card-body">
                            <h3 class="text-warning"><?php echo number_format($stats['unverified']); ?></h3>
                            <p class="text-muted mb-0">Pending Verification</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Filters -->
            <div class="btn-group mb-4" role="group">
                <a href="companies.php" class="btn btn-outline-primary <?php echo empty($filter) ? 'active' : ''; ?>">
                    All Companies
                </a>
                <a href="?filter=verified" class="btn btn-outline-success <?php echo $filter == 'verified' ? 'active' : ''; ?>">
                    Verified
                </a>
                <a href="?filter=unverified" class="btn btn-outline-warning <?php echo $filter == 'unverified' ? 'active' : ''; ?>">
                    Pending Verification
                </a>
            </div>

            <!-- Search -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" action="">
                        <?php if($filter): ?>
                            <input type="hidden" name="filter" value="<?php echo $filter; ?>">
                        <?php endif; ?>
                        <div class="row">
                            <div class="col-md-10">
                                <input type="text" class="form-control" name="search" 
                                       placeholder="Search by company name, industry, or owner email..." 
                                       value="<?php echo htmlspecialchars($search); ?>">
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-search"></i> Search
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Companies Table -->
            <?php if(mysqli_num_rows($companies) > 0): ?>
                <div class="card">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Logo</th>
                                    <th>Company Name</th>
                                    <th>Owner</th>
                                    <th>Industry</th>
                                    <th>Employees</th>
                                    <th>Jobs</th>
                                    <th>Status</th>
                                    <th>Joined</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($company = mysqli_fetch_assoc($companies)): ?>
                                <tr>
                                    <td>
                                        <?php if($company['logo']): ?>
                                            <img src="../assets/uploads/logos/<?php echo htmlspecialchars($company['logo']); ?>" 
                                                 alt="Logo" class="company-logo">
                                        <?php else: ?>
                                            <div class="company-logo bg-light d-flex align-items-center justify-content-center">
                                                <i class="fas fa-building text-secondary"></i>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($company['name']); ?></strong>
                                        <br>
                                        <small class="text-muted"><?php echo htmlspecialchars($company['city'] ?? '-'); ?></small>
                                    </td>
                                    <td>
                                        <?php echo htmlspecialchars($company['owner_name']); ?>
                                        <br>
                                        <small class="text-muted"><?php echo htmlspecialchars($company['owner_email']); ?></small>
                                    </td>
                                    <td><?php echo htmlspecialchars($company['industry'] ?? '-'); ?></td>
                                    <td><?php echo htmlspecialchars($company['employee_count'] ?? '-'); ?></td>
                                    <td>
                                        <span class="badge bg-info"><?php echo $company['job_count']; ?> jobs</span>
                                    </td>
                                    <td>
                                        <?php if($company['is_verified']): ?>
                                            <span class="badge bg-success">
                                                <i class="fas fa-check-circle"></i> Verified
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-warning">
                                                <i class="fas fa-clock"></i> Pending
                                            </span>
                                        <?php endif; ?>
                                        <br>
                                        <?php if($company['is_active']): ?>
                                            <small class="text-success">Owner Active</small>
                                        <?php else: ?>
                                            <small class="text-danger">Owner Inactive</small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <small><?php echo date('d M Y', strtotime($company['created_at'])); ?></small>
                                    </td>
                                    <td>
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" 
                                                    type="button" data-bs-toggle="dropdown">
                                                <i class="fas fa-cog"></i>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li>
                                                    <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#viewModal<?php echo $company['id']; ?>">
                                                        <i class="fas fa-eye"></i> View Details
                                                    </a>
                                                </li>
                                                <li><hr class="dropdown-divider"></li>
                                                <?php if(!$company['is_verified']): ?>
                                                    <li>
                                                        <a class="dropdown-item text-success" 
                                                           href="?verify=<?php echo $company['id']; ?>&filter=<?php echo $filter; ?>">
                                                            <i class="fas fa-check-circle"></i> Verify Company
                                                        </a>
                                                    </li>
                                                <?php else: ?>
                                                    <li>
                                                        <a class="dropdown-item text-warning" 
                                                           href="?unverify=<?php echo $company['id']; ?>&filter=<?php echo $filter; ?>"
                                                           onclick="return confirm('Remove verification?')">
                                                            <i class="fas fa-times-circle"></i> Unverify
                                                        </a>
                                                    </li>
                                                <?php endif; ?>
                                                <li>
                                                    <a class="dropdown-item text-danger" 
                                                       href="?delete=<?php echo $company['id']; ?>&filter=<?php echo $filter; ?>"
                                                       onclick="return confirm('Delete this company? This will also delete all their jobs!')">
                                                        <i class="fas fa-trash"></i> Delete
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>

                                <!-- View Modal -->
                                <div class="modal fade" id="viewModal<?php echo $company['id']; ?>" tabindex="-1">
                                    <div class="modal-dialog modal-lg">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Company Details</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="row">
                                                    <div class="col-md-3 text-center mb-3">
                                                        <?php if($company['logo']): ?>
                                                            <img src="../assets/uploads/logos/<?php echo htmlspecialchars($company['logo']); ?>" 
                                                                 alt="Logo" class="img-fluid" style="max-height: 150px;">
                                                        <?php else: ?>
                                                            <div class="bg-light p-4 rounded">
                                                                <i class="fas fa-building fa-5x text-secondary"></i>
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div class="col-md-9">
                                                        <h4><?php echo htmlspecialchars($company['name']); ?></h4>
                                                        <?php if($company['description']): ?>
                                                            <p class="text-muted"><?php echo nl2br(htmlspecialchars($company['description'])); ?></p>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                                
                                                <hr>
                                                
                                                <div class="row">
                                                    <div class="col-md-6 mb-2">
                                                        <strong>Industry:</strong> <?php echo htmlspecialchars($company['industry'] ?? 'Not specified'); ?>
                                                    </div>
                                                    <div class="col-md-6 mb-2">
                                                        <strong>Employees:</strong> <?php echo htmlspecialchars($company['employee_count'] ?? 'Not specified'); ?>
                                                    </div>
                                                    <div class="col-md-6 mb-2">
                                                        <strong>Founded:</strong> <?php echo htmlspecialchars($company['founded_year'] ?? 'Not specified'); ?>
                                                    </div>
                                                    <div class="col-md-6 mb-2">
                                                        <strong>Website:</strong> 
                                                        <?php if($company['website']): ?>
                                                            <a href="<?php echo htmlspecialchars($company['website']); ?>" target="_blank">
                                                                <?php echo htmlspecialchars($company['website']); ?>
                                                            </a>
                                                        <?php else: ?>
                                                            Not provided
                                                        <?php endif; ?>
                                                    </div>
                                                    <div class="col-md-6 mb-2">
                                                        <strong>Email:</strong> <?php echo htmlspecialchars($company['email'] ?? 'Not provided'); ?>
                                                    </div>
                                                    <div class="col-md-6 mb-2">
                                                        <strong>Phone:</strong> <?php echo htmlspecialchars($company['phone'] ?? 'Not provided'); ?>
                                                    </div>
                                                    <div class="col-12 mb-2">
                                                        <strong>Address:</strong> <?php echo htmlspecialchars($company['address'] ?? 'Not provided'); ?>
                                                    </div>
                                                </div>
                                                
                                                <hr>
                                                
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <strong>Owner:</strong> <?php echo htmlspecialchars($company['owner_name']); ?>
                                                        <br>
                                                        <small class="text-muted"><?php echo htmlspecialchars($company['owner_email']); ?></small>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <strong>Joined:</strong> <?php echo date('d F Y', strtotime($company['created_at'])); ?>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                <?php if(!$company['is_verified']): ?>
                                                    <a href="?verify=<?php echo $company['id']; ?>" class="btn btn-success">
                                                        <i class="fas fa-check-circle"></i> Verify Company
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Pagination -->
                <div class="mt-4">
                    <?php 
                    $base_url = "?filter=$filter&search=$search";
                    echo render_pagination($pagination['current_page'], $pagination['total_pages'], $base_url); 
                    ?>
                </div>

            <?php else: ?>
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-building fa-4x text-muted mb-3"></i>
                        <h4>No Companies Found</h4>
                        <p class="text-muted">Try adjusting your filters</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>