<?php
session_start();
require_once '../config/database.php';
require_once '../config/functions.php';

if(!isset($_GET['id'])) {
    header("Location: jobs.php");
    exit();
}

$job_id = intval($_GET['id']);
$query = "SELECT j.*, c.name as company_name, c.description as company_desc, c.logo, c.website, c.location as company_location
          FROM jobs j 
          LEFT JOIN companies c ON j.company_id = c.id 
          WHERE j.id = $job_id";
$result = mysqli_query($conn, $query);

if(mysqli_num_rows($result) == 0) {
    header("Location: jobs.php");
    exit();
}

$job = mysqli_fetch_assoc($result);

// Cek apakah user sudah melamar
$already_applied = false;
if(isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $check_query = "SELECT * FROM applications WHERE job_id = $job_id AND user_id = $user_id";
    $check_result = mysqli_query($conn, $check_query);
    $already_applied = mysqli_num_rows($check_result) > 0;
}

$page_title = $job['title'] . ' - JobPortal';
$css_path = '../assets/css/style.css';
$js_path = '../assets/js/main.js';
$base_url = '../';
include '../includes/header.php';
?>

<div class="container my-5">
    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <div class="d-flex align-items-start mb-4">
                        <?php if($job['logo']): ?>
                            <img src="../assets/uploads/logos/<?php echo htmlspecialchars($job['logo']); ?>" 
                                 alt="Logo" class="company-logo-large me-4">
                        <?php else: ?>
                            <div class="company-logo-placeholder-large me-4">
                                <i class="fas fa-building fa-3x"></i>
                            </div>
                        <?php endif; ?>
                        <div>
                            <h2 class="mb-2"><?php echo htmlspecialchars($job['title']); ?></h2>
                            <h5 class="text-primary mb-3"><?php echo htmlspecialchars($job['company_name']); ?></h5>
                            <div class="d-flex flex-wrap gap-3">
                                <span class="badge bg-primary"><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($job['location']); ?></span>
                                <span class="badge bg-info"><i class="fas fa-briefcase"></i> <?php echo ucfirst(str_replace('-', ' ', $job['type'])); ?></span>
                                <?php if($job['salary_min'] && $job['salary_max']): ?>
                                <span class="badge bg-success"><i class="fas fa-money-bill-wave"></i> 
                                    Rp <?php echo number_format($job['salary_min'], 0, ',', '.'); ?> - 
                                    Rp <?php echo number_format($job['salary_max'], 0, ',', '.'); ?>
                                </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <?php if(isset($_SESSION['user_id']) && $_SESSION['role'] == 'jobseeker'): ?>
                        <?php if($already_applied): ?>
                            <div class="alert alert-info">
                                <i class="fas fa-check-circle"></i> Anda sudah melamar pekerjaan ini
                            </div>
                        <?php else: ?>
                            <a href="apply_job.php?id=<?php echo $job_id; ?>" class="btn btn-primary btn-lg w-100 mb-4">
                                <i class="fas fa-paper-plane"></i> Lamar Sekarang
                            </a>
                        <?php endif; ?>
                    <?php elseif(!isset($_SESSION['user_id'])): ?>
                        <a href="login.php" class="btn btn-primary btn-lg w-100 mb-4">
                            <i class="fas fa-sign-in-alt"></i> Login untuk Melamar
                        </a>
                    <?php endif; ?>
                    
                    <hr>
                    
                    <h4 class="mb-3">Deskripsi Pekerjaan</h4>
                    <div class="job-description">
                        <?php echo nl2br(htmlspecialchars($job['description'])); ?>
                    </div>
                    
                    <?php if($job['requirements']): ?>
                    <hr>
                    <h4 class="mb-3">Persyaratan</h4>
                    <div class="job-requirements">
                        <?php echo nl2br(htmlspecialchars($job['requirements'])); ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <h5 class="card-title">Tentang Perusahaan</h5>
                    <hr>
                    <h6><?php echo htmlspecialchars($job['company_name']); ?></h6>
                    <p class="text-muted small"><?php echo htmlspecialchars($job['company_desc'] ?? 'Tidak ada deskripsi'); ?></p>
                    <?php if($job['company_location']): ?>
                    <p class="mb-2"><i class="fas fa-map-marker-alt text-primary"></i> <?php echo htmlspecialchars($job['company_location']); ?></p>
                    <?php endif; ?>
                    <?php if($job['website']): ?>
                    <p class="mb-0"><i class="fas fa-globe text-primary"></i> 
                        <a href="<?php echo htmlspecialchars($job['website']); ?>" target="_blank">Website</a>
                    </p>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">Informasi Lowongan</h5>
                    <hr>
                    <p class="mb-2"><strong>Diposting:</strong> <?php echo date('d M Y', strtotime($job['created_at'])); ?></p>
                    <p class="mb-2"><strong>Status:</strong> 
                        <span class="badge bg-<?php echo $job['status'] == 'active' ? 'success' : 'secondary'; ?>">
                            <?php echo ucfirst($job['status']); ?>
                        </span>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
