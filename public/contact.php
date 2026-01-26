<?php
session_start();
require_once '../config/database.php';
require_once '../config/functions.php';
require_once '../config/constants.php';

$success = '';
$error = '';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = sanitize_input($_POST['name']);
    $email = sanitize_input($_POST['email']);
    $subject = sanitize_input($_POST['subject']);
    $message = sanitize_input($_POST['message']);
    
    if(empty($name) || empty($email) || empty($subject) || empty($message)) {
        $error = 'Semua field harus diisi!';
    } elseif(!validate_email($email)) {
        $error = 'Format email tidak valid!';
    } else {
        // Here you would typically send an email or store in database
        // For now, we'll just show a success message
        $success = 'Terima kasih! Pesan Anda telah terkirim. Kami akan menghubungi Anda segera.';
    }
}

$page_title = 'Hubungi Kami';
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
        .contact-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 80px 0;
        }
        .contact-card {
            border: none;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            border-radius: 15px;
            transition: transform 0.3s;
        }
        .contact-card:hover {
            transform: translateY(-5px);
        }
        .info-card {
            background: #f8f9fc;
            padding: 30px;
            border-radius: 15px;
            height: 100%;
            text-align: center;
            transition: all 0.3s;
        }
        .info-card:hover {
            background: #667eea;
            color: white;
        }
        .info-card:hover i {
            color: white !important;
        }
        .icon-circle {
            width: 80px;
            height: 80px;
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
        }
        .map-container {
            height: 400px;
            border-radius: 15px;
            overflow: hidden;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top">
        <div class="container">
            <a class="navbar-brand" href="../index.php">
                <i class="fas fa-briefcase me-2"></i><strong>JobPortal</strong>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="../index.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="jobs.php">Cari Lowongan</a></li>
                    <li class="nav-item"><a class="nav-link" href="companies.php">Perusahaan</a></li>
                    <li class="nav-item"><a class="nav-link active" href="contact.php">Kontak</a></li>
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user"></i> <?php echo htmlspecialchars($_SESSION['name']); ?>
                            </a>
                            <ul class="dropdown-menu">
                                <?php if($_SESSION['role'] == 'jobseeker'): ?>
                                    <li><a class="dropdown-item" href="../jobseeker/dashboard.php">Dashboard</a></li>
                                <?php elseif($_SESSION['role'] == 'employer'): ?>
                                    <li><a class="dropdown-item" href="../employer/dashboard.php">Dashboard</a></li>
                                <?php endif; ?>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="../auth/logout.php">Logout</a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item"><a class="nav-link" href="../auth/login.php">Login</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Header -->
    <section class="contact-header">
        <div class="container text-center">
            <h1 class="display-4 fw-bold mb-3">Hubungi Kami</h1>
            <p class="lead">Kami siap membantu Anda. Jangan ragu untuk menghubungi kami!</p>
        </div>
    </section>

    <!-- Contact Info -->
    <section class="py-5">
        <div class="container">
            <div class="row mb-5">
                <div class="col-md-4 mb-4">
                    <div class="info-card">
                        <div class="icon-circle">
                            <i class="fas fa-map-marker-alt fa-2x text-primary"></i>
                        </div>
                        <h5 class="fw-bold mb-3">Alamat Kantor</h5>
                        <p class="mb-0">Jl. Raya Soreang No. 123<br>Soreang, Bandung<br>Jawa Barat, Indonesia</p>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="info-card">
                        <div class="icon-circle">
                            <i class="fas fa-phone fa-2x text-success"></i>
                        </div>
                        <h5 class="fw-bold mb-3">Telepon</h5>
                        <p class="mb-0">
                            <strong>Phone:</strong> +62 22 1234 5678<br>
                            <strong>WhatsApp:</strong> +62 812 3456 7890<br>
                            <strong>Jam Kerja:</strong> 08:00 - 17:00 WIB
                        </p>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="info-card">
                        <div class="icon-circle">
                            <i class="fas fa-envelope fa-2x text-danger"></i>
                        </div>
                        <h5 class="fw-bold mb-3">Email</h5>
                        <p class="mb-0">
                            <strong>General:</strong> info@jobportal.com<br>
                            <strong>Support:</strong> support@jobportal.com<br>
                            <strong>Partnership:</strong> partner@jobportal.com
                        </p>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Contact Form -->
                <div class="col-lg-6 mb-4">
                    <div class="card contact-card">
                        <div class="card-body p-4">
                            <h3 class="mb-4"><i class="fas fa-paper-plane"></i> Kirim Pesan</h3>
                            
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
                            
                            <form method="POST" action="">
                                <div class="mb-3">
                                    <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="name" required 
                                           placeholder="Masukkan nama Anda"
                                           value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>">
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Email <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control" name="email" required 
                                           placeholder="email@example.com"
                                           value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Subjek <span class="text-danger">*</span></label>
                                    <select class="form-select" name="subject" required>
                                        <option value="">Pilih subjek...</option>
                                        <option value="General Inquiry">Pertanyaan Umum</option>
                                        <option value="Job Posting">Posting Lowongan</option>
                                        <option value="Technical Support">Bantuan Teknis</option>
                                        <option value="Partnership">Kerjasama</option>
                                        <option value="Complaint">Keluhan</option>
                                        <option value="Other">Lainnya</option>
                                    </select>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Pesan <span class="text-danger">*</span></label>
                                    <textarea class="form-control" name="message" rows="6" required 
                                              placeholder="Tuliskan pesan Anda di sini..."><?php echo isset($_POST['message']) ? htmlspecialchars($_POST['message']) : ''; ?></textarea>
                                </div>
                                
                                <button type="submit" class="btn btn-primary btn-lg w-100">
                                    <i class="fas fa-paper-plane"></i> Kirim Pesan
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Map & Social Media -->
                <div class="col-lg-6 mb-4">
                    <div class="card contact-card mb-4">
                        <div class="card-body p-0">
                            <div class="map-container">
                                <iframe 
                                    src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d126748.56347862248!2d107.50949!3d-6.9174639!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e68e6398252477f%3A0x146a1f93d3e815b2!2sBandung%2C%20Bandung%20City%2C%20West%20Java!5e0!3m2!1sen!2sid!4v1234567890123!5m2!1sen!2sid" 
                                    width="100%" 
                                    height="400" 
                                    style="border:0;" 
                                    allowfullscreen="" 
                                    loading="lazy">
                                </iframe>
                            </div>
                        </div>
                    </div>

                    <div class="card contact-card">
                        <div class="card-body p-4 text-center">
                            <h5 class="mb-4">Ikuti Kami</h5>
                            <div class="d-flex justify-content-center gap-3">
                                <a href="#" class="btn btn-primary btn-lg">
                                    <i class="fab fa-facebook fa-2x"></i>
                                </a>
                                <a href="#" class="btn btn-info btn-lg">
                                    <i class="fab fa-twitter fa-2x"></i>
                                </a>
                                <a href="#" class="btn btn-primary btn-lg">
                                    <i class="fab fa-linkedin fa-2x"></i>
                                </a>
                                <a href="#" class="btn btn-danger btn-lg">
                                    <i class="fab fa-instagram fa-2x"></i>
                                </a>
                                <a href="#" class="btn btn-success btn-lg">
                                    <i class="fab fa-whatsapp fa-2x"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="fw-bold">Pertanyaan yang Sering Diajukan</h2>
                <p class="text-muted">Temukan jawaban untuk pertanyaan umum</p>
            </div>
            
            <div class="row">
                <div class="col-lg-8 mx-auto">
                    <div class="accordion" id="faqAccordion">
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                    Bagaimana cara mendaftar di JobPortal?
                                </button>
                            </h2>
                            <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    Anda dapat mendaftar dengan mengklik tombol "Daftar" di pojok kanan atas, kemudian pilih jenis akun (Pencari Kerja atau Employer) dan isi formulir pendaftaran.
                                </div>
                            </div>
                        </div>
                        
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                    Apakah JobPortal gratis?
                                </button>
                            </h2>
                            <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    Ya! JobPortal gratis untuk pencari kerja. Employer dapat memposting lowongan dengan biaya yang terjangkau.
                                </div>
                            </div>
                        </div>
                        
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                                    Berapa lama proses verifikasi perusahaan?
                                </button>
                            </h2>
                            <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    Proses verifikasi perusahaan biasanya memakan waktu 1-3 hari kerja setelah semua dokumen lengkap diterima.
                                </div>
                            </div>
                        </div>
                        
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq4">
                                    Bagaimana cara melamar pekerjaan?
                                </button>
                            </h2>
                            <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    Setelah login, cari lowongan yang sesuai, klik "Lihat Detail", kemudian klik tombol "Apply Now". Pastikan CV Anda sudah terupload.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4">
        <div class="container text-center">
            <p class="mb-0">&copy; 2024 JobPortal. All rights reserved.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>