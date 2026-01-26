<?php
session_start();
require_once '../config/database.php';
require_once '../config/functions.php';
require_once '../config/constants.php';

check_role('jobseeker');

$user_id = $_SESSION['user_id'];

// Get user profile for job recommendations
$profile = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM jobseeker_profiles WHERE user_id = $user_id"));

// Get filters
$keyword = isset($_GET['keyword']) ? sanitize_input($_GET['keyword']) : '';
$location = isset($_GET['location']) ? sanitize_input($_GET['location']) : '';
$category = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$type = isset($_GET['type']) ? sanitize_input($_GET['type']) : '';
$salary_min = isset($_GET['salary_min']) ? (int)$_GET['salary_min'] : 0;
$recommended = isset($_GET['recommended']) ? true : false;

// Build query
$where = "WHERE j.status = 'active' AND j.id NOT IN (SELECT job_id FROM applications WHERE user_id = $user_id)";

if(!empty($keyword)) {
    $where .= " AND (j.title LIKE '%$keyword%' OR j.description LIKE '%$keyword%' OR c.name LIKE '%$keyword%')";
}

if(!empty($location)) {
    $where .= " AND (j.city LIKE '%$location%' OR j.location LIKE '%$location%')";
}

if($category > 0) {
    $where .= " AND j.category_id = $category";
}

if(!empty($type)) {
    $where .= " AND j.type = '$type'";
}

if($salary_min > 0) {
    $where .= " AND j.salary_min >= $salary_min";
}

// Add recommendation filter based on user profile
if($recommended && $profile['city']) {
    $where .= " AND j.city = '{$profile['city']}'";
}

$query = "SELECT j.*, c.name as company_name, c.logo, cat.name as category_name,
          (SELECT COUNT(*) FROM saved_jobs WHERE job_id = j.id AND user_id = $user_id) as is_saved
          FROM jobs j
          LEFT JOIN companies c ON j.company_id = c.id
          LEFT JOIN job_categories cat ON j.category_id = cat.id
          $where
          ORDER BY j.created_at DESC";

$pagination = paginate($query, 12);
$jobs = $pagination['result'];

// Get categories
$categories = mysqli_query($conn, "SELECT * FROM job_categories WHERE is_active = 1 ORDER BY name");

// Get saved jobs count
$saved_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM saved_jobs WHERE user_id = $user_id"))['count'];

