<?php
session_start();
require_once '../config/database.php';
require_once '../config/functions.php';
require_once '../config/constants.php';

check_role('jobseeker');

$user_id = $_SESSION['user_id'];

// Get user data
$user_query = "SELECT * FROM users WHERE id = $user_id";
$user = mysqli_fetch_assoc(mysqli_query($conn, $user_query));

// Get profile data
$profile_query = "SELECT * FROM jobseeker_profiles WHERE user_id = $user_id";
$profile_result = mysqli_query($conn, $profile_query);
$profile = mysqli_fetch_assoc($profile_result);

if(!$profile) {
    // Create profile if not exists
    mysqli_query($conn, "INSERT INTO jobseeker_profiles (user_id) VALUES ($user_id)");
    $profile = mysqli_fetch_assoc(mysqli_query($conn, $profile_query));
}

// Get experiences
$exp_query = "SELECT * FROM jobseeker_experience WHERE user_id = $user_id ORDER BY start_date DESC";
$experiences = mysqli_query($conn, $exp_query);

// Get education
$edu_query = "SELECT * FROM jobseeker_education WHERE user_id = $user_id ORDER BY start_date DESC";
$educations = mysqli_query($conn, $edu_query);

// Get skills
$skills_query = "SELECT * FROM jobseeker_skills WHERE user_id = $user_id ORDER BY skill_name";
$skills = mysqli_query($conn, $skills_query);

$error = '';
$success = '';

