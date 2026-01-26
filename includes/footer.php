<!-- Footer -->
    <footer class="bg-dark text-white py-5 mt-5">
        <div class="container">
            <div class="row">
                <!-- About Section -->
                <div class="col-md-4 mb-4">
                    <h5 class="mb-3">
                        <i class="fas fa-briefcase me-2"></i>
                        <strong>JobPortal</strong>
                    </h5>
                    <p class="text-muted">
                        Platform terpercaya untuk mencari dan menemukan pekerjaan impian Anda di Indonesia. 
                        Menghubungkan talenta terbaik dengan perusahaan berkualitas.
                    </p>
                    <div class="social-links mt-3">
                        <a href="<?php echo FACEBOOK_URL; ?>" class="text-white me-3" target="_blank">
                            <i class="fab fa-facebook fa-2x"></i>
                        </a>
                        <a href="<?php echo TWITTER_URL; ?>" class="text-white me-3" target="_blank">
                            <i class="fab fa-twitter fa-2x"></i>
                        </a>
                        <a href="<?php echo LINKEDIN_URL; ?>" class="text-white me-3" target="_blank">
                            <i class="fab fa-linkedin fa-2x"></i>
                        </a>
                        <a href="<?php echo INSTAGRAM_URL; ?>" class="text-white" target="_blank">
                            <i class="fab fa-instagram fa-2x"></i>
                        </a>
                    </div>
                </div>
                
                <!-- For Job Seekers -->
                <div class="col-md-2 mb-4">
                    <h6 class="text-uppercase fw-bold mb-3">Pencari Kerja</h6>
                    <ul class="list-unstyled">
                        <li class="mb-2">
                            <a href="<?php echo BASE_URL; ?>public/jobs.php" class="text-muted text-decoration-none">
                                <i class="fas fa-chevron-right me-2"></i>Cari Lowongan
                            </a>
                        </li>
                        <li class="mb-2">
                            <a href="<?php echo BASE_URL; ?>public/companies.php" class="text-muted text-decoration-none">
                                <i class="fas fa-chevron-right me-2"></i>Perusahaan
                            </a>
                        </li>
                        <li class="mb-2">
                            <a href="<?php echo BASE_URL; ?>auth/register.php" class="text-muted text-decoration-none">
                                <i class="fas fa-chevron-right me-2"></i>Daftar
                            </a>
                        </li>
                        <li class="mb-2">
                            <a href="<?php echo BASE_URL; ?>auth/login.php" class="text-muted text-decoration-none">
                                <i class="fas fa-chevron-right me-2"></i>Login
                            </a>
                        </li>
                    </ul>
                </div>
                
                <!-- For Employers -->
                <div class="col-md-2 mb-4">
                    <h6 class="text-uppercase fw-bold mb-3">Perusahaan</h6>
                    <ul class="list-unstyled">
                        <li class="mb-2">
                            <a href="<?php echo BASE_URL; ?>auth/register.php" class="text-muted text-decoration-none">
                                <i class="fas fa-chevron-right me-2"></i>Daftar Perusahaan
                            </a>
                        </li>
                        <li class="mb-2">
                            <a href="<?php echo BASE_URL; ?>employer/post_job.php" class="text-muted text-decoration-none">
                                <i class="fas fa-chevron-right me-2"></i>Pasang Lowongan
                            </a>
                        </li>
                        <li class="mb-2">
                            <a href="<?php echo BASE_URL; ?>employer/pricing.php" class="text-muted text-decoration-none">
                                <i class="fas fa-chevron-right me-2"></i>Harga
                            </a>
                        </li>
                    </ul>
                </div>
                
                <!-- About & Support -->
                <div class="col-md-2 mb-4">
                    <h6 class="text-uppercase fw-bold mb-3">Tentang</h6>
                    <ul class="list-unstyled">
                        <li class="mb-2">
                            <a href="<?php echo BASE_URL; ?>public/about.php" class="text-muted text-decoration-none">
                                <i class="fas fa-chevron-right me-2"></i>Tentang Kami
                            </a>
                        </li>
                        <li class="mb-2">
                            <a href="<?php echo BASE_URL; ?>public/contact.php" class="text-muted text-decoration-none">
                                <i class="fas fa-chevron-right me-2"></i>Kontak
                            </a>
                        </li>
                        <li class="mb-2">
                            <a href="<?php echo BASE_URL; ?>public/faq.php" class="text-muted text-decoration-none">
                                <i class="fas fa-chevron-right me-2"></i>FAQ
                            </a>
                        </li>
                        <li class="mb-2">
                            <a href="<?php echo BASE_URL; ?>public/blog.php" class="text-muted text-decoration-none">
                                <i class="fas fa-chevron-right me-2"></i>Blog
                            </a>
                        </li>
                    </ul>
                </div>
                
                <!-- Contact Info -->
                <div class="col-md-2 mb-4">
                    <h6 class="text-uppercase fw-bold mb-3">Kontak</h6>
                    <ul class="list-unstyled text-muted">
                        <li class="mb-2">
                            <i class="fas fa-map-marker-alt me-2"></i>
                            Soreang, Bandung
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-phone me-2"></i>
                            +62 812 3456 7890
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-envelope me-2"></i>
                            <?php echo SUPPORT_EMAIL; ?>
                        </li>
                    </ul>
                </div>
            </div>
            
            <hr class="bg-secondary my-4">
            
            <!-- Bottom Footer -->
            <div class="row">
                <div class="col-md-6 text-center text-md-start mb-3 mb-md-0">
                    <p class="mb-0 text-muted">
                        &copy; <?php echo date('Y'); ?> JobPortal. All rights reserved.
                    </p>
                </div>
                <div class="col-md-6 text-center text-md-end">
                    <a href="<?php echo BASE_URL; ?>public/privacy.php" class="text-muted text-decoration-none me-3">Privacy Policy</a>
                    <a href="<?php echo BASE_URL; ?>public/terms.php" class="text-muted text-decoration-none me-3">Terms of Service</a>
                    <a href="<?php echo BASE_URL; ?>public/sitemap.php" class="text-muted text-decoration-none">Sitemap</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Back to Top Button -->
    <button id="backToTop" class="btn btn-primary rounded-circle position-fixed bottom-0 end-0 m-4" 
            style="display: none; width: 50px; height: 50px; z-index: 1000;">
        <i class="fas fa-arrow-up"></i>
    </button>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Common JavaScript -->
    <script>
        // Back to top button
        window.onscroll = function() {
            const backToTop = document.getElementById('backToTop');
            if (document.body.scrollTop > 300 || document.documentElement.scrollTop > 300) {
                backToTop.style.display = 'block';
            } else {
                backToTop.style.display = 'none';
            }
        };
        
        document.getElementById('backToTop')?.addEventListener('click', function() {
            window.scrollTo({top: 0, behavior: 'smooth'});
        });
        
        // Toast notifications
        function showSuccessToast(message) {
            document.getElementById('successToastBody').textContent = message;
            const toast = new bootstrap.Toast(document.getElementById('successToast'));
            toast.show();
        }
        
        function showErrorToast(message) {
            document.getElementById('errorToastBody').textContent = message;
            const toast = new bootstrap.Toast(document.getElementById('errorToast'));
            toast.show();
        }
        
        // Loading overlay
        function showLoading() {
            document.getElementById('loadingOverlay').classList.add('show');
        }
        
        function hideLoading() {
            document.getElementById('loadingOverlay').classList.remove('show');
        }
        
        // Auto-hide alerts after 5 seconds
        document.addEventListener('DOMContentLoaded', function() {
            const alerts = document.querySelectorAll('.alert:not(.alert-permanent)');
            alerts.forEach(function(alert) {
                setTimeout(function() {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                }, 5000);
            });
        });
        
        // Confirm delete actions
        document.querySelectorAll('[data-confirm]').forEach(function(element) {
            element.addEventListener('click', function(e) {
                if (!confirm(this.getAttribute('data-confirm'))) {
                    e.preventDefault();
                }
            });
        });
        
        // Form validation feedback
        (function () {
            'use strict';
            const forms = document.querySelectorAll('.needs-validation');
            Array.prototype.slice.call(forms).forEach(function (form) {
                form.addEventListener('submit', function (event) {
                    if (!form.checkValidity()) {
                        event.preventDefault();
                        event.stopPropagation();
                    }
                    form.classList.add('was-validated');
                }, false);
            });
        })();
    </script>
    
    <?php if(isset($extra_js)): ?>
        <!-- Page specific JavaScript -->
        <?php echo $extra_js; ?>
    <?php endif; ?>

</body>
</html>