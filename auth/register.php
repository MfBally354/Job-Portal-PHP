<?php
// ==========================================
// REGISTER.PHP
// ==========================================
// Copy to auth/register.php

session_start();
require_once '../config/database.php';
require_once '../config/functions.php';

if(isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

$error = '';
$success = '';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = sanitize_input($_POST['name']);
    $email = sanitize_input($_POST['email']);
    $phone = sanitize_input($_POST['phone']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $role = sanitize_input($_POST['role']);
    
    // Validation
    if(empty($name) || empty($email) || empty($password) || empty($role)) {
        $error = 'Semua field wajib diisi!';
    } elseif(!validate_email($email)) {
        $error = 'Format email tidak valid!';
    } elseif($password != $confirm_password) {
        $error = 'Password tidak cocok!';
    } elseif(strlen($password) < 8) {
        $error = 'Password minimal 8 karakter!';
    } elseif(!empty($phone) && !validate_phone($phone)) {
        $error = 'Format nomor telepon tidak valid!';
    } else {
        // Check if email exists
        $check = "SELECT id FROM users WHERE email = '$email'";
        $result = mysqli_query($conn, $check);
        
        if(mysqli_num_rows($result) > 0) {
            $error = 'Email sudah terdaftar!';
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            $query = "INSERT INTO users (name, email, phone, password, role) 
                      VALUES ('$name', '$email', '$phone', '$hashed_password', '$role')";
            
            if(mysqli_query($conn, $query)) {
                $user_id = mysqli_insert_id($conn);
                
                // Create profile based on role
                if($role == 'jobseeker') {
                    mysqli_query($conn, "INSERT INTO jobseeker_profiles (user_id) VALUES ($user_id)");
                } elseif($role == 'employer') {
                    mysqli_query($conn, "INSERT INTO companies (user_id, name) VALUES ($user_id, '$name')");
                }
                
                // Send welcome notification
                send_notification($user_id, 'Selamat Datang!', 'Terima kasih telah bergabung dengan JobPortal', 'success');
                
                $success = 'Registrasi berhasil! Silakan login.';
                header("refresh:2;url=login.php");
            } else {
                $error = 'Registrasi gagal: ' . mysqli_error($conn);
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar - JobPortal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 50px 0;
        }
        .register-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="register-card p-5">
                    <div class="text-center mb-4">
                        <h2 class="fw-bold"><i class="fas fa-briefcase text-primary"></i> JobPortal</h2>
                        <p class="text-muted">Buat akun baru</p>
                    </div>
                    
                    <?php if($error): ?>
                        <div class="alert alert-danger alert-dismissible fade show">
                            <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <?php if($success): ?>
                        <div class="alert alert-success alert-dismissible fade show">
                            <i class="fas fa-check-circle"></i> <?php echo $success; ?>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="name" required 
                                   placeholder="Nama lengkap Anda" value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Email Address <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" name="email" required 
                                   placeholder="email@example.com" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">No. Telepon</label>
                            <input type="text" class="form-control" name="phone" 
                                   placeholder="08123456789" value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>">
                            <small class="text-muted">Format: 08xxxxxxxxx</small>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Daftar Sebagai <span class="text-danger">*</span></label>
                            <select class="form-select" name="role" required>
                                <option value="">Pilih role...</option>
                                <option value="jobseeker" <?php echo (isset($_POST['role']) && $_POST['role'] == 'jobseeker') ? 'selected' : ''; ?>>
                                    Pencari Kerja
                                </option>
                                <option value="employer" <?php echo (isset($_POST['role']) && $_POST['role'] == 'employer') ? 'selected' : ''; ?>>
                                    Perusahaan / Employer
                                </option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Password <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" name="password" required 
                                   placeholder="Minimal 8 karakter">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Konfirmasi Password <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" name="confirm_password" required 
                                   placeholder="Ketik ulang password">
                        </div>
                        
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" required id="terms">
                            <label class="form-check-label" for="terms">
                                Saya setuju dengan <a href="#">Terms & Conditions</a> dan <a href="#">Privacy Policy</a>
                            </label>
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100 mb-3">
                            <i class="fas fa-user-plus"></i> Daftar Sekarang
                        </button>
                    </form>
                    
                    <hr class="my-4">
                    
                    <div class="text-center">
                        <p class="mb-0">Sudah punya akun? <a href="login.php" class="fw-bold text-decoration-none">Login di sini</a></p>
                    </div>
                    
                    <div class="text-center mt-3">
                        <a href="../index.php" class="text-muted text-decoration-none">
                            <i class="fas fa-arrow-left"></i> Kembali ke Home
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>