// Handle form submissions
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch($action) {
        case 'update_basic':
            $name = sanitize_input($_POST['name']);
            $phone = sanitize_input($_POST['phone']);
            $bio = sanitize_input($_POST['bio']);
            $date_of_birth = sanitize_input($_POST['date_of_birth']);
            $gender = sanitize_input($_POST['gender']);
            $city = sanitize_input($_POST['city']);
            $province = sanitize_input($_POST['province']);
            $address = sanitize_input($_POST['address']);
            $linkedin_url = sanitize_input($_POST['linkedin_url']);
            $portfolio_url = sanitize_input($_POST['portfolio_url']);
            
            // Update user
            mysqli_query($conn, "UPDATE users SET name = '$name', phone = '$phone' WHERE id = $user_id");
            
            // Update profile
            $update_profile = "UPDATE jobseeker_profiles SET 
                bio = '$bio', date_of_birth = '$date_of_birth', gender = '$gender',
                city = '$city', province = '$province', address = '$address',
                linkedin_url = '$linkedin_url', portfolio_url = '$portfolio_url'
                WHERE user_id = $user_id";
            
            if(mysqli_query($conn, $update_profile)) {
                $success = 'Profile updated successfully!';
                $_SESSION['name'] = $name;
            } else {
                $error = 'Failed to update profile';
            }
            break;
            
        case 'upload_cv':
            if(isset($_FILES['cv']) && $_FILES['cv']['error'] == 0) {
                $upload = upload_file($_FILES['cv'], CV_PATH, ALLOWED_CV_EXT, MAX_CV_SIZE);
                if($upload['success']) {
                    // Delete old CV
                    if(!empty($profile['cv_path']) && file_exists(CV_PATH . $profile['cv_path'])) {
                        unlink(CV_PATH . $profile['cv_path']);
                    }
                    mysqli_query($conn, "UPDATE jobseeker_profiles SET cv_path = '{$upload['file_name']}' WHERE user_id = $user_id");
                    $success = 'CV uploaded successfully!';
                } else {
                    $error = $upload['message'];
                }
            }
            break;
            
        case 'add_experience':
            $job_title = sanitize_input($_POST['job_title']);
            $company_name = sanitize_input($_POST['company_name']);
            $exp_location = sanitize_input($_POST['exp_location']);
            $start_date = sanitize_input($_POST['start_date']);
            $end_date = isset($_POST['is_current']) ? NULL : sanitize_input($_POST['end_date']);
            $is_current = isset($_POST['is_current']) ? 1 : 0;
            $description = sanitize_input($_POST['description']);
            
            $end_date_sql = $end_date ? "'$end_date'" : 'NULL';
            
            $insert_exp = "INSERT INTO jobseeker_experience 
                (user_id, job_title, company_name, location, start_date, end_date, is_current, description)
                VALUES ($user_id, '$job_title', '$company_name', '$exp_location', '$start_date', $end_date_sql, $is_current, '$description')";
            
            if(mysqli_query($conn, $insert_exp)) {
                $success = 'Experience added successfully!';
            } else {
                $error = 'Failed to add experience';
            }
            break;
            
        case 'add_education':
            $degree = sanitize_input($_POST['degree']);
            $institution = sanitize_input($_POST['institution']);
            $field = sanitize_input($_POST['field_of_study']);
            $edu_start = sanitize_input($_POST['edu_start_date']);
            $edu_end = isset($_POST['edu_is_current']) ? NULL : sanitize_input($_POST['edu_end_date']);
            $edu_current = isset($_POST['edu_is_current']) ? 1 : 0;
            $gpa = sanitize_input($_POST['gpa']);
            $edu_desc = sanitize_input($_POST['edu_description']);
            
            $edu_end_sql = $edu_end ? "'$edu_end'" : 'NULL';
            
            $insert_edu = "INSERT INTO jobseeker_education 
                (user_id, degree, institution, field_of_study, start_date, end_date, is_current, gpa, description)
                VALUES ($user_id, '$degree', '$institution', '$field', '$edu_start', $edu_end_sql, $edu_current, '$gpa', '$edu_desc')";
            
            if(mysqli_query($conn, $insert_edu)) {
                $success = 'Education added successfully!';
            } else {
                $error = 'Failed to add education';
            }
            break;
            
        case 'add_skill':
            $skill_name = sanitize_input($_POST['skill_name']);
            $skill_level = sanitize_input($_POST['skill_level']);
            $years = (int)$_POST['years_of_experience'];
            
            $insert_skill = "INSERT INTO jobseeker_skills (user_id, skill_name, level, years_of_experience)
                VALUES ($user_id, '$skill_name', '$skill_level', $years)";
            
            if(mysqli_query($conn, $insert_skill)) {
                $success = 'Skill added successfully!';
            } else {
                $error = 'Failed to add skill';
            }
            break;
    }
    
    // Refresh data
    $user = mysqli_fetch_assoc(mysqli_query($conn, $user_query));
    $profile = mysqli_fetch_assoc(mysqli_query($conn, $profile_query));
    $experiences = mysqli_query($conn, $exp_query);
    $educations = mysqli_query($conn, $edu_query);
    $skills = mysqli_query($conn, $skills_query);
}

// Handle delete actions
if(isset($_GET['delete_exp'])) {
    $exp_id = (int)$_GET['delete_exp'];
    mysqli_query($conn, "DELETE FROM jobseeker_experience WHERE id = $exp_id AND user_id = $user_id");
    header("Location: profile.php");
    exit();
}

if(isset($_GET['delete_edu'])) {
    $edu_id = (int)$_GET['delete_edu'];
    mysqli_query($conn, "DELETE FROM jobseeker_education WHERE id = $edu_id AND user_id = $user_id");
    header("Location: profile.php");
    exit();
}

if(isset($_GET['delete_skill'])) {
    $skill_id = (int)$_GET['delete_skill'];
    mysqli_query($conn, "DELETE FROM jobseeker_skills WHERE id = $skill_id AND user_id = $user_id");
    header("Location: profile.php");
    exit();
}

