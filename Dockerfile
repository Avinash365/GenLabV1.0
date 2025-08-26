FROM php:8.2-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    locales \
    zip \
    unzip \
    vim \
    jpegoptim \
    optipng \
    pngquant \
    gifsicle \
    libonig-dev \
    libzip-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:2.6 /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy project files
COPY . .

# Create user (same UID/GID as local user to avoid permission issues)
RUN groupadd -g 1000 avinash && \
    useradd -u 1000 -ms /bin/bash -g avinash avinash

# Set permissions for Laravel
RUN chown -R avinash:avinash /var/www/html && \
    chmod -R 755 /var/www/html/storage /var/www/html/bootstrap/cache

# Switch to the new user
USER avinash

EXPOSE 9000
CMD ["php-fpm"]
