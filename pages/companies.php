<?php
session_start();
require_once '../config/database.php';
require_once '../config/functions.php';

$query = "SELECT c.*, COUNT(j.id) as job_count 
          FROM companies c 
          LEFT JOIN jobs j ON c.id = j.company_id AND j.status = 'active'
          GROUP BY c.id 
          ORDER BY c.created_at DESC";
$result = mysqli_query($conn, $query);

$page_title = 'Perusahaan - JobPortal';
$css_path = '../assets/css/style.css';
$js_path = '../assets/js/main.js';
$base_url = '../';
include '../includes/header.php';
?>

<div class="container my-5">
    <h2 class="mb-4"><i class="fas fa-building"></i> Daftar Perusahaan</h2>
    
    <div class="row">
        <?php if(mysqli_num_rows($result) > 0): ?>
            <?php while($company = mysqli_fetch_assoc($result)): ?>
            <div class="col-md-4 mb-4">
                <div class="card h-100 shadow-sm hover-card">
                    <div class="card-body text-center">
                        <?php if($company['logo']): ?>
                            <img src="../assets/uploads/logos/<?php echo htmlspecialchars($company['logo']); ?>" 
                                 alt="Logo" class="company-logo-large mb-3">
                        <?php else: ?>
                            <div class="company-logo-placeholder-large mb-3 mx-auto">
                                <i class="fas fa-building fa-3x"></i>
                            </div>
                        <?php endif; ?>
                        <h5 class="card-title"><?php echo htmlspecialchars($company['name']); ?></h5>
                        <p class="text-muted mb-3"><?php echo substr(htmlspecialchars($company['description'] ?? ''), 0, 100); ?>...</p>
                        <p class="mb-3">
                            <i class="fas fa-map-marker-alt text-primary"></i> 
                            <?php echo htmlspecialchars($company['location'] ?? 'Tidak disebutkan'); ?>
                        </p>
                        <p class="mb-3">
                            <span class="badge bg-primary"><?php echo $company['job_count']; ?> Lowongan Aktif</span>
                        </p>
                        <?php if($company['website']): ?>
                        <a href="<?php echo htmlspecialchars($company['website']); ?>" target="_blank" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-globe"></i> Website
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="col-12">
                <div class="alert alert-info">Belum ada perusahaan terdaftar.</div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
