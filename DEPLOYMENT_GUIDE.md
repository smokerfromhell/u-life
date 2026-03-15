# InfinityFree Deployment Guide

## Part 1: Export Database from Laragon

### Step 1.1: Open phpMyAdmin in Laragon
1. Right-click Laragon tray icon → **Menu** → **MySQL** → **phpMyAdmin**
2. Browser will open to `http://localhost/phpmyadmin`

### Step 1.2: Export Your Database
1. In phpMyAdmin, click **Databases** in the top menu
2. Click on your `laravel` database name
3. Click **Export** tab in the top menu
4. Click **Go** button (bottom right)
5. File will download as `laravel.sql`

---

## Part 2: Create InfinityFree Database

### Step 2.1: Login to InfinityFree
1. Go to `ifastnet.com` → Login
2. Click **Control Panel** → **MySQL Databases**

### Step 2.2: Create New Database
1. Under "Create New Database":
   - Database Name: `yourname_ulife` (or similar)
   - Database Username: `yourname_admin`
   - Password: Create a strong password
2. Click **Create Database**
3. **Save these details** - you'll need them later:
   - Database Name
   - Username
   - Password
   - MySQL Host (shown on the page, e.g., `sql307.byetcluster.com`)

---

## Part 3: Import Database to InfinityFree

### Step 3.1: Open InfinityFree phpMyAdmin
1. In InfinityFree Control Panel → **phpMyAdmin**
2. Login with your InfinityFree MySQL credentials

### Step 3.2: Import the Database
1. Click on your **new database** name in the left sidebar
2. Click **Import** tab in the top menu
3. Under "File to import":
   - Click **Choose File**
   - Select the `laravel.sql` file you exported
4. Scroll down and click **Go**

---

## Part 4: Update .env Configuration

Edit your `.env` file with InfinityFree credentials:

```env
APP_NAME=Ulife
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

DB_CONNECTION=mysql
DB_HOST=sql307.byetcluster.com    # Your MySQL host from Step 2.2
DB_PORT=3306
DB_DATABASE=yourname_ulife        # Your database name
DB_USERNAME=yourname_admin        # Your database username
DB_PASSWORD=your_password_here     # Your database password

SESSION_DRIVER=file
CACHE_STORE=file
QUEUE_CONNECTION=sync
```

---

## Part 5: Upload Files to InfinityFree

### Step 5.1: Prepare Files
In your project folder, make sure you have:
- All Laravel files EXCEPT `node_modules` and `.git`
- The `vendor` folder (run `composer install --no-dev` if missing)
- The `public/build` folder (run `npm install && npm run build` if missing)

### Step 5.2: Upload via File Manager
1. Go to InfinityFree Control Panel → **File Manager**
2. Navigate to `public_html` folder
3. Click **Upload**
4. Upload all your project files
5. **Important**: Move all files from `public_html/ulife` to `public_html/` if they uploaded to a subfolder

### Step 5.3: Fix Permissions
1. In File Manager, right-click `storage` folder → **Change Permissions** → `755`
2. Right-click `bootstrap/cache` folder → **Change Permissions** → `755`

---

## Part 6: Final Steps

### Clear Config Cache
Since you can't run Artisan commands, create a web-based clear cache file:

Create `clear-cache.php` in public_html:
```php
<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
\Illuminate\Support\Facades\Artisan::call('config:clear');
\Illuminate\Support\Facades\Artisan::call('cache:clear');
echo 'Cache cleared!';
```

Visit `yourdomain.com/clear-cache.php` then DELETE the file.

---

## Troubleshooting

**Error 500 - Internal Server Error:**
- Check `.env` file has correct database credentials
- Ensure `storage` and `bootstrap/cache` have 755 permissions

**Database Connection Error:**
- Verify DB_HOST is correct (check InfinityFree MySQL Databases page)
- Make sure database name, username, password are exact

**White Page:**
- Check `storage/logs/laravel.log` via File Manager for errors
