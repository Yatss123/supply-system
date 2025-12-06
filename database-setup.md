# 🗄️ Database Setup for Production Deployment

## 📋 Database Configuration Guide

### 1. Platform-Specific Database Setup

#### 🚂 Railway Database Setup
Railway provides automatic MySQL database provisioning:

1. **Add MySQL Service**
   - In Railway dashboard, click "New"
   - Select "Database" → "MySQL"
   - Railway automatically creates connection variables

2. **Environment Variables (Auto-generated)**
   ```
   MYSQL_HOST=containers-us-west-xxx.railway.app
   MYSQL_PORT=6543
   MYSQL_DATABASE=railway
   MYSQL_USER=root
   MYSQL_PASSWORD=auto-generated-password
   ```

3. **Update Your .env**
   ```
   DB_CONNECTION=mysql
   DB_HOST=${{MYSQL_HOST}}
   DB_PORT=${{MYSQL_PORT}}
   DB_DATABASE=${{MYSQL_DATABASE}}
   DB_USERNAME=${{MYSQL_USER}}
   DB_PASSWORD=${{MYSQL_PASSWORD}}
   ```

#### 🟣 Heroku Database Setup
Heroku uses JawsDB MySQL addon:

1. **Add JawsDB Addon**
   ```bash
   heroku addons:create jawsdb:kitefin
   ```

2. **Get Database URL**
   ```bash
   heroku config:get JAWSDB_URL
   # Returns: mysql://username:password@hostname:port/database_name
   ```

3. **Configure Environment**
   Heroku automatically sets `DATABASE_URL`, but you can also set individual variables:
   ```bash
   heroku config:set DB_CONNECTION=mysql
   heroku config:set DB_HOST=your-jawsdb-host
   heroku config:set DB_PORT=3306
   heroku config:set DB_DATABASE=your-jawsdb-database
   heroku config:set DB_USERNAME=your-jawsdb-username
   heroku config:set DB_PASSWORD=your-jawsdb-password
   ```

#### 🌊 DigitalOcean Database Setup
DigitalOcean App Platform provides managed databases:

1. **Add Database Component**
   - In app settings, add "Database"
   - Choose MySQL 8.0
   - Select appropriate size

2. **Environment Variables (Auto-injected)**
   ```
   DB_HOST=${db.HOSTNAME}
   DB_PORT=${db.PORT}
   DB_DATABASE=${db.DATABASE}
   DB_USERNAME=${db.USERNAME}
   DB_PASSWORD=${db.PASSWORD}
   ```

### 2. Database Migration Commands

#### 🔄 Initial Migration
```bash
# Run all migrations
php artisan migrate --force

# Check migration status
php artisan migrate:status
```

#### 🌱 Database Seeding
```bash
# Run all seeders
php artisan db:seed --force

# Run specific seeders
php artisan db:seed --class=RoleSeeder --force
php artisan db:seed --class=SuperAdminSeeder --force
php artisan db:seed --class=TestSupplySeeder --force
```

#### 🔄 Fresh Migration (if needed)
```bash
# ⚠️ WARNING: This will drop all tables and recreate them
php artisan migrate:fresh --seed --force
```

### 3. Database Connection Testing

#### 🧪 Test Database Connection
Create a simple test script to verify database connectivity:

```php
<?php
// test-db-connection.php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    DB::connection()->getPdo();
    echo "✅ Database connection successful!\n";
    
    // Test basic query
    $users = DB::table('users')->count();
    echo "👥 Users in database: {$users}\n";
    
    $supplies = DB::table('supplies')->count();
    echo "📦 Supplies in database: {$supplies}\n";
    
} catch (Exception $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n";
}
?>
```

#### 🏃‍♂️ Run Database Test
```bash
php test-db-connection.php
```

### 4. Production Database Optimization

#### 📊 Database Indexing
Ensure proper indexes are in place for performance:

```sql
-- Add indexes for frequently queried columns
CREATE INDEX idx_supplies_status ON supplies(status);
CREATE INDEX idx_supplies_department_id ON supplies(department_id);
CREATE INDEX idx_loan_requests_status ON loan_requests(status);
CREATE INDEX idx_borrowed_items_status ON borrowed_items(status);
CREATE INDEX idx_users_department_id ON users(department_id);
```

