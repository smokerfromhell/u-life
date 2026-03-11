@echo off
cd /d %~dp0
php artisan migrate:fresh --seed
pause
