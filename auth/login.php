<?php
// ==========================================
// LOGIN.PHP
// ==========================================
session_start();
require_once '../config/database.php';
require_once '../config/functions.php';

if(isset($_SESSION['user_id'])) {
    $redirect = $_SESSION['role'] == 'admin' ? '../admin/dashboard.php' : 
                ($_SESSION['role'] == 'employer' ? '../employer/dashboard.php' : '../jobseeker/dashboard.php');
    header("Location: $redirect");
    exit();
}

$error = '';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = sanitize_input($_POST['email']);
    $password = $_POST['password'];
    $remember = isset($_POST['remember']);
    
    if(empty($email) || empty($password)) {
        $error = 'Email dan password harus diisi!';
    } else {
        $query = "SELECT * FROM users WHERE email = '$email' AND is_active = 1";
        $result = mysqli_query($conn, $query);
        
        if(mysqli_num_rows($result) == 1) {
            $user = mysqli_fetch_assoc($result);
            
            if(password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['name'] = $user['name'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role'];
                
                // Remember me
                if($remember) {
                    setcookie('remember_user', $user['id'], time() + (86400 * 30), '/');
                }
                
                // Log activity
                log_activity($user['id'], 'login', 'User logged in');
                
                // Redirect based on role
                if($user['role'] == 'admin') {
                    header("Location: ../admin/dashboard.php");
                } elseif($user['role'] == 'employer') {
                    header("Location: ../employer/dashboard.php");
                } else {
                    header("Location: ../jobseeker/dashboard.php");
                }
                exit();
            } else {
                $error = 'Password salah!';
            }
        } else {
            $error = 'Email tidak ditemukan atau akun tidak aktif!';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - JobPortal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        .login-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="login-card p-5">
                    <div class="text-center mb-4">
                        <h2 class="fw-bold"><i class="fas fa-briefcase text-primary"></i> JobPortal</h2>
                        <p class="text-muted">Login ke akun Anda</p>
                    </div>
                    
                    <?php if($error): ?>
                        <div class="alert alert-danger alert-dismissible fade show">
                            <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label class="form-label">Email Address</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                <input type="email" class="form-control" name="email" required 
                                       placeholder="email@example.com" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                <input type="password" class="form-control" name="password" required 
                                       placeholder="••••••••">
                            </div>
                        </div>
                        
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" name="remember" id="remember">
                            <label class="form-check-label" for="remember">
                                Remember Me
                            </label>
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100 mb-3">
                            <i class="fas fa-sign-in-alt"></i> Login
                        </button>
                    </form>
                    
                    <div class="text-center">
                        <a href="forgot_password.php" class="text-decoration-none">Lupa Password?</a>
                    </div>
                    
                    <hr class="my-4">
                    
                    <div class="text-center">
                        <p class="mb-0">Belum punya akun? <a href="register.php" class="fw-bold text-decoration-none">Daftar Sekarang</a></p>
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