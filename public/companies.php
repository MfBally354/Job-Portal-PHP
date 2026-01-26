<?php
session_start();
require_once '../config/database.php';
require_once '../config/functions.php';
require_once '../config/constants.php';

// Get filters
$search = isset($_GET['search']) ? sanitize_input($_GET['search']) : '';
$industry = isset($_GET['industry']) ? sanitize_input($_GET['industry']) : '';
$city = isset($_GET['city']) ? sanitize_input($_GET['city']) : '';

// Build query
$where = "WHERE c.is_verified = 1";

if(!empty($search)) {
    $where .= " AND (c.name LIKE '%$search%' OR c.description LIKE '%$search%')";
}

if(!empty($industry)) {
    $where .= " AND c.industry = '$industry'";
}

if(!empty($city)) {
    $where .= " AND c.city LIKE '%$city%'";
}

$query = "SELECT c.*, 
          (SELECT COUNT(*) FROM jobs WHERE company_id = c.id AND status = 'active') as job_count
          FROM companies c
          $where
          ORDER BY job_count DESC, c.name ASC";

$pagination = paginate($query, 12);
$companies = $pagination['result'];

// Get industries for filter
$industries_query = "SELECT DISTINCT industry FROM companies WHERE industry IS NOT NULL AND industry != '' ORDER BY industry";
$industries = mysqli_query($conn, $industries_query);

$page_title = 'Perusahaan Terdaftar';
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
        .company-card {
            transition: all 0.3s ease;
            height: 100%;
            border: none;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .company-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
        }
        .company-logo-lg {
            width: 120px;
            height: 120px;
            object-fit: contain;
            border: 2px solid #e3e6f0;
            padding: 15px;
            border-radius: 10px;
            background: white;
        }
        .verified-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            background: #28a745;
            color: white;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
        }
        .header-search {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 60px 0;
            color: white;
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
                    <li class="nav-item"><a class="nav-link" href="jobs.php">Cari Lowongan</a></li>
                    <li class="nav-item"><a class="nav-link active" href="companies.php">Perusahaan</a></li>
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user"></i> <?php echo htmlspecialchars($_SESSION['name']); ?>
                            </a>
                            <ul class="dropdown-menu">
                                <?php if($_SESSION['role'] == 'jobseeker'): ?>
                                    <li><a class="dropdown-item" href="../jobseeker/dashboard.php">Dashboard</a></li>
                                <?php elseif($_SESSION['role'] == 'employer'): ?>
                                    <li><a class="dropdown-item" href="../employer/dashboard.php">Dashboard</a></li>
                                <?php endif; ?>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="../auth/logout.php">Logout</a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item"><a class="nav-link" href="../auth/login.php">Login</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Header Search -->
    <section class="header-search">
        <div class="container">
            <h1 class="mb-3"><i class="fas fa-building"></i> Perusahaan Terdaftar</h1>
            <p class="lead mb-4">Temukan perusahaan terpercaya dan jelajahi peluang karir</p>
            
            <form method="GET" action="" class="bg-white p-4 rounded">
                <div class="row g-3">
                    <div class="col-md-4">
                        <input type="text" class="form-control" name="search" 
                               placeholder="Cari nama perusahaan..." 
                               value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    <div class="col-md-3">
                        <select class="form-select" name="industry">
                            <option value="">Semua Industri</option>
                            <?php while($ind = mysqli_fetch_assoc($industries)): ?>
                                <option value="<?php echo htmlspecialchars($ind['industry']); ?>" 
                                        <?php echo $industry == $ind['industry'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($ind['industry']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <input type="text" class="form-control" name="city" 
                               placeholder="Kota..." 
                               value="<?php echo htmlspecialchars($city); ?>">
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

    <!-- Companies Grid -->
    <section class="py-5">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="mb-0">
                    Ditemukan <strong><?php echo $pagination['total_records']; ?></strong> perusahaan
                </h5>
            </div>

            <?php if(mysqli_num_rows($companies) > 0): ?>
                <div class="row">
                    <?php while($company = mysqli_fetch_assoc($companies)): ?>
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card company-card">
                            <div class="card-body text-center position-relative">
                                <div class="verified-badge">
                                    <i class="fas fa-check-circle"></i> Verified
                                </div>
                                
                                <?php if($company['logo']): ?>
                                    <img src="../assets/uploads/logos/<?php echo htmlspecialchars($company['logo']); ?>" 
                                         alt="Logo" class="company-logo-lg mb-3">
                                <?php else: ?>
                                    <div class="company-logo-lg d-inline-flex align-items-center justify-content-center bg-light mb-3">
                                        <i class="fas fa-building fa-3x text-secondary"></i>
                                    </div>
                                <?php endif; ?>
                                
                                <h5 class="card-title mb-2"><?php echo htmlspecialchars($company['name']); ?></h5>
                                
                                <?php if($company['industry']): ?>
                                    <p class="text-muted mb-2">
                                        <i class="fas fa-industry"></i> <?php echo htmlspecialchars($company['industry']); ?>
                                    </p>
                                <?php endif; ?>
                                
                                <?php if($company['city']): ?>
                                    <p class="text-muted mb-3">
                                        <i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($company['city']); ?>
                                    </p>
                                <?php endif; ?>
                                
                                <?php if($company['description']): ?>
                                    <p class="text-muted small mb-3" style="height: 60px; overflow: hidden;">
                                        <?php echo htmlspecialchars(substr($company['description'], 0, 120)) . '...'; ?>
                                    </p>
                                <?php endif; ?>
                                
                                <div class="mb-3">
                                    <?php if($company['employee_count']): ?>
                                        <span class="badge bg-light text-dark me-2">
                                            <i class="fas fa-users"></i> <?php echo htmlspecialchars($company['employee_count']); ?> karyawan
                                        </span>
                                    <?php endif; ?>
                                    <span class="badge bg-primary">
                                        <i class="fas fa-briefcase"></i> <?php echo $company['job_count']; ?> lowongan aktif
                                    </span>
                                </div>
                                
                                <?php if($company['job_count'] > 0): ?>
                                    <a href="jobs.php?company=<?php echo $company['id']; ?>" class="btn btn-primary w-100">
                                        <i class="fas fa-briefcase"></i> Lihat Lowongan
                                    </a>
                                <?php else: ?>
                                    <button class="btn btn-outline-secondary w-100" disabled>
                                        Tidak ada lowongan aktif
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>

                <!-- Pagination -->
                <div class="mt-4">
                    <?php 
                    $base_url = "?search=$search&industry=$industry&city=$city";
                    echo render_pagination($pagination['current_page'], $pagination['total_pages'], $base_url); 
                    ?>
                </div>

            <?php else: ?>
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-building fa-5x text-muted mb-4"></i>
                        <h3>Tidak ada perusahaan ditemukan</h3>
                        <p class="text-muted mb-4">Coba ubah kriteria pencarian Anda</p>
                        <a href="companies.php" class="btn btn-primary">Reset Pencarian</a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4 mt-5">
        <div class="container text-center">
            <p class="mb-0">&copy; 2024 JobPortal. All rights reserved.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>