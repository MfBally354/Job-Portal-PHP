<?php
session_start();
require_once '../config/database.php';
require_once '../config/functions.php';

check_login();

$user_id = $_SESSION['user_id'];
$query = "SELECT * FROM users WHERE id = $user_id";
$result = mysqli_query($conn, $query);
$user = mysqli_fetch_assoc($result);

$success = '';
$error = '';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = sanitize_input($_POST['name']);
    $phone = sanitize_input($_POST['phone']);
    
    $update_query = "UPDATE users SET name = '$name', phone = '$phone' WHERE id = $user_id";
    
    if(mysqli_query($conn, $update_query)) {
        $_SESSION['name'] = $name;
        $success = 'Profil berhasil diupdate!';
        $user['name'] = $name;
        $user['phone'] = $phone;
    } else {
        $error = 'Gagal mengupdate profil';
    }
}

$page_title = 'Profil Saya - JobPortal';
$css_path = '../assets/css/style.css';
$js_path = '../assets/js/main.js';
$base_url = '../';
include '../includes/header.php';
?>

<div class="container my-5">
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card shadow">
                <div class="card-body p-4">
                    <h3 class="mb-4"><i class="fas fa-user"></i> Profil Saya</h3>
                    
                    <?php if($success): ?>
                        <div class="alert alert-success"><?php echo $success; ?></div>
                    <?php endif; ?>
                    
                    <?php if($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">Nama Lengkap</label>
                            <input type="text" class="form-control" name="name" 
                                   value="<?php echo htmlspecialchars($user['name']); ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" 
                                   value="<?php echo htmlspecialchars($user['email']); ?>" disabled>
                            <small class="text-muted">Email tidak dapat diubah</small>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">No. Telepon</label>
                            <input type="text" class="form-control" name="phone" 
                                   value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Role</label>
                            <input type="text" class="form-control" 
                                   value="<?php echo ucfirst($user['role']); ?>" disabled>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Simpan Perubahan
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
