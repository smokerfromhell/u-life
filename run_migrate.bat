@echo off
cd /d c:\laragon\www\ulife
php artisan migrate:fresh --seed
pause
