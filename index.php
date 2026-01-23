<?php
session_start();
require_once 'config/database.php';
$page_title = 'Home - Portal Lowongan Kerja';
include 'includes/header.php';
?>

<div class="hero-section bg-light py-5">
    <div class="container text-center">
        <h1 class="display-4">Temukan Pekerjaan Impian Anda</h1>
        <p class="lead">Ribuan lowongan kerja menunggu Anda</p>
        <form class="row g-3 justify-content-center mt-4" action="pages/jobs.php" method="GET">
            <div class="col-md-4">
                <input type="text" class="form-control" name="keyword" placeholder="Posisi atau kata kunci">
            </div>
            <div class="col-md-3">
                <input type="text" class="form-control" name="location" placeholder="Lokasi">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">Cari Lowongan</button>
            </div>
        </form>
    </div>
</div>

<div class="container my-5">
    <h2 class="text-center mb-4">Lowongan Terbaru</h2>
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
        <div class="col-md-4 mb-4">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title"><?php echo htmlspecialchars($job['title']); ?></h5>
                    <h6 class="card-subtitle mb-2 text-muted"><?php echo htmlspecialchars($job['company_name']); ?></h6>
                    <p class="card-text"><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($job['location']); ?></p>
                    <p class="card-text"><i class="fas fa-briefcase"></i> <?php echo htmlspecialchars($job['type']); ?></p>
                    <a href="pages/job_detail.php?id=<?php echo $job['id']; ?>" class="btn btn-primary btn-sm">Lihat Detail</a>
                </div>
            </div>
        </div>
        <?php 
            endwhile;
        else:
            echo '<div class="col-12"><p class="text-center">Belum ada lowongan tersedia.</p></div>';
        endif;
        ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
