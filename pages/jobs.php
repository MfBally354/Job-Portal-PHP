<?php
session_start();
require_once '../config/database.php';
require_once '../config/functions.php';

$keyword = isset($_GET['keyword']) ? sanitize_input($_GET['keyword']) : '';
$location = isset($_GET['location']) ? sanitize_input($_GET['location']) : '';
$type = isset($_GET['type']) ? sanitize_input($_GET['type']) : '';

$where = "WHERE j.status = 'active'";
if(!empty($keyword)) {
    $where .= " AND (j.title LIKE '%$keyword%' OR j.description LIKE '%$keyword%' OR c.name LIKE '%$keyword%')";
}
if(!empty($location)) {
    $where .= " AND j.location LIKE '%$location%'";
}
if(!empty($type)) {
    $where .= " AND j.type = '$type'";
}

$query = "SELECT j.*, c.name as company_name, c.logo 
          FROM jobs j 
          LEFT JOIN companies c ON j.company_id = c.id 
          $where 
          ORDER BY j.created_at DESC";
$result = mysqli_query($conn, $query);

$page_title = 'Semua Lowongan - JobPortal';
$css_path = '../assets/css/style.css';
$js_path = '../assets/js/main.js';
$base_url = '../';
include '../includes/header.php';
?>

<div class="container my-5">
    <h2 class="mb-4">Semua Lowongan Kerja</h2>
    
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="">
                <div class="row g-3">
                    <div class="col-md-4">
                        <input type="text" class="form-control" name="keyword" 
                               placeholder="Kata kunci" value="<?php echo htmlspecialchars($keyword); ?>">
                    </div>
                    <div class="col-md-3">
                        <input type="text" class="form-control" name="location" 
                               placeholder="Lokasi" value="<?php echo htmlspecialchars($location); ?>">
                    </div>
                    <div class="col-md-3">
                        <select class="form-select" name="type">
                            <option value="">Semua Tipe</option>
                            <option value="full-time" <?php echo $type == 'full-time' ? 'selected' : ''; ?>>Full Time</option>
                            <option value="part-time" <?php echo $type == 'part-time' ? 'selected' : ''; ?>>Part Time</option>
                            <option value="contract" <?php echo $type == 'contract' ? 'selected' : ''; ?>>Contract</option>
                            <option value="internship" <?php echo $type == 'internship' ? 'selected' : ''; ?>>Internship</option>
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
    </div>
    
    <div class="row">
        <?php
        if(mysqli_num_rows($result) > 0):
            while($job = mysqli_fetch_assoc($result)):
        ?>
        <div class="col-md-6 mb-4">
            <div class="card h-100 shadow-sm hover-card">
                <div class="card-body">
                    <div class="d-flex align-items-start mb-3">
                        <?php if($job['logo']): ?>
                            <img src="../assets/uploads/logos/<?php echo htmlspecialchars($job['logo']); ?>" 
                                 alt="Logo" class="company-logo me-3">
                        <?php else: ?>
                            <div class="company-logo-placeholder me-3">
                                <i class="fas fa-building"></i>
                            </div>
                        <?php endif; ?>
                        <div class="flex-grow-1">
                            <h5 class="card-title mb-1"><?php echo htmlspecialchars($job['title']); ?></h5>
                            <h6 class="text-muted"><?php echo htmlspecialchars($job['company_name']); ?></h6>
                        </div>
                    </div>
                    <p class="mb-2"><i class="fas fa-map-marker-alt text-primary"></i> <?php echo htmlspecialchars($job['location']); ?></p>
                    <p class="mb-2"><i class="fas fa-briefcase text-primary"></i> <?php echo ucfirst(str_replace('-', ' ', $job['type'])); ?></p>
                    <?php if($job['salary_min'] && $job['salary_max']): ?>
                    <p class="mb-3"><i class="fas fa-money-bill-wave text-success"></i> 
                        Rp <?php echo number_format($job['salary_min'], 0, ',', '.'); ?> - 
                        Rp <?php echo number_format($job['salary_max'], 0, ',', '.'); ?>
                    </p>
                    <?php endif; ?>
                    <p class="text-muted mb-3"><?php echo substr(strip_tags($job['description']), 0, 150); ?>...</p>
                    <div class="d-flex justify-content-between align-items-center">
                        <small class="text-muted"><?php echo time_ago($job['created_at']); ?></small>
                        <a href="job_detail.php?id=<?php echo $job['id']; ?>" class="btn btn-primary btn-sm">
                            Lihat Detail
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php 
        endwhile;
    else:
        echo '<div class="col-12"><div class="alert alert-info">Tidak ada lowongan yang sesuai dengan pencarian Anda.</div></div>';
    endif;
    ?>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
