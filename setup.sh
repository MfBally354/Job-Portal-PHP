#!/bin/bash

echo "📁 Membuat folder internal..."

# Assets
mkdir -p assets/uploads/{logos,cv,documents,avatars}

# Folder utama lain
mkdir -p config includes admin employer jobseeker auth public

echo "📄 Membuat file..."

# Config
touch config/{database.php,functions.php,constants.php}

# Includes
touch includes/{header.php,footer.php,navbar.php}

# Admin
touch admin/{dashboard.php,users.php,companies.php,jobs.php}

# Employer
touch employer/{dashboard.php,profile.php,post_job.php,my_jobs.php}

# Jobseeker
touch jobseeker/{dashboard.php,profile.php,browse_jobs.php,my_applications.php}

# Auth
touch auth/{login.php,register.php,logout.php}

# Public
touch public/{jobs.php,job_detail.php,companies.php,contact.php}

# Root
touch index.php database_complete.sql

echo "✅ Struktur internal repo berhasil dibuat"

