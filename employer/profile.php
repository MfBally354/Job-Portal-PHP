<?php
session_start();
require_once '../config/database.php';
require_once '../config/functions.php';
require_once '../config/constants.php';

check_role('employer');

$user_id = $_SESSION['user_id'];

// Get user data
$user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM users WHERE id = $user_id"));

// Get company data
$company_query = "SELECT * FROM companies WHERE user_id = $user_id";
$company_result = mysqli_query($conn, $company_query);
$company = mysqli_fetch_assoc($company_result);

if(!$company) {
    // Create company if not exists
    mysqli_query($conn, "INSERT INTO companies (user_id, name) VALUES ($user_id, '{$user['name']}')");
    $company = mysqli_fetch_assoc(mysqli_query($conn, $company_query));
}

$error = '';
$success = '';

// Handle form submission
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';
    
    if($action == 'update_profile') {
        $name = sanitize_input($_POST['name']);
        $description = sanitize_input($_POST['description']);
        $website = sanitize_input($_POST['website']);
        $email = sanitize_input($_POST['email']);
        $phone = sanitize_input($_POST['phone']);
        $address = sanitize_input($_POST['address']);
        $city = sanitize_input($_POST['city']);
        $province = sanitize_input($_POST['province']);
        $postal_code = sanitize_input($_POST['postal_code']);
        $industry = sanitize_input($_POST['industry']);
        $employee_count = sanitize_input($_POST['employee_count']);
        $founded_year = !empty($_POST['founded_year']) ? (int)$_POST['founded_year'] : NULL;
        
        $founded_sql = $founded_year ? $founded_year : 'NULL';
        
        $update_query = "UPDATE companies SET 
            name = '$name',
            description = '$description',
            website = '$website',
            email = '$email',
            phone = '$phone',
            address = '$address',
            city = '$city',
            province = '$province',
            postal_code = '$postal_code',
            industry = '$industry',
            employee_count = '$employee_count',
            founded_year = $founded_sql
            WHERE user_id = $user_id";
        
        if(mysqli_query($conn, $update_query)) {
            $success = 'Company profile updated successfully!';
            $company = mysqli_fetch_assoc(mysqli_query($conn, $company_query));
        } else {
            $error = 'Failed to update profile: ' . mysqli_error($conn);
        }
    }
    
    if($action == 'upload_logo') {
        if(isset($_FILES['logo']) && $_FILES['logo']['error'] == 0) {
            $upload = upload_image($_FILES['logo'], LOGO_PATH, MAX_IMAGE_SIZE);
            
            if($upload['success']) {
                // Delete old logo
                if(!empty($company['logo']) && file_exists(LOGO_PATH . $company['logo'])) {
                    unlink(LOGO_PATH . $company['logo']);
                }
                
                mysqli_query($conn, "UPDATE companies SET logo = '{$upload['file_name']}' WHERE user_id = $user_id");
                $success = 'Logo uploaded successfully!';
                $company = mysqli_fetch_assoc(mysqli_query($conn, $company_query));
            } else {
                $error = $upload['message'];
            }
        }
    }
}

