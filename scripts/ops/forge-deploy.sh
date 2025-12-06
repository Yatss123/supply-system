#!/usr/bin/env bash
# Forge-ready deploy script you can paste into Forge or run manually
set -euo pipefail

# Optional maintenance mode
php artisan down || true

# PHP dependencies
composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev

# Frontend assets (requires Node/npm installed)
if command -v npm >/dev/null 2>&1; then
  npm ci
  npm run build
fi

# Cache and optimize
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Storage symlink
php artisan storage:link || true

# Migrations
php artisan migrate --force

# Restart queue workers
php artisan queue:restart || true

# Exit maintenance
php artisan up || true