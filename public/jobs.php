<?php
session_start();
require_once '../config/database.php';
require_once '../config/functions.php';
require_once '../config/constants.php';

// Get filters
$keyword = isset($_GET['keyword']) ? sanitize_input($_GET['keyword']) : '';
$location = isset($_GET['location']) ? sanitize_input($_GET['location']) : '';
$category = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$type = isset($_GET['type']) ? sanitize_input($_GET['type']) : '';
$level = isset($_GET['level']) ? sanitize_input($_GET['level']) : '';
$salary_min = isset($_GET['salary_min']) ? (int)$_GET['salary_min'] : 0;
$salary_max = isset($_GET['salary_max']) ? (int)$_GET['salary_max'] : 0;

// Build query
$where = "WHERE j.status = 'active'";

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

if(!empty($level)) {
    $where .= " AND j.level = '$level'";
}

if($salary_min > 0) {
    $where .= " AND j.salary_min >= $salary_min";
}

if($salary_max > 0) {
    $where .= " AND j.salary_max <= $salary_max";
}

$query = "SELECT j.*, c.name as company_name, c.logo, c.city as company_city, cat.name as category_name
          FROM jobs j
          LEFT JOIN companies c ON j.company_id = c.id
          LEFT JOIN job_categories cat ON j.category_id = cat.id
          $where
          ORDER BY j.created_at DESC";

$pagination = paginate($query, 12);
$jobs_result = $pagination['result'];

// Get categories for filter
$categories_query = "SELECT * FROM job_categories WHERE is_active = 1 ORDER BY name";
$categories_result = mysqli_query($conn, $categories_query);

