<?php
session_start();
require_once 'config/database.php';
require_once 'config/functions.php';
require_once 'config/constants.php';

$page_title = 'Portal Lowongan Kerja Terpercaya';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        :root {
            --primary-color: #4e73df;
            --secondary-color: #858796;
            --success-color: #1cc88a;
            --info-color: #36b9cc;
            --warning-color: #f6c23e;
            --danger-color: #e74a3b;
        }
        
        .hero-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 100px 0;
            color: white;
        }
        
        .search-box {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        
        .job-card {
            transition: all 0.3s ease;
            border: none;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
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
        
        .category-card {
            text-align: center;
            padding: 30px;
            border: 1px solid #e3e6f0;
            border-radius: 10px;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .category-card:hover {
            background: var(--primary-color);
            color: white;
            transform: translateY(-5px);
        }
        
        .category-card i {
            font-size: 3rem;
            margin-bottom: 15px;
        }
        
        .stats-section {
            background: #f8f9fc;
            padding: 60px 0;
        }
        
        .stat-box {
            text-align: center;
            padding: 30px;
        }
        
        .stat-box h2 {
            color: var(--primary-color);
            font-weight: bold;
            font-size: 3rem;
        }
        
        .feature-icon {
            width: 80px;
            height: 80px;
            background: var(--primary-color);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            margin: 0 auto 20px;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-briefcase me-2"></i>
                <strong>JobPortal</strong>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="public/jobs.php">Cari Lowongan</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="public/companies.php">Perusahaan</a>
                    </li>
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user"></i> <?php echo htmlspecialchars($_SESSION['name']); ?>
                            </a>
                            <ul class="dropdown-menu">
                                <?php if($_SESSION['role'] == 'admin'): ?>
                                    <li><a class="dropdown-item" href="admin/dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                                <?php elseif($_SESSION['role'] == 'employer'): ?>
                                    <li><a class="dropdown-item" href="employer/dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                                <?php else: ?>
                                    <li><a class="dropdown-item" href="jobseeker/dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                                    <li><a class="dropdown-item" href="jobseeker/profile.php"><i class="fas fa-user-edit"></i> Profil</a></li>
                                    <li><a class="dropdown-item" href="jobseeker/my_applications.php"><i class="fas fa-file-alt"></i> Lamaran Saya</a></li>
                                <?php endif; ?>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="auth/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="auth/login.php"><i class="fas fa-sign-in-alt"></i> Login</a>
                        </li>
                        <li class="nav-item">
                            <a class="btn btn-primary btn-sm ms-2" href="auth/register.php">Daftar Sekarang</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 mx-auto text-center">
                    <h1 class="display-4 fw-bold mb-3">Temukan Pekerjaan Impian Anda</h1>
                    <p class="lead mb-5">Ribuan lowongan kerja dari perusahaan terpercaya menunggu Anda</p>
                    
                    <div class="search-box">
                        <form action="public/jobs.php" method="GET">
                            <div class="row g-3">
                                <div class="col-md-5">
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                                        <input type="text" class="form-control" name="keyword" placeholder="Posisi atau kata kunci...">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-map-marker-alt"></i></span>
                                        <input type="text" class="form-control" name="location" placeholder="Lokasi...">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="fas fa-search"></i> Cari Lowongan
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="stats-section">
        <div class="container">
            <div class="row">
                <div class="col-md-3">
                    <div class="stat-box">
                        <?php
                        $jobs_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM jobs WHERE status = 'active'"))['count'];
                        ?>
                        <h2><?php echo number_format($jobs_count); ?>+</h2>
                        <p class="text-muted">Lowongan Aktif</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-box">
                        <?php
                        $companies_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM companies"))['count'];
                        ?>
                        <h2><?php echo number_format($companies_count); ?>+</h2>
                        <p class="text-muted">Perusahaan</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-box">
                        <?php
                        $jobseekers_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM users WHERE role = 'jobseeker'"))['count'];
                        ?>
                        <h2><?php echo number_format($jobseekers_count); ?>+</h2>
                        <p class="text-muted">Pencari Kerja</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-box">
                        <?php
                        $applications_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM applications"))['count'];
                        ?>
                        <h2><?php echo number_format($applications_count); ?>+</h2>
                        <p class="text-muted">Lamaran Terkirim</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Latest Jobs -->
    <section class="py-5">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="fw-bold">Lowongan Terbaru</h2>
                <p class="text-muted">Peluang karir terbaik menunggu Anda</p>
            </div>
            
            <div class="row">
                <?php
                $jobs_query = "SELECT j.*, c.name as company_name, c.logo, c.city as company_city, cat.name as category_name
                               FROM jobs j
                               LEFT JOIN companies c ON j.company_id = c.id
                               LEFT JOIN job_categories cat ON j.category_id = cat.id
                               WHERE j.status = 'active'
                               ORDER BY j.created_at DESC
                               LIMIT 6";
                $jobs_result = mysqli_query($conn, $jobs_query);
                
                if(mysqli_num_rows($jobs_result) > 0):
                    while($job = mysqli_fetch_assoc($jobs_result)):
                ?>
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card job-card h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-start mb-3">
                                <?php if($job['logo']): ?>
                                    <img src="assets/uploads/logos/<?php echo htmlspecialchars($job['logo']); ?>" 
                                         alt="Logo" class="company-logo me-3">
                                <?php else: ?>
                                    <div class="company-logo me-3 d-flex align-items-center justify-content-center bg-light">
                                        <i class="fas fa-building text-secondary"></i>
                                    </div>
                                <?php endif; ?>
                                <div class="flex-grow-1">
                                    <h5 class="card-title mb-1">
                                        <a href="public/job_detail.php?id=<?php echo $job['id']; ?>" class="text-decoration-none text-dark">
                                            <?php echo htmlspecialchars($job['title']); ?>
                                        </a>
                                    </h5>
                                    <h6 class="text-primary mb-0"><?php echo htmlspecialchars($job['company_name']); ?></h6>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <span class="badge bg-<?php echo get_job_type_badge($job['type']); ?> me-2">
                                    <i class="fas fa-briefcase"></i> <?php echo ucfirst(str_replace('-', ' ', $job['type'])); ?>
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
                                <a href="public/job_detail.php?id=<?php echo $job['id']; ?>" class="btn btn-sm btn-primary">
                                    Lihat Detail
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php 
                    endwhile;
                else:
                ?>
                <div class="col-12">
                    <div class="alert alert-info text-center">
                        Belum ada lowongan tersedia saat ini.
                    </div>
                </div>
                <?php endif; ?>
            </div>
            
            <div class="text-center mt-4">
                <a href="public/jobs.php" class="btn btn-outline-primary btn-lg">
                    Lihat Semua Lowongan <i class="fas fa-arrow-right ms-2"></i>
                </a>
            </div>
        </div>
    </section>

    <!-- Categories -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="fw-bold">Kategori Populer</h2>
                <p class="text-muted">Temukan pekerjaan berdasarkan kategori</p>
            </div>
            
            <div class="row">
                <?php
                $categories_query = "SELECT cat.*, COUNT(j.id) as job_count 
                                    FROM job_categories cat
                                    LEFT JOIN jobs j ON cat.id = j.category_id AND j.status = 'active'
                                    WHERE cat.is_active = 1
                                    GROUP BY cat.id
                                    ORDER BY job_count DESC
                                    LIMIT 8";
                $categories_result = mysqli_query($conn, $categories_query);
                
                while($category = mysqli_fetch_assoc($categories_result)):
                ?>
                <div class="col-md-3 col-sm-6 mb-4">
                    <a href="public/jobs.php?category=<?php echo $category['id']; ?>" class="text-decoration-none text-dark">
                        <div class="category-card">
                            <i class="fas <?php echo htmlspecialchars($category['icon']); ?>"></i>
                            <h5 class="mt-3"><?php echo htmlspecialchars($category['name']); ?></h5>
                            <p class="mb-0 text-muted"><?php echo $category['job_count']; ?> Lowongan</p>
                        </div>
                    </a>
                </div>
                <?php endwhile; ?>
            </div>
        </div>
    </section>

    <!-- Features -->
    <section class="py-5">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="fw-bold">Mengapa Memilih Kami?</h2>
                <p class="text-muted">Platform terpercaya untuk mencari pekerjaan</p>
            </div>
            
            <div class="row">
                <div class="col-md-4 mb-4">
                    <div class="text-center">
                        <div class="feature-icon">
                            <i class="fas fa-search"></i>
                        </div>
                        <h4>Mudah Dicari</h4>
                        <p class="text-muted">Sistem pencarian canggih dengan filter lengkap untuk menemukan pekerjaan yang tepat</p>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="text-center">
                        <div class="feature-icon">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <h4>Terpercaya</h4>
                        <p class="text-muted">Semua perusahaan telah diverifikasi untuk keamanan Anda</p>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="text-center">
                        <div class="feature-icon">
                            <i class="fas fa-bolt"></i>
                        </div>
                        <h4>Cepat & Efisien</h4>
                        <p class="text-muted">Proses lamaran yang mudah dan notifikasi real-time</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-5 bg-primary text-white">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h2 class="fw-bold mb-3">Siap Memulai Karir Anda?</h2>
                    <p class="lead mb-0">Daftar sekarang dan temukan ribuan peluang karir terbaik</p>
                </div>
                <div class="col-md-4 text-md-end">
                    <a href="auth/register.php" class="btn btn-light btn-lg">
                        Daftar Sekarang <i class="fas fa-arrow-right ms-2"></i>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-dark text-white py-5">
        <div class="container">
            <div class="row">
                <div class="col-md-4 mb-4">
                    <h5><i class="fas fa-briefcase me-2"></i>JobPortal</h5>
                    <p class="text-muted">Platform terpercaya untuk mencari dan menemukan pekerjaan impian Anda di Indonesia.</p>
                    <div class="social-links">
                        <a href="#" class="text-white me-3"><i class="fab fa-facebook fa-2x"></i></a>
                        <a href="#" class="text-white me-3"><i class="fab fa-twitter fa-2x"></i></a>
                        <a href="#" class="text-white me-3"><i class="fab fa-linkedin fa-2x"></i></a>
                        <a href="#" class="text-white"><i class="fab fa-instagram fa-2x"></i></a>
                    </div>
                </div>
                <div class="col-md-2 mb-4">
                    <h5>Untuk Pencari Kerja</h5>
                    <ul class="list-unstyled">
                        <li><a href="public/jobs.php" class="text-muted text-decoration-none">Cari Lowongan</a></li>
                        <li><a href="auth/register.php" class="text-muted text-decoration-none">Daftar</a></li>
                        <li><a href="auth/login.php" class="text-muted text-decoration-none">Login</a></li>
                    </ul>
                </div>
                <div class="col-md-2 mb-4">
                    <h5>Untuk Perusahaan</h5>
                    <ul class="list-unstyled">
                        <li><a href="auth/register.php" class="text-muted text-decoration-none">Daftar Perusahaan</a></li>
                        <li><a href="employer/post_job.php" class="text-muted text-decoration-none">Pasang Lowongan</a></li>
                    </ul>
                </div>
                <div class="col-md-2 mb-4">
                    <h5>Tentang</h5>
                    <ul class="list-unstyled">
                        <li><a href="public/about.php" class="text-muted text-decoration-none">Tentang Kami</a></li>
                        <li><a href="public/contact.php" class="text-muted text-decoration-none">Kontak</a></li>
                    </ul>
                </div>
                <div class="col-md-2 mb-4">
                    <h5>Legal</h5>
                    <ul class="list-unstyled">
                        <li><a href="#" class="text-muted text-decoration-none">Privacy Policy</a></li>
                        <li><a href="#" class="text-muted text-decoration-none">Terms of Service</a></li>
                    </ul>
                </div>
            </div>
            <hr class="bg-secondary">
            <div class="text-center">
                <p class="mb-0">&copy; 2024 JobPortal. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>