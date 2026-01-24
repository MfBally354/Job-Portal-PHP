<?php
session_start();
require_once '../config/database.php';
require_once '../config/functions.php';
require_once '../config/constants.php';

check_role('employer');

$user_id = $_SESSION['user_id'];

// Get company info
$company_query = "SELECT * FROM companies WHERE user_id = $user_id";
$company = mysqli_fetch_assoc(mysqli_query($conn, $company_query));

if(!$company) {
    header("Location: profile.php?setup=1");
    exit();
}

$company_id = $company['id'];

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
    
    // Validation
    if(empty($title) || empty($description) || empty($city) || empty($type)) {
        $error = 'Please fill in all required fields!';
    } else {
        // Generate slug
        $slug = generate_slug($title);
        
        // Check if slug exists
        $slug_check = mysqli_query($conn, "SELECT id FROM jobs WHERE slug = '$slug'");
        if(mysqli_num_rows($slug_check) > 0) {
            $slug = $slug . '-' . time();
        }
        
        // Prepare deadline
        $deadline_sql = $deadline ? "'$deadline'" : 'NULL';
        
        // Insert job
        $insert_query = "INSERT INTO jobs (
            company_id, category_id, title, slug, description, responsibilities, requirements, 
            benefits, location, city, province, type, level, education, experience, 
            salary_min, salary_max, salary_type, is_salary_negotiable, 
            positions_available, deadline, status
        ) VALUES (
            $company_id, $category_id, '$title', '$slug', '$description', '$responsibilities', 
            '$requirements', '$benefits', '$location', '$city', '$province', '$type', '$level', 
            '$education', '$experience', $salary_min, $salary_max, '$salary_type', 
            $is_salary_negotiable, $positions_available, $deadline_sql, '$status'
        )";
        
        if(mysqli_query($conn, $insert_query)) {
            $job_id = mysqli_insert_id($conn);
            
            // Insert skills
            if(!empty($skills)) {
                foreach($skills as $skill) {
                    $skill_name = sanitize_input($skill);
                    if(!empty($skill_name)) {
                        mysqli_query($conn, "INSERT INTO job_skills (job_id, skill_name) VALUES ($job_id, '$skill_name')");
                    }
                }
            }
            
            // Send notification
            send_notification(
                $user_id,
                'Job Posted Successfully',
                'Your job posting "' . $title . '" has been published!',
                'success',
                'my_jobs.php'
            );
            
            $_SESSION['success'] = 'Job posted successfully!';
            header("Location: my_jobs.php");
            exit();
        } else {
            $error = 'Failed to post job: ' . mysqli_error($conn);
        }
    }
}