#### ⚡ Query Optimization
```bash
# Enable query logging to identify slow queries
php artisan tinker
>>> DB::enableQueryLog();
>>> // Perform some operations
>>> DB::getQueryLog();
```

#### 🗄️ Database Backup Strategy
```bash
# Create database backup
mysqldump -h hostname -u username -p database_name > backup.sql

# Restore from backup
mysql -h hostname -u username -p database_name < backup.sql
```

### 5. Environment-Specific Configurations

#### 🔒 Production Security Settings
```env
# In .env.production
DB_CONNECTION=mysql
DB_HOST=your-production-host
DB_PORT=3306
DB_DATABASE=supply_system_prod
DB_USERNAME=supply_user
DB_PASSWORD=secure-random-password

# Connection pool settings
DB_POOL_MIN=2
DB_POOL_MAX=10
DB_TIMEOUT=60
```

#### 🧪 Staging Environment
```env
# In .env.staging
DB_CONNECTION=mysql
DB_HOST=your-staging-host
DB_PORT=3306
DB_DATABASE=supply_system_staging
DB_USERNAME=supply_staging_user
DB_PASSWORD=staging-password
```

### 6. Common Database Issues & Solutions

#### ❌ "Connection Refused" Error
**Causes:**
- Incorrect host/port
- Database server not running
- Firewall blocking connection

**Solutions:**
```bash
# Test connection manually
mysql -h hostname -P port -u username -p

# Check if port is open
telnet hostname port
```

#### ❌ "Access Denied" Error
**Causes:**
- Wrong username/password
- User doesn't have proper permissions

**Solutions:**
```sql
-- Grant proper permissions
GRANT ALL PRIVILEGES ON database_name.* TO 'username'@'%';
FLUSH PRIVILEGES;
```

#### ❌ "Database Does Not Exist" Error
**Solutions:**
```sql
-- Create database
CREATE DATABASE supply_system_prod CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

#### ❌ Migration Timeout
**Solutions:**
```bash
# Increase timeout in config/database.php
'mysql' => [
    'options' => [
        PDO::ATTR_TIMEOUT => 120,
    ],
],
```

### 7. Database Monitoring

#### 📊 Performance Monitoring
```bash
# Check database size
SELECT 
    table_schema AS 'Database',
    ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS 'Size (MB)'
FROM information_schema.tables 
WHERE table_schema = 'your_database_name';

# Check table sizes
SELECT 
    table_name AS 'Table',
    ROUND(((data_length + index_length) / 1024 / 1024), 2) AS 'Size (MB)'
FROM information_schema.TABLES 
WHERE table_schema = 'your_database_name'
ORDER BY (data_length + index_length) DESC;
```

#### 🔍 Query Performance
```sql
-- Enable slow query log
SET GLOBAL slow_query_log = 'ON';
SET GLOBAL long_query_time = 2;

-- Check slow queries
SHOW VARIABLES LIKE 'slow_query_log%';
```

### 8. Backup & Recovery

#### 📅 Automated Backup Script
```bash
#!/bin/bash
# backup-database.sh

DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="/path/to/backups"
DB_NAME="supply_system_prod"
DB_USER="username"
DB_PASS="password"
DB_HOST="hostname"

# Create backup
mysqldump -h $DB_HOST -u $DB_USER -p$DB_PASS $DB_NAME > $BACKUP_DIR/backup_$DATE.sql

# Compress backup
gzip $BACKUP_DIR/backup_$DATE.sql

# Keep only last 7 days of backups
find $BACKUP_DIR -name "backup_*.sql.gz" -mtime +7 -delete

echo "Backup completed: backup_$DATE.sql.gz"
```

#### 🔄 Recovery Process
```bash
# Restore from backup
gunzip backup_20240101_120000.sql.gz
mysql -h hostname -u username -p database_name < backup_20240101_120000.sql
```

---

## ✅ Database Setup Checklist

- [ ] Database service provisioned on hosting platform
- [ ] Environment variables configured correctly
- [ ] Database connection tested successfully
- [ ] Migrations run without errors
- [ ] Seeders executed successfully
- [ ] Database indexes optimized
- [ ] Backup strategy implemented
- [ ] Monitoring setup configured

**🎉 Your database is now ready for production use!**