$page_title = 'Browse Jobs';
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
        .browse-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px 0;
        }
        .job-card {
            transition: all 0.3s ease;
            border: none;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            height: 100%;
            position: relative;
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
        .save-btn {
            position: absolute;
            top: 15px;
            right: 15px;
            background: white;
            border: 2px solid #e9ecef;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s;
            z-index: 10;
        }
        .save-btn:hover {
            transform: scale(1.1);
            border-color: #e74a3b;
        }
        .save-btn.saved {
            background: #e74a3b;
            border-color: #e74a3b;
            color: white;
        }
        .filter-sidebar {
            position: sticky;
            top: 80px;
            max-height: calc(100vh - 100px);
            overflow-y: auto;
        }
        .quick-filter-btn {
            margin: 5px;
            border-radius: 20px;
        }
        .badge-new {
            position: absolute;
            top: 10px;
            left: 10px;
            z-index: 5;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top">
        <div class="container-fluid">
            <a class="navbar-brand" href="../index.php">
                <i class="fas fa-briefcase me-2"></i><strong>JobPortal</strong>
            </a>
            <div class="ms-auto d-flex gap-2">
                <a href="dashboard.php" class="btn btn-outline-light btn-sm">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
                <a href="saved_jobs.php" class="btn btn-outline-danger btn-sm position-relative">
                    <i class="fas fa-heart"></i> Saved
                    <?php if($saved_count > 0): ?>
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                        <?php echo $saved_count; ?>
                    </span>
                    <?php endif; ?>
                </a>
            </div>
        </div>
    </nav>

    <!-- Header -->
    <section class="browse-header">
        <div class="container">
            <h2 class="mb-3"><i class="fas fa-search"></i> Jelajahi Lowongan Kerja</h2>
            <p class="lead mb-4">Temukan pekerjaan yang sempurna untuk Anda</p>
            
            <!-- Search Bar -->
            <form method="GET" action="" class="bg-white p-4 rounded shadow">
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                            <input type="text" class="form-control" name="keyword" 
                                   placeholder="Job title, keywords..." 
                                   value="<?php echo htmlspecialchars($keyword); ?>">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-map-marker-alt"></i></span>
                            <input type="text" class="form-control" name="location" 
                                   placeholder="Location..." 
                                   value="<?php echo htmlspecialchars($location); ?>">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <select class="form-select" name="category">
                            <option value="">All Categories</option>
                            <?php while($cat = mysqli_fetch_assoc($categories)): ?>
                                <option value="<?php echo $cat['id']; ?>" <?php echo $category == $cat['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cat['name']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-search"></i> Search
                        </button>
                    </div>
                </div>
            </form>

            <!-- Quick Filters -->
            <div class="mt-3">
                <small class="text-white-50">Quick Filters:</small><br>
                <a href="?recommended=1" class="btn btn-sm btn-outline-light quick-filter-btn">
                    <i class="fas fa-star"></i> Recommended for You
                </a>
                <a href="?type=full-time" class="btn btn-sm btn-outline-light quick-filter-btn">
                    <i class="fas fa-briefcase"></i> Full Time
                </a>
                <a href="?type=remote" class="btn btn-sm btn-outline-light quick-filter-btn">
                    <i class="fas fa-home"></i> Remote
                </a>
                <a href="?salary_min=5000000" class="btn btn-sm btn-outline-light quick-filter-btn">
                    <i class="fas fa-dollar-sign"></i> High Salary
                </a>
                <?php if($profile['city']): ?>
                <a href="?location=<?php echo urlencode($profile['city']); ?>" class="btn btn-sm btn-outline-light quick-filter-btn">
                    <i class="fas fa-map-marker-alt"></i> Near Me
                </a>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Main Content -->
    <section class="py-5">
        <div class="container">
            <div class="row">
                <!-- Filters Sidebar -->
                <div class="col-md-3">
                    <div class="filter-sidebar">
                        <div class="card mb-3">
                            <div class="card-body">
                                <h6 class="fw-bold mb-3"><i class="fas fa-filter"></i> Filters</h6>
                                
                                <form method="GET" action="">
                                    <input type="hidden" name="keyword" value="<?php echo htmlspecialchars($keyword); ?>">
                                    
                                    <!-- Job Type -->
                                    <div class="mb-3">
                                        <label class="form-label fw-bold small">Job Type</label>
                                        <?php foreach(JOB_TYPES as $key => $value): ?>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="type" 
                                                   value="<?php echo $key; ?>" id="type_<?php echo $key; ?>"
                                                   <?php echo $type == $key ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="type_<?php echo $key; ?>">
                                                <?php echo $value; ?>
                                            </label>
                                        </div>
                                        <?php endforeach; ?>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="type" value="" 
                                                   id="type_all" <?php echo empty($type) ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="type_all">All Types</label>
                                        </div>
                                    </div>

                                    <!-- Salary Range -->
                                    <div class="mb-3">
                                        <label class="form-label fw-bold small">Min Salary (Rp)</label>
                                        <select class="form-select form-select-sm" name="salary_min">
                                            <option value="0">Any</option>
                                            <option value="3000000" <?php echo $salary_min == 3000000 ? 'selected' : ''; ?>>3 Juta+</option>
                                            <option value="5000000" <?php echo $salary_min == 5000000 ? 'selected' : ''; ?>>5 Juta+</option>
                                            <option value="7000000" <?php echo $salary_min == 7000000 ? 'selected' : ''; ?>>7 Juta+</option>
                                            <option value="10000000" <?php echo $salary_min == 10000000 ? 'selected' : ''; ?>>10 Juta+</option>
                                            <option value="15000000" <?php echo $salary_min == 15000000 ? 'selected' : ''; ?>>15 Juta+</option>
                                        </select>
                                    </div>

                                    <button type="submit" class="btn btn-primary btn-sm w-100 mb-2">
                                        <i class="fas fa-filter"></i> Apply Filters
                                    </button>
                                    <a href="browse_jobs.php" class="btn btn-outline-secondary btn-sm w-100">
                                        <i class="fas fa-redo"></i> Reset
                                    </a>
                                </form>
                            </div>
                        </div>

                        <!-- Profile Completeness -->
                        <div class="card">
                            <div class="card-body">
                                <h6 class="fw-bold mb-3"><i class="fas fa-user-check"></i> Profile Tips</h6>
                                <p class="small mb-2">
                                    <?php if($profile['cv_path']): ?>
                                        <i class="fas fa-check-circle text-success"></i> CV Uploaded
                                    <?php else: ?>
                                        <i class="fas fa-times-circle text-danger"></i> Upload your CV
                                    <?php endif; ?>
                                </p>
                                <p class="small mb-3">
                                    Complete your profile to get better job recommendations!
                                </p>
                                <a href="profile.php" class="btn btn-outline-primary btn-sm w-100">
                                    <i class="fas fa-edit"></i> Complete Profile
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Jobs Grid -->
                <div class="col-md-9">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="mb-0">
                            <strong><?php echo $pagination['total_records']; ?></strong> jobs found
                        </h5>
                        <select class="form-select form-select-sm w-auto" onchange="location.href=this.value">
                            <option>Sort By</option>
                            <option value="?sort=newest">Newest First</option>
                            <option value="?sort=salary_high">Highest Salary</option>
                            <option value="?sort=popular">Most Popular</option>
                        </select>
                    </div>

                    <?php if(mysqli_num_rows($jobs) > 0): ?>
                        <div class="row">
                            <?php while($job = mysqli_fetch_assoc($jobs)): 
                                $is_new = (strtotime($job['created_at']) > strtotime('-3 days'));
                            ?>
                            <div class="col-md-6 col-lg-4 mb-4">
                                <div class="card job-card">
                                    <?php if($is_new): ?>
                                    <span class="badge bg-success badge-new">NEW</span>
                                    <?php endif; ?>
                                    
                                    <button class="save-btn <?php echo $job['is_saved'] ? 'saved' : ''; ?>" 
                                            onclick="toggleSave(<?php echo $job['id']; ?>, this)">
                                        <i class="fas fa-heart"></i>
                                    </button>
                                    
                                    <div class="card-body">
                                        <div class="d-flex align-items-start mb-3">
                                            <?php if($job['logo']): ?>
                                                <img src="../assets/uploads/logos/<?php echo htmlspecialchars($job['logo']); ?>" 
                                                     alt="Logo" class="company-logo me-3">
                                            <?php else: ?>
                                                <div class="company-logo me-3 bg-light d-flex align-items-center justify-content-center">
                                                    <i class="fas fa-building text-secondary"></i>
                                                </div>
                                            <?php endif; ?>
                                            <div>
                                                <h6 class="mb-1">
                                                    <a href="../public/job_detail.php?id=<?php echo $job['id']; ?>" 
                                                       class="text-decoration-none text-dark">
                                                        <?php echo htmlspecialchars($job['title']); ?>
                                                    </a>
                                                </h6>
                                                <p class="text-primary mb-0 small">
                                                    <?php echo htmlspecialchars($job['company_name']); ?>
                                                </p>
                                            </div>
                                        </div>
                                        
                                        <div class="mb-2">
                                            <span class="badge bg-<?php echo get_job_type_badge($job['type']); ?> me-1">
                                                <?php echo ucfirst(str_replace('-', ' ', $job['type'])); ?>
                                            </span>
                                            <?php if($job['category_name']): ?>
                                            <span class="badge bg-secondary">
                                                <?php echo htmlspecialchars($job['category_name']); ?>
                                            </span>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <p class="text-muted small mb-2">
                                            <i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($job['city']); ?>
                                        </p>
                                        
                                        <?php if($job['salary_min'] && $job['salary_max']): ?>
                                        <p class="text-success fw-bold small mb-3">
                                            <i class="fas fa-money-bill-wave"></i>
                                            <?php echo format_currency($job['salary_min']); ?> - 
                                            <?php echo format_currency($job['salary_max']); ?>
                                        </p>
                                        <?php endif; ?>
                                        
                                        <div class="d-flex justify-content-between align-items-center">
                                            <small class="text-muted">
                                                <?php echo time_ago($job['created_at']); ?>
                                            </small>
                                            <a href="../public/apply_job.php?id=<?php echo $job['id']; ?>" 
                                               class="btn btn-sm btn-primary">
                                                Apply Now
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endwhile; ?>
                        </div>

                        <!-- Pagination -->
                        <div class="mt-4">
                            <?php 
                            $base_url = "?keyword=$keyword&location=$location&category=$category&type=$type&salary_min=$salary_min";
                            echo render_pagination($pagination['current_page'], $pagination['total_pages'], $base_url); 
                            ?>
                        </div>

                    <?php else: ?>
                        <div class="card">
                            <div class="card-body text-center py-5">
                                <i class="fas fa-search fa-4x text-muted mb-3"></i>
                                <h4>No Jobs Found</h4>
                                <p class="text-muted mb-4">Try adjusting your search criteria</p>
                                <a href="browse_jobs.php" class="btn btn-primary">
                                    <i class="fas fa-redo"></i> Clear Filters
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleSave(jobId, button) {
            const isSaved = button.classList.contains('saved');
            const action = isSaved ? 'unsave' : 'save';
            
            fetch('../api/save_job.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({job_id: jobId, action: action})
            })
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    button.classList.toggle('saved');
                    // Update saved count in navbar
                    location.reload();
                } else {
                    alert(data.message || 'Failed to save job');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred');
            });
        }
    </script>
</body>
</html>