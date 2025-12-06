# 🚂 Railway Deployment Steps for Supply Management System

## ✅ Step 1: Code Preparation (COMPLETED)
- [x] Updated `.env.production` with Railway variables
- [x] Generated secure `APP_KEY`
- [x] Created `nixpacks.toml` for optimized builds
- [x] Updated `railway.json` configuration

## 🚀 Step 2: Create Railway Project

### 2.1 Sign Up & Connect GitHub
1. Go to [railway.app](https://railway.app)
2. Click "Login" and sign up with GitHub
3. Authorize Railway to access your repositories

### 2.2 Create New Project
1. Click "New Project"
2. Select "Deploy from GitHub repo"
3. Choose your supply management system repository
4. Railway will automatically detect it's a PHP/Laravel project

## 🗄️ Step 3: Add MySQL Database

### 3.1 Add Database Service
1. In your Railway project dashboard
2. Click "New" → "Database" → "MySQL"
3. Railway will automatically provision a MySQL database
4. Database connection variables will be auto-generated

### 3.2 Verify Database Variables
Railway automatically creates these variables:
- `MYSQL_HOST`
- `MYSQL_PORT` 
- `MYSQL_DATABASE`
- `MYSQL_USER`
- `MYSQL_PASSWORD`

## ⚙️ Step 4: Configure Environment Variables

### 4.1 Access Variables Tab
1. Click on your web service (not the database)
2. Go to "Variables" tab
3. Add the following environment variables:

### 4.2 Required Environment Variables
```
APP_NAME=Supply Management System
APP_ENV=production
APP_KEY=base64:wLproxfbLkeSJi5OpV6lnVVP7CUKyY6ON5OcETbEyC4=
APP_DEBUG=false
APP_URL=${{RAILWAY_STATIC_URL}}

LOG_CHANNEL=stack
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=${{MYSQL_HOST}}
DB_PORT=${{MYSQL_PORT}}
DB_DATABASE=${{MYSQL_DATABASE}}
DB_USERNAME=${{MYSQL_USER}}
DB_PASSWORD=${{MYSQL_PASSWORD}}

CACHE_DRIVER=file
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120
SESSION_SECURE_COOKIE=true
SESSION_SAME_SITE=strict

MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yourdomain.com
MAIL_FROM_NAME=Supply Management System
```

### 4.3 Optional: Email Configuration
If you want email notifications to work:
1. Use Gmail SMTP settings shown above
2. Generate an "App Password" in your Google Account
3. Replace `your-email@gmail.com` and `your-app-password`

## 🚀 Step 5: Deploy Application

### 5.1 Automatic Deployment
1. Railway automatically deploys when you push to GitHub
2. Or click "Deploy" in the Railway dashboard
3. Monitor the build logs in the "Deployments" tab

### 5.2 Build Process
Railway will automatically:
- Install PHP dependencies with Composer
- Install Node.js dependencies
- Build frontend assets with Vite
- Cache Laravel configurations

## 🗄️ Step 6: Run Database Migrations

### 6.1 Access Railway CLI (Option 1)
```bash
# Install Railway CLI
npm install -g @railway/cli

# Login to Railway
railway login

# Link to your project
railway link

# Run migrations
railway run php artisan migrate --force

# Seed database
railway run php artisan db:seed --force
```

### 6.2 Use Railway Dashboard (Option 2)
1. Go to your project dashboard
2. Click on your web service
3. Go to "Settings" → "Service Settings"
4. Add these as "Deploy Commands":
   ```
   php artisan migrate --force
   php artisan db:seed --force
   ```

## 🔗 Step 7: Get Your Live URL

### 7.1 Find Your URL
1. In Railway dashboard, click on your web service
2. Go to "Settings" → "Networking"
3. Click "Generate Domain"
4. Your app will be available at: `https://your-app-name.railway.app`

### 7.2 Custom Domain (Optional)
1. In "Networking" settings
2. Click "Custom Domain"
3. Add your own domain name
4. Configure DNS records as shown

## ✅ Step 8: Verify Deployment

### 8.1 Test Basic Functionality
Visit your Railway URL and test:
- [x] Application loads without errors
- [x] Login/Register pages work
- [x] Database connection successful
- [x] QR code generation works

### 8.2 Test Key Features
- [x] User registration and login
- [x] Supply management interface
- [x] QR code scanning functionality
- [x] Loan request system
- [x] Admin approval workflows
- [x] Mobile responsiveness

## 🔧 Step 9: Post-Deployment Configuration

### 9.1 Create Admin User
Use Railway CLI or dashboard console:
```bash
railway run php artisan tinker
>>> $user = App\Models\User::create(['name' => 'Admin', 'email' => 'admin@example.com', 'password' => bcrypt('password'), 'department_id' => 1]);
>>> $adminRole = App\Models\Role::where('name', 'super_admin')->first();
>>> $user->role_id = $adminRole->id;
>>> $user->save();
```

### 9.2 Test QR Functionality
1. Navigate to supplies management
2. Generate QR codes for supplies
3. Test QR scanning on mobile devices
4. Verify loan request workflows

## 🚨 Troubleshooting

### Common Issues & Solutions

#### 1. Build Failures
- Check build logs in Railway dashboard
- Ensure all dependencies are in `composer.json`
- Verify Node.js version compatibility

#### 2. Database Connection Issues
- Verify MySQL service is running
- Check environment variables are set correctly
- Ensure database migrations completed

#### 3. 500 Internal Server Error
- Check application logs in Railway dashboard
- Verify `APP_KEY` is set
- Ensure file permissions are correct

#### 4. QR Codes Not Working
- Verify `simplesoftwareio/simple-qrcode` package installed
- Check if GD extension is available
- Test QR generation in local environment first

## 📞 Support Resources

- **Railway Docs**: [docs.railway.app](https://docs.railway.app)
- **Laravel Deployment**: [laravel.com/docs/deployment](https://laravel.com/docs/deployment)
- **Railway Discord**: [discord.gg/railway](https://discord.gg/railway)

---

## 🎉 Congratulations!

Your Supply Management System is now live on Railway! 

**Live URL**: `https://your-app-name.railway.app`

The system includes:
- ✅ QR code generation and scanning
- ✅ Supply inventory management
- ✅ Loan request workflows
- ✅ Multi-department support
- ✅ Role-based access control
- ✅ Mobile-responsive design
- ✅ Admin approval system

**Next Steps:**
1. Share the URL with your team for testing
2. Create additional admin/user accounts
3. Import your supply inventory
4. Train users on the QR workflow
5. Monitor usage and performance