$page_title = 'My Profile';
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
        .profile-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px 0;
        }
        .section-card {
            border-left: 4px solid #667eea;
            margin-bottom: 20px;
        }
        .timeline-item {
            border-left: 2px solid #e9ecef;
            padding-left: 20px;
            padding-bottom: 20px;
            position: relative;
        }
        .timeline-item::before {
            content: '';
            width: 12px;
            height: 12px;
            background: #667eea;
            border-radius: 50%;
            position: absolute;
            left: -7px;
            top: 5px;
        }
        .skill-badge {
            padding: 10px 15px;
            margin: 5px;
            border-radius: 20px;
            display: inline-block;
        }
        .progress-circle {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background: conic-gradient(#667eea 0% 75%, #e9ecef 75% 100%);
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .progress-circle-inner {
            width: 80px;
            height: 80px;
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 20px;
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

    <!-- Profile Header -->
    <section class="profile-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h2><?php echo htmlspecialchars($user['name']); ?></h2>
                    <p class="mb-1"><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($user['email']); ?></p>
                    <?php if($user['phone']): ?>
                    <p class="mb-0"><i class="fas fa-phone"></i> <?php echo htmlspecialchars($user['phone']); ?></p>
                    <?php endif; ?>
                </div>
                <div class="col-md-4 text-md-end">
                    <div class="progress-circle mx-auto mx-md-0 ms-md-auto">
                        <div class="progress-circle-inner">75%</div>
                    </div>
                    <small class="d-block mt-2">Profile Completeness</small>
                </div>
            </div>
        </div>
    </section>

    <!-- Main Content -->
    <section class="py-5">
        <div class="container">
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

            <div class="row">
                <!-- Main Profile -->
                <div class="col-lg-8">
                    <!-- Basic Information -->
                    <div class="card section-card mb-4">
                        <div class="card-header bg-white d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><i class="fas fa-user"></i> Basic Information</h5>
                            <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#editBasicModal">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="text-muted small">Full Name</label>
                                    <p class="mb-0"><?php echo htmlspecialchars($user['name']); ?></p>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="text-muted small">Email</label>
                                    <p class="mb-0"><?php echo htmlspecialchars($user['email']); ?></p>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="text-muted small">Phone</label>
                                    <p class="mb-0"><?php echo htmlspecialchars($user['phone'] ?? 'Not set'); ?></p>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="text-muted small">Date of Birth</label>
                                    <p class="mb-0"><?php echo $profile['date_of_birth'] ? date('d M Y', strtotime($profile['date_of_birth'])) : 'Not set'; ?></p>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="text-muted small">Gender</label>
                                    <p class="mb-0"><?php echo ucfirst($profile['gender'] ?? 'Not set'); ?></p>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="text-muted small">Location</label>
                                    <p class="mb-0"><?php echo htmlspecialchars($profile['city'] ?? 'Not set'); ?><?php echo $profile['province'] ? ', ' . htmlspecialchars($profile['province']) : ''; ?></p>
                                </div>
                                <div class="col-12 mb-3">
                                    <label class="text-muted small">Bio</label>
                                    <p class="mb-0"><?php echo nl2br(htmlspecialchars($profile['bio'] ?? 'No bio added yet')); ?></p>
                                </div>
                                <?php if($profile['linkedin_url']): ?>
                                <div class="col-md-6 mb-3">
                                    <label class="text-muted small">LinkedIn</label>
                                    <p class="mb-0"><a href="<?php echo htmlspecialchars($profile['linkedin_url']); ?>" target="_blank">View Profile</a></p>
                                </div>
                                <?php endif; ?>
                                <?php if($profile['portfolio_url']): ?>
                                <div class="col-md-6 mb-3">
                                    <label class="text-muted small">Portfolio</label>
                                    <p class="mb-0"><a href="<?php echo htmlspecialchars($profile['portfolio_url']); ?>" target="_blank">View Portfolio</a></p>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Work Experience -->
                    <div class="card section-card mb-4">
                        <div class="card-header bg-white d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><i class="fas fa-briefcase"></i> Work Experience</h5>
                            <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addExperienceModal">
                                <i class="fas fa-plus"></i> Add Experience
                            </button>
                        </div>
                        <div class="card-body">
                            <?php if(mysqli_num_rows($experiences) > 0): ?>
                                <?php while($exp = mysqli_fetch_assoc($experiences)): ?>
                                <div class="timeline-item">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h6 class="mb-1"><?php echo htmlspecialchars($exp['job_title']); ?></h6>
                                            <p class="text-primary mb-1"><?php echo htmlspecialchars($exp['company_name']); ?></p>
                                            <p class="text-muted small mb-2">
                                                <i class="fas fa-calendar"></i>
                                                <?php echo date('M Y', strtotime($exp['start_date'])); ?> - 
                                                <?php echo $exp['is_current'] ? 'Present' : date('M Y', strtotime($exp['end_date'])); ?>
                                                <?php if($exp['location']): ?>
                                                    | <i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($exp['location']); ?>
                                                <?php endif; ?>
                                            </p>
                                            <?php if($exp['description']): ?>
                                            <p class="mb-0"><?php echo nl2br(htmlspecialchars($exp['description'])); ?></p>
                                            <?php endif; ?>
                                        </div>
                                        <div>
                                            <a href="?delete_exp=<?php echo $exp['id']; ?>" class="btn btn-sm btn-outline-danger" 
                                               onclick="return confirm('Delete this experience?')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <p class="text-muted text-center py-3">No work experience added yet</p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Education -->
                    <div class="card section-card mb-4">
                        <div class="card-header bg-white d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><i class="fas fa-graduation-cap"></i> Education</h5>
                            <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addEducationModal">
                                <i class="fas fa-plus"></i> Add Education
                            </button>
                        </div>
                        <div class="card-body">
                            <?php if(mysqli_num_rows($educations) > 0): ?>
                                <?php while($edu = mysqli_fetch_assoc($educations)): ?>
                                <div class="timeline-item">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h6 class="mb-1"><?php echo htmlspecialchars($edu['degree']); ?></h6>
                                            <p class="text-primary mb-1"><?php echo htmlspecialchars($edu['institution']); ?></p>
                                            <?php if($edu['field_of_study']): ?>
                                            <p class="mb-1"><?php echo htmlspecialchars($edu['field_of_study']); ?></p>
                                            <?php endif; ?>
                                            <p class="text-muted small mb-2">
                                                <i class="fas fa-calendar"></i>
                                                <?php echo date('M Y', strtotime($edu['start_date'])); ?> - 
                                                <?php echo $edu['is_current'] ? 'Present' : date('M Y', strtotime($edu['end_date'])); ?>
                                                <?php if($edu['gpa']): ?>
                                                    | GPA: <?php echo $edu['gpa']; ?>
                                                <?php endif; ?>
                                            </p>
                                            <?php if($edu['description']): ?>
                                            <p class="mb-0"><?php echo nl2br(htmlspecialchars($edu['description'])); ?></p>
                                            <?php endif; ?>
                                        </div>
                                        <div>
                                            <a href="?delete_edu=<?php echo $edu['id']; ?>" class="btn btn-sm btn-outline-danger"
                                               onclick="return confirm('Delete this education?')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <p class="text-muted text-center py-3">No education added yet</p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Skills -->
                    <div class="card section-card">
                        <div class="card-header bg-white d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><i class="fas fa-cogs"></i> Skills</h5>
                            <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addSkillModal">
                                <i class="fas fa-plus"></i> Add Skill
                            </button>
                        </div>
                        <div class="card-body">
                            <?php if(mysqli_num_rows($skills) > 0): ?>
                                <?php while($skill = mysqli_fetch_assoc($skills)): ?>
                                <span class="skill-badge bg-light border d-inline-flex align-items-center">
                                    <strong><?php echo htmlspecialchars($skill['skill_name']); ?></strong>
                                    <span class="badge bg-primary ms-2"><?php echo ucfirst($skill['level']); ?></span>
                                    <?php if($skill['years_of_experience']): ?>
                                        <small class="ms-2 text-muted"><?php echo $skill['years_of_experience']; ?> yrs</small>
                                    <?php endif; ?>
                                    <a href="?delete_skill=<?php echo $skill['id']; ?>" class="ms-2 text-danger" 
                                       onclick="return confirm('Delete this skill?')">
                                        <i class="fas fa-times"></i>
                                    </a>
                                </span>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <p class="text-muted text-center py-3">No skills added yet</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="col-lg-4">
                    <!-- CV Upload -->
                    <div class="card mb-4">
                        <div class="card-header bg-white">
                            <h5 class="mb-0"><i class="fas fa-file-pdf"></i> Resume/CV</h5>
                        </div>
                        <div class="card-body">
                            <?php if($profile['cv_path']): ?>
                                <div class="alert alert-success">
                                    <i class="fas fa-check-circle"></i> CV Uploaded
                                    <p class="mb-2 small"><?php echo htmlspecialchars($profile['cv_path']); ?></p>
                                    <a href="<?php echo CV_URL . $profile['cv_path']; ?>" target="_blank" class="btn btn-sm btn-outline-primary w-100 mb-2">
                                        <i class="fas fa-eye"></i> View CV
                                    </a>
                                </div>
                            <?php else: ?>
                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle"></i> No CV uploaded
                                </div>
                            <?php endif; ?>
                            
                            <form method="POST" enctype="multipart/form-data">
                                <input type="hidden" name="action" value="upload_cv">
                                <div class="mb-3">
                                    <input type="file" class="form-control" name="cv" accept=".pdf,.doc,.docx" required>
                                    <small class="text-muted">PDF, DOC, DOCX (Max 5MB)</small>
                                </div>
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-upload"></i> Upload New CV
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Profile Tips -->
                    <div class="card">
                        <div class="card-header bg-white">
                            <h5 class="mb-0"><i class="fas fa-lightbulb"></i> Profile Tips</h5>
                        </div>
                        <div class="card-body">
                            <ul class="list-unstyled mb-0">
                                <li class="mb-2"><i class="fas fa-check text-success"></i> Add a professional photo</li>
                                <li class="mb-2"><i class="fas fa-check text-success"></i> Write a compelling bio</li>
                                <li class="mb-2"><i class="fas fa-check text-success"></i> List your work experience</li>
                                <li class="mb-2"><i class="fas fa-check text-success"></i> Add your education</li>
                                <li class="mb-2"><i class="fas fa-check text-success"></i> Include relevant skills</li>
                                <li><i class="fas fa-check text-success"></i> Keep your CV updated</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Modals -->
    <!-- Edit Basic Info Modal -->
    <div class="modal fade" id="editBasicModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="POST">
                    <input type="hidden" name="action" value="update_basic">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Basic Information</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Full Name</label>
                                <input type="text" class="form-control" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Phone</label>
                                <input type="text" class="form-control" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Date of Birth</label>
                                <input type="date" class="form-control" name="date_of_birth" value="<?php echo $profile['date_of_birth']; ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Gender</label>
                                <select class="form-select" name="gender">
                                    <option value="">Select</option>
                                    <option value="male" <?php echo $profile['gender'] == 'male' ? 'selected' : ''; ?>>Male</option>
                                    <option value="female" <?php echo $profile['gender'] == 'female' ? 'selected' : ''; ?>>Female</option>
                                    <option value="other" <?php echo $profile['gender'] == 'other' ? 'selected' : ''; ?>>Other</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">City</label>
                                <input type="text" class="form-control" name="city" value="<?php echo htmlspecialchars($profile['city'] ?? ''); ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Province</label>
                                <select class="form-select" name="province">
                                    <option value="">Select Province</option>
                                    <?php foreach(INDONESIAN_PROVINCES as $prov): ?>
                                        <option value="<?php echo $prov; ?>" <?php echo $profile['province'] == $prov ? 'selected' : ''; ?>><?php echo $prov; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-12 mb-3">
                                <label class="form-label">Address</label>
                                <textarea class="form-control<function_calls>
<invoke name="artifacts">
<parameter name="command">update</parameter>
<parameter name="id">jobseeker_profile</parameter>
<parameter name="old_str">                                <textarea class="form-control</parameter>
<parameter name="new_str">                                <textarea class="form-control" name="address" rows="2"><?php echo htmlspecialchars($profile['address'] ?? ''); ?></textarea>
</div>
<div class="col-12 mb-3">
<label class="form-label">Bio</label>
<textarea class="form-control" name="bio" rows="4"><?php echo htmlspecialchars($profile['bio'] ?? ''); ?></textarea>
</div>
<div class="col-md-6 mb-3">
<label class="form-label">LinkedIn URL</label>
<input type="url" class="form-control" name="linkedin_url" value="<?php echo htmlspecialchars($profile['linkedin_url'] ?? ''); ?>">
</div>
<div class="col-md-6 mb-3">
<label class="form-label">Portfolio URL</label>
<input type="url" class="form-control" name="portfolio_url" value="<?php echo htmlspecialchars($profile['portfolio_url'] ?? ''); ?>">
</div>
</div>
</div>
<div class="modal-footer">
<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
<button type="submit" class="btn btn-primary">Save Changes</button>
</div>
</form>
</div>
</div>
</div>
<!-- Add Experience Modal -->
<div class="modal fade" id="addExperienceModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="action" value="add_experience">
                <div class="modal-header">
                    <h5 class="modal-title">Add Work Experience</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Job Title</label>
                            <input type="text" class="form-control" name="job_title" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Company Name</label>
                            <input type="text" class="form-control" name="company_name" required>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Location</label>
                            <input type="text" class="form-control" name="exp_location">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Start Date</label>
                            <input type="date" class="form-control" name="start_date" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">End Date</label>
                            <input type="date" class="form-control" name="end_date" id="exp_end_date">
                        </div>
                        <div class="col-12 mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_current" id="exp_current" onchange="toggleEndDate(this, 'exp_end_date')">
                                <label class="form-check-label" for="exp_current">I currently work here</label>
                            </div>
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" rows="4"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Experience</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Education Modal -->
<div class="modal fade" id="addEducationModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="action" value="add_education">
                <div class="modal-header">
                    <h5 class="modal-title">Add Education</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Degree</label>
                            <select class="form-select" name="degree" required>
                                <option value="">Select Degree</option>
                                <?php foreach(EDUCATION_LEVELS as $key => $val): ?>
                                    <option value="<?php echo $key; ?>"><?php echo $val; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Institution</label>
                            <input type="text" class="form-control" name="institution" required>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Field of Study</label>
                            <input type="text" class="form-control" name="field_of_study">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Start Date</label>
                            <input type="date" class="form-control" name="edu_start_date" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">End Date</label>
                            <input type="date" class="form-control" name="edu_end_date" id="edu_end_date">
                        </div>
                        <div class="col-12 mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="edu_is_current" id="edu_current" onchange="toggleEndDate(this, 'edu_end_date')">
                                <label class="form-check-label" for="edu_current">Currently studying here</label>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">GPA (Optional)</label>
                            <input type="number" step="0.01" class="form-control" name="gpa">
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="edu_description" rows="3"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Education</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Skill Modal -->
<div class="modal fade" id="addSkillModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="action" value="add_skill">
                <div class="modal-header">
                    <h5 class="modal-title">Add Skill</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Skill Name</label>
                        <input type="text" class="form-control" name="skill_name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Proficiency Level</label>
                        <select class="form-select" name="skill_level" required>
                            <?php foreach(SKILL_LEVELS as $key => $val): ?>
                                <option value="<?php echo $key; ?>"><?php echo $val; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Years of Experience</label>
                        <input type="number" class="form-control" name="years_of_experience" min="0">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Skill</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function toggleEndDate(checkbox, fieldId) {
        const field = document.getElementById(fieldId);
        field.disabled = checkbox.checked;
        if(checkbox.checked) field.value = '';
    }
</script>
</body>
</html</parameter>