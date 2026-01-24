<?php
// ==========================================
// CONSTANTS.PHP - Konstanta Global
// ==========================================

// Base URL
define('BASE_URL', 'http://localhost/job-portal/');

// Upload Paths
define('UPLOAD_PATH', dirname(__DIR__) . '/assets/uploads/');
define('LOGO_PATH', UPLOAD_PATH . 'logos/');
define('CV_PATH', UPLOAD_PATH . 'cv/');
define('DOCUMENT_PATH', UPLOAD_PATH . 'documents/');
define('AVATAR_PATH', UPLOAD_PATH . 'avatars/');

// Upload URLs
define('LOGO_URL', BASE_URL . 'assets/uploads/logos/');
define('CV_URL', BASE_URL . 'assets/uploads/cv/');
define('DOCUMENT_URL', BASE_URL . 'assets/uploads/documents/');
define('AVATAR_URL', BASE_URL . 'assets/uploads/avatars/');

// File size limits (in bytes)
define('MAX_CV_SIZE', 5 * 1024 * 1024); // 5MB
define('MAX_IMAGE_SIZE', 2 * 1024 * 1024); // 2MB
define('MAX_DOCUMENT_SIZE', 10 * 1024 * 1024); // 10MB

// Allowed file extensions
define('ALLOWED_CV_EXT', ['pdf', 'doc', 'docx']);
define('ALLOWED_IMAGE_EXT', ['jpg', 'jpeg', 'png', 'gif']);
define('ALLOWED_DOC_EXT', ['pdf', 'doc', 'docx', 'txt']);

// Pagination
define('ITEMS_PER_PAGE', 10);
define('JOBS_PER_PAGE', 12);

// Job Types
define('JOB_TYPES', [
    'full-time' => 'Full Time',
    'part-time' => 'Part Time',
    'contract' => 'Contract',
    'internship' => 'Internship',
    'freelance' => 'Freelance'
]);

// Job Levels
define('JOB_LEVELS', [
    'entry' => 'Entry Level',
    'junior' => 'Junior',
    'mid' => 'Mid Level',
    'senior' => 'Senior',
    'manager' => 'Manager',
    'director' => 'Director'
]);

// Application Status
define('APPLICATION_STATUS', [
    'pending' => 'Pending',
    'reviewed' => 'Reviewed',
    'shortlisted' => 'Shortlisted',
    'interview' => 'Interview',
    'offered' => 'Offered',
    'rejected' => 'Rejected',
    'withdrawn' => 'Withdrawn'
]);

// Education Levels
define('EDUCATION_LEVELS', [
    'SMA/SMK' => 'SMA/SMK',
    'D3' => 'Diploma 3',
    'D4' => 'Diploma 4',
    'S1' => 'Sarjana (S1)',
    'S2' => 'Magister (S2)',
    'S3' => 'Doktor (S3)'
]);

// Skill Levels
define('SKILL_LEVELS', [
    'beginner' => 'Beginner',
    'intermediate' => 'Intermediate',
    'advanced' => 'Advanced',
    'expert' => 'Expert'
]);

// Indonesian Cities (Major)
define('INDONESIAN_CITIES', [
    'Jakarta', 'Surabaya', 'Bandung', 'Medan', 'Semarang',
    'Makassar', 'Palembang', 'Tangerang', 'Depok', 'Bekasi',
    'Bogor', 'Malang', 'Yogyakarta', 'Denpasar', 'Balikpapan',
    'Batam', 'Pekanbaru', 'Bandar Lampung', 'Padang', 'Manado'
]);

// Indonesian Provinces
define('INDONESIAN_PROVINCES', [
    'DKI Jakarta', 'Jawa Barat', 'Jawa Tengah', 'Jawa Timur', 'DI Yogyakarta',
    'Banten', 'Bali', 'Sumatera Utara', 'Sumatera Barat', 'Sumatera Selatan',
    'Riau', 'Kepulauan Riau', 'Lampung', 'Kalimantan Timur', 'Kalimantan Selatan',
    'Sulawesi Selatan', 'Sulawesi Utara', 'Papua', 'Maluku', 'Nusa Tenggara Timur'
]);

// Email Configuration
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'your-email@gmail.com');
define('SMTP_PASSWORD', 'your-app-password');
define('SMTP_FROM_EMAIL', 'noreply@jobportal.com');
define('SMTP_FROM_NAME', 'JobPortal');

// Site Settings
define('SITE_NAME', 'JobPortal');
define('SITE_TITLE', 'Portal Lowongan Kerja Terpercaya');
define('SITE_DESCRIPTION', 'Temukan pekerjaan impian Anda di JobPortal - Platform lowongan kerja terpercaya di Indonesia');
define('SITE_KEYWORDS', 'lowongan kerja, cari kerja, karir, pekerjaan, job portal');

// Social Media
define('FACEBOOK_URL', 'https://facebook.com/jobportal');
define('TWITTER_URL', 'https://twitter.com/jobportal');
define('LINKEDIN_URL', 'https://linkedin.com/company/jobportal');
define('INSTAGRAM_URL', 'https://instagram.com/jobportal');

// Admin Contact
define('ADMIN_EMAIL', 'admin@jobportal.com');
define('SUPPORT_EMAIL', 'support@jobportal.com');

// Create upload directories if not exist
if(!file_exists(LOGO_PATH)) mkdir(LOGO_PATH, 0755, true);
if(!file_exists(CV_PATH)) mkdir(CV_PATH, 0755, true);
if(!file_exists(DOCUMENT_PATH)) mkdir(DOCUMENT_PATH, 0755, true);
if(!file_exists(AVATAR_PATH)) mkdir(AVATAR_PATH, 0755, true);
?>