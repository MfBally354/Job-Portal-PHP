-- Database Job Portal - Lengkap
CREATE DATABASE IF NOT EXISTS job_portal_v2;
USE job_portal_v2;

-- Tabel users
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    role ENUM('admin', 'employer', 'jobseeker') DEFAULT 'jobseeker',
    is_active TINYINT(1) DEFAULT 1,
    email_verified TINYINT(1) DEFAULT 0,
    avatar VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_role (role)
);

-- Tabel companies
CREATE TABLE companies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    name VARCHAR(200) NOT NULL,
    description TEXT,
    logo VARCHAR(255),
    website VARCHAR(255),
    address TEXT,
    city VARCHAR(100),
    province VARCHAR(100),
    phone VARCHAR(20),
    email VARCHAR(100),
    employee_count VARCHAR(50),
    industry VARCHAR(100),
    founded_year YEAR,
    is_verified TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_verified (is_verified),
    INDEX idx_city (city)
);

-- Tabel job_categories
CREATE TABLE job_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL,
    icon VARCHAR(50),
    description TEXT,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabel jobs
CREATE TABLE jobs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    company_id INT NOT NULL,
    category_id INT,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    requirements TEXT,
    responsibilities TEXT,
    benefits TEXT,
    location VARCHAR(255),
    city VARCHAR(100),
    province VARCHAR(100),
    type ENUM('full-time', 'part-time', 'contract', 'internship', 'freelance') DEFAULT 'full-time',
    level ENUM('entry', 'junior', 'mid', 'senior', 'manager', 'director') DEFAULT 'entry',
    education VARCHAR(100),
    experience VARCHAR(50),
    salary_min DECIMAL(15,2),
    salary_max DECIMAL(15,2),
    salary_type ENUM('monthly', 'yearly', 'hourly', 'project') DEFAULT 'monthly',
    is_salary_negotiable TINYINT(1) DEFAULT 0,
    positions_available INT DEFAULT 1,
    deadline DATE,
    status ENUM('draft', 'active', 'closed', 'cancelled') DEFAULT 'active',
    views INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES job_categories(id) ON DELETE SET NULL,
    INDEX idx_status (status),
    INDEX idx_city (city),
    INDEX idx_type (type),
    INDEX idx_category (category_id),
    FULLTEXT idx_search (title, description)
);

-- Tabel job_skills
CREATE TABLE job_skills (
    id INT AUTO_INCREMENT PRIMARY KEY,
    job_id INT NOT NULL,
    skill_name VARCHAR(100) NOT NULL,
    is_required TINYINT(1) DEFAULT 1,
    FOREIGN KEY (job_id) REFERENCES jobs(id) ON DELETE CASCADE,
    INDEX idx_skill (skill_name)
);

-- Tabel jobseeker_profiles
CREATE TABLE jobseeker_profiles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    bio TEXT,
    cv_path VARCHAR(255),
    date_of_birth DATE,
    gender ENUM('male', 'female', 'other'),
    address TEXT,
    city VARCHAR(100),
    province VARCHAR(100),
    postal_code VARCHAR(10),
    linkedin_url VARCHAR(255),
    portfolio_url VARCHAR(255),
    expected_salary_min DECIMAL(15,2),
    expected_salary_max DECIMAL(15,2),
    job_type_preference VARCHAR(255),
    willing_to_relocate TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Tabel jobseeker_education
CREATE TABLE jobseeker_education (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    degree VARCHAR(100) NOT NULL,
    institution VARCHAR(200) NOT NULL,
    field_of_study VARCHAR(100),
    start_date DATE,
    end_date DATE,
    is_current TINYINT(1) DEFAULT 0,
    gpa DECIMAL(3,2),
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Tabel jobseeker_experience
CREATE TABLE jobseeker_experience (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    job_title VARCHAR(100) NOT NULL,
    company_name VARCHAR(200) NOT NULL,
    location VARCHAR(255),
    start_date DATE,
    end_date DATE,
    is_current TINYINT(1) DEFAULT 0,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Tabel jobseeker_skills
CREATE TABLE jobseeker_skills (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    skill_name VARCHAR(100) NOT NULL,
    level ENUM('beginner', 'intermediate', 'advanced', 'expert') DEFAULT 'intermediate',
    years_of_experience INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_skill (skill_name)
);

-- Tabel applications
CREATE TABLE applications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    job_id INT NOT NULL,
    user_id INT NOT NULL,
    cv_path VARCHAR(255),
    cover_letter TEXT,
    status ENUM('pending', 'reviewed', 'shortlisted', 'interview', 'offered', 'rejected', 'withdrawn') DEFAULT 'pending',
    notes TEXT,
    applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (job_id) REFERENCES jobs(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_application (job_id, user_id),
    INDEX idx_status (status),
    INDEX idx_user (user_id)
);

-- Tabel saved_jobs
CREATE TABLE saved_jobs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    job_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (job_id) REFERENCES jobs(id) ON DELETE CASCADE,
    UNIQUE KEY unique_saved (user_id, job_id)
);

-- Tabel notifications
CREATE TABLE notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    type VARCHAR(50),
    link VARCHAR(255),
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_read (user_id, is_read)
);

-- Tabel job_views
CREATE TABLE job_views (
    id INT AUTO_INCREMENT PRIMARY KEY,
    job_id INT NOT NULL,
    user_id INT,
    ip_address VARCHAR(45),
    viewed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (job_id) REFERENCES jobs(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Insert admin default
INSERT INTO users (name, email, password, role, is_active, email_verified) VALUES 
('Admin', 'admin@jobportal.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 1, 1);

-- Insert kategori defaulSt
INSERT INTO job_categories (name, slug, icon, description) VALUES
('IT & Software', 'it-software', 'fa-laptop-code', 'Pekerjaan di bidang teknologi informasi'),
('Marketing', 'marketing', 'fa-bullhorn', 'Pekerjaan di bidang pemasaran'),
('Finance', 'finance', 'fa-chart-line', 'Pekerjaan di bidang keuangan'),
('Human Resources', 'human-resources', 'fa-users', 'Pekerjaan di bidang SDM'),
('Sales', 'sales', 'fa-handshake', 'Pekerjaan di bidang penjualan'),
('Customer Service', 'customer-service', 'fa-headset', 'Pekerjaan layanan pelanggan'),
('Design', 'design', 'fa-palette', 'Pekerjaan di bidang desain'),
('Engineering', 'engineering', 'fa-cogs', 'Pekerjaan di bidang teknik'),
('Healthcare', 'healthcare', 'fa-heartbeat', 'Pekerjaan di bidang kesehatan'),
('Education', 'education', 'fa-graduation-cap', 'Pekerjaan di bidang pendidikan');