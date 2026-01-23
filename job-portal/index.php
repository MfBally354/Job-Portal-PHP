<?php
session_start();
require_once 'config/database.php';
require_once 'config/functions.php';
$page_title = 'Home - Portal Lowongan Kerja';
$css_path = 'assets/css/style.css';
$js_path = 'assets/js/main.js';
$base_url = '';
include 'includes/header.php';
?>

<div class="hero-section text-white py-5">
    <div class="container text-center">
        <h1 class="display-4 fw-bold mb-3">Temukan Pekerjaan Impian Anda</h1>
        <p class="lead mb-4">Ribuan lowongan kerja dari perusahaan terpercaya menunggu Anda</p>
        <form class="row g-3 justify-content-center" action="pages/jobs.php" method="GET">
            <div class="col-md-4">
                <input type="text" class="form-control form-control-lg" name="keyword" placeholder="Posisi atau kata kunci">
            </div>
            <div class="col-md-3">
                <input type="text" class="form-control form-control-lg" name="location" placeholder="Lokasi">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-warning btn-lg w-100">
                    <i class="fas fa-search"></i> Cari
                </button>
            </div>
        </form>
    </div>
</div>

<div class="container my-5">
    <div class="text-center mb-5">
        <h2 class="fw-bold">Lowongan Kerja Terbaru</h2>
        <p class="text-muted">Temukan peluang karir terbaik untuk Anda</p>
    </div>
    
    <div class="row">
        <?php
        $query = "SELECT j.*, c.name as company_name, c.logo 
                  FROM jobs j 
                  LEFT JOIN companies c ON j.company_id = c.id 
                  WHERE j.status = 'active' 
                  ORDER BY j.created_at DESC 
                  LIMIT 6";
        $result = mysqli_query($conn, $query);
        
        if(mysqli_num_rows($result) > 0):
            while($job = mysqli_fetch_assoc($result)):
        ?>
        <div class="col-md-6 col-lg-4 mb-4">
            <div class="card h-100 shadow-sm hover-card">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <?php if($job['logo']): ?>
                            <img src="assets/uploads/logos/<?php echo htmlspecialchars($job['logo']); ?>" 
                                 alt="Logo" class="company-logo me-3">
                        <?php else: ?>
                            <div class="company-logo-placeholder me-3">
                                <i class="fas fa-building"></i>
                            </div>
                        <?php endif; ?>
                        <div>
                            <h5 class="card-title mb-1"><?php echo htmlspecialchars($job['title']); ?></h5>
                            <h6 class="text-muted mb-0"><?php echo htmlspecialchars($job['company_name']); ?></h6>
                        </div>
                    </div>
                    <p class="mb-2"><i class="fas fa-map-marker-alt text-primary"></i> <?php echo htmlspecialchars($job['location']); ?></p>
                    <p class="mb-2"><i class="fas fa-briefcase text-primary"></i> <?php echo ucfirst($job['type']); ?></p>
                    <?php if($job['salary_min'] && $job['salary_max']): ?>
                    <p class="mb-3"><i class="fas fa-money-bill-wave text-success"></i> 
                        Rp <?php echo number_format($job['salary_min'], 0, ',', '.'); ?> - 
                        Rp <?php echo number_format($job['salary_max'], 0, ',', '.'); ?>
                    </p>
                    <?php endif; ?>
                    <div class="d-flex justify-content-between align-items-center">
                        <small class="text-muted"><?php echo time_ago($job['created_at']); ?></small>
                        <a href="pages/job_detail.php?id=<?php echo $job['id']; ?>" class="btn btn-primary btn-sm">
                            Lihat Detail
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <?php 
            endwhile;
        else:
            echo '<div class="col-12"><div class="alert alert-info text-center">Belum ada lowongan tersedia saat ini.</div></div>';
        endif;
        ?>
    </div>
    
    <div class="text-center mt-4">
        <a href="pages/jobs.php" class="btn btn-outline-primary btn-lg">Lihat Semua Lowongan</a>
    </div>
</div>

<div class="bg-light py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-bold">Mengapa Memilih JobPortal?</h2>
        </div>
        <div class="row text-center">
            <div class="col-md-4 mb-4">
                <div class="feature-box">
                    <i class="fas fa-search fa-3x text-primary mb-3"></i>
                    <h4>Mudah Dicari</h4>
                    <p>Sistem pencarian canggih membantu Anda menemukan pekerjaan yang sesuai</p>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="feature-box">
                    <i class="fas fa-shield-alt fa-3x text-success mb-3"></i>
                    <h4>Terpercaya</h4>
                    <p>Semua perusahaan telah terverifikasi dan terpercaya</p>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="feature-box">
                    <i class="fas fa-rocket fa-3x text-warning mb-3"></i>
                    <h4>Cepat & Efisien</h4>
                    <p>Proses lamaran yang cepat dan notifikasi real-time</p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