$page_title = 'Cari Lowongan Kerja';
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
        .filter-sidebar {
            position: sticky;
            top: 20px;
        }
        .filter-card {
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top">
        <div class="container">
            <a class="navbar-brand" href="../index.php">
                <i class="fas fa-briefcase me-2"></i><strong>JobPortal</strong>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="../index.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link active" href="jobs.php">Cari Lowongan</a></li>
                    <li class="nav-item"><a class="nav-link" href="companies.php">Perusahaan</a></li>
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user"></i> <?php echo htmlspecialchars($_SESSION['name']); ?>
                            </a>
                            <ul class="dropdown-menu">
                                <?php if($_SESSION['role'] == 'jobseeker'): ?>
                                    <li><a class="dropdown-item" href="../jobseeker/dashboard.php">Dashboard</a></li>
                                    <li><a class="dropdown-item" href="../jobseeker/my_applications.php">Lamaran Saya</a></li>
                                <?php endif; ?>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="../auth/logout.php">Logout</a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item"><a class="nav-link" href="../auth/login.php">Login</a></li>
                        <li class="nav-item"><a class="btn btn-primary btn-sm ms-2" href="../auth/register.php">Daftar</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Search Header -->
    <section class="bg-light py-4">
        <div class="container">
            <h2 class="mb-3"><i class="fas fa-search"></i> Cari Lowongan Kerja</h2>
            <form method="GET" action="">
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                            <input type="text" class="form-control" name="keyword" placeholder="Kata kunci..." 
                                   value="<?php echo htmlspecialchars($keyword); ?>">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-map-marker-alt"></i></span>
                            <input type="text" class="form-control" name="location" placeholder="Lokasi..." 
                                   value="<?php echo htmlspecialchars($location); ?>">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <select class="form-select" name="category">
                            <option value="">Semua Kategori</option>
                            <?php while($cat = mysqli_fetch_assoc($categories_result)): ?>
                                <option value="<?php echo $cat['id']; ?>" <?php echo $category == $cat['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cat['name']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-search"></i> Cari
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </section>

    <!-- Main Content -->
    <section class="py-5">
        <div class="container">
            <div class="row">
                <!-- Filters Sidebar -->
                <div class="col-md-3">
                    <div class="filter-sidebar">
                        <div class="card filter-card mb-4">
                            <div class="card-body">
                                <h5 class="card-title mb-3"><i class="fas fa-filter"></i> Filter</h5>
                                
                                <form method="GET" action="">
                                    <input type="hidden" name="keyword" value="<?php echo htmlspecialchars($keyword); ?>">
                                    <input type="hidden" name="location" value="<?php echo htmlspecialchars($location); ?>">
                                    
                                    <!-- Job Type -->
                                    <div class="mb-4">
                                        <h6 class="fw-bold">Tipe Pekerjaan</h6>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="type" value="" id="type_all" <?php echo empty($type) ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="type_all">Semua</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="type" value="full-time" id="type_fulltime" <?php echo $type == 'full-time' ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="type_fulltime">Full Time</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="type" value="part-time" id="type_parttime" <?php echo $type == 'part-time' ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="type_parttime">Part Time</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="type" value="contract" id="type_contract" <?php echo $type == 'contract' ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="type_contract">Contract</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="type" value="internship" id="type_internship" <?php echo $type == 'internship' ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="type_internship">Internship</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="type" value="freelance" id="type_freelance" <?php echo $type == 'freelance' ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="type_freelance">Freelance</label>
                                        </div>
                                    </div>
                                    
                                    <!-- Experience Level -->
                                    <div class="mb-4">
                                        <h6 class="fw-bold">Level</h6>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="level" value="" id="level_all" <?php echo empty($level) ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="level_all">Semua</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="level" value="entry" id="level_entry" <?php echo $level == 'entry' ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="level_entry">Entry Level</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="level" value="junior" id="level_junior" <?php echo $level == 'junior' ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="level_junior">Junior</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="level" value="mid" id="level_mid" <?php echo $level == 'mid' ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="level_mid">Mid Level</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="level" value="senior" id="level_senior" <?php echo $level == 'senior' ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="level_senior">Senior</label>
                                        </div>
                                    </div>
                                    
                                    <!-- Salary Range -->
                                    <div class="mb-4">
                                        <h6 class="fw-bold">Range Gaji (Juta/bulan)</h6>
                                        <div class="row g-2">
                                            <div class="col-6">
                                                <input type="number" class="form-control form-control-sm" name="salary_min" 
                                                       placeholder="Min" value="<?php echo $salary_min; ?>">
                                            </div>
                                            <div class="col-6">
                                                <input type="number" class="form-control form-control-sm" name="salary_max" 
                                                       placeholder="Max" value="<?php echo $salary_max; ?>">
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <button type="submit" class="btn btn-primary w-100 mb-2">
                                        <i class="fas fa-filter"></i> Terapkan Filter
                                    </button>
                                    <a href="jobs.php" class="btn btn-outline-secondary w-100">
                                        <i class="fas fa-redo"></i> Reset Filter
                                    </a>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Jobs List -->
                <div class="col-md-9">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="mb-0">
                            Ditemukan <strong><?php echo $pagination['total_records']; ?></strong> lowongan
                        </h5>
                        <div>
                            <select class="form-select form-select-sm" onchange="location = this.value;">
                                <option>Urutkan</option>
                                <option value="?sort=newest">Terbaru</option>
                                <option value="?sort=salary_high">Gaji Tertinggi</option>
                                <option value="?sort=popular">Paling Populer</option>
                            </select>
                        </div>
                    </div>
                    
                    <?php if(mysqli_num_rows($jobs_result) > 0): ?>
                        <div class="row">
                            <?php while($job = mysqli_fetch_assoc($jobs_result)): ?>
                            <div class="col-md-6 mb-4">
                                <div class="card job-card">
                                    <div class="card-body">
                                        <div class="d-flex align-items-start mb-3">
                                            <?php if($job['logo']): ?>
                                                <img src="../assets/uploads/logos/<?php echo htmlspecialchars($job['logo']); ?>" 
                                                     alt="Logo" class="company-logo me-3">
                                            <?php else: ?>
                                                <div class="company-logo me-3 d-flex align-items-center justify-content-center bg-light">
                                                    <i class="fas fa-building text-secondary"></i>
                                                </div>
                                            <?php endif; ?>
                                            <div class="flex-grow-1">
                                                <h5 class="card-title mb-1">
                                                    <a href="job_detail.php?id=<?php echo $job['id']; ?>" class="text-decoration-none text-dark">
                                                        <?php echo htmlspecialchars($job['title']); ?>
                                                    </a>
                                                </h5>
                                                <h6 class="text-primary mb-0"><?php echo htmlspecialchars($job['company_name']); ?></h6>
                                            </div>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <span class="badge bg-<?php echo get_job_type_badge($job['type']); ?> me-2">
                                                <?php echo ucfirst(str_replace('-', ' ', $job['type'])); ?>
                                            </span>
                                            <?php if($job['category_name']): ?>
                                            <span class="badge bg-secondary">
                                                <?php echo htmlspecialchars($job['category_name']); ?>
                                            </span>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <p class="text-muted mb-2">
                                            <i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($job['city'] ?? $job['location']); ?>
                                        </p>
                                        
                                        <?php if($job['salary_min'] && $job['salary_max']): ?>
                                        <p class="text-success fw-bold mb-3">
                                            <i class="fas fa-money-bill-wave"></i>
                                            <?php echo format_currency($job['salary_min']); ?> - <?php echo format_currency($job['salary_max']); ?>
                                        </p>
                                        <?php endif; ?>
                                        
                                        <div class="d-flex justify-content-between align-items-center">
                                            <small class="text-muted">
                                                <i class="far fa-clock"></i> <?php echo time_ago($job['created_at']); ?>
                                            </small>
                                            <a href="job_detail.php?id=<?php echo $job['id']; ?>" class="btn btn-sm btn-primary">
                                                Lihat Detail
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
                            $base_url = "?keyword=$keyword&location=$location&category=$category&type=$type&level=$level&salary_min=$salary_min&salary_max=$salary_max";
                            echo render_pagination($pagination['current_page'], $pagination['total_pages'], $base_url); 
                            ?>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info text-center">
                            <i class="fas fa-info-circle fa-3x mb-3"></i>
                            <h5>Tidak ada lowongan ditemukan</h5>
                            <p>Coba ubah kriteria pencarian Anda</p>
                            <a href="jobs.php" class="btn btn-primary">Reset Pencarian</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4">
        <div class="container text-center">
            <p class="mb-0">&copy; 2024 JobPortal. All rights reserved.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>