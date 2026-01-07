@echo off
REM Run Laravel scheduler every minute

"C:\laragon\bin\php\php-8.2.30\php.exe" "C:\laragon\www\smartbin1\artisan" schedule:run >> "C:\laragon\www\smartbin1\storage\logs\scheduler.log" 2>&1