$page_title = 'Company Profile';
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
        .company-logo-preview {
            width: 200px;
            height: 200px;
            object-fit: contain;
            border: 2px solid #e3e6f0;
            padding: 10px;
            border-radius: 10px;
        }
        .form-section {
            background: #f8f9fc;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        .verification-status {
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="../index.php">
                <i class="fas fa-briefcase me-2"></i><strong>JobPortal</strong>
            </a>
            <div class="ms-auto">
                <a href="dashboard.php" class="btn btn-outline-light btn-sm">
                    <i class="fas fa-arrow-left"></i> Back to Dashboard
                </a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <section class="py-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-10 mx-auto">
                    <?php if($error): ?>
                        <div class="alert alert-danger alert-dismissible fade show">
                            <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <?php if($success): ?>
                        <div class="alert alert-success alert-dismissible fade show">
                            <i class="fas fa-check-circle"></i> <?php echo $success; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <!-- Verification Status -->
                    <?php if($company['is_verified']): ?>
                        <div class="verification-status bg-success text-white">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-check-circle fa-3x me-3"></i>
                                <div>
                                    <h5 class="mb-0">Company Verified</h5>
                                    <p class="mb-0">Your company has been verified by admin</p>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="verification-status bg-warning">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-clock fa-3x me-3"></i>
                                <div>
                                    <h5 class="mb-0">Verification Pending</h5>
                                    <p class="mb-0">Complete your profile to get verified. Some features may be limited until verification.</p>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <h2 class="mb-4"><i class="fas fa-building"></i> Company Profile</h2>

                    <div class="row">
                        <!-- Logo Section -->
                        <div class="col-md-4 mb-4">
                            <div class="card">
                                <div class="card-header bg-white">
                                    <h5 class="mb-0"><i class="fas fa-image"></i> Company Logo</h5>
                                </div>
                                <div class="card-body text-center">
                                    <?php if($company['logo']): ?>
                                        <img src="../assets/uploads/logos/<?php echo htmlspecialchars($company['logo']); ?>" 
                                             alt="Logo" class="company-logo-preview mb-3">
                                    <?php else: ?>
                                        <div class="company-logo-preview bg-light d-flex align-items-center justify-content-center mx-auto mb-3">
                                            <i class="fas fa-building fa-5x text-secondary"></i>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <form method="POST" enctype="multipart/form-data">
                                        <input type="hidden" name="action" value="upload_logo">
                                        <div class="mb-3">
                                            <input type="file" class="form-control" name="logo" accept="image/*" required>
                                            <small class="text-muted">PNG, JPG, GIF (Max 2MB)</small>
                                        </div>
                                        <button type="submit" class="btn btn-primary w-100">
                                            <i class="fas fa-upload"></i> Upload Logo
                                        </button>
                                    </form>
                                </div>
                            </div>

                            <!-- Profile Completeness -->
                            <div class="card mt-4">
                                <div class="card-header bg-white">
                                    <h5 class="mb-0"><i class="fas fa-tasks"></i> Profile Completeness</h5>
                                </div>
                                <div class="card-body">
                                    <?php
                                    $completeness = 0;
                                    $fields = ['name', 'description', 'logo', 'website', 'address', 'city', 'industry', 'employee_count'];
                                    foreach($fields as $field) {
                                        if(!empty($company[$field])) $completeness += 12.5;
                                    }
                                    $completeness = round($completeness);
                                    ?>
                                    <div class="progress mb-2" style="height: 25px;">
                                        <div class="progress-bar bg-success" style="width: <?php echo $completeness; ?>%">
                                            <?php echo $completeness; ?>%
                                        </div>
                                    </div>
                                    <small class="text-muted">Complete your profile to increase visibility</small>
                                </div>
                            </div>
                        </div>

                        <!-- Profile Form -->
                        <div class="col-md-8">
                            <div class="card">
                                <div class="card-header bg-white">
                                    <h5 class="mb-0"><i class="fas fa-edit"></i> Company Information</h5>
                                </div>
                                <div class="card-body">
                                    <form method="POST">
                                        <input type="hidden" name="action" value="update_profile">
                                        
                                        <!-- Basic Info -->
                                        <div class="form-section">
                                            <h5 class="mb-3">Basic Information</h5>
                                            
                                            <div class="mb-3">
                                                <label class="form-label">Company Name <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control" name="name" required 
                                                       value="<?php echo htmlspecialchars($company['name']); ?>">
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">Company Description</label>
                                                <textarea class="form-control" name="description" rows="5" 
                                                          placeholder="Tell us about your company..."><?php echo htmlspecialchars($company['description'] ?? ''); ?></textarea>
                                                <small class="text-muted">Provide a brief overview of your company, what you do, and your culture</small>
                                            </div>

                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">Industry</label>
                                                    <input type="text" class="form-control" name="industry" 
                                                           placeholder="e.g., Technology, Healthcare" 
                                                           value="<?php echo htmlspecialchars($company['industry'] ?? ''); ?>">
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">Company Size</label>
                                                    <select class="form-select" name="employee_count">
                                                        <option value="">Select size</option>
                                                        <option value="1-10" <?php echo $company['employee_count'] == '1-10' ? 'selected' : ''; ?>>1-10 employees</option>
                                                        <option value="11-50" <?php echo $company['employee_count'] == '11-50' ? 'selected' : ''; ?>>11-50 employees</option>
                                                        <option value="51-200" <?php echo $company['employee_count'] == '51-200' ? 'selected' : ''; ?>>51-200 employees</option>
                                                        <option value="201-500" <?php echo $company['employee_count'] == '201-500' ? 'selected' : ''; ?>>201-500 employees</option>
                                                        <option value="501-1000" <?php echo $company['employee_count'] == '501-1000' ? 'selected' : ''; ?>>501-1000 employees</option>
                                                        <option value="1000+" <?php echo $company['employee_count'] == '1000+' ? 'selected' : ''; ?>>1000+ employees</option>
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">Founded Year</label>
                                                <input type="number" class="form-control" name="founded_year" 
                                                       min="1800" max="<?php echo date('Y'); ?>" 
                                                       value="<?php echo $company['founded_year']; ?>">
                                            </div>
                                        </div>

                                        <!-- Contact Info -->
                                        <div class="form-section">
                                            <h5 class="mb-3">Contact Information</h5>
                                            
                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">Email</label>
                                                    <input type="email" class="form-control" name="email" 
                                                           value="<?php echo htmlspecialchars($company['email'] ?? ''); ?>">
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">Phone</label>
                                                    <input type="text" class="form-control" name="phone" 
                                                           value="<?php echo htmlspecialchars($company['phone'] ?? ''); ?>">
                                                </div>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">Website</label>
                                                <input type="url" class="form-control" name="website" 
                                                       placeholder="https://yourcompany.com" 
                                                       value="<?php echo htmlspecialchars($company['website'] ?? ''); ?>">
                                            </div>
                                        </div>

                                        <!-- Location -->
                                        <div class="form-section">
                                            <h5 class="mb-3">Location</h5>
                                            
                                            <div class="mb-3">
                                                <label class="form-label">Address</label>
                                                <textarea class="form-control" name="address" rows="2"><?php echo htmlspecialchars($company['address'] ?? ''); ?></textarea>
                                            </div>

                                            <div class="row">
                                                <div class="col-md-5 mb-3">
                                                    <label class="form-label">City</label>
                                                    <input type="text" class="form-control" name="city" 
                                                           value="<?php echo htmlspecialchars($company['city'] ?? ''); ?>">
                                                </div>
                                                <div class="col-md-4 mb-3">
                                                    <label class="form-label">Province</label>
                                                    <select class="form-select" name="province">
                                                        <option value="">Select Province</option>
                                                        <?php foreach(INDONESIAN_PROVINCES as $prov): ?>
                                                            <option value="<?php echo $prov; ?>" <?php echo $company['province'] == $prov ? 'selected' : ''; ?>>
                                                                <?php echo $prov; ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                                <div class="col-md-3 mb-3">
                                                    <label class="form-label">Postal Code</label>
                                                    <input type="text" class="form-control" name="postal_code" 
                                                           value="<?php echo htmlspecialchars($company['postal_code'] ?? ''); ?>">
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Submit Button -->
                                        <div class="d-flex gap-2">
                                            <button type="submit" class="btn btn-primary btn-lg">
                                                <i class="fas fa-save"></i> Save Changes
                                            </button>
                                            <a href="dashboard.php" class="btn btn-outline-secondary btn-lg">
                                                <i class="fas fa-times"></i> Cancel
                                            </a>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>