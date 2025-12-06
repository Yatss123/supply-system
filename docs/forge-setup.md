# Laravel Forge Deployment (Supply System)

This guide covers provisioning a server via Laravel Forge and deploying this repository with a production-ready configuration.

## Recommended Server
- Provider: DigitalOcean (or Hetzner/Linode)
- Size: 1 vCPU / 2GB RAM (upgrade to 2 vCPU / 4GB for higher load)
- Stack: Nginx + PHP-FPM 8.2, MySQL 8, Redis (optional), Supervisor for queues

## Provision with Forge
1. Create Server:
   - Region close to your users.
   - Enable SSH keys, install PHP 8.2.
2. Create Database:
   - Name: `supply_system` (or your choice)
   - User: `forge` (auto-created) with a strong password.
3. Create Site:
   - Domain: your production domain (e.g., `supply.yourdomain.com`)
   - Project Type: "Static/Git" (typical Laravel site).
   - Connect GitHub repo: `Yatss123/supply-system` and branch `main`.
   - Deploy Method: Quick Deploy enabled.
4. SSL:
   - Issue Let's Encrypt certificate once DNS points to the server.

## Environment Variables (Forge → Sites → Environment)
Set the following in the site's environment editor.

### Core
- `APP_NAME=Supply System`
- `APP_ENV=production`
- `APP_DEBUG=false`
- `APP_URL=https://your-domain.example`
- `APP_KEY=<paste a generated key>`

### Database (local server DB)
- `DB_CONNECTION=mysql`
- `DB_HOST=127.0.0.1`
- `DB_PORT=3306`
- `DB_DATABASE=supply_system` (or your chosen DB name)
- `DB_USERNAME=forge` (or your DB user)
- `DB_PASSWORD=<forge-db-password>`

### Cache / Session / Files
- `CACHE_DRIVER=file` (use `redis` if enabled and configure `REDIS_HOST`)
- `SESSION_DRIVER=file`
- `FILESYSTEM_DISK=public`

### Mail (optional)
- `MAIL_MAILER=smtp`
- `MAIL_HOST=<smtp-host>`
- `MAIL_PORT=587`
- `MAIL_USERNAME=<smtp-user>`
- `MAIL_PASSWORD=<smtp-password>`
- `MAIL_ENCRYPTION=tls`
- `MAIL_FROM_ADDRESS=no-reply@your-domain.example`
- `MAIL_FROM_NAME=Supply System`

Note: `.env.production` lines like `DB_USERNAME=${{MYSQL_USER}}` are CI placeholders. In Forge, set real values as above.

## Deploy Script (Forge → Sites → Deployment → Deploy Script)
Paste the script below. Forge runs it inside the site's directory.

```
#!/usr/bin/env bash
set -euo pipefail

# Optional: enter maintenance mode during deploy
php artisan down || true

# Install PHP dependencies
composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev

# Build frontend assets if Node is available on the server
if command -v npm >/dev/null 2>&1; then
  npm ci
  npm run build
fi

# Cache configs and routes
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Ensure storage symlink exists
php artisan storage:link || true

# Run database migrations
php artisan migrate --force

# Restart queue workers (if used)
php artisan queue:restart || true

# Exit maintenance mode
php artisan up || true
```

## Queue Workers
- Enable Supervisor via Forge (Sites → Queue). Add a worker:
  - Command: `php artisan queue:work --sleep=3 --tries=3 --timeout=90`
  - Number of processes: start with 1–2; scale as needed.

## Scheduler (Cron)
- Forge Scheduler: every minute
  - Command: `php /home/forge/your-domain.example/artisan schedule:run >> /dev/null 2>&1`

## First Deploy Checklist
- DNS pointed and SSL issued.
- Environment set and `APP_KEY` pasted (generate locally: `php artisan key:generate --show`).
- Click Deploy or enable Quick Deploy on push.
- Verify: `/health.php` returns 200, home page loads, migrations succeeded.

## Post-Deploy
- Seed initial roles/users if needed:
  - `php artisan db:seed --class=RoleSeeder` (and others in `database/seeders`).
- Monitor logs: `/storage/logs/laravel.log` and Forge server logs.
- Backups: enable provider snapshots and DB backups.

## Notes
- If using S3/Spaces for file uploads, switch:
  - `FILESYSTEM_DISK=s3` and set `AWS_*` env vars; keep `php artisan storage:link`.
- If you prefer zero-downtime assets uploads, build in CI and commit `public/build` or push to a CDN.