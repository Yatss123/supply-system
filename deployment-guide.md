# 🚀 Supply Management System - Deployment Guide

## 📋 Pre-Deployment Checklist

### 1. Environment Configuration
- [ ] Create production `.env` file
- [ ] Set `APP_ENV=production`
- [ ] Set `APP_DEBUG=false`
- [ ] Generate secure `APP_KEY`
- [ ] Configure database credentials
- [ ] Set up mail configuration

### 2. Security & Performance
- [ ] Update `APP_URL` to your domain
- [ ] Configure HTTPS settings
- [ ] Set secure session settings
- [ ] Optimize for production

### 3. Dependencies & Build
- [ ] Run `composer install --optimize-autoloader --no-dev`
- [ ] Run `npm run build` for assets
- [ ] Clear and cache configurations

## 🌟 Option 1: Railway Deployment (Recommended for Testing)

### Why Railway?
- ✅ Free tier perfect for testing
- ✅ Auto-deployment from GitHub
- ✅ Built-in database support
- ✅ Zero configuration needed
- ✅ Automatic HTTPS

### Step-by-Step Railway Deployment

#### 1. Prepare Your Code
```bash
# Create production environment file
cp .env.example .env.production

# Install dependencies
composer install --optimize-autoloader --no-dev

# Build assets
npm run build
```

#### 2. Create Railway Project
1. Go to [railway.app](https://railway.app)
2. Sign up with GitHub
3. Click "New Project"
4. Select "Deploy from GitHub repo"
5. Choose your repository

#### 3. Configure Environment Variables
In Railway dashboard, add these environment variables:
```
APP_NAME=Supply Management System
APP_ENV=production
APP_DEBUG=false
APP_KEY=base64:YOUR_GENERATED_KEY
APP_URL=https://your-app.railway.app

DB_CONNECTION=mysql
DB_HOST=${{MYSQL_HOST}}
DB_PORT=${{MYSQL_PORT}}
DB_DATABASE=${{MYSQL_DATABASE}}
DB_USERNAME=${{MYSQL_USER}}
DB_PASSWORD=${{MYSQL_PASSWORD}}

MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your-email@gmail.com
MAIL_FROM_NAME="Supply Management System"
```

#### 4. Add MySQL Database
1. In Railway dashboard, click "New"
2. Select "Database" → "MySQL"
3. Railway will automatically connect it to your app

#### 5. Deploy
Railway will automatically deploy when you push to GitHub!

## 💎 Option 2: Heroku Deployment

### Step-by-Step Heroku Deployment

#### 1. Install Heroku CLI
Download from [heroku.com/cli](https://devcenter.heroku.com/articles/heroku-cli)

#### 2. Prepare Your App
```bash
# Login to Heroku
heroku login

# Create Heroku app
heroku create your-supply-system

# Add MySQL addon
heroku addons:create jawsdb:kitefin

# Set environment variables
heroku config:set APP_NAME="Supply Management System"
heroku config:set APP_ENV=production
heroku config:set APP_DEBUG=false
heroku config:set APP_KEY=$(php artisan key:generate --show)
```

#### 3. Create Procfile
Create `Procfile` in root directory:
```
web: vendor/bin/heroku-php-apache2 public/
```

#### 4. Deploy
```bash
git add .
git commit -m "Deploy to Heroku"
git push heroku main

# Run migrations
heroku run php artisan migrate --force
heroku run php artisan db:seed --force
```

## 🔧 Option 3: DigitalOcean App Platform

### Step-by-Step DigitalOcean Deployment

#### 1. Create App
1. Go to [cloud.digitalocean.com](https://cloud.digitalocean.com)
2. Click "Create" → "Apps"
3. Connect your GitHub repository

#### 2. Configure Build Settings
```yaml
# .do/app.yaml
name: supply-management-system
services:
- name: web
  source_dir: /
  github:
    repo: your-username/your-repo
    branch: main
  run_command: heroku-php-apache2 public/
  environment_slug: php
  instance_count: 1
  instance_size_slug: basic-xxs
  
databases:
- name: db
  engine: MYSQL
  version: "8"
```

#### 3. Set Environment Variables
Add the same environment variables as Railway example.

## 🛠️ Post-Deployment Configuration

### 1. Run Database Migrations
```bash
# Railway
railway run php artisan migrate --force

# Heroku
heroku run php artisan migrate --force

# DigitalOcean (via console)
php artisan migrate --force
```

### 2. Seed Database
```bash
# Run seeders
php artisan db:seed --force

# Or specific seeders
php artisan db:seed --class=RoleSeeder --force
php artisan db:seed --class=SuperAdminSeeder --force
php artisan db:seed --class=TestSupplySeeder --force
```

### 3. Configure Storage
```bash
# Link storage
php artisan storage:link

# Clear and cache configs
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## 🔒 Security Considerations

### 1. Environment Variables
- Never commit `.env` files
- Use strong, unique `APP_KEY`
- Use secure database passwords
- Configure proper CORS settings

### 2. HTTPS Configuration
Most platforms provide automatic HTTPS, but ensure:
- `APP_URL` uses `https://`
- Force HTTPS in production
- Secure cookie settings

### 3. Database Security
- Use strong database passwords
- Limit database access
- Regular backups
- Monitor for suspicious activity

## 📊 Testing Your Deployment

### 1. Basic Functionality Test
- [ ] Application loads without errors
- [ ] User registration/login works
- [ ] Database connections successful
- [ ] QR code generation works
- [ ] File uploads work (if applicable)

### 2. Performance Testing
- [ ] Page load times acceptable
- [ ] Database queries optimized
- [ ] Asset loading efficient
- [ ] Mobile responsiveness

### 3. Security Testing
- [ ] HTTPS working
- [ ] Authentication secure
- [ ] No sensitive data exposed
- [ ] CSRF protection active

## 🚨 Troubleshooting Common Issues

### 1. "Application Key Not Set"
```bash
php artisan key:generate
```

### 2. Database Connection Issues
- Check environment variables
- Verify database credentials
- Ensure database server is running

### 3. Asset Loading Problems
```bash
npm run build
php artisan config:cache
```

### 4. Permission Issues
```bash
chmod -R 755 storage
chmod -R 755 bootstrap/cache
```

## 📞 Support & Resources

- **Laravel Deployment Docs**: [laravel.com/docs/deployment](https://laravel.com/docs/deployment)
- **Railway Docs**: [docs.railway.app](https://docs.railway.app)
- **Heroku PHP Docs**: [devcenter.heroku.com/categories/php](https://devcenter.heroku.com/categories/php)
- **DigitalOcean Docs**: [docs.digitalocean.com/products/app-platform](https://docs.digitalocean.com/products/app-platform)

---

**🎉 Your Supply Management System is now ready for online testing!**