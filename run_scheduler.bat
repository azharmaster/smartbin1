@echo off
cd /d C:\laragon\www\smartbin1

"C:\laragon\bin\php\php-8.2.30\php.exe" artisan smartbin:simulate >> "C:\laragon\www\smartbin1\storage\logs\scheduler.log" 2>&1

