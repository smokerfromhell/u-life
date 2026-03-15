# Railway Deployment Guide

Railway is much easier for Laravel 12 deployment. Here's how to deploy your app:

## Prerequisites
- Railway account (you have this)
- GitHub account (to connect your repo)

---

## Step 1: Prepare Your Laravel App

### 1.1 Update composer.json for Railway
Make sure your `composer.json` has these scripts:
```json
"scripts": {
    "post-install-cmd": [
        "Illuminate\\Foundation\\ComposerScripts::postInstall",
        "@php artisan key:generate --force",
        "@php artisan config:cache"
    ],
    "post-autoload-dump": [
        "Illuminate\\Foundation\\ComposerScripts::postAutoloadDump",
        "@php artisan package:discover --ansi"
    ]
}
```

### 1.2 Create railway.json
Create a file named `railway.json` in your project root:
```json
{
  "$schema": "https://railway.app/railway.schema.json",
  "build": {
    "builder": "NIXPACKS"
  },
  "deploy": {
    "numReplicas": 1,
    "restartPolicyType": "ON_FAILURE",
    "restartPolicyDelay": 5
  }
}
```

### 1.3 Update .env for Railway
Update your `.env` with Railway environment variables:
```env
APP_NAME=Ulife
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-app-name.up.railway.app

DB_CONNECTION=mysql
DB_HOST=${DB_HOST}
DB_PORT=3306
DB_DATABASE=${DB_NAME}
DB_USERNAME=${DB_USER}
DB_PASSWORD=${DB_PASSWORD}

SESSION_DRIVER=file
CACHE_STORE=file
QUEUE_CONNECTION=sync
```

---

## Step 2: Push to GitHub

### 2.1 Create GitHub Repository
1. Go to github.com → New Repository
2. Name it `ulife` or similar
3. Push your code:
```bash
git init
git add .
git commit -m "Initial commit"
git branch -M main
git remote add origin https://github.com/YOUR_USERNAME/ulife.git
git push -u origin main
```

---

## Step 3: Deploy on Railway

### 3.1 Create New Project
1. Go to [railway.app](https://railway.app) → New Project
2. Select **"Deploy from GitHub repo"**
3. Select your `ulife` repository

### 3.2 Add MySQL Database
1. In your Railway project → Click **"+ New"**
2. Select **MySQL** (free tier available)
3. Wait for it to provision

### 3.3 Configure Environment Variables
1. Go to **Variables** tab in your Railway project
2. Add these variables (Railway auto-fills DB_* variables):
```
APP_KEY=base64:DcJW4keM+GMNOasKQ03OAzJMmFydq9QE2oGTjHAwAz4=
APP_URL=https://your-app-name.up.railway.app
```

### 3.4 Deploy
1. Click **Deploy** button
2. Watch the build logs
3. Once deployed, click the **URL** to view your app

---

## Step 4: Run Migrations

Since Railway has CLI access:
1. Go to your Railway project → **Deployments**
2. Click on the latest deployment
3. Click **Redeploy** (if needed)
4. Or use Railway CLI to run migrations:
```bash
npm i -g @railway/cli
railway login
railway link
railway run php artisan migrate
```

---

## Troubleshooting

**Build Failed:**
- Check that PHP 8.2+ is selected in Railway settings
- Make sure `vendor` folder is NOT in `.gitignore` or run `composer install` on Railway

**Database Connection Error:**
- Ensure MySQL service is added and linked
- Check DB_* environment variables are set

**500 Error:**
- Run `railway run php artisan config:cache`
- Check `storage/logs/laravel.log`
