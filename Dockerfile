FROM php:8.0-apache

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    zip \
    unzip \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mysqli mbstring exif pcntl bcmath gd zip

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Set working directory
WORKDIR /var/www/html

# Copy application files
COPY . /var/www/html/

# Create upload directories
RUN mkdir -p /var/www/html/assets/uploads/logos \
    && mkdir -p /var/www/html/assets/uploads/cv \
    && mkdir -p /var/www/html/assets/uploads/documents \
    && mkdir -p /var/www/html/assets/uploads/avatars

# Set permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/assets/uploads

# Expose port 80
EXPOSE 80