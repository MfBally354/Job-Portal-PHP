<?php
// Determine active page
$current_page = basename($_SERVER['PHP_SELF']);
$current_dir = basename(dirname($_SERVER['PHP_SELF']));

// Get base URL for links
$base = ($current_dir == 'public' || $current_dir == 'auth') ? '../' : '';
?>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top shadow">
    <div class="container">
        <a class="navbar-brand fw-bold" href="<?php echo $base; ?>index.php">
            <i class="fas fa-briefcase me-2"></i>
            <span class="text-primary-gradient">Job</span><span class="text-white">Portal</span>
        </a>
        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" 
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarNav">
            <!-- Left Menu -->
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link <?php echo $current_page == 'index.php' ? 'active' : ''; ?>" 
                       href="<?php echo $base; ?>index.php">
                        <i class="fas fa-home"></i> Home
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $current_page == 'jobs.php' ? 'active' : ''; ?>" 
                       href="<?php echo $base; ?>public/jobs.php">
                        <i class="fas fa-search"></i> Cari Lowongan
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $current_page == 'companies.php' ? 'active' : ''; ?>" 
                       href="<?php echo $base; ?>public/companies.php">
                        <i class="fas fa-building"></i> Perusahaan
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $current_page == 'contact.php' ? 'active' : ''; ?>" 
                       href="<?php echo $base; ?>public/contact.php">
                        <i class="fas fa-envelope"></i> Kontak
                    </a>
                </li>
            </ul>
            
            <!-- Right Menu -->
            <ul class="navbar-nav ms-auto">
                <?php if(isset($_SESSION['user_id'])): ?>
                    
                    <!-- Notifications Dropdown -->
                    <?php if($_SESSION['role'] == 'jobseeker' || $_SESSION['role'] == 'employer'): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link position-relative" href="#" role="button" 
                           data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-bell fa-lg"></i>
                            <?php 
                            $unread_count = get_unread_notifications_count($_SESSION['user_id']);
                            if($unread_count > 0): 
                            ?>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                <?php echo $unread_count > 9 ? '9+' : $unread_count; ?>
                            </span>
                            <?php endif; ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" style="width: 300px; max-height: 400px; overflow-y: auto;">
                            <li class="dropdown-header d-flex justify-content-between align-items-center">
                                <span>Notifications</span>
                                <span class="badge bg-primary"><?php echo $unread_count; ?> new</span>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <?php
                            $notif_query = "SELECT * FROM notifications WHERE user_id = {$_SESSION['user_id']} 
                                          ORDER BY created_at DESC LIMIT 5";
                            $notif_result = mysqli_query($conn, $notif_query);
                            
                            if(mysqli_num_rows($notif_result) > 0):
                                while($notif = mysqli_fetch_assoc($notif_result)):
                            ?>
                            <li>
                                <a class="dropdown-item <?php echo $notif['is_read'] ? '' : 'bg-light'; ?>" 
                                   href="<?php echo $notif['link'] ?? '#'; ?>">
                                    <div class="d-flex">
                                        <div class="flex-shrink-0">
                                            <i class="fas fa-<?php echo $notif['type'] == 'success' ? 'check-circle text-success' : 
                                                                      ($notif['type'] == 'warning' ? 'exclamation-triangle text-warning' : 
                                                                       'info-circle text-info'); ?> fa-lg me-2"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <strong class="d-block"><?php echo htmlspecialchars($notif['title']); ?></strong>
                                            <small class="text-muted"><?php echo htmlspecialchars($notif['message']); ?></small>
                                            <br>
                                            <small class="text-muted"><?php echo time_ago($notif['created_at']); ?></small>
                                        </div>
                                    </div>
                                </a>
                            </li>
                            <?php 
                                endwhile;
                            else:
                            ?>
                            <li class="dropdown-item text-center text-muted">No notifications</li>
                            <?php endif; ?>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item text-center text-primary" href="<?php echo $base; ?>notifications.php">
                                    View all notifications
                                </a>
                            </li>
                        </ul>
                    </li>
                    <?php endif; ?>
                    
                    <!-- User Dropdown -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" 
                           data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-user-circle fa-lg"></i>
                            <span class="ms-1"><?php echo htmlspecialchars($_SESSION['name']); ?></span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <?php if($_SESSION['role'] == 'admin'): ?>
                                <li>
                                    <a class="dropdown-item" href="<?php echo $base; ?>admin/dashboard.php">
                                        <i class="fas fa-tachometer-alt"></i> Admin Dashboard
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="<?php echo $base; ?>admin/users.php">
                                        <i class="fas fa-users"></i> Users
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="<?php echo $base; ?>admin/companies.php">
                                        <i class="fas fa-building"></i> Companies
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="<?php echo $base; ?>admin/jobs.php">
                                        <i class="fas fa-briefcase"></i> Jobs
                                    </a>
                                </li>
                                
                            <?php elseif($_SESSION['role'] == 'employer'): ?>
                                <li>
                                    <a class="dropdown-item" href="<?php echo $base; ?>employer/dashboard.php">
                                        <i class="fas fa-tachometer-alt"></i> Dashboard
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="<?php echo $base; ?>employer/profile.php">
                                        <i class="fas fa-building"></i> Company Profile
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="<?php echo $base; ?>employer/post_job.php">
                                        <i class="fas fa-plus-circle"></i> Post Job
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="<?php echo $base; ?>employer/my_jobs.php">
                                        <i class="fas fa-list"></i> My Jobs
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="<?php echo $base; ?>employer/applicants.php">
                                        <i class="fas fa-users"></i> Applicants
                                    </a>
                                </li>
                                
                            <?php else: // jobseeker ?>
                                <li>
                                    <a class="dropdown-item" href="<?php echo $base; ?>jobseeker/dashboard.php">
                                        <i class="fas fa-tachometer-alt"></i> Dashboard
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="<?php echo $base; ?>jobseeker/profile.php">
                                        <i class="fas fa-user-edit"></i> My Profile
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="<?php echo $base; ?>jobseeker/my_applications.php">
                                        <i class="fas fa-file-alt"></i> My Applications
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="<?php echo $base; ?>jobseeker/saved_jobs.php">
                                        <i class="fas fa-heart"></i> Saved Jobs
                                    </a>
                                </li>
                            <?php endif; ?>
                            
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item" href="<?php echo $base; ?>settings.php">
                                    <i class="fas fa-cog"></i> Settings
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item text-danger" href="<?php echo $base; ?>auth/logout.php">
                                    <i class="fas fa-sign-out-alt"></i> Logout
                                </a>
                            </li>
                        </ul>
                    </li>
                    
                <?php else: ?>
                    <!-- Not logged in -->
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo $base; ?>auth/login.php">
                            <i class="fas fa-sign-in-alt"></i> Login
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-gradient btn-sm ms-2" href="<?php echo $base; ?>auth/register.php">
                            <i class="fas fa-user-plus"></i> Daftar Sekarang
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>