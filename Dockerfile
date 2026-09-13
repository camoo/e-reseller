FROM php:8.4-cli-alpine

# Install system dependencies and PHP extensions
RUN apk add --no-cache \
    bash \
    git \
    unzip \
    icu-dev \
    libzip-dev \
    libpng-dev \
    oniguruma-dev \
    libxml2-dev \
    && docker-php-ext-install \
    intl \
    pdo_mysql \
    zip \
    bcmath

# Copy Composer binary from official image
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /app

# Default command: start PHP built-in web server
CMD ["php", "-S", "0.0.0.0:8000", "-t", "web"]
