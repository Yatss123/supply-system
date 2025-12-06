# Hostinger Deployment (Laravel)

This guide prepares and deploys this project to Hostinger Shared Hosting (hPanel) with SSH.

## Prerequisites
- SSH enabled on Hostinger (hPanel → Advanced → SSH Access)
- PHP 8.1+ available
- Composer available on server (Hostinger provides it)
- Your repo is deployed on the server or you can upload a ZIP

## Folder Layout Options
- Preferred: Keep the Laravel project as a sibling of `public_html`, and put only `public` contents into `public_html`.
- Alternative: Put the whole project inside `public_html` (not recommended on shared hosting).

This repo includes `public/index.hostinger.php` which can be placed in `public_html/index.php` and will try two layouts:
- `../vendor` and `../bootstrap` (project root is parent of `public_html`)
- `../supply_system/vendor` and `../supply_system/bootstrap` (project root is a sibling folder named `supply_system`)

## Steps (SSH)

1) Navigate to the repo folder
```
cd ~/<your-repo-folder>
```

2) Copy `public` assets to `public_html`
- Option A (ZIP provided): Upload `dist/public_html.zip` to server and extract into `~/public_html`.
- Option B (manual): Copy `.htaccess`, `health.php`, `simple-health.php`, and `public/build` to `~/public_html`.
- Place `public/index.hostinger.php` as `~/public_html/index.php`.

3) Environment setup
```
cp .env.example .env
nano .env
```
Set at minimum:
```
APP_ENV=production
APP_DEBUG=false
APP_URL=https://<your-domain>

DB_CONNECTION=mysql
DB_HOST=<db-host>
DB_PORT=3306
DB_DATABASE=<db-name>
DB_USERNAME=<db-user>
DB_PASSWORD=<db-pass>
```

4) Install dependencies and initialize
```
composer install --no-dev --optimize-autoloader
php artisan key:generate
php artisan storage:link
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

5) Cron jobs (hPanel → Advanced → Cron Jobs)
- Laravel scheduler (every minute):
```
*/1 * * * * /usr/bin/php ~/<your-repo-folder>/artisan schedule:run >> /dev/null 2>&1
```
- Optional: queues, if used. Prefer Supervisor on VPS; on shared hosting use a long-running process only when necessary.

6) SSL & domain
- Add domain in hPanel, point DNS to Hostinger
- Enable SSL (hPanel → SSL) and force HTTPS via `.htaccess` if needed

## Troubleshooting
- White screen: check `storage/logs/laravel.log`
- 500 error: confirm `vendor/autoload.php` and `bootstrap/app.php` paths resolved by `index.hostinger.php`
- Permissions: ensure `storage` and `bootstrap/cache` writable
- Node build: build locally via `npm run build` and upload `public/build`

## Quick Verification
- Load `/health.php` and `/simple-health.php` on your domain
- Load `/` and ensure static assets from `public/build` are served
- Run `php artisan tinker` to confirm app boots on server