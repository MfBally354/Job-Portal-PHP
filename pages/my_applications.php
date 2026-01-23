<?php
session_start();
require_once '../config/database.php';
require_once '../config/functions.php';

check_login();

if($_SESSION['role'] != 'jobseeker') {
    header("Location: ../index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$query = "SELECT a.*, j.title, j.location, j.type, c.name as company_name, c.logo 
          FROM applications a 
          LEFT JOIN jobs j ON a.job_id = j.id 
          LEFT JOIN companies c ON j.company_id = c.id 
          WHERE a.user_id = $user_id 
          ORDER BY a.applied_at DESC";
$result = mysqli_query($conn, $query);

$page_title = 'Lamaran Saya - JobPortal';
$css_path = '../assets/css/style.css';
$js_path = '../assets/js/main.js';
$base_url = '../';
include '../includes/header.php';
?>

<div class="container my-5">
    <h2 class="mb-4"><i class="fas fa-file-alt"></i> Lamaran Saya</h2>
    
    <?php if(mysqli_num_rows($result) > 0): ?>
    <div class="row">
        <?php while($app = mysqli_fetch_assoc($result)): ?>
        <div class="col-md-6 mb-4">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-start mb-3">
                        <?php if($app['logo']): ?>
                            <img src="../assets/uploads/logos/<?php echo htmlspecialchars($app['logo']); ?>" 
                                 alt="Logo" class="company-logo me-3">
                        <?php else: ?>
                            <div class="company-logo-placeholder me-3">
                                <i class="fas fa-building"></i>
                            </div>
                        <?php endif; ?>
                        <div class="flex-grow-1">
                            <h5 class="mb-1"><?php echo htmlspecialchars($app['title']); ?></h5>
                            <h6 class="text-muted"><?php echo htmlspecialchars($app['company_name']); ?></h6>
                        </div>
                    </div>
                    
                    <p class="mb-2"><i class="fas fa-map-marker-alt text-primary"></i> <?php echo htmlspecialchars($app['location']); ?></p>
                    <p class="mb-3"><i class="fas fa-calendar text-primary"></i> Dilamar: <?php echo date('d M Y', strtotime($app['applied_at'])); ?></p>
                    
                    <div class="mb-3">
                        <span class="badge bg-<?php 
                            echo $app['status'] == 'pending' ? 'warning' : 
                                ($app['status'] == 'reviewed' ? 'info' : 
                                ($app['status'] == 'accepted' ? 'success' : 'danger')); 
                        ?>">
                            <?php echo ucfirst($app['status']); ?>
                        </span>
                    </div>
                    
                    <a href="job_detail.php?id=<?php echo $app['job_id']; ?>" class="btn btn-outline-primary btn-sm">
                        Lihat Detail
                    </a>
                </div>
            </div>
        </div>
        <?php endwhile; ?>
    </div>
    <?php else: ?>
    <div class="alert alert-info">
        <p class="mb-0">Anda belum melamar pekerjaan apapun. <a href="jobs.php">Cari lowongan sekarang</a></p>
    </div>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>
