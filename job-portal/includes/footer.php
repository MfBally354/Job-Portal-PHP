    <footer class="bg-dark text-white mt-5 py-4">
        <div class="container">
            <div class="row">
                <div class="col-md-4">
                    <h5><i class="fas fa-briefcase"></i> JobPortal</h5>
                    <p>Platform terpercaya untuk mencari dan menemukan pekerjaan impian Anda.</p>
                </div>
                <div class="col-md-4">
                    <h5>Link Cepat</h5>
                    <ul class="list-unstyled">
                        <li><a href="<?php echo isset($base_url) ? $base_url . 'pages/' : ''; ?>jobs.php" class="text-white">Cari Lowongan</a></li>
                        <li><a href="<?php echo isset($base_url) ? $base_url . 'pages/' : ''; ?>companies.php" class="text-white">Perusahaan</a></li>
                        <li><a href="#" class="text-white">Tentang Kami</a></li>
                    </ul>
                </div>
                <div class="col-md-4">
                    <h5>Ikuti Kami</h5>
                    <div class="social-links">
                        <a href="#" class="text-white me-3"><i class="fab fa-facebook fa-2x"></i></a>
                        <a href="#" class="text-white me-3"><i class="fab fa-twitter fa-2x"></i></a>
                        <a href="#" class="text-white me-3"><i class="fab fa-linkedin fa-2x"></i></a>
                        <a href="#" class="text-white"><i class="fab fa-instagram fa-2x"></i></a>
                    </div>
                </div>
            </div>
            <hr class="bg-light">
            <p class="text-center mb-0">&copy; 2024 JobPortal. Semua hak dilindungi.</p>
        </div>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?php echo isset($js_path) ? $js_path : '../assets/js/main.js'; ?>"></script>
</body>
</html>
