# Use PHP 8.1 with Apache
FROM php:8.1-apache

# Set working directory
WORKDIR /var/www/html

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libzip-dev \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    libgmp-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip gmp

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy composer files first
COPY composer.json composer.lock ./

# Clear composer cache and install dependencies with specific version constraints
RUN composer clear-cache && \
    composer config --no-plugins allow-plugins.kylekatarnls/update-helper true && \
    composer install --no-dev --optimize-autoloader --no-interaction --ignore-platform-reqs --no-scripts --prefer-dist

# Copy application code
COPY . .

# Create required directories and set permissions
RUN mkdir -p /var/www/html/storage/app/public \
    && mkdir -p /var/www/html/storage/framework/cache \
    && mkdir -p /var/www/html/storage/framework/sessions \
    && mkdir -p /var/www/html/storage/framework/views \
    && mkdir -p /var/www/html/storage/logs \
    && mkdir -p /var/www/html/bootstrap/cache \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/storage \
    && chmod -R 755 /var/www/html/bootstrap/cache

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Configure Apache
RUN echo '<VirtualHost *:8000>\n\
    DocumentRoot /var/www/html/public\n\
    <Directory /var/www/html/public>\n\
        AllowOverride All\n\
        Require all granted\n\
    </Directory>\n\
</VirtualHost>' > /etc/apache2/sites-available/000-default.conf

# Set ServerName to suppress Apache warning
RUN echo 'ServerName localhost' >> /etc/apache2/apache2.conf

# Change Apache port to 8000
RUN sed -i 's/Listen 80/Listen 8000/' /etc/apache2/ports.conf

# Expose port 8000
EXPOSE 8000

# Create startup script
RUN echo '#!/bin/bash\n\
set -e\n\
\n\
echo "Starting Laravel application setup..."\n\
\n\
# Set default database environment variables if not provided\n\
export DB_HOST=${DB_HOST:-mysql}\n\
export DB_PORT=${DB_PORT:-3306}\n\
export DB_DATABASE=${DB_DATABASE:-railway}\n\
export DB_USERNAME=${DB_USERNAME:-root}\n\
export DB_PASSWORD=${DB_PASSWORD:-}\n\
\n\
# Clear DATABASE_URL to avoid conflicts with individual DB variables\n\
unset DATABASE_URL\n\
\n\
# Force Laravel to use individual DB variables by explicitly setting default connection\n\
export DB_CONNECTION=${DB_CONNECTION:-mysql}\n\
\n\
# Set Laravel environment variables\n\
export APP_ENV=${APP_ENV:-production}\n\
export APP_DEBUG=${APP_DEBUG:-false}\n\
export APP_URL=${APP_URL:-https://supply-system-production.up.railway.app}\n\
export APP_KEY=${APP_KEY:-base64:$(openssl rand -base64 32)}\n\
\n\
echo "Application configuration:"\n\
echo "  Environment: $APP_ENV"\n\
echo "  Debug: $APP_DEBUG"\n\
echo "  URL: $APP_URL"\n\
echo "  Key: [HIDDEN]"\n\
\n\
echo "Database configuration:"\n\
echo "  Host: $DB_HOST"\n\
echo "  Port: $DB_PORT"\n\
echo "  Database: $DB_DATABASE"\n\
echo "  Username: $DB_USERNAME"\n\
\n\
# Wait for database to be ready with retry logic\n\
echo "Waiting for database connection..."\n\
for i in {1..30}; do\n\
    if timeout 10 php -r "new PDO(\"mysql:host=$DB_HOST;port=$DB_PORT;dbname=$DB_DATABASE\", \"$DB_USERNAME\", \"$DB_PASSWORD\");" 2>/dev/null; then\n\
        echo "Database connection established!"\n\
        break\n\
    else\n\
        echo "Attempt $i: Database not ready, waiting 3 seconds..."\n\
        sleep 3\n\
    fi\n\
    if [ $i -eq 30 ]; then\n\
        echo "ERROR: Database connection failed after 30 attempts"\n\
        echo "Please check Railway database service is running and environment variables are set"\n\
        exit 1\n\
    fi\n\
done\n\
\n\
# Clear Laravel configuration cache to ensure fresh config
echo "Clearing Laravel configuration cache..."\n\
# Ensure proper ownership of cache directories\n\
chown -R www-data:www-data /var/www/html/bootstrap/cache\n\
chown -R www-data:www-data /var/www/html/storage\n\
chmod -R 755 /var/www/html/bootstrap/cache\n\
chmod -R 755 /var/www/html/storage\n\
# Remove cached config files manually if artisan commands fail\n\
rm -f /var/www/html/bootstrap/cache/config.php\n\
rm -f /var/www/html/bootstrap/cache/services.php\n\
rm -f /var/www/html/bootstrap/cache/packages.php\n\
rm -rf /var/www/html/storage/framework/cache/data/*\n\
# Clear Laravel caches\n\
php artisan config:clear || echo "Config clear failed, but cache files removed manually"\n\
php artisan cache:clear || echo "Cache clear failed, but cache files removed manually"\n\
php artisan view:clear || echo "View clear failed"\n\
\n\
# Run migrations\n\
echo "Running database migrations..."\n\
php artisan migrate --force\n\
if [ $? -eq 0 ]; then\n\
    echo "✓ Database migrations completed successfully"\n\
else\n\
    echo "✗ Database migrations failed"\n\
    exit 1\n\
fi\n\
\n\
# Run seeders for initial data\n\
echo "Running database seeders..."\n\
php artisan db:seed --class=RoleSeeder --force\n\
if [ $? -eq 0 ]; then\n\
    echo "✓ RoleSeeder completed successfully"\n\
else\n\
    echo "✗ RoleSeeder failed"\n\
fi\n\
\n\
php artisan db:seed --class=SuperAdminSeeder --force\n\
if [ $? -eq 0 ]; then\n\
    echo "✓ SuperAdminSeeder completed successfully"\n\
    echo "✓ Super admin login: admin@example.com / password"\n\
else\n\
    echo "✗ SuperAdminSeeder failed"\n\
fi\n\
\n\
echo "✓ Laravel application setup completed!"\n\
echo "Starting Apache web server..."\n\
\n\
# Start Apache\n\
exec apache2-foreground' > /usr/local/bin/start.sh

# Make startup script executable
RUN chmod +x /usr/local/bin/start.sh

# Start with our custom script
CMD ["/usr/local/bin/start.sh"]