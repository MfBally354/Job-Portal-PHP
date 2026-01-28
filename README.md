# 💼 JobPortal - Platform Lowongan Kerja Terpercaya

<div align="center">

<img width="1920" height="1080" alt="Screenshot (670)" src="https://github.com/user-attachments/assets/489e8917-e9bf-4544-ac83-d6099f261689" />


**Platform pencarian kerja modern yang menghubungkan talenta terbaik dengan perusahaan berkualitas di Indonesia**

[![PHP Version](https://img.shields.io/badge/PHP-7.4%2B-blue.svg)](https://php.net)
[![MySQL](https://img.shields.io/badge/MySQL-5.7%2B-orange.svg)](https://www.mysql.com/)
[![Bootstrap](https://img.shields.io/badge/Bootstrap-5.3.2-purple.svg)](https://getbootstrap.com)
[![License](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE)

[Demo](#demo) • [Features](#features) • [Installation](#installation) • [Screenshots](#screenshots) • [Documentation](#documentation)

</div>

---

## 📖 Tentang Proyek

JobPortal adalah platform web-based yang dirancang untuk memudahkan pencari kerja menemukan peluang karir terbaik dan membantu perusahaan merekrut talenta berkualitas. Dengan antarmuka yang intuitif dan fitur-fitur lengkap, JobPortal menjadi solusi all-in-one untuk kebutuhan rekrutmen modern.

### 🎯 Tujuan Proyek

- Menyediakan platform terpercaya untuk pencarian lowongan kerja
- Memudahkan perusahaan dalam proses rekrutmen
- Menghubungkan jobseeker dengan peluang karir yang sesuai
- Meningkatkan efisiensi proses hiring di Indonesia

---

## ✨ Features

### 👤 Untuk Job Seekers

<div align="center">

<img width="1920" height="1080" alt="image" src="https://github.com/user-attachments/assets/db335f7e-dac6-4d44-8dff-7d6f12ada944" />


</div>

- ✅ **Pencarian Lowongan Advanced** - Filter berdasarkan kategori, lokasi, gaji, dan tipe pekerjaan
- ✅ **Profile Management** - Kelola profil profesional dengan CV, pengalaman, dan skills
- ✅ **One-Click Apply** - Lamar pekerjaan dengan mudah dan cepat
- ✅ **Job Recommendations** - Rekomendasi pekerjaan berdasarkan profil dan preferensi
- ✅ **Application Tracking** - Monitor status lamaran secara real-time
- ✅ **Save Jobs** - Simpan lowongan favorit untuk dilamar nanti
- ✅ **Notifications** - Notifikasi real-time untuk update lamaran

### 🏢 Untuk Employers

<div align="center">

<img width="1920" height="1080" alt="Screenshot (671)" src="https://github.com/user-attachments/assets/13c3751b-5762-48bf-922f-61560c3a9fdd" />


</div>

- ✅ **Company Profile** - Kelola profil perusahaan dengan logo dan informasi lengkap
- ✅ **Post Jobs** - Posting lowongan dengan editor lengkap
- ✅ **Applicant Management** - Kelola pelamar dengan sistem status tracking
- ✅ **Verification Badge** - Status terverifikasi untuk kredibilitas perusahaan
- ✅ **Analytics Dashboard** - Statistik views, aplikasi, dan performa lowongan
- ✅ **Bulk Actions** - Kelola multiple aplikasi sekaligus

### 🛡️ Untuk Admin

<div align="center">

<img width="1920" height="1080" alt="image" src="https://github.com/user-attachments/assets/92290ac3-ac7f-4553-887b-7a220a9a505c" />


</div>

- ✅ **User Management** - Kelola user (jobseekers & employers)
- ✅ **Company Verification** - Verifikasi dan moderasi perusahaan
- ✅ **Job Moderation** - Moderasi posting lowongan
- ✅ **Analytics & Reports** - Dashboard statistik lengkap
- ✅ **Category Management** - Kelola kategori pekerjaan
- ✅ **System Settings** - Konfigurasi sistem

---

## 🖼️ Screenshots

### Landing Page
<img width="1920" height="1080" alt="image" src="https://github.com/user-attachments/assets/4c9e60c7-2eb5-4393-8e71-6d4f1e43ca65" />


### Browse Jobs
<img width="1920" height="1080" alt="image" src="https://github.com/user-attachments/assets/bc051659-8edd-4d1c-9c50-722d4e3c43bf" />


### Job Detail
<img width="1920" height="1080" alt="image" src="https://github.com/user-attachments/assets/d776818a-3973-4cd9-9e5a-aed4393c5e86" />


### Dashboard Jobseeker
<img width="1920" height="1080" alt="image" src="https://github.com/user-attachments/assets/c684012c-328a-48ce-8606-4d5bde5bd7c3" />


### Dashboard Employer
<img width="1920" height="1080" alt="image" src="https://github.com/user-attachments/assets/23960ff0-430a-4329-91bb-84bb1d54061b" />


### Admin Panel
<img width="1920" height="1080" alt="image" src="https://github.com/user-attachments/assets/9efd08b1-a025-460e-b178-0f5e1f37e2f2" />


---

## 🛠️ Tech Stack

### Backend
- **PHP 7.4+** - Server-side scripting
- **MySQL 5.7+** - Database management
- **MySQLi** - Database connectivity

### Frontend
- **HTML5 & CSS3** - Structure and styling
- **Bootstrap 5.3.2** - Responsive framework
- **JavaScript (Vanilla)** - Client-side interactivity
- **Font Awesome 6.5.1** - Icons
- **Chart.js** - Data visualization

### Development Tools
- **Docker** (Optional) - Containerization
- **Git** - Version control

---

## 📋 Requirements

### Minimum Requirements
- PHP >= 7.4
- MySQL >= 5.7 atau MariaDB >= 10.2
- Apache/Nginx Web Server
- 50 MB Free Disk Space

### Recommended
- PHP >= 8.0
- MySQL >= 8.0
- 2 GB RAM
- SSL Certificate (untuk production)

---

## 🚀 Installation

### Method 1: Native PHP Installation

#### 1. Clone Repository
```bash
git clone https://github.com/MfBally354/Job-Portal-PHP.git
cd job-portal
```

#### 2. Setup Database
```bash
# Login ke MySQL
mysql -u root -p

# Buat database
CREATE DATABASE job_portal;

# Import database
mysql -u root -p job_portal < database_complete.sql
```

#### 3. Konfigurasi Database
Edit file `config/database.php`:
```php
define('DB_HOST', '127.0.0.1');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'job_portal');
```

#### 4. Set Permissions
```bash
chmod 755 -R assets/uploads/
chmod 755 -R config/
```

#### 5. Jalankan Aplikasi
```bash
# Jika menggunakan PHP built-in server
php -S localhost:8000

# Atau akses via Apache/Nginx
# http://localhost/Job-portal
```

### Method 2: Docker Installation

#### 1. Clone Repository
```bash
git clone https://github.com/MfBally354/Job-Portal-PHP.git
cd Job-Portal-PHP
```

#### 2. Jalankan Docker Compose
```bash
docker compose up -d
```

#### 3. Import Database
```bash
docker compose exec db mysql -u root -p job_portal < database_complete.sql
```

#### 4. Akses Aplikasi
```
http://localhost:8080
```

---

## 👥 Default Users

Setelah instalasi, gunakan akun berikut untuk login:

### Admin Account
```
Email: admin@jobportal.com
Password: password
```

> ⚠️ **PENTING**: Segera ubah password default setelah login pertama!

---

## 📁 Project Structure

```
Job-Portal-PHP/
├── admin/                  # Admin panel files
│   ├── dashboard.php
│   ├── users.php
│   ├── companies.php
│   └── jobs.php
├── assets/                 # Static assets
│   └── uploads/           # User uploaded files
│       ├── logos/         # Company logos
│       ├── cv/            # User CVs
│       ├── documents/     # Documents
│       └── avatars/       # Profile pictures
├── auth/                   # Authentication
│   ├── login.php
│   ├── register.php
│   └── logout.php
├── config/                 # Configuration files
│   ├── database.php       # Database connection
│   ├── functions.php      # Helper functions
│   └── constants.php      # Constants
├── employer/              # Employer dashboard
│   ├── dashboard.php
│   ├── profile.php
│   ├── post_job.php
│   ├── my_jobs.php
│   └── applicants.php
├── jobseeker/             # Jobseeker dashboard
│   ├── dashboard.php
│   ├── profile.php
│   ├── browse_jobs.php
│   ├── my_applications.php
│   └── saved_jobs.php
├── public/                # Public pages
│   ├── jobs.php
│   ├── job_detail.php
│   ├── companies.php
│   ├── contact.php
│   └── apply_job.php
├── includes/              # Reusable components
│   ├── header.php
│   ├── footer.php
│   └── navbar.php
├── api/                   # API endpoints
│   └── save_job.php
├── index.php              # Landing page
├── database_complete.sql  # Database schema
├── docker-compose.yml     # Docker configuration
└── README.md             # This file
```

---

## 🔧 Configuration

### Base URL
Edit `config/constants.php`:
```php
define('BASE_URL', 'http://jobportal.com/'); //Just example
```

### Upload Settings
Sesuaikan ukuran maksimal file di `config/constants.php`:
```php
define('MAX_CV_SIZE', 5 * 1024 * 1024);      // 5MB
define('MAX_IMAGE_SIZE', 2 * 1024 * 1024);    // 2MB
```

### Email Settings
Konfigurasi SMTP di `config/constants.php`:
```php
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'your-email@gmail.com');
define('SMTP_PASSWORD', 'your-app-password');
```

---

## 📊 Database Schema

### Main Tables
- `users` - User accounts (admin, employer, jobseeker)
- `companies` - Company profiles
- `jobs` - Job postings
- `applications` - Job applications
- `job_categories` - Job categories
- `jobseeker_profiles` - Jobseeker profiles
- `jobseeker_experience` - Work experience
- `jobseeker_education` - Education history
- `jobseeker_skills` - Skills
- `notifications` - System notifications
- `saved_jobs` - Saved job listings
- `activity_logs` - User activity logs

### Entity Relationship Diagram
<img width="1139" height="838" alt="image" src="https://github.com/user-attachments/assets/d570bed6-f8b9-426f-ad53-e24d3a6bd746" />


---

## 🔐 Security Features

- ✅ **Password Hashing** - Menggunakan PHP password_hash()
- ✅ **SQL Injection Prevention** - Prepared statements
- ✅ **XSS Protection** - Input sanitization
- ✅ **CSRF Protection** - Form tokens
- ✅ **Role-Based Access Control** - Admin, Employer, Jobseeker
- ✅ **Session Management** - Secure session handling
- ✅ **File Upload Validation** - Type and size checking

---

## 🌐 Browser Support

| Browser | Supported Version |
|---------|------------------|
| Chrome  | ✅ Latest 2 versions |
| Firefox | ✅ Latest 2 versions |
| Safari  | ✅ Latest 2 versions |
| Edge    | ✅ Latest 2 versions |
| Opera   | ✅ Latest 2 versions |

---

## 🐛 Troubleshooting

### Database Connection Error
```bash
# Pastikan MySQL running
sudo service mysql start  # Linux
brew services start mysql # Mac

# Cek kredensial di config/database.php
```

### Permission Denied pada Uploads
```bash
chmod 755 -R assets/uploads/
chown www-data:www-data assets/uploads/  # Linux
```

### Port 8000 Already in Use
```bash
# Gunakan port lain
php -S localhost:8080
```

---

## 🤝 Contributing

Kontribusi sangat diterima! Berikut cara berkontribusi:

1. Fork repository ini
2. Buat branch baru (`git checkout -b feature/AmazingFeature`)
3. Commit changes (`git commit -m 'Add some AmazingFeature'`)
4. Push ke branch (`git push origin feature/AmazingFeature`)
5. Buat Pull Request

### Coding Standards
- Ikuti PSR-12 coding style untuk PHP
- Gunakan meaningful variable names
- Tambahkan comments untuk logic yang kompleks
- Test fitur sebelum submit PR

---

## 📝 TODO / Roadmap

- [ ] Email notification system
- [ ] Advanced search with AI recommendations
- [ ] Mobile responsive optimization
- [ ] Multi-language support
- [ ] API documentation
- [ ] Unit testing
- [ ] LinkedIn integration
- [ ] Video interview feature
- [ ] Skill assessment tests
- [ ] Salary comparison tool

---

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

```
MIT License

Copyright (c) 2024 JobPortal

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.
```

---

## 👨‍💻 Authors

- **Your Name** - *Initial work* - [YourGitHub](https://github.com/yourusername)

---

## 🙏 Acknowledgments

- Bootstrap Team untuk framework yang awesome
- Font Awesome untuk icon library
- Chart.js untuk data visualization
- Komunitas PHP Indonesia
- Stack Overflow community

---

## 📞 Support

Butuh bantuan? Hubungi kami:

- 📧 Email: support@jobportal.com
- 💬 Discord: [Join our server](https://discord.gg/jobportal)
- 🐛 Issues: [GitHub Issues](https://github.com/yourusername/job-portal/issues)
- 📖 Docs: [Documentation](https://docs.jobportal.com)

---

## ⭐ Show Your Support

Jika project ini membantu kamu, berikan ⭐️ di GitHub!

---

<div align="center">

**Made with ❤️ by JobPortal Team**

[Website](https://jobportal.com) • [Documentation](https://docs.jobportal.com) • [Report Bug](https://github.com/yourusername/job-portal/issues) • [Request Feature](https://github.com/yourusername/job-portal/issues)

</div>
