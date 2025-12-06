#!/bin/bash

echo "=== Railway Deployment Debug Log ==="
echo "Timestamp: $(date)"
echo "Working Directory: $(pwd)"
echo "User: $(whoami)"

# Environment Variables Check
echo ""
echo "=== Environment Variables ==="
echo "PORT: ${PORT:-'NOT SET'}"
echo "APP_ENV: ${APP_ENV:-'NOT SET'}"
echo "APP_DEBUG: ${APP_DEBUG:-'NOT SET'}"
echo "APP_KEY: ${APP_KEY:0:10}... (truncated for security)"
echo "DATABASE_URL: ${DATABASE_URL:0:20}... (truncated for security)"
echo "PWD: $PWD"
echo "PHP Version: $(php --version | head -n 1)"

# Set environment variables with defaults
export APP_ENV=${APP_ENV:-production}
export APP_DEBUG=${APP_DEBUG:-false}

echo ""
echo "=== Setting up Laravel Application ==="

# Generate application key if not exists
echo "Checking APP_KEY..."
if [ -z "$APP_KEY" ]; then
    echo "Generating new APP_KEY..."
    php artisan key:generate --force --no-interaction
    if [ $? -eq 0 ]; then
        echo "✓ APP_KEY generated successfully"
    else
        echo "✗ Failed to generate APP_KEY"
        exit 1
    fi
else
    echo "✓ APP_KEY already exists"
fi

# Create storage directories with proper permissions
echo ""
echo "Setting up storage directories..."
mkdir -p storage/logs storage/framework/cache storage/framework/sessions storage/framework/views storage/app/public
chmod -R 775 storage bootstrap/cache
if [ $? -eq 0 ]; then
    echo "✓ Storage directories created and permissions set"
else
    echo "✗ Failed to set up storage directories"
fi

# Create storage link
echo ""
echo "Creating storage link..."
php artisan storage:link --force
if [ $? -eq 0 ]; then
    echo "✓ Storage link created successfully"
else
    echo "✗ Failed to create storage link"
fi

# Wait for database with retry mechanism
echo ""
echo "Waiting for database connection..."
max_attempts=30
attempt=1
while [ $attempt -le $max_attempts ]; do
    echo "Database connection attempt $attempt/$max_attempts..."
    php artisan migrate:status --no-interaction > /dev/null 2>&1
    if [ $? -eq 0 ]; then
        echo "✓ Database connection successful"
        break
    else
        echo "Database not ready, waiting 2 seconds..."
        sleep 2
        attempt=$((attempt + 1))
    fi
done

if [ $attempt -gt $max_attempts ]; then
    echo "✗ Failed to connect to database after $max_attempts attempts"
    echo "Attempting to show database connection error:"
    php artisan migrate:status --no-interaction
    exit 1
fi

# Run migrations
echo ""
echo "Running database migrations..."
php artisan migrate --force --no-interaction
if [ $? -eq 0 ]; then
    echo "✓ Migrations completed successfully"
else
    echo "✗ Migrations failed"
    exit 1
fi

# Cache configuration only in production
if [ "$APP_ENV" = "production" ]; then
    echo ""
    echo "Caching configuration for production..."
    php artisan config:cache --no-interaction
    if [ $? -eq 0 ]; then
        echo "✓ Configuration cached successfully"
    else
        echo "✗ Failed to cache configuration"
    fi
    
    php artisan route:cache --no-interaction
    if [ $? -eq 0 ]; then
        echo "✓ Routes cached successfully"
    else
        echo "✗ Failed to cache routes"
    fi
else
    echo ""
    echo "Clearing configuration cache for non-production..."
    php artisan config:clear --no-interaction
    php artisan route:clear --no-interaction
    echo "✓ Configuration cache cleared"
fi

# Seed essential data
echo ""
echo "Seeding essential data..."
php artisan db:seed --class=SuperAdminSeeder --force --no-interaction
if [ $? -eq 0 ]; then
    echo "✓ Essential data seeded successfully"
else
    echo "✗ Failed to seed essential data (this might be normal if data already exists)"
fi

# Check health check files
echo ""
echo "=== Health Check Files Status ==="
echo "ℹ Using physical health.php file for health checks"
if [ -f "public/health.php" ]; then
    echo "✓ Physical health.php file exists"
else
    echo "✗ Physical health.php file missing"
fi

if [ -f "public/simple-health.php" ]; then
    echo "✓ simple-health.php exists (backup)"
else
    echo "ℹ simple-health.php not found"
fi

# Test health endpoint locally
echo ""
echo "=== Testing Health Endpoint Locally ==="
echo "Testing Laravel health route..."
# Start PHP server in background for testing
php -S localhost:8080 -t public > /dev/null 2>&1 &
SERVER_PID=$!
sleep 2

# Test the health endpoint
HEALTH_RESPONSE=$(curl -s http://localhost:8080/health.php 2>/dev/null || echo "ERROR")
if [[ "$HEALTH_RESPONSE" == *"status"* ]]; then
    echo "✓ Health endpoint responding correctly"
    echo "Response: $HEALTH_RESPONSE"
else
    echo "✗ Health endpoint not responding properly"
    echo "Response: $HEALTH_RESPONSE"
fi

# Clean up test server
kill $SERVER_PID 2>/dev/null || true

# Final status
echo ""
echo "=== Deployment Setup Complete ==="
echo "Application should be accessible at: http://0.0.0.0:${PORT:-8000}"
echo "Health check endpoint: http://0.0.0.0:${PORT:-8000}/health.php"
echo "Current time: $(date)"
echo "Ready to start PHP server..."
echo "================================="