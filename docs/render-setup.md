# Render Environment Setup (Laravel)

This project includes a `render.yaml` blueprint to deploy as a Docker-based Web Service on Render. Set the environment variables below in the Render service (Settings → Environment) before the first deploy.

## Required Variables
- `APP_ENV=production`
- `APP_DEBUG=false`
- `APP_URL=https://<your-render-service>.onrender.com` (or your custom domain)
- `APP_KEY=<paste a generated key>`

### Database (choose one)
PostgreSQL (Render Managed PostgreSQL):
- `DB_CONNECTION=pgsql`
- `DB_HOST=<render-postgres-host>`
- `DB_PORT=5432`
- `DB_DATABASE=<render-postgres-db>`
- `DB_USERNAME=<render-postgres-user>`
- `DB_PASSWORD=<render-postgres-password>`

MySQL (self-managed or external):
- `DB_CONNECTION=mysql`
- `DB_HOST=<mysql-host>`
- `DB_PORT=3306`
- `DB_DATABASE=<mysql-db>`
- `DB_USERNAME=<mysql-user>`
- `DB_PASSWORD=<mysql-password>`

### Cache / Session / Files
- `CACHE_DRIVER=file` (or `redis` if you add Render Redis)
- `SESSION_DRIVER=file`
- `FILESYSTEM_DISK=public`

### Mail (optional)
- `MAIL_MAILER=smtp`
- `MAIL_HOST=<smtp-host>`
- `MAIL_PORT=<smtp-port>`
- `MAIL_USERNAME=<smtp-user>`
- `MAIL_PASSWORD=<smtp-password>`
- `MAIL_ENCRYPTION=tls` (or `ssl`)
- `MAIL_FROM_ADDRESS=no-reply@your-domain.example`
- `MAIL_FROM_NAME=Supply System`

## Generate `APP_KEY`
You must generate a valid key and paste it into the `APP_KEY` env var.
- After the first build completes, open Render Shell and run:
  - `php artisan key:generate --show` → copy the printed key and set it as `APP_KEY`
- Alternatively, run once locally:
  - `php -r "require 'vendor/autoload.php'; echo 'base64:'.base64_encode(random_bytes(32));"`

## Notes
- The provided `render.yaml` runs database migrations on startup.
- If you use `QUEUE_CONNECTION=database`, ensure migrations include queue tables: `php artisan queue:table && php artisan migrate`.
- For file storage, run once in the container: `php artisan storage:link`.
- After updating environment variables, redeploy or trigger a manual deploy so config cache refreshes.