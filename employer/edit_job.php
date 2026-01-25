<?php
session_start();
require_once '../config/database.php';
require_once '../config/functions.php';
require_once '../config/constants.php';

check_role('employer');

$user_id = $_SESSION['user_id'];
$company = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM companies WHERE user_id = $user_id"));
$company_id = $company['id'];

if(!isset($_GET['id'])) {
    header("Location: my_jobs.php");
    exit();
}

$job_id = (int)$_GET['id'];

// Get job data
$job_query = "SELECT * FROM jobs WHERE id = $job_id AND company_id = $company_id";
$job_result = mysqli_query($conn, $job_query);

if(mysqli_num_rows($job_result) == 0) {
    $_SESSION['error'] = 'Job not found or access denied';
    header("Location: my_jobs.php");
    exit();
}

$job = mysqli_fetch_assoc($job_result);

// Get existing skills
$skills_query = "SELECT * FROM job_skills WHERE job_id = $job_id";
$existing_skills = mysqli_query($conn, $skills_query);

// Get categories
$categories_query = "SELECT * FROM job_categories WHERE is_active = 1 ORDER BY name";
$categories_result = mysqli_query($conn, $categories_query);

$error = '';
$success = '';

// Handle form submission
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = sanitize_input($_POST['title']);
    $category_id = (int)$_POST['category_id'];
    $description = sanitize_input($_POST['description']);
    $responsibilities = sanitize_input($_POST['responsibilities']);
    $requirements = sanitize_input($_POST['requirements']);
    $benefits = sanitize_input($_POST['benefits']);
    $location = sanitize_input($_POST['location']);
    $city = sanitize_input($_POST['city']);
    $province = sanitize_input($_POST['province']);
    $type = sanitize_input($_POST['type']);
    $level = sanitize_input($_POST['level']);
    $education = sanitize_input($_POST['education']);
    $experience = sanitize_input($_POST['experience']);
    $salary_min = !empty($_POST['salary_min']) ? (int)$_POST['salary_min'] : 0;
    $salary_max = !empty($_POST['salary_max']) ? (int)$_POST['salary_max'] : 0;
    $salary_type = sanitize_input($_POST['salary_type']);
    $is_salary_negotiable = isset($_POST['is_salary_negotiable']) ? 1 : 0;
    $positions_available = (int)$_POST['positions_available'];
    $deadline = !empty($_POST['deadline']) ? sanitize_input($_POST['deadline']) : NULL;
    $status = sanitize_input($_POST['status']);
    $skills = isset($_POST['skills']) ? $_POST['skills'] : [];
    
    if(empty($title) || empty($description) || empty($city) || empty($type)) {
        $error = 'Please fill in all required fields!';
    } else {
        // Generate new slug if title changed
        if($title != $job['title']) {
            $slug = generate_slug($title);
            $slug_check = mysqli_query($conn, "SELECT id FROM jobs WHERE slug = '$slug' AND id != $job_id");
            if(mysqli_num_rows($slug_check) > 0) {
                $slug = $slug . '-' . time();
            }
        } else {
            $slug = $job['slug'];
        }
        
        $deadline_sql = $deadline ? "'$deadline'" : 'NULL';
        
        // Update job
        $update_query = "UPDATE jobs SET 
            category_id = $category_id,
            title = '$title',
            slug = '$slug',
            description = '$description',
            responsibilities = '$responsibilities',
            requirements = '$requirements',
            benefits = '$benefits',
            location = '$location',
            city = '$city',
            province = '$province',
            type = '$type',
            level = '$level',
            education = '$education',
            experience = '$experience',
            salary_min = $salary_min,
            salary_max = $salary_max,
            salary_type = '$salary_type',
            is_salary_negotiable = $is_salary_negotiable,
            positions_available = $positions_available,
            deadline = $deadline_sql,
            status = '$status',
            updated_at = CURRENT_TIMESTAMP
            WHERE id = $job_id AND company_id = $company_id";
        
        if(mysqli_query($conn, $update_query)) {
            // Delete old skills
            mysqli_query($conn, "DELETE FROM job_skills WHERE job_id = $job_id");
            
            // Insert new skills
            if(!empty($skills)) {
                foreach($skills as $skill) {
                    $skill_name = sanitize_input($skill);
                    if(!empty($skill_name)) {
                        mysqli_query($conn, "INSERT INTO job_skills (job_id, skill_name) VALUES ($job_id, '$skill_name')");
                    }
                }
            }
            
            $_SESSION['success'] = 'Job updated successfully!';
            header("Location: my_jobs.php");
            exit();
        } else {
            $error = 'Failed to update job: ' . mysqli_error($conn);
        }
    }
}