$page_title = 'Post New Job';
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
        .skill-tag {
            display: inline-block;
            background: #e7f3ff;
            color: #0066cc;
            padding: 5px 10px;
            margin: 5px;
            border-radius: 15px;
            cursor: pointer;
        }
        .skill-tag:hover {
            background: #d0e8ff;
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
                    <div class="card shadow">
                        <div class="card-header bg-primary text-white">
                            <h4 class="mb-0"><i class="fas fa-plus-circle"></i> Post New Job</h4>
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
                                                   placeholder="e.g., Senior Software Engineer" 
                                                   value="<?php echo isset($_POST['title']) ? htmlspecialchars($_POST['title']) : ''; ?>">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Category <span class="text-danger">*</span></label>
                                            <select class="form-select" name="category_id" required>
                                                <option value="">Select Category</option>
                                                <?php while($cat = mysqli_fetch_assoc($categories_result)): ?>
                                                    <option value="<?php echo $cat['id']; ?>" 
                                                            <?php echo (isset($_POST['category_id']) && $_POST['category_id'] == $cat['id']) ? 'selected' : ''; ?>>
                                                        <?php echo htmlspecialchars($cat['name']); ?>
                                                    </option>
                                                <?php endwhile; ?>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Job Description <span class="text-danger">*</span></label>
                                        <textarea class="form-control" name="description" rows="6" required 
                                                  placeholder="Provide a detailed description of the job position..."><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
                                        <small class="text-muted">Describe the role, day-to-day activities, and what makes this opportunity exciting</small>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Responsibilities</label>
                                        <textarea class="form-control" name="responsibilities" rows="5" 
                                                  placeholder="List the main responsibilities..."><?php echo isset($_POST['responsibilities']) ? htmlspecialchars($_POST['responsibilities']) : ''; ?></textarea>
                                        <small class="text-muted">Use bullet points for better readability</small>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Requirements</label>
                                        <textarea class="form-control" name="requirements" rows="5" 
                                                  placeholder="List the requirements and qualifications..."><?php echo isset($_POST['requirements']) ? htmlspecialchars($_POST['requirements']) : ''; ?></textarea>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Benefits</label>
                                        <textarea class="form-control" name="benefits" rows="4" 
                                                  placeholder="What benefits do you offer?"><?php echo isset($_POST['benefits']) ? htmlspecialchars($_POST['benefits']) : ''; ?></textarea>
                                    </div>
                                </div>

                                <!-- Job Details -->
                                <div class="form-section">
                                    <h5 class="mb-3"><i class="fas fa-clipboard-list"></i> Job Details</h5>
                                    
                                    <div class="row">
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Job Type <span class="text-danger">*</span></label>
                                            <select class="form-select" name="type" required>
                                                <option value="">Select Type</option>
                                                <?php foreach(JOB_TYPES as $key => $value): ?>
                                                    <option value="<?php echo $key; ?>" 
                                                            <?php echo (isset($_POST['type']) && $_POST['type'] == $key) ? 'selected' : ''; ?>>
                                                        <?php echo $value; ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Experience Level</label>
                                            <select class="form-select" name="level">
                                                <option value="entry">Entry Level</option>
                                                <?php foreach(JOB_LEVELS as $key => $value): ?>
                                                    <option value="<?php echo $key; ?>" 
                                                            <?php echo (isset($_POST['level']) && $_POST['level'] == $key) ? 'selected' : ''; ?>>
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
                                                    <option value="<?php echo $key; ?>" 
                                                            <?php echo (isset($_POST['education']) && $_POST['education'] == $key) ? 'selected' : ''; ?>>
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
                                                   placeholder="e.g., 2-3 years" 
                                                   value="<?php echo isset($_POST['experience']) ? htmlspecialchars($_POST['experience']) : ''; ?>">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Positions Available</label>
                                            <input type="number" class="form-control" name="positions_available" 
                                                   value="<?php echo isset($_POST['positions_available']) ? $_POST['positions_available'] : '1'; ?>" min="1">
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Required Skills</label>
                                        <div id="skillsContainer">
                                            <div class="input-group mb-2">
                                                <input type="text" class="form-control" name="skills[]" placeholder="e.g., JavaScript">
                                                <button type="button" class="btn btn-outline-secondary" onclick="addSkillField()">
                                                    <i class="fas fa-plus"></i>
                                                </button>
                                            </div>
                                        </div>
                                        <small class="text-muted">Add relevant skills for this position</small>
                                    </div>
                                </div>

                                <!-- Location -->
                                <div class="form-section">
                                    <h5 class="mb-3"><i class="fas fa-map-marker-alt"></i> Location</h5>
                                    
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">City <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" name="city" required 
                                                   placeholder="e.g., Jakarta" 
                                                   value="<?php echo isset($_POST['city']) ? htmlspecialchars($_POST['city']) : ''; ?>">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Province</label>
                                            <select class="form-select" name="province">
                                                <option value="">Select Province</option>
                                                <?php foreach(INDONESIAN_PROVINCES as $prov): ?>
                                                    <option value="<?php echo $prov; ?>" 
                                                            <?php echo (isset($_POST['province']) && $_POST['province'] == $prov) ? 'selected' : ''; ?>>
                                                        <?php echo $prov; ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Full Address</label>
                                        <textarea class="form-control" name="location" rows="2" 
                                                  placeholder="Complete office address (optional)"><?php echo isset($_POST['location']) ? htmlspecialchars($_POST['location']) : ''; ?></textarea>
                                    </div>
                                </div>

                                <!-- Salary -->
                                <div class="form-section">
                                    <h5 class="mb-3"><i class="fas fa-money-bill-wave"></i> Salary Information</h5>
                                    
                                    <div class="row">
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Minimum Salary (Rp)</label>
                                            <input type="number" class="form-control" name="salary_min" 
                                                   placeholder="e.g., 5000000" 
                                                   value="<?php echo isset($_POST['salary_min']) ? $_POST['salary_min'] : ''; ?>">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Maximum Salary (Rp)</label>
                                            <input type="number" class="form-control" name="salary_max" 
                                                   placeholder="e.g., 8000000" 
                                                   value="<?php echo isset($_POST['salary_max']) ? $_POST['salary_max'] : ''; ?>">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Salary Type</label>
                                            <select class="form-select" name="salary_type">
                                                <option value="monthly">Monthly</option>
                                                <option value="yearly">Yearly</option>
                                                <option value="hourly">Hourly</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="is_salary_negotiable" id="negotiable">
                                        <label class="form-check-label" for="negotiable">
                                            Salary is negotiable
                                        </label>
                                    </div>
                                </div>

                                <!-- Additional Settings -->
                                <div class="form-section">
                                    <h5 class="mb-3"><i class="fas fa-cog"></i> Additional Settings</h5>
                                    
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Application Deadline</label>
                                            <input type="date" class="form-control" name="deadline" 
                                                   value="<?php echo isset($_POST['deadline']) ? $_POST['deadline'] : ''; ?>" 
                                                   min="<?php echo date('Y-m-d'); ?>">
                                            <small class="text-muted">Leave empty for no deadline</small>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Status</label>
                                            <select class="form-select" name="status">
                                                <option value="active">Active (Publish Now)</option>
                                                <option value="draft">Draft (Save for later)</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <!-- Submit Buttons -->
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary btn-lg">
                                        <i class="fas fa-check"></i> Post Job
                                    </button>
                                    <button type="submit" name="status" value="draft" class="btn btn-outline-secondary btn-lg">
                                        <i class="fas fa-save"></i> Save as Draft
                                    </button>
                                    <a href="dashboard.php" class="btn btn-outline-danger btn-lg">
                                        <i class="fas fa-times"></i> Cancel
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
                <input type="text" class="form-control" name="skills[]" placeholder="e.g., Python">
                <button type="button" class="btn btn-outline-danger" onclick="this.parentElement.remove()">
                    <i class="fas fa-minus"></i>
                </button>
            `;
            container.appendChild(div);
        }
    </script>
</body>
</html>