#!/bin/bash

echo "🚀 Preparing Supply Management System for Deployment..."

# Check if .env.production exists
if [ ! -f .env.production ]; then
    echo "❌ .env.production file not found!"
    echo "Please copy .env.production template and configure your environment variables."
    exit 1
fi

# Install production dependencies
echo "📦 Installing production dependencies..."
composer install --optimize-autoloader --no-dev --no-interaction

# Generate application key if not set
echo "🔑 Generating application key..."
php artisan key:generate --env=production --force

# Clear and cache configurations
echo "🧹 Clearing and caching configurations..."
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

# Cache configurations for production
echo "⚡ Caching configurations for production..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Build frontend assets
echo "🎨 Building frontend assets..."
npm install
npm run build

# Set proper permissions
echo "🔒 Setting proper permissions..."
chmod -R 755 storage
chmod -R 755 bootstrap/cache

# Create storage link
echo "🔗 Creating storage link..."
php artisan storage:link

echo "✅ Deployment preparation completed!"
echo ""
echo "Next steps:"
echo "1. Upload your files to your hosting provider"
echo "2. Configure your database connection in production"
echo "3. Run: php artisan migrate --force"
echo "4. Run: php artisan db:seed --force"
echo ""
echo "🎉 Your Supply Management System is ready for deployment!"