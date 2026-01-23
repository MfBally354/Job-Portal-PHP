<?php
session_start();
require_once '../config/database.php';
require_once '../config/functions.php';

check_login();

if($_SESSION['role'] != 'employer') {
    header("Location: ../index.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Get company info
$company_query = "SELECT * FROM companies WHERE user_id = $user_id";
$company_result = mysqli_query($conn, $company_query);
$company = mysqli_fetch_assoc($company_result);

// Get jobs posted by this company
if($company) {
    $company_id = $company['id'];
    $jobs_query = "SELECT j.*, COUNT(a.id) as applicant_count 
                   FROM jobs j 
                   LEFT JOIN applications a ON j.id = a.job_id 
                   WHERE j.company_id = $company_id 
                   GROUP BY j.id 
                   ORDER BY j.created_at DESC";
    $jobs_result = mysqli_query($conn, $jobs_query);
}

$page_title = 'Dashboard Employer - JobPortal';
$css_path = '../assets/css/style.css';
$js_path = '../assets/js/main.js';
$base_url = '../';
include '../includes/header.php';
?>

<div class="container my-5">
    <h2 class="mb-4"><i class="fas fa-tachometer-alt"></i> Dashboard Employer</h2>
    
    <?php if(!$company): ?>
        <div class="alert alert-warning">
            <h5>Lengkapi Profil Perusahaan</h5>
            <p>Anda perlu melengkapi profil perusahaan sebelum dapat memposting lowongan.</p>
            <a href="company_profile.php" class="btn btn-primary">Lengkapi Profil</a>
        </div>
    <?php else: ?>
        
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <h3><?php echo mysqli_num_rows($jobs_result); ?></h3>
                        <p>Total Lowongan</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <h3>
                            <?php 
                            $active_count = 0;
                            mysqli_data_seek($jobs_result, 0);
                            while($j = mysqli_fetch_assoc($jobs_result)) {
                                if($j['status'] == 'active') $active_count++;
                            }
                            echo $active_count;
                            mysqli_data_seek($jobs_result, 0);
                            ?>
                        </h3>
                        <p>Lowongan Aktif</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <h3>
                            <?php 
                            $total_applicants = 0;
                            mysqli_data_seek($jobs_result, 0);
                            while($j = mysqli_fetch_assoc($jobs_result)) {
                                $total_applicants += $j['applicant_count'];
                            }
                            echo $total_applicants;
                            mysqli_data_seek($jobs_result, 0);
                            ?>
                        </h3>
                        <p>Total Pelamar</p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4>Lowongan Saya</h4>
                    <a href="post_job.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Posting Lowongan Baru
                    </a>
                </div>
                
                <?php if(mysqli_num_rows($jobs_result) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Judul</th>
                                <th>Lokasi</th>
                                <th>Tipe</th>
                                <th>Pelamar</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($job = mysqli_fetch_assoc($jobs_result)): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($job['title']); ?></td>
                                <td><?php echo htmlspecialchars($job['location']); ?></td>
                                <td><?php echo ucfirst(str_replace('-', ' ', $job['type'])); ?></td>
                                <td><span class="badge bg-info"><?php echo $job['applicant_count']; ?></span></td>
                                <td>
                                    <span class="badge bg-<?php echo $job['status'] == 'active' ? 'success' : 'secondary'; ?>">
                                        <?php echo ucfirst($job['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="job_applicants.php?id=<?php echo $job['id']; ?>" class="btn btn-sm btn-info">
                                        <i class="fas fa-users"></i> Pelamar
                                    </a>
                                    <a href="edit_job.php?id=<?php echo $job['id']; ?>" class="btn btn-sm btn-warning">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                    <div class="alert alert-info">Belum ada lowongan yang diposting.</div>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>