$page_title = 'Edit Job';
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
        .form-section {
            background: #f8f9fc;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body class="bg-light">
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="../index.php">
                <i class="fas fa-briefcase me-2"></i><strong>JobPortal</strong>
            </a>
            <div class="ms-auto">
                <a href="my_jobs.php" class="btn btn-outline-light btn-sm">
                    <i class="fas fa-arrow-left"></i> Back to My Jobs
                </a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <section class="py-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-10 mx-auto">
                    <div class="card shadow">
                        <div class="card-header bg-warning text-dark">
                            <h4 class="mb-0"><i class="fas fa-edit"></i> Edit Job: <?php echo htmlspecialchars($job['title']); ?></h4>
                        </div>
                        <div class="card-body">
                            <?php if($error): ?>
                                <div class="alert alert-danger alert-dismissible fade show">
                                    <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            <?php endif; ?>

                            <form method="POST" action="">
                                <!-- Basic Information -->
                                <div class="form-section">
                                    <h5 class="mb-3"><i class="fas fa-info-circle"></i> Basic Information</h5>
                                    
                                    <div class="row">
                                        <div class="col-md-8 mb-3">
                                            <label class="form-label">Job Title <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" name="title" required 
                                                   value="<?php echo htmlspecialchars($job['title']); ?>">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Category <span class="text-danger">*</span></label>
                                            <select class="form-select" name="category_id" required>
                                                <option value="">Select Category</option>
                                                <?php while($cat = mysqli_fetch_assoc($categories_result)): ?>
                                                    <option value="<?php echo $cat['id']; ?>" 
                                                            <?php echo $job['category_id'] == $cat['id'] ? 'selected' : ''; ?>>
                                                        <?php echo htmlspecialchars($cat['name']); ?>
                                                    </option>
                                                <?php endwhile; ?>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Job Description <span class="text-danger">*</span></label>
                                        <textarea class="form-control" name="description" rows="6" required><?php echo htmlspecialchars($job['description']); ?></textarea>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Responsibilities</label>
                                        <textarea class="form-control" name="responsibilities" rows="5"><?php echo htmlspecialchars($job['responsibilities']); ?></textarea>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Requirements</label>
                                        <textarea class="form-control" name="requirements" rows="5"><?php echo htmlspecialchars($job['requirements']); ?></textarea>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Benefits</label>
                                        <textarea class="form-control" name="benefits" rows="4"><?php echo htmlspecialchars($job['benefits']); ?></textarea>
                                    </div>
                                </div>

                                <!-- Job Details -->
                                <div class="form-section">
                                    <h5 class="mb-3"><i class="fas fa-clipboard-list"></i> Job Details</h5>
                                    
                                    <div class="row">
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Job Type <span class="text-danger">*</span></label>
                                            <select class="form-select" name="type" required>
                                                <?php foreach(JOB_TYPES as $key => $value): ?>
                                                    <option value="<?php echo $key; ?>" <?php echo $job['type'] == $key ? 'selected' : ''; ?>>
                                                        <?php echo $value; ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Experience Level</label>
                                            <select class="form-select" name="level">
                                                <?php foreach(JOB_LEVELS as $key => $value): ?>
                                                    <option value="<?php echo $key; ?>" <?php echo $job['level'] == $key ? 'selected' : ''; ?>>
                                                        <?php echo $value; ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Education Required</label>
                                            <select class="form-select" name="education">
                                                <option value="">Not specified</option>
                                                <?php foreach(EDUCATION_LEVELS as $key => $value): ?>
                                                    <option value="<?php echo $key; ?>" <?php echo $job['education'] == $key ? 'selected' : ''; ?>>
                                                        <?php echo $value; ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Experience Required</label>
                                            <input type="text" class="form-control" name="experience" 
                                                   value="<?php echo htmlspecialchars($job['experience']); ?>">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Positions Available</label>
                                            <input type="number" class="form-control" name="positions_available" 
                                                   value="<?php echo $job['positions_available']; ?>" min="1">
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Required Skills</label>
                                        <div id="skillsContainer">
                                            <?php if(mysqli_num_rows($existing_skills) > 0): ?>
                                                <?php while($skill = mysqli_fetch_assoc($existing_skills)): ?>
                                                <div class="input-group mb-2">
                                                    <input type="text" class="form-control" name="skills[]" 
                                                           value="<?php echo htmlspecialchars($skill['skill_name']); ?>">
                                                    <button type="button" class="btn btn-outline-danger" onclick="this.parentElement.remove()">
                                                        <i class="fas fa-minus"></i>
                                                    </button>
                                                </div>
                                                <?php endwhile; ?>
                                            <?php endif; ?>
                                            <div class="input-group mb-2">
                                                <input type="text" class="form-control" name="skills[]" placeholder="Add skill">
                                                <button type="button" class="btn btn-outline-secondary" onclick="addSkillField()">
                                                    <i class="fas fa-plus"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Location -->
                                <div class="form-section">
                                    <h5 class="mb-3"><i class="fas fa-map-marker-alt"></i> Location</h5>
                                    
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">City <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" name="city" required 
                                                   value="<?php echo htmlspecialchars($job['city']); ?>">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Province</label>
                                            <select class="form-select" name="province">
                                                <option value="">Select Province</option>
                                                <?php foreach(INDONESIAN_PROVINCES as $prov): ?>
                                                    <option value="<?php echo $prov; ?>" <?php echo $job['province'] == $prov ? 'selected' : ''; ?>>
                                                        <?php echo $prov; ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Full Address</label>
                                        <textarea class="form-control" name="location" rows="2"><?php echo htmlspecialchars($job['location']); ?></textarea>
                                    </div>
                                </div>

                                <!-- Salary -->
                                <div class="form-section">
                                    <h5 class="mb-3"><i class="fas fa-money-bill-wave"></i> Salary Information</h5>
                                    
                                    <div class="row">
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Minimum Salary (Rp)</label>
                                            <input type="number" class="form-control" name="salary_min" 
                                                   value="<?php echo $job['salary_min']; ?>">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Maximum Salary (Rp)</label>
                                            <input type="number" class="form-control" name="salary_max" 
                                                   value="<?php echo $job['salary_max']; ?>">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Salary Type</label>
                                            <select class="form-select" name="salary_type">
                                                <option value="monthly" <?php echo $job['salary_type'] == 'monthly' ? 'selected' : ''; ?>>Monthly</option>
                                                <option value="yearly" <?php echo $job['salary_type'] == 'yearly' ? 'selected' : ''; ?>>Yearly</option>
                                                <option value="hourly" <?php echo $job['salary_type'] == 'hourly' ? 'selected' : ''; ?>>Hourly</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="is_salary_negotiable" id="negotiable" 
                                               <?php echo $job['is_salary_negotiable'] ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="negotiable">Salary is negotiable</label>
                                    </div>
                                </div>

                                <!-- Additional Settings -->
                                <div class="form-section">
                                    <h5 class="mb-3"><i class="fas fa-cog"></i> Additional Settings</h5>
                                    
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Application Deadline</label>
                                            <input type="date" class="form-control" name="deadline" 
                                                   value="<?php echo $job['deadline']; ?>" 
                                                   min="<?php echo date('Y-m-d'); ?>">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Status</label>
                                            <select class="form-select" name="status">
                                                <option value="active" <?php echo $job['status'] == 'active' ? 'selected' : ''; ?>>Active</option>
                                                <option value="draft" <?php echo $job['status'] == 'draft' ? 'selected' : ''; ?>>Draft</option>
                                                <option value="closed" <?php echo $job['status'] == 'closed' ? 'selected' : ''; ?>>Closed</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <!-- Submit Buttons -->
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary btn-lg">
                                        <i class="fas fa-save"></i> Save Changes
                                    </button>
                                    <a href="my_jobs.php" class="btn btn-outline-secondary btn-lg">
                                        <i class="fas fa-times"></i> Cancel
                                    </a>
                                    <a href="../public/job_detail.php?id=<?php echo $job_id; ?>" target="_blank" class="btn btn-outline-info btn-lg ms-auto">
                                        <i class="fas fa-eye"></i> Preview Job
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function addSkillField() {
            const container = document.getElementById('skillsContainer');
            const div = document.createElement('div');
            div.className = 'input-group mb-2';
            div.innerHTML = `
                <input type="text" class="form-control" name="skills[]" placeholder="Add skill">
                <button type="button" class="btn btn-outline-danger" onclick="this.parentElement.remove()">
                    <i class="fas fa-minus"></i>
                </button>
            `;
            container.appendChild(div);
        }
    </script>
</body>
